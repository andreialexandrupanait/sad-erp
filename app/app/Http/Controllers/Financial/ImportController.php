<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;
use App\Models\Client;
use App\Models\SettingOption;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController extends Controller
{
    // ==================== REVENUE IMPORT/EXPORT ====================

    public function showRevenueImportForm()
    {
        return view('financial.revenues.import');
    }

    public function importRevenues(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xls,xlsx|max:5120',
            'download_smartbill_pdfs' => 'nullable|boolean',
            'dry_run' => 'nullable|boolean',
        ]);

        $file = $request->file('csv_file');

        // Detect file type and parse accordingly
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, ['xls', 'xlsx'])) {
            // Parse Excel file
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $csvData = $worksheet->toArray();
        } else {
            // Parse CSV file
            $csvData = array_map('str_getcsv', file($file->getRealPath()));
        }

        // Find the actual header row (Smartbill exports have metadata rows before headers)
        $headerRowIndex = 0;
        $header = null;

        // Look for the row containing recognizable column names
        foreach ($csvData as $index => $row) {
            $row = array_map('trim', $row);
            // Check if this row contains typical Smartbill or invoice column names
            $hasInvoiceColumns = false;
            foreach ($row as $cell) {
                $cell = strtolower($cell);
                if (in_array($cell, ['serie', 'numar', 'data', 'client', 'total', 'cif', 'moneda'])) {
                    $hasInvoiceColumns = true;
                    break;
                }
            }

            if ($hasInvoiceColumns) {
                $headerRowIndex = $index;
                $header = $row;
                break;
            }
        }

        // If no header found, use first row as fallback
        if ($header === null) {
            $header = array_shift($csvData);
            $header = array_map('trim', $header);
        } else {
            // Remove all rows up to and including the header row
            $csvData = array_slice($csvData, $headerRowIndex + 1);
        }

        // Detect if this is a Smartbill export
        $isSmartbillExport = $this->detectSmartbillExport($header);
        $downloadPdfs = $request->boolean('download_smartbill_pdfs', false);
        $dryRun = $request->boolean('dry_run', false);

        // Log headers for debugging if Smartbill export
        if ($isSmartbillExport) {
            \Log::info('Smartbill import detected. Column headers:', ['headers' => $header]);
        }

        if ($dryRun) {
            \Log::info('DRY RUN MODE - No data will be saved');
        }

        $imported = 0;
        $skipped = 0;
        $pdfsDownloaded = 0;
        $errors = [];
        $duplicatesFound = [];

        foreach ($csvData as $index => $row) {
            $rowNumber = $index + 2;

            if (empty(array_filter($row))) continue;

            $data = array_combine($header, $row);

            // Map Smartbill columns to our expected format
            if ($isSmartbillExport) {
                $data = $this->mapSmartbillColumns($data);
                // Log first row mapping for debugging
                if ($index === 0) {
                    \Log::info('Smartbill first row mapped data:', ['data' => $data]);
                }
            }

            // For Smartbill exports, adjust validation
            $validationRules = [
                'document_name' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0',
                'currency' => 'required|in:RON,EUR',
                'occurred_at' => 'required|date',
            ];

            // Validate required fields
            $validator = Validator::make($data, $validationRules);

            if ($validator->fails()) {
                $errorMsg = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                $errors[] = $errorMsg;
                // Log first few validation errors for debugging
                if ($skipped < 3) {
                    \Log::warning('Smartbill import validation failed', [
                        'row' => $rowNumber,
                        'data' => $data,
                        'errors' => $validator->errors()->all()
                    ]);
                }
                $skipped++;
                continue;
            }

            try {
                // Find or create client - prioritize CIF match for Smartbill imports
                $clientId = null;
                $client = null;

                // For Smartbill imports, try to match by CIF first
                if ($isSmartbillExport && !empty($data['cif_client'] ?? $data['CIF'] ?? '')) {
                    $cif = trim($data['cif_client'] ?? $data['CIF']);
                    $clientName = trim($data['client_name'] ?? $data['client'] ?? '');

                    // Clean CIF: remove 'RO' prefix and whitespace
                    $cleanCif = preg_replace('/^RO/i', '', $cif);
                    $cleanCif = preg_replace('/\s+/', '', $cleanCif);

                    // Try exact match or with RO prefix
                    $client = Client::where(function($query) use ($cif, $cleanCif) {
                        $query->where('tax_id', $cif)
                              ->orWhere('tax_id', $cleanCif)
                              ->orWhere('tax_id', 'RO' . $cleanCif);
                    })->first();

                    // If client exists, check if it's a placeholder and update it
                    if ($client && !empty($clientName)) {
                        $isPlaceholder = str_starts_with($client->name, 'Client CIF') ||
                                       str_contains($client->notes ?? '', 'Auto-created from Smartbill import');

                        if ($isPlaceholder && $client->name !== $clientName) {
                            if ($dryRun) {
                                \Log::info("DRY RUN: Would update placeholder client", [
                                    'client_id' => $client->id,
                                    'old_name' => $client->name,
                                    'new_name' => $clientName,
                                    'cif' => $cif
                                ]);
                            } else {
                                try {
                                    $formattedName = $this->formatClientName($clientName);
                                    $client->update([
                                        'name' => $formattedName,
                                        'company_name' => $formattedName,
                                        'notes' => 'Updated with real name from Smartbill import on ' . now()->format('Y-m-d H:i'),
                                    ]);
                                    \Log::info("Updated placeholder client from Smartbill import", [
                                        'client_id' => $client->id,
                                        'old_name' => $client->name,
                                        'new_name' => $clientName,
                                        'cif' => $cif
                                    ]);
                                } catch (\Exception $e) {
                                    \Log::warning("Failed to update placeholder client", [
                                        'client_id' => $client->id,
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            }
                        }
                    }

                    // If no client found by CIF, create new client automatically
                    if (!$client && !empty($clientName)) {
                        if ($dryRun) {
                            \Log::info("DRY RUN: Would create new client", [
                                'name' => $clientName,
                                'cif' => $cif
                            ]);
                        } else {
                            try {
                                $formattedName = $this->formatClientName($clientName);
                                $client = Client::create([
                                    'name' => $formattedName,
                                    'company_name' => $formattedName,
                                    'tax_id' => $cif,
                                    'notes' => 'Auto-created from Smartbill import on ' . now()->format('Y-m-d H:i'),
                                ]);
                                \Log::info("Auto-created client from Smartbill import", [
                                    'client_id' => $client->id,
                                    'name' => $clientName,
                                    'cif' => $cif
                                ]);
                            } catch (\Exception $e) {
                                \Log::warning("Failed to auto-create client from Smartbill import", [
                                    'name' => $clientName,
                                    'cif' => $cif,
                                    'error' => $e->getMessage()
                                ]);
                                // Continue without client if creation fails
                                $client = null;
                            }
                        }
                    }

                    $clientId = $client?->id;
                }

                // Fallback: try to match by name if CIF match didn't work (for non-Smartbill imports)
                if (!$clientId && !empty($data['client_name'] ?? $data['client'] ?? '')) {
                    $clientName = trim($data['client_name'] ?? $data['client']);
                    $client = Client::where('name', 'like', "%{$clientName}%")->first();
                    $clientId = $client?->id;
                }

                $occurredAt = Carbon::parse($data['occurred_at']);

                $revenueData = [
                    'document_name' => trim($data['document_name']),
                    'amount' => (float) $data['amount'],
                    'currency' => strtoupper(trim($data['currency'])),
                    'occurred_at' => $occurredAt,
                    'year' => $occurredAt->year,
                    'month' => $occurredAt->month,
                    'client_id' => $clientId,
                    'note' => trim($data['note'] ?? ''),
                ];

                // Add Smartbill-specific fields if this is a Smartbill export
                if ($isSmartbillExport) {
                    $revenueData['smartbill_series'] = trim($data['serie'] ?? $data['Serie'] ?? '');
                    $revenueData['smartbill_invoice_number'] = trim($data['numar'] ?? $data['Numar'] ?? '');
                    $revenueData['smartbill_client_cif'] = trim($data['cif_client'] ?? $data['CIF'] ?? '');
                    $revenueData['smartbill_imported_at'] = now();
                }

                // Check for duplicates before creating
                $existingRevenue = null;

                // For Smartbill imports, check by series + invoice number
                if ($isSmartbillExport && !empty($revenueData['smartbill_series']) && !empty($revenueData['smartbill_invoice_number'])) {
                    $existingRevenue = FinancialRevenue::where('smartbill_series', $revenueData['smartbill_series'])
                        ->where('smartbill_invoice_number', $revenueData['smartbill_invoice_number'])
                        ->first();
                }
                // For regular imports, check by document name + occurred_at date
                elseif (!empty($revenueData['document_name']) && !empty($revenueData['occurred_at'])) {
                    $existingRevenue = FinancialRevenue::where('document_name', $revenueData['document_name'])
                        ->whereDate('occurred_at', $revenueData['occurred_at'])
                        ->first();
                }

                // If duplicate found, update client link if needed and skip
                if ($existingRevenue) {
                    // Track duplicate
                    $invoiceRef = $revenueData['smartbill_series'] ?? $revenueData['document_name'];
                    $invoiceNum = $revenueData['smartbill_invoice_number'] ?? '';
                    $duplicatesFound[] = [
                        'invoice' => $invoiceRef . ($invoiceNum ? "-{$invoiceNum}" : ''),
                        'date' => $revenueData['occurred_at']->format('Y-m-d'),
                        'amount' => $revenueData['amount'],
                    ];

                    // Update client link if it changed (e.g., placeholder was updated)
                    if ($existingRevenue->client_id !== $clientId && $clientId) {
                        if ($dryRun) {
                            \Log::info("DRY RUN: Would update client link for existing revenue", [
                                'revenue_id' => $existingRevenue->id,
                                'old_client_id' => $existingRevenue->client_id,
                                'new_client_id' => $clientId,
                            ]);
                        } else {
                            $existingRevenue->update(['client_id' => $clientId]);
                            \Log::info("Updated client link for existing revenue", [
                                'revenue_id' => $existingRevenue->id,
                                'old_client_id' => $existingRevenue->client_id,
                                'new_client_id' => $clientId,
                                'smartbill_series' => $revenueData['smartbill_series'] ?? null,
                                'smartbill_invoice_number' => $revenueData['smartbill_invoice_number'] ?? null,
                            ]);
                        }
                    }
                    $skipped++;
                    continue; // Skip creating duplicate
                }

                // Create revenue (or skip in dry-run mode)
                if ($dryRun) {
                    \Log::info("DRY RUN: Would create revenue", $revenueData);
                } else {
                    $revenue = FinancialRevenue::create($revenueData);
                }
                $imported++;

                // Download PDF from Smartbill if requested
                if ($isSmartbillExport && $downloadPdfs && !empty($revenueData['smartbill_series']) && !empty($revenueData['smartbill_invoice_number'])) {
                    try {
                        $pdfDownloaded = $this->downloadSmartbillPdf(
                            $revenue,
                            $revenueData['smartbill_series'],
                            $revenueData['smartbill_invoice_number']
                        );
                        if ($pdfDownloaded) {
                            $pdfsDownloaded++;
                        }
                    } catch (\Exception $e) {
                        // Log but don't fail the import
                        \Log::warning("Failed to download PDF for invoice {$revenueData['smartbill_series']}-{$revenueData['smartbill_invoice_number']}: " . $e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                $skipped++;
            }
        }

        $message = "Import completed: {$imported} revenues imported, {$skipped} skipped";
        if ($pdfsDownloaded > 0) {
            $message .= ", {$pdfsDownloaded} PDFs downloaded from Smartbill";
        }

        if ($dryRun) {
            $message = "DRY RUN: Would import {$imported} revenues, {$skipped} duplicates would be skipped";
        }

        $flashData = [
            'success' => $message,
            'import_errors' => $errors,
        ];

        if (!empty($duplicatesFound)) {
            $flashData['duplicates_found'] = $duplicatesFound;
        }

        return redirect()
            ->route('financial.revenues.index')
            ->with($flashData);
    }

    /**
     * Detect if CSV is a Smartbill export
     */
    protected function detectSmartbillExport($header)
    {
        // Check for both invoice exports and payment report exports
        $smartbillColumns = [
            'serie', 'Serie',
            'numar', 'Numar',
            'cif_client', 'CIF',
            'Factura',  // Payment reports
            'Data incasarii',  // Payment reports
        ];

        foreach ($smartbillColumns as $col) {
            if (in_array($col, $header)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Map Smartbill column names to our expected format
     */
    protected function mapSmartbillColumns($data)
    {
        $mapped = [];

        // Common Smartbill export column mappings (case-insensitive)
        $columnMap = [
            // Document/Invoice info
            'Serie' => 'serie',
            'Numar' => 'numar',
            'Numar document' => 'numar',
            'Factura' => 'document_name',  // For payment reports

            // Date fields
            'Data' => 'occurred_at',
            'Data emitere' => 'occurred_at',
            'Data factura' => 'occurred_at',
            'Data incasarii' => 'occurred_at',  // For payment reports
            'Data scadenta' => 'due_date',

            // Amount fields
            'Total' => 'amount',
            'Total factura' => 'amount',
            'Suma' => 'amount',
            'Valoare' => 'amount',
            'Valoare totala' => 'amount',
            'Valoare Totala' => 'amount',  // Capitalized version

            // Currency
            'Moneda' => 'currency',
            'Valuta' => 'currency',

            // Client info
            'Client' => 'client_name',
            'Nume client' => 'client_name',
            'Partener' => 'client_name',
            'CIF' => 'cif_client',
            'CIF client' => 'cif_client',
            'CUI' => 'cif_client',

            // Notes/Observations
            'Observatii' => 'note',
            'Mentiuni' => 'note',
            'Nota' => 'note',
        ];

        // First pass: map known columns
        foreach ($data as $key => $value) {
            $mappedKey = $columnMap[$key] ?? null;

            if ($mappedKey) {
                $mapped[$mappedKey] = $value;
            } else {
                // Keep original key for unmapped columns
                $mapped[$key] = $value;
            }
        }

        // Extract serie and numar from document_name if it contains them (e.g., "SAD0425")
        if (!empty($mapped['document_name']) && empty($mapped['serie']) && empty($mapped['numar'])) {
            $docName = trim($mapped['document_name']);
            // Try to split series (letters) from number (digits)
            if (preg_match('/^([A-Z]+)(\d+)$/i', $docName, $matches)) {
                $mapped['serie'] = $matches[1];
                $mapped['numar'] = $matches[2];
            }
        }

        // Create document_name from Serie + Numar if not present
        if (empty($mapped['document_name']) && !empty($mapped['serie']) && !empty($mapped['numar'])) {
            $mapped['document_name'] = trim($mapped['serie']) . '-' . trim($mapped['numar']);
        }

        // Set default currency if not present
        if (empty($mapped['currency'])) {
            $mapped['currency'] = 'RON';
        }

        // Convert date format from DD/MM/YYYY to YYYY-MM-DD if needed
        if (!empty($mapped['occurred_at'])) {
            $dateStr = trim($mapped['occurred_at']);
            // Check if it's in DD/MM/YYYY format
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $dateStr, $matches)) {
                // Convert to YYYY-MM-DD
                $mapped['occurred_at'] = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
            }
        }

        return $mapped;
    }

    /**
     * Download PDF from Smartbill for an invoice
     */
    protected function downloadSmartbillPdf($revenue, $series, $number)
    {
        $organization = auth()->user()->organization;
        $smartbillSettings = $organization->settings['smartbill'] ?? [];

        if (empty($smartbillSettings['username']) || empty($smartbillSettings['token']) || empty($smartbillSettings['cif'])) {
            return false;
        }

        $smartbillService = new \App\Services\SmartbillService(
            $smartbillSettings['username'],
            $smartbillSettings['token'],
            $smartbillSettings['cif']
        );

        $pdfContent = $smartbillService->downloadInvoicePdf($series, $number);

        if (!$pdfContent) {
            return false;
        }

        // Store PDF in the financial disk with proper directory structure
        // Use naming convention: "Factura SAD0XXX.pdf" (matching existing files)
        $invoiceNumber = str_pad($number, 4, '0', STR_PAD_LEFT); // Pad to 4 digits
        $filename = "Factura {$series}{$invoiceNumber}.pdf";

        // Determine year/month from revenue
        $year = $revenue->occurred_at->year;
        $month = $revenue->occurred_at->month;
        $monthName = $this->getRomanianMonthName($month);

        // Store in same structure as regular revenue files: /year/MonthName/Incasari/filename
        $path = "{$year}/{$monthName}/Incasari/{$filename}";

        \Storage::disk('financial')->put($path, $pdfContent);

        // Create file record
        \App\Models\FinancialFile::create([
            'entity_type' => \App\Models\FinancialRevenue::class,
            'entity_id' => $revenue->id,
            'organization_id' => $organization->id,
            'file_name' => $filename,
            'file_path' => $path,
            'file_type' => 'application/pdf',
            'file_size' => strlen($pdfContent),
            'uploaded_by' => auth()->id(),
        ]);

        return true;
    }

    public function downloadRevenueTemplate()
    {
        $filename = 'revenue_import_template_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, [
                'document_name',
                'amount',
                'currency',
                'occurred_at',
                'client_name',
                'note'
            ]);

            // Example row
            fputcsv($file, [
                'Factura #2025001',
                '1500.00',
                'RON',
                date('Y-m-d'),
                'Example Client SRL',
                'Monthly retainer fee'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportRevenues(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month');
        $currency = $request->get('currency');
        $clientId = $request->get('client_id');

        $revenues = FinancialRevenue::with('client')
            ->forYear($year)
            ->when($month, fn($q) => $q->where('month', $month))
            ->when($currency, fn($q) => $q->where('currency', $currency))
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->orderBy('occurred_at')
            ->get();

        $filename = 'revenues_export_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($revenues) {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, [
                'document_name',
                'amount',
                'currency',
                'occurred_at',
                'client_name',
                'client_tax_id',
                'note',
                'year',
                'month'
            ]);

            foreach ($revenues as $revenue) {
                fputcsv($file, [
                    $revenue->document_name,
                    $revenue->amount,
                    $revenue->currency,
                    $revenue->occurred_at->format('Y-m-d'),
                    $revenue->client?->name ?? '',
                    $revenue->client?->tax_id ?? '',
                    $revenue->note ?? '',
                    $revenue->year,
                    $revenue->month,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ==================== EXPENSE IMPORT/EXPORT ====================

    public function showExpenseImportForm()
    {
        return view('financial.expenses.import');
    }

    public function importExpenses(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $csvData = array_map('str_getcsv', file($file->getRealPath()));

        // Get header row
        $header = array_shift($csvData);
        $header = array_map('trim', $header);

        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($csvData as $index => $row) {
            $rowNumber = $index + 2;

            if (empty(array_filter($row))) continue;

            $data = array_combine($header, $row);

            // Validate required fields
            $validator = Validator::make($data, [
                'document_name' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0',
                'currency' => 'required|in:RON,EUR',
                'occurred_at' => 'required|date',
            ]);

            if ($validator->fails()) {
                $errors[] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                $skipped++;
                continue;
            }

            try {
                // Find category by label if provided
                $categoryId = null;
                if (!empty($data['category'] ?? '')) {
                    $categoryLabel = trim($data['category']);
                    $category = SettingOption::active()->ordered()
                        ->where('name', 'like', "%{$categoryLabel}%")
                        ->first();
                    $categoryId = $category?->id;
                }

                $occurredAt = Carbon::parse($data['occurred_at']);

                $expenseData = [
                    'document_name' => trim($data['document_name']),
                    'amount' => (float) $data['amount'],
                    'currency' => strtoupper(trim($data['currency'])),
                    'occurred_at' => $occurredAt,
                    'year' => $occurredAt->year,
                    'month' => $occurredAt->month,
                    'category_option_id' => $categoryId,
                    'note' => trim($data['note'] ?? ''),
                ];

                FinancialExpense::create($expenseData);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                $skipped++;
            }
        }

        return redirect()
            ->route('financial.cheltuieli.index')
            ->with('success', "Import completed: {$imported} expenses imported, {$skipped} skipped")
            ->with('import_errors', $errors);
    }

    public function downloadExpenseTemplate()
    {
        $filename = 'expense_import_template_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, [
                'document_name',
                'amount',
                'currency',
                'occurred_at',
                'category',
                'note'
            ]);

            // Example row
            fputcsv($file, [
                'Factura Hosting #12345',
                '250.00',
                'RON',
                date('Y-m-d'),
                'Cloud & Hosting',
                'Monthly server hosting'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportExpenses(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month');
        $currency = $request->get('currency');
        $categoryId = $request->get('category_id');

        $expenses = FinancialExpense::with('category')
            ->forYear($year)
            ->when($month, fn($q) => $q->where('month', $month))
            ->when($currency, fn($q) => $q->where('currency', $currency))
            ->when($categoryId, fn($q) => $q->where('category_option_id', $categoryId))
            ->orderBy('occurred_at')
            ->get();

        $filename = 'expenses_export_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($expenses) {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, [
                'document_name',
                'amount',
                'currency',
                'occurred_at',
                'category',
                'note',
                'year',
                'month'
            ]);

            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->document_name,
                    $expense->amount,
                    $expense->currency,
                    $expense->occurred_at->format('Y-m-d'),
                    $expense->category?->name ?? '',
                    $expense->note ?? '',
                    $expense->year,
                    $expense->month,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get Romanian month name from month number
     */
    protected function getRomanianMonthName($monthNumber)
    {
        $months = [
            1 => 'Ianuarie',
            2 => 'Februarie',
            3 => 'Martie',
            4 => 'Aprilie',
            5 => 'Mai',
            6 => 'Iunie',
            7 => 'Iulie',
            8 => 'August',
            9 => 'Septembrie',
            10 => 'Octombrie',
            11 => 'Noiembrie',
            12 => 'Decembrie',
        ];

        return $months[$monthNumber] ?? 'Unknown';
    }

    /**
     * Format client name to Title Case
     * Converts "ASOCIATIA ROMANA" to "Asociatia Romana"
     */
    protected function formatClientName($name)
    {
        if (empty($name)) {
            return $name;
        }

        // Convert to Title Case using multibyte string function
        return mb_convert_case(trim($name), MB_CASE_TITLE, 'UTF-8');
    }
}
