<?php

namespace App\Services\Financial;

use App\Models\Client;
use App\Models\FinancialFile;
use App\Models\FinancialRevenue;
use App\Services\SmartbillService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Service for handling revenue imports from CSV/Excel files.
 *
 * Supports both regular CSV imports and Smartbill exports with
 * automatic column mapping, client matching/creation, and PDF download.
 */
class RevenueImportService
{
    /**
     * Pre-loaded clients indexed by CIF for fast lookup.
     */
    protected array $clientsByCif = [];

    /**
     * Pre-loaded clients indexed by name for fallback lookup.
     */
    protected Collection $clientsByName;

    /**
     * Import statistics.
     */
    protected array $stats = [
        'imported' => 0,
        'skipped' => 0,
        'duplicates' => 0,
        'clients_created' => 0,
        'clients_updated' => 0,
        'pdfs_downloaded' => 0,
        'errors' => [],
    ];

    /**
     * Organization ID for the current import.
     */
    protected int $organizationId;

    /**
     * User ID for the current import.
     */
    protected int $userId;

    /**
     * Progress callback function.
     */
    protected ?\Closure $progressCallback = null;

    /**
     * Parse a file (CSV or Excel) into array data.
     *
     * @param string $filePath Path to the file
     * @param string $extension File extension (csv, txt, xls, xlsx)
     * @return array Parsed data rows
     */
    public function parseFile(string $filePath, string $extension): array
    {
        if (in_array(strtolower($extension), ['xls', 'xlsx'])) {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            return $worksheet->toArray();
        }

        $csvContent = file_get_contents($filePath);
        return array_map('str_getcsv', explode("\n", $csvContent));
    }

    /**
     * Find header row in CSV data.
     *
     * Smartbill exports have metadata rows before headers, so we need
     * to search for the row containing recognizable column names.
     *
     * @param array $csvData Raw CSV data
     * @return array [headerRowIndex, header]
     */
    public function findHeaderRow(array $csvData): array
    {
        $recognizedColumns = ['serie', 'numar', 'data', 'client', 'total', 'cif', 'moneda'];

        foreach ($csvData as $index => $row) {
            $row = array_map('trim', $row);
            foreach ($row as $cell) {
                if (in_array(strtolower($cell), $recognizedColumns)) {
                    return [$index, $row];
                }
            }
        }

        // No header found, use first row
        $header = array_shift($csvData);
        return [0, array_map('trim', $header ?? [])];
    }

    /**
     * Detect if the data is from a Smartbill export.
     *
     * @param array $header Column headers
     * @return bool
     */
    public function detectSmartbillExport(array $header): bool
    {
        $smartbillColumns = [
            'serie', 'Serie',
            'numar', 'Numar',
            'cif_client', 'CIF',
            'Factura',
            'Data incasarii',
        ];

        foreach ($smartbillColumns as $col) {
            if (in_array($col, $header)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Map Smartbill column names to our expected format.
     *
     * @param array $data Row data with Smartbill column names
     * @return array Mapped data
     */
    public function mapSmartbillColumns(array $data): array
    {
        $columnMap = [
            // Document/Invoice info
            'Serie' => 'serie',
            'Numar' => 'numar',
            'Numar document' => 'numar',
            'Factura' => 'document_name',

            // Date fields
            'Data' => 'occurred_at',
            'Data emitere' => 'occurred_at',
            'Data factura' => 'occurred_at',
            'Data incasarii' => 'occurred_at',
            'Data scadenta' => 'due_date',

            // Amount fields
            'Total' => 'amount',
            'Total factura' => 'amount',
            'Suma' => 'amount',
            'Valoare' => 'amount',
            'Valoare totala' => 'amount',
            'Valoare Totala' => 'amount',

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

            // Client address
            'Adresa' => 'client_address',
            'Adresa client' => 'client_address',
            'Adresa Client' => 'client_address',

            // Client contact person
            'Persoana contact' => 'client_contact',
            'Persoana de contact' => 'client_contact',
            'Contact' => 'client_contact',

            // Notes
            'Observatii' => 'note',
            'Mentiuni' => 'note',
            'Nota' => 'note',
        ];

        $mapped = [];

        foreach ($data as $key => $value) {
            $mappedKey = $columnMap[$key] ?? null;
            $mapped[$mappedKey ?? $key] = $value;
        }

        // Extract serie and numar from document_name if needed
        if (!empty($mapped['document_name']) && empty($mapped['serie']) && empty($mapped['numar'])) {
            $docName = trim($mapped['document_name']);
            if (preg_match('/^([A-Z]+)(\d+)$/i', $docName, $matches)) {
                $mapped['serie'] = $matches[1];
                $mapped['numar'] = $matches[2];
            }
        }

        // Create document_name from Serie + Numar if not present
        if (empty($mapped['document_name']) && !empty($mapped['serie']) && !empty($mapped['numar'])) {
            $mapped['document_name'] = trim($mapped['serie']) . '-' . trim($mapped['numar']);
        }

        // Set default currency
        if (empty($mapped['currency'])) {
            $mapped['currency'] = 'RON';
        }

        // Convert DD/MM/YYYY to YYYY-MM-DD
        if (!empty($mapped['occurred_at'])) {
            $dateStr = trim($mapped['occurred_at']);
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $dateStr, $matches)) {
                $mapped['occurred_at'] = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
            }
        }

        return $mapped;
    }

    /**
     * Pre-load all clients for fast lookup during import.
     *
     * This eliminates N+1 queries by loading all clients once and
     * indexing them by CIF variants (with/without RO prefix).
     */
    public function loadClientsIndex(): void
    {
        $allClients = Client::all();
        $this->clientsByCif = [];

        foreach ($allClients as $client) {
            if (!empty($client->tax_id)) {
                $this->clientsByCif[$client->tax_id] = $client;

                $cleanCif = preg_replace('/^RO/i', '', $client->tax_id);
                $cleanCif = preg_replace('/\s+/', '', $cleanCif);

                if ($cleanCif !== $client->tax_id) {
                    $this->clientsByCif[$cleanCif] = $client;
                    $this->clientsByCif['RO' . $cleanCif] = $client;
                }
            }
        }

        $this->clientsByName = $allClients->keyBy(fn($c) => strtolower($c->name));
    }

    /**
     * Find client by CIF.
     *
     * @param string $cif Client CIF/Tax ID
     * @return Client|null
     */
    public function findClientByCif(string $cif): ?Client
    {
        $cleanCif = preg_replace('/^RO/i', '', $cif);
        $cleanCif = preg_replace('/\s+/', '', $cleanCif);

        return $this->clientsByCif[$cif]
            ?? $this->clientsByCif[$cleanCif]
            ?? $this->clientsByCif['RO' . $cleanCif]
            ?? null;
    }

    /**
     * Find client by name (fallback).
     *
     * @param string $name Client name
     * @return Client|null
     */
    public function findClientByName(string $name): ?Client
    {
        return $this->clientsByName[strtolower($name)] ?? null;
    }

    /**
     * Format client name to Title Case.
     *
     * @param string $name Raw name
     * @return string Formatted name
     */
    public function formatClientName(string $name): string
    {
        if (empty($name)) {
            return $name;
        }

        return mb_convert_case(trim($name), MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Find or create client from import data.
     *
     * @param array $data Row data
     * @param bool $dryRun If true, don't actually create/update
     * @return int|null Client ID
     */
    public function findOrCreateClient(array $data, bool $dryRun = false): ?int
    {
        $cif = trim($data['cif_client'] ?? $data['CIF'] ?? '');
        $clientName = trim($data['client_name'] ?? $data['client'] ?? '');
        $clientAddress = trim($data['client_address'] ?? '');
        $clientContact = trim($data['client_contact'] ?? '');

        if (empty($cif) && empty($clientName)) {
            return null;
        }

        // Try CIF match first
        if (!empty($cif)) {
            $client = $this->findClientByCif($cif);

            if ($client) {
                // Check if it's a placeholder that needs updating
                $this->updatePlaceholderClient($client, $clientName, $clientAddress, $clientContact, $dryRun);
                return $client->id;
            }

            // Create new client if we have a name
            if (!empty($clientName)) {
                return $this->createClient($clientName, $cif, $clientAddress, $clientContact, $dryRun);
            }
        }

        // Fallback to name match
        if (!empty($clientName)) {
            $client = $this->findClientByName($clientName);
            if ($client) {
                return $client->id;
            }
        }

        return null;
    }

    /**
     * Update a placeholder client with real name, address, and contact.
     */
    protected function updatePlaceholderClient(Client $client, string $clientName, string $clientAddress, string $clientContact, bool $dryRun): void
    {
        $isPlaceholder = str_starts_with($client->name, 'Client CIF')
            || str_contains($client->notes ?? '', 'Auto-created from Smartbill import');

        // Check if we need to update name (placeholder), address (missing), or contact (missing)
        $needsNameUpdate = $isPlaceholder && !empty($clientName) && $client->name !== $clientName;
        $needsAddressUpdate = !empty($clientAddress) && empty($client->address);
        $needsContactUpdate = !empty($clientContact) && empty($client->contact_person);

        if (!$needsNameUpdate && !$needsAddressUpdate && !$needsContactUpdate) {
            return;
        }

        if ($dryRun) {
            Log::info('DRY RUN: Would update client', [
                'client_id' => $client->id,
                'old_name' => $client->name,
                'new_name' => $needsNameUpdate ? $clientName : '(unchanged)',
                'address' => $needsAddressUpdate ? $clientAddress : '(unchanged)',
                'contact' => $needsContactUpdate ? $clientContact : '(unchanged)',
            ]);
            return;
        }

        try {
            $updateData = [];

            if ($needsNameUpdate) {
                $formattedName = $this->formatClientName($clientName);
                $updateData['name'] = $formattedName;
                $updateData['company_name'] = $formattedName;
                $updateData['notes'] = 'Updated with real name from Smartbill import on ' . now()->format('Y-m-d H:i');
            }

            if ($needsAddressUpdate) {
                $updateData['address'] = $clientAddress;
            }

            if ($needsContactUpdate) {
                $updateData['contact_person'] = $clientContact;
            }

            $client->update($updateData);
            $this->stats['clients_updated']++;

            Log::info('Updated client', [
                'client_id' => $client->id,
                'updates' => array_keys($updateData),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to update client', [
                'client_id' => $client->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create a new client from import data.
     */
    protected function createClient(string $name, string $cif, string $address, string $contact, bool $dryRun): ?int
    {
        if ($dryRun) {
            Log::info('DRY RUN: Would create new client', [
                'name' => $name,
                'cif' => $cif,
                'address' => $address,
                'contact' => $contact,
            ]);
            return null;
        }

        try {
            $formattedName = $this->formatClientName($name);
            $clientData = [
                'name' => $formattedName,
                'company_name' => $formattedName,
                'tax_id' => $cif,
                'notes' => 'Auto-created from Smartbill import on ' . now()->format('Y-m-d H:i'),
            ];

            // Add address if available
            if (!empty($address)) {
                $clientData['address'] = $address;
            }

            // Add contact person if available
            if (!empty($contact)) {
                $clientData['contact_person'] = $contact;
            }

            $client = Client::create($clientData);

            // Add to index for subsequent rows
            $this->clientsByCif[$cif] = $client;
            $cleanCif = preg_replace('/^RO/i', '', $cif);
            $cleanCif = preg_replace('/\s+/', '', $cleanCif);
            $this->clientsByCif[$cleanCif] = $client;
            $this->clientsByCif['RO' . $cleanCif] = $client;
            $this->clientsByName[strtolower($formattedName)] = $client;

            $this->stats['clients_created']++;

            Log::info('Auto-created client', [
                'client_id' => $client->id,
                'name' => $formattedName,
                'cif' => $cif,
                'address' => $address ?: '(none)',
                'contact' => $contact ?: '(none)',
            ]);

            return $client->id;
        } catch (\Exception $e) {
            Log::warning('Failed to create client', [
                'name' => $name,
                'cif' => $cif,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Check if a revenue already exists (duplicate detection).
     *
     * @param array $data Revenue data
     * @param bool $isSmartbill Whether this is a Smartbill import
     * @return FinancialRevenue|null Existing revenue if duplicate
     */
    public function findDuplicate(array $data, bool $isSmartbill): ?FinancialRevenue
    {
        $series = trim($data['serie'] ?? $data['Serie'] ?? '');
        $number = trim($data['numar'] ?? $data['Numar'] ?? '');

        // For Smartbill: check by series + invoice number + date + amount
        // Same invoice can have multiple payments on different dates or amounts
        if ($isSmartbill && !empty($series) && !empty($number)) {
            $query = FinancialRevenue::where('smartbill_series', $series)
                ->where('smartbill_invoice_number', $number);
            
            // Also check date and amount to allow multiple payments for same invoice
            $occurredAt = $data['occurred_at'] ?? null;
            if ($occurredAt) {
                $query->whereDate('occurred_at', $occurredAt);
            }
            
            $amount = $data['amount'] ?? null;
            if ($amount) {
                $query->where('amount', (float) $amount);
            }
            
            return $query->first();
        }

        // For regular imports: check by document name + date
        $documentName = trim($data['document_name'] ?? '');
        $occurredAt = $data['occurred_at'] ?? null;

        if (!empty($documentName) && !empty($occurredAt)) {
            return FinancialRevenue::where('document_name', $documentName)
                ->whereDate('occurred_at', $occurredAt)
                ->first();
        }

        return null;
    }

    /**
     * Validate a revenue row.
     *
     * @param array $data Row data
     * @return array [isValid, errors]
     */
    public function validateRow(array $data): array
    {
        $validator = Validator::make($data, [
            'document_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|in:RON,EUR',
            'occurred_at' => 'required|date',
        ]);

        if ($validator->fails()) {
            return [false, $validator->errors()->all()];
        }

        return [true, []];
    }

    /**
     * Process a single revenue row.
     *
     * @param array $data Row data
     * @param bool $isSmartbill Whether this is a Smartbill import
     * @param bool $dryRun Whether to skip actual database operations
     * @return FinancialRevenue|null Created revenue or null
     */
    public function processRow(array $data, bool $isSmartbill, bool $dryRun = false): ?FinancialRevenue
    {
        // Find or create client
        $clientId = null;
        if ($isSmartbill) {
            $clientId = $this->findOrCreateClient($data, $dryRun);
        } else {
            $clientName = trim($data['client_name'] ?? $data['client'] ?? '');
            if (!empty($clientName)) {
                $client = $this->findClientByName($clientName);
                $clientId = $client?->id;
            }
        }

        $occurredAt = Carbon::parse($data['occurred_at']);

        $revenueData = [
            'organization_id' => $this->organizationId,
            'user_id' => $this->userId,
            'document_name' => trim($data['document_name']),
            'amount' => (float) $data['amount'],
            'currency' => strtoupper(trim($data['currency'])),
            'occurred_at' => $occurredAt,
            'year' => $occurredAt->year,
            'month' => $occurredAt->month,
            'client_id' => $clientId,
            'note' => trim($data['note'] ?? ''),
        ];

        // Add Smartbill-specific fields
        if ($isSmartbill) {
            $revenueData['smartbill_series'] = trim($data['serie'] ?? $data['Serie'] ?? '');
            $revenueData['smartbill_invoice_number'] = trim($data['numar'] ?? $data['Numar'] ?? '');
            $revenueData['smartbill_client_cif'] = trim($data['cif_client'] ?? $data['CIF'] ?? '');
            $revenueData['smartbill_imported_at'] = now();
        }

        if ($dryRun) {
            Log::info('DRY RUN: Would create revenue', $revenueData);
            return null;
        }

        return FinancialRevenue::create($revenueData);
    }

    /**
     * Import revenues from parsed CSV data.
     *
     * @param array $csvData Parsed CSV rows
     * @param int $organizationId Organization ID
     * @param int $userId User ID
     * @param bool $downloadPdfs Whether to download PDFs for Smartbill imports
     * @param bool $dryRun Whether to skip actual database operations
     * @param array|null $smartbillSettings Smartbill API settings
     * @return array Import statistics
     */
    public function import(
        array $csvData,
        int $organizationId,
        int $userId,
        bool $downloadPdfs = false,
        bool $dryRun = false,
        ?array $smartbillSettings = null,
        ?\Closure $progressCallback = null
    ): array {
        $this->progressCallback = $progressCallback;
        $this->organizationId = $organizationId;
        $this->userId = $userId;
        $this->stats = [
            'imported' => 0,
            'skipped' => 0,
            'duplicates' => 0,
            'clients_created' => 0,
            'clients_updated' => 0,
            'pdfs_downloaded' => 0,
            'errors' => [],
            'duplicates_found' => [],
        ];

        // Find header row
        [$headerRowIndex, $header] = $this->findHeaderRow($csvData);
        $dataRows = array_slice($csvData, $headerRowIndex + 1);

        // Detect Smartbill format
        $isSmartbill = $this->detectSmartbillExport($header);

        if ($isSmartbill) {
            Log::info('Smartbill import detected', ['headers' => $header]);
        }

        if ($dryRun) {
            Log::info('DRY RUN MODE - No data will be saved');
        }

        // Pre-load clients for fast lookup
        $this->loadClientsIndex();

        foreach ($dataRows as $index => $row) {
            $rowNumber = $headerRowIndex + $index + 2;

            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Skip malformed rows
            if (count($row) !== count($header)) {
                continue;
            }

            $data = array_combine($header, $row);

            // Map Smartbill columns
            if ($isSmartbill) {
                $data = $this->mapSmartbillColumns($data);
            }

            // Validate row
            [$isValid, $validationErrors] = $this->validateRow($data);
            if (!$isValid) {
                $this->stats['errors'][] = "Row {$rowNumber}: " . implode(', ', $validationErrors);
                $this->stats['skipped']++;
                continue;
            }

            try {
                // Check for duplicates
                $existingRevenue = $this->findDuplicate($data, $isSmartbill);

                if ($existingRevenue) {
                    $this->stats['duplicates']++;
                    $this->stats['duplicates_found'][] = [
                        'invoice' => ($data['serie'] ?? $data['document_name']) . '-' . ($data['numar'] ?? ''),
                        'date' => $data['occurred_at'],
                        'amount' => $data['amount'],
                    ];

                    // Update client link if needed
                    $newClientId = $this->findOrCreateClient($data, $dryRun);
                    if ($existingRevenue->client_id !== $newClientId && $newClientId && !$dryRun) {
                        $existingRevenue->update(['client_id' => $newClientId]);
                    }

                    $this->stats['skipped']++;
                    continue;
                }

                // Create revenue
                $revenue = $this->processRow($data, $isSmartbill, $dryRun);
                $this->stats['imported']++;

                // Download PDF if requested
                if ($revenue && $isSmartbill && $downloadPdfs && $smartbillSettings) {
                    $this->downloadPdf($revenue, $data, $smartbillSettings);
                }

                // Report progress
                if ($this->progressCallback) {
                    ($this->progressCallback)($index + 1, count($dataRows), $this->stats);
                }
            } catch (\Exception $e) {
                $this->stats['errors'][] = "Row {$rowNumber}: " . $e->getMessage();
                $this->stats['skipped']++;
                
                // Report progress even on error
                if ($this->progressCallback) {
                    ($this->progressCallback)($index + 1, count($dataRows), $this->stats);
                }
            }
        }

        return $this->stats;
    }

    /**
     * Download PDF from Smartbill for an invoice.
     */
    protected function downloadPdf(
        FinancialRevenue $revenue,
        array $data,
        array $smartbillSettings
    ): void {
        $series = trim($data['serie'] ?? $data['Serie'] ?? '');
        $number = trim($data['numar'] ?? $data['Numar'] ?? '');

        if (empty($series) || empty($number)) {
            return;
        }

        if (empty($smartbillSettings['username']) || empty($smartbillSettings['token']) || empty($smartbillSettings['cif'])) {
            return;
        }

        try {
            $smartbillService = new SmartbillService(
                $smartbillSettings['username'],
                $smartbillSettings['token'],
                $smartbillSettings['cif']
            );

            $pdfContent = $smartbillService->downloadInvoicePdf($series, $number);

            if (!$pdfContent) {
                return;
            }

            // Store PDF
            $invoiceNumber = str_pad($number, 4, '0', STR_PAD_LEFT);
            $filename = "Factura {$series}{$invoiceNumber}.pdf";

            $year = $revenue->occurred_at->year;
            $month = $revenue->occurred_at->month;
            $monthName = romanian_month($month);

            $path = "{$year}/{$monthName}/Incasari/{$filename}";
            Storage::disk('financial')->put($path, $pdfContent);

            // Create file record
            FinancialFile::create([
                'entity_type' => FinancialRevenue::class,
                'entity_id' => $revenue->id,
                'organization_id' => $this->organizationId,
                'file_name' => $filename,
                'file_path' => $path,
                'file_type' => 'application/pdf',
                'file_size' => strlen($pdfContent),
                'uploaded_by' => $this->userId,
            ]);

            $this->stats['pdfs_downloaded']++;
        } catch (\Exception $e) {
            Log::warning("Failed to download PDF for invoice {$series}-{$number}: " . $e->getMessage());
        }
    }

    /**
     * Generate preview data for import.
     *
     * @param array $csvData Parsed CSV rows
     * @param int $limit Maximum rows to preview
     * @return array Preview data
     */
    public function preview(array $csvData, int $limit = 50): array
    {
        [$headerRowIndex, $header] = $this->findHeaderRow($csvData);
        $dataRows = array_slice($csvData, $headerRowIndex + 1);

        $isSmartbill = $this->detectSmartbillExport($header);

        // Pre-load clients
        $this->loadClientsIndex();

        $previewRows = [];
        $summary = [
            'total' => 0,
            'new' => 0,
            'duplicates' => 0,
            'errors' => 0,
            'new_clients' => 0,
            'total_amount_ron' => 0,
            'total_amount_eur' => 0,
        ];

        foreach ($dataRows as $index => $row) {
            if (empty(array_filter($row))) {
                continue;
            }
            if (count($row) !== count($header)) {
                continue;
            }

            $summary['total']++;
            $data = array_combine($header, $row);

            if ($isSmartbill) {
                $data = $this->mapSmartbillColumns($data);
            }

            // Validate
            [$isValid, $errors] = $this->validateRow($data);
            $hasError = !$isValid;
            $errorMsg = $hasError ? implode(', ', $errors) : '';

            // Check duplicates
            $isDuplicate = false;
            if (!$hasError) {
                $isDuplicate = $this->findDuplicate($data, $isSmartbill) !== null;
            }

            // Check client status
            $clientStatus = 'none';
            $clientName = trim($data['client_name'] ?? $data['client'] ?? '');
            $cif = trim($data['cif_client'] ?? $data['CIF'] ?? '');

            if (!empty($cif)) {
                $existingClient = $this->findClientByCif($cif);
                if ($existingClient) {
                    $clientStatus = 'existing';
                    $clientName = $existingClient->name;
                } elseif (!empty($clientName)) {
                    $clientStatus = 'new';
                    $summary['new_clients']++;
                }
            }

            // Update summary
            if ($hasError) {
                $summary['errors']++;
            } elseif ($isDuplicate) {
                $summary['duplicates']++;
            } else {
                $summary['new']++;
                $currency = strtoupper(trim($data['currency'] ?? 'RON'));
                $amount = (float) ($data['amount'] ?? 0);
                if ($currency === 'EUR') {
                    $summary['total_amount_eur'] += $amount;
                } else {
                    $summary['total_amount_ron'] += $amount;
                }
            }

            // Add to preview (limited)
            if ($index < $limit) {
                $previewRows[] = [
                    'row' => $index + 2,
                    'document_name' => $data['document_name'] ?? '',
                    'amount' => $data['amount'] ?? '',
                    'currency' => strtoupper(trim($data['currency'] ?? 'RON')),
                    'date' => $data['occurred_at'] ?? '',
                    'client_name' => $clientName,
                    'client_status' => $clientStatus,
                    'is_duplicate' => $isDuplicate,
                    'has_error' => $hasError,
                    'error_msg' => $errorMsg,
                ];
            }
        }

        return [
            'is_smartbill' => $isSmartbill,
            'summary' => $summary,
            'preview_rows' => $previewRows,
            'has_more' => $summary['total'] > $limit,
        ];
    }
}
