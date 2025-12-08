<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\SafeJsonResponse;
use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;
use App\Models\Client;
use App\Models\SettingOption;
use App\Models\SmartbillImport;
use App\Jobs\ImportSmartbillInvoicesJob;
use App\Services\Financial\RevenueImportService;
use App\Services\Financial\ExpenseImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController extends Controller
{
    use SafeJsonResponse;

    protected RevenueImportService $revenueImportService;
    protected ExpenseImportService $expenseImportService;

    public function __construct(
        RevenueImportService $revenueImportService,
        ExpenseImportService $expenseImportService
    ) {
        $this->revenueImportService = $revenueImportService;
        $this->expenseImportService = $expenseImportService;
    }
    // ==================== REVENUE IMPORT/EXPORT ====================

    public function showRevenueImportForm()
    {
        // Authorization check
        $this->authorize('import', FinancialRevenue::class);

        // Get recent imports for the current organization
        $recentImports = SmartbillImport::where('organization_id', auth()->user()->organization_id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('financial.revenues.import', compact('recentImports'));
    }

    /**
     * Preview import before processing
     */
    public function previewRevenues(Request $request)
    {
        // Authorization check
        $this->authorize('import', FinancialRevenue::class);

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xls,xlsx|max:5120',
        ]);

        try {
            $file = $request->file('csv_file');
            $extension = strtolower($file->getClientOriginalExtension());

            // Parse file using service
            $csvData = $this->revenueImportService->parseFile($file->getRealPath(), $extension);

            // Get preview data using service
            $previewData = $this->revenueImportService->preview($csvData);

            return response()->json([
                'success' => true,
                'is_smartbill' => $previewData['is_smartbill'],
                'summary' => $previewData['summary'],
                'preview_rows' => $previewData['preview_rows'],
                'has_more' => $previewData['has_more'],
            ]);
        } catch (\Exception $e) {
            return $this->safeJsonError($e, 'Revenue preview');
        }
    }

    public function importRevenues(Request $request)
    {
        // Authorization check
        $this->authorize('import', FinancialRevenue::class);

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

            // Convert to CSV format for storage
            $csvContent = '';
            foreach ($csvData as $row) {
                $csvContent .= implode(',', array_map(function($cell) {
                    return '"' . str_replace('"', '""', $cell ?? '') . '"';
                }, $row)) . "\n";
            }
        } else {
            // Read CSV file content directly
            $csvContent = file_get_contents($file->getRealPath());
            $csvData = array_map('str_getcsv', explode("\n", $csvContent));
        }

        // Count rows (excluding empty ones and header)
        $totalRows = count(array_filter($csvData, function($row) {
            return !empty(array_filter($row));
        }));

        // For small files (< 50 rows) without PDF download, process synchronously
        $downloadPdfs = $request->boolean('download_smartbill_pdfs', false);
        $dryRun = $request->boolean('dry_run', false);

        if ($totalRows < 50 && !$downloadPdfs) {
            return $this->importRevenuesSynchronously($request, $csvData);
        }

        // For larger files or when downloading PDFs, use background job
        $organization = auth()->user()->organization;
        $userId = auth()->id();

        // Store file temporarily
        $tempFileName = 'imports/smartbill_' . uniqid() . '.csv';
        Storage::disk('local')->put($tempFileName, $csvContent);

        // Create import record
        $import = SmartbillImport::create([
            'organization_id' => $organization->id,
            'user_id' => $userId,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $tempFileName,
            'status' => 'pending',
            'options' => [
                'download_pdfs' => $downloadPdfs,
                'dry_run' => $dryRun,
            ],
            'total_rows' => $totalRows - 1, // Subtract header row
            'processed_rows' => 0,
        ]);

        // Dispatch background job
        ImportSmartbillInvoicesJob::dispatch($import->id, $organization->id, $userId);

        return redirect()
            ->route('financial.revenues.import')
            ->with('success', "Import started! Processing {$totalRows} rows in the background. You can track progress below.");
    }

    /**
     * Process small imports synchronously using the service
     */
    protected function importRevenuesSynchronously(Request $request, array $csvData)
    {
        $downloadPdfs = $request->boolean('download_smartbill_pdfs', false);
        $dryRun = $request->boolean('dry_run', false);

        // Get Smartbill settings if PDF download is requested
        $smartbillSettings = null;
        if ($downloadPdfs) {
            $organization = auth()->user()->organization;
            $smartbillSettings = $organization->settings['smartbill'] ?? null;
        }

        // Use the service for import
        $stats = $this->revenueImportService->import(
            $csvData,
            auth()->user()->organization_id,
            auth()->id(),
            $downloadPdfs,
            $dryRun,
            $smartbillSettings
        );

        $message = "Import completed: {$stats['imported']} revenues imported, {$stats['skipped']} skipped";
        if ($stats['pdfs_downloaded'] > 0) {
            $message .= ", {$stats['pdfs_downloaded']} PDFs downloaded from Smartbill";
        }

        if ($dryRun) {
            $message = "DRY RUN: Would import {$stats['imported']} revenues, {$stats['duplicates']} duplicates would be skipped";
        }

        $flashData = [
            'success' => $message,
            'import_errors' => $stats['errors'],
        ];

        if (!empty($stats['duplicates_found'])) {
            $flashData['duplicates_found'] = $stats['duplicates_found'];
        }

        return redirect()
            ->route('financial.revenues.index')
            ->with($flashData);
    }

    /**
     * Detect if CSV is a Smartbill export
     * @deprecated Use RevenueImportService::detectSmartbillExport() instead
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
        $monthName = romanian_month($month);

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
        // Authorization check
        $this->authorize('export', FinancialRevenue::class);

        $year = $request->get('year', now()->year);
        $month = $request->get('month');
        $currency = $request->get('currency');
        $clientId = $request->get('client_id');

        // Filter by organization for security
        $revenues = FinancialRevenue::with('client')
            ->where('organization_id', auth()->user()->organization_id)
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
        // Authorization check
        $this->authorize('import', FinancialExpense::class);

        return view('financial.expenses.import');
    }

    public function importExpenses(Request $request)
    {
        // Authorization check
        $this->authorize('import', FinancialExpense::class);

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        try {
            $file = $request->file('csv_file');
            $csvData = $this->expenseImportService->parseFile($file->getRealPath());

            // Use the service for import (fixes N+1 query on category lookup)
            $stats = $this->expenseImportService->import(
                $csvData,
                auth()->user()->organization_id,
                auth()->id()
            );

            return redirect()
                ->route('financial.cheltuieli.index')
                ->with('success', "Import completed: {$stats['imported']} expenses imported, {$stats['skipped']} skipped")
                ->with('import_errors', $stats['errors']);
        } catch (\Exception $e) {
            return redirect()
                ->route('financial.expenses.import')
                ->with('error', 'Import failed. Please check your file format.');
        }
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
        // Authorization check
        $this->authorize('export', FinancialExpense::class);

        $year = $request->get('year', now()->year);
        $month = $request->get('month');
        $currency = $request->get('currency');
        $categoryId = $request->get('category_id');

        // Filter by organization for security
        $expenses = FinancialExpense::with('category')
            ->where('organization_id', auth()->user()->organization_id)
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

    // ==================== IMPORT STATUS MANAGEMENT ====================

    /**
     * Get import status (for polling)
     */
    public function getImportStatus($importId)
    {
        $import = SmartbillImport::find($importId);

        if (!$import) {
            return response()->json([
                'success' => false,
                'message' => 'Import not found'
            ], 404);
        }

        // Check authorization
        if ($import->organization_id !== auth()->user()->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'import' => [
                'id' => $import->id,
                'status' => $import->status,
                'file_name' => $import->file_name,
                'total_rows' => $import->total_rows,
                'processed_rows' => $import->processed_rows,
                'progress_percentage' => $import->progress_percentage,
                'stats' => $import->stats,
                'errors' => $import->errors,
                'started_at' => $import->started_at?->format('Y-m-d H:i:s'),
                'completed_at' => $import->completed_at?->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * Cancel a running import
     */
    public function cancelImport($importId)
    {
        $import = SmartbillImport::find($importId);

        if (!$import) {
            return response()->json([
                'success' => false,
                'message' => 'Import not found'
            ], 404);
        }

        // Check authorization
        if ($import->organization_id !== auth()->user()->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Only running or pending imports can be cancelled
        if (!in_array($import->status, ['running', 'pending'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only running or pending imports can be cancelled'
            ], 400);
        }

        $import->update([
            'status' => 'cancelled',
            'completed_at' => now(),
            'errors' => array_merge($import->errors ?? [], ['Cancelled by user']),
        ]);

        // Clean up the temp file
        if ($import->file_path && Storage::disk('local')->exists($import->file_path)) {
            Storage::disk('local')->delete($import->file_path);
        }

        return response()->json([
            'success' => true,
            'message' => 'Import cancelled successfully'
        ]);
    }

    /**
     * Delete an import record
     */
    public function deleteImport($importId)
    {
        $import = SmartbillImport::find($importId);

        if (!$import) {
            return response()->json([
                'success' => false,
                'message' => 'Import not found'
            ], 404);
        }

        // Check authorization
        if ($import->organization_id !== auth()->user()->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Don't delete running imports - cancel them first
        if ($import->status === 'running') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete a running import. Cancel it first.'
            ], 400);
        }

        // Clean up the temp file
        if ($import->file_path && Storage::disk('local')->exists($import->file_path)) {
            Storage::disk('local')->delete($import->file_path);
        }

        $import->delete();

        return response()->json([
            'success' => true,
            'message' => 'Import deleted successfully'
        ]);
    }
}
