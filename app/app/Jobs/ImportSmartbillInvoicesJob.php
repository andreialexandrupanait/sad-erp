<?php

namespace App\Jobs;

use App\Models\Organization;
use App\Models\SmartbillImport;
use App\Models\Client;
use App\Models\FinancialRevenue;
use App\Services\SmartbillService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ImportSmartbillInvoicesJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 3600; // 1 hour timeout

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * Exponential backoff: 2 min, 5 min, 10 min
     */
    public function backoff(): array
    {
        return [120, 300, 600]; // 2 min, 5 min, 10 min
    }

    protected $importId;
    protected $organizationId;
    protected $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $importId, int $organizationId, int $userId)
    {
        $this->importId = $importId;
        $this->organizationId = $organizationId;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting Smartbill import job', [
            'import_id' => $this->importId,
            'organization_id' => $this->organizationId,
        ]);

        $import = SmartbillImport::find($this->importId);

        if (!$import) {
            Log::error('Smartbill import record not found', ['import_id' => $this->importId]);
            return;
        }

        // Check if cancelled
        if ($import->status === 'cancelled') {
            Log::info('Smartbill import was cancelled', ['import_id' => $this->importId]);
            return;
        }

        try {
            // Update status to running
            $import->update([
                'status' => 'running',
                'started_at' => now(),
            ]);

            $organization = Organization::find($this->organizationId);
            if (!$organization) {
                throw new \Exception('Organization not found');
            }

            // Read CSV data from stored file
            $filePath = $import->file_path;
            if (!Storage::disk('local')->exists($filePath)) {
                throw new \Exception('Import file not found: ' . $filePath);
            }

            $fileContent = Storage::disk('local')->get($filePath);
            $csvData = $this->parseCsvContent($fileContent);

            $options = $import->options ?? [];
            $downloadPdfs = $options['download_pdfs'] ?? false;
            $dryRun = $options['dry_run'] ?? false;

            // Pre-load all existing clients by tax_id for performance
            $existingClients = $this->preloadClientsByTaxId();

            $stats = [
                'imported' => 0,
                'skipped' => 0,
                'duplicates' => 0,
                'clients_created' => 0,
                'pdfs_downloaded' => 0,
            ];
            $errors = [];

            // Process in chunks of 50
            $chunks = array_chunk($csvData, 50);
            $processedRows = 0;

            foreach ($chunks as $chunkIndex => $chunk) {
                // Check if cancelled mid-import
                $import->refresh();
                if ($import->status === 'cancelled') {
                    Log::info('Smartbill import cancelled during processing', ['import_id' => $this->importId]);
                    return;
                }

                foreach ($chunk as $index => $row) {
                    $rowNumber = ($chunkIndex * 50) + $index + 2; // +2 for header and 1-based index

                    try {
                        $result = $this->processRow($row, $rowNumber, $organization, $existingClients, $downloadPdfs, $dryRun);

                        if ($result['status'] === 'imported') {
                            $stats['imported']++;
                            if ($result['client_created'] ?? false) {
                                $stats['clients_created']++;
                            }
                            if ($result['pdf_downloaded'] ?? false) {
                                $stats['pdfs_downloaded']++;
                            }
                        } elseif ($result['status'] === 'duplicate') {
                            $stats['duplicates']++;
                            $stats['skipped']++;
                        } else {
                            $stats['skipped']++;
                        }

                        if (!empty($result['error'])) {
                            $errors[] = $result['error'];
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                        $stats['skipped']++;
                    }

                    $processedRows++;
                }

                // Update progress after each chunk
                $import->update([
                    'processed_rows' => $processedRows,
                    'stats' => $stats,
                ]);
            }

            // Cleanup: delete the temporary file
            Storage::disk('local')->delete($filePath);

            // Mark as completed
            $import->update([
                'status' => 'completed',
                'completed_at' => now(),
                'stats' => $stats,
                'errors' => !empty($errors) ? array_slice($errors, 0, 100) : null, // Limit to 100 errors
            ]);

            Log::info('Smartbill import completed', [
                'import_id' => $this->importId,
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            Log::error('Smartbill import failed', [
                'import_id' => $this->importId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $import->update([
                'status' => 'failed',
                'completed_at' => now(),
                'errors' => [$e->getMessage()],
            ]);

            throw $e;
        }
    }

    /**
     * Parse CSV content into array
     */
    protected function parseCsvContent(string $content): array
    {
        $lines = explode("\n", $content);
        $csvData = [];

        // Find header row (skip Smartbill metadata rows)
        $headerIndex = 0;
        $header = null;

        foreach ($lines as $index => $line) {
            $row = str_getcsv($line);
            $row = array_map('trim', $row);

            // Check if this row contains Smartbill column names
            foreach ($row as $cell) {
                $cell = strtolower($cell);
                if (in_array($cell, ['serie', 'numar', 'data', 'client', 'total', 'cif', 'moneda'])) {
                    $headerIndex = $index;
                    $header = $row;
                    break 2;
                }
            }
        }

        if ($header === null) {
            $header = str_getcsv(array_shift($lines));
            $header = array_map('trim', $header);
        } else {
            $lines = array_slice($lines, $headerIndex + 1);
        }

        // Map Smartbill columns
        $columnMap = $this->getColumnMap();

        foreach ($lines as $line) {
            if (empty(trim($line))) continue;

            $row = str_getcsv($line);
            if (count($row) !== count($header)) continue;

            $data = array_combine($header, $row);
            $mappedData = [];

            foreach ($data as $key => $value) {
                $mappedKey = $columnMap[$key] ?? $key;
                $mappedData[$mappedKey] = $value;
            }

            // Build document_name if not present
            if (empty($mappedData['document_name']) && !empty($mappedData['serie']) && !empty($mappedData['numar'])) {
                $mappedData['document_name'] = trim($mappedData['serie']) . '-' . trim($mappedData['numar']);
            }

            // Set default currency
            if (empty($mappedData['currency'])) {
                $mappedData['currency'] = 'RON';
            }

            // Convert date format
            if (!empty($mappedData['occurred_at'])) {
                $dateStr = trim($mappedData['occurred_at']);
                if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $dateStr, $matches)) {
                    $mappedData['occurred_at'] = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
                }
            }

            $csvData[] = $mappedData;
        }

        return $csvData;
    }

    /**
     * Get column mapping for Smartbill exports
     */
    protected function getColumnMap(): array
    {
        return [
            'Serie' => 'serie',
            'Numar' => 'numar',
            'Data' => 'occurred_at',
            'Data emitere' => 'occurred_at',
            'Data factura' => 'occurred_at',
            'Data incasarii' => 'occurred_at',
            'Total' => 'amount',
            'Total factura' => 'amount',
            'Suma' => 'amount',
            'Valoare' => 'amount',
            'Valoare totala' => 'amount',
            'Valoare Totala' => 'amount',
            'Moneda' => 'currency',
            'Valuta' => 'currency',
            'Client' => 'client_name',
            'Nume client' => 'client_name',
            'Partener' => 'client_name',
            'CIF' => 'cif_client',
            'CIF client' => 'cif_client',
            'CUI' => 'cif_client',
            'Observatii' => 'note',
            'Mentiuni' => 'note',
            'Factura' => 'document_name',
        ];
    }

    /**
     * Preload all clients by tax_id for efficient lookup
     */
    protected function preloadClientsByTaxId(): array
    {
        $clients = Client::whereNotNull('tax_id')
            ->where('tax_id', '!=', '')
            ->get(['id', 'name', 'tax_id', 'notes']);

        $indexed = [];
        foreach ($clients as $client) {
            // Index by original tax_id
            $indexed[$client->tax_id] = $client;

            // Also index by cleaned version (without RO prefix)
            $cleanTaxId = preg_replace('/^RO/i', '', $client->tax_id);
            $cleanTaxId = preg_replace('/\s+/', '', $cleanTaxId);
            if ($cleanTaxId !== $client->tax_id) {
                $indexed[$cleanTaxId] = $client;
            }
        }

        return $indexed;
    }

    /**
     * Process a single row
     */
    protected function processRow(array $data, int $rowNumber, Organization $organization, array &$existingClients, bool $downloadPdfs, bool $dryRun): array
    {
        $result = ['status' => 'skipped', 'error' => null];

        // Validate required fields
        if (empty($data['document_name']) || empty($data['amount']) || empty($data['occurred_at'])) {
            $result['error'] = "Row {$rowNumber}: Missing required fields (document_name, amount, or occurred_at)";
            return $result;
        }

        // Parse amount
        $amount = (float) str_replace([',', ' '], ['.', ''], $data['amount']);
        if ($amount <= 0) {
            $result['error'] = "Row {$rowNumber}: Invalid amount";
            return $result;
        }

        // Find or create client
        $clientId = null;
        $clientCreated = false;

        $cif = trim($data['cif_client'] ?? $data['CIF'] ?? '');
        $clientName = trim($data['client_name'] ?? $data['client'] ?? '');

        if (!empty($cif)) {
            $cleanCif = preg_replace('/^RO/i', '', $cif);
            $cleanCif = preg_replace('/\s+/', '', $cleanCif);

            // Check pre-loaded clients
            $client = $existingClients[$cif] ?? $existingClients[$cleanCif] ?? $existingClients['RO' . $cleanCif] ?? null;

            if (!$client && !empty($clientName) && !$dryRun) {
                // Create new client
                $formattedName = mb_convert_case(trim($clientName), MB_CASE_TITLE, 'UTF-8');
                $client = Client::create([
                    'name' => $formattedName,
                    'company_name' => $formattedName,
                    'tax_id' => $cif,
                    'notes' => 'Auto-created from Smartbill import on ' . now()->format('Y-m-d H:i'),
                ]);

                // Add to cache
                $existingClients[$cif] = $client;
                $existingClients[$cleanCif] = $client;
                $clientCreated = true;

                Log::info("Auto-created client from Smartbill import", [
                    'client_id' => $client->id,
                    'name' => $clientName,
                    'cif' => $cif
                ]);
            }

            $clientId = $client?->id;
        }

        // Parse date
        try {
            $occurredAt = Carbon::parse($data['occurred_at']);
        } catch (\Exception $e) {
            $result['error'] = "Row {$rowNumber}: Invalid date format";
            return $result;
        }

        $series = trim($data['serie'] ?? '');
        $number = trim($data['numar'] ?? '');

        // Check for duplicates
        if (!empty($series) && !empty($number)) {
            $exists = FinancialRevenue::where('smartbill_series', $series)
                ->where('smartbill_invoice_number', $number)
                ->exists();

            if ($exists) {
                $result['status'] = 'duplicate';
                return $result;
            }
        } else {
            $exists = FinancialRevenue::where('document_name', $data['document_name'])
                ->whereDate('occurred_at', $occurredAt)
                ->exists();

            if ($exists) {
                $result['status'] = 'duplicate';
                return $result;
            }
        }

        if ($dryRun) {
            $result['status'] = 'imported';
            $result['client_created'] = $clientCreated;
            return $result;
        }

        // Create revenue record
        $revenue = FinancialRevenue::create([
            'document_name' => trim($data['document_name']),
            'amount' => $amount,
            'currency' => strtoupper(trim($data['currency'] ?? 'RON')),
            'occurred_at' => $occurredAt,
            'year' => $occurredAt->year,
            'month' => $occurredAt->month,
            'client_id' => $clientId,
            'note' => trim($data['note'] ?? ''),
            'smartbill_series' => $series ?: null,
            'smartbill_invoice_number' => $number ?: null,
            'smartbill_client_cif' => $cif ?: null,
            'smartbill_imported_at' => now(),
        ]);

        $result['status'] = 'imported';
        $result['client_created'] = $clientCreated;

        // Download PDF if requested
        if ($downloadPdfs && !empty($series) && !empty($number)) {
            try {
                $pdfDownloaded = $this->downloadSmartbillPdf($organization, $revenue, $series, $number);
                $result['pdf_downloaded'] = $pdfDownloaded;
            } catch (\Exception $e) {
                Log::warning("Failed to download PDF for invoice {$series}-{$number}: " . $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Download PDF from Smartbill
     */
    protected function downloadSmartbillPdf(Organization $organization, FinancialRevenue $revenue, string $series, string $number): bool
    {
        $smartbillSettings = $organization->settings['smartbill'] ?? [];

        if (empty($smartbillSettings['username']) || empty($smartbillSettings['token']) || empty($smartbillSettings['cif'])) {
            return false;
        }

        $smartbillService = new SmartbillService(
            $smartbillSettings['username'],
            $smartbillSettings['token'],
            $smartbillSettings['cif']
        );

        $pdfContent = $smartbillService->downloadInvoicePdf($series, $number);

        if (!$pdfContent) {
            return false;
        }

        // Store PDF
        $invoiceNumber = str_pad($number, 4, '0', STR_PAD_LEFT);
        $filename = "Factura {$series}{$invoiceNumber}.pdf";

        $year = $revenue->occurred_at->year;
        $month = $revenue->occurred_at->month;
        $monthName = $this->getRomanianMonthName($month);

        $path = "{$year}/{$monthName}/Incasari/{$filename}";

        Storage::disk('financial')->put($path, $pdfContent);

        // Create file record
        \App\Models\FinancialFile::create([
            'entity_type' => FinancialRevenue::class,
            'entity_id' => $revenue->id,
            'organization_id' => $organization->id,
            'file_name' => $filename,
            'file_path' => $path,
            'file_type' => 'application/pdf',
            'file_size' => strlen($pdfContent),
            'uploaded_by' => $this->userId,
        ]);

        return true;
    }

    /**
     * Get Romanian month name
     */
    protected function getRomanianMonthName(int $month): string
    {
        $months = [
            1 => 'Ianuarie', 2 => 'Februarie', 3 => 'Martie', 4 => 'Aprilie',
            5 => 'Mai', 6 => 'Iunie', 7 => 'Iulie', 8 => 'August',
            9 => 'Septembrie', 10 => 'Octombrie', 11 => 'Noiembrie', 12 => 'Decembrie',
        ];

        return $months[$month] ?? 'Unknown';
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Smartbill import job failed permanently', [
            'import_id' => $this->importId,
            'error' => $exception->getMessage(),
        ]);
    }
}
