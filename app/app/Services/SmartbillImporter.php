<?php

namespace App\Services;

use App\Models\Client;
use App\Models\FinancialRevenue;
use App\Models\FinancialFile;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class SmartbillImporter
{
    protected $smartbillService;
    protected $organization;
    protected $userId;
    protected $stats = [
        'total' => 0,
        'created' => 0,
        'updated' => 0,
        'skipped' => 0,
        'errors' => 0,
        'clients_created' => 0,
        'pdfs_downloaded' => 0,
    ];

    public function __construct(Organization $organization, $userId)
    {
        $this->organization = $organization;
        $this->userId = $userId;

        // Get Smartbill credentials from organization settings
        $smartbillSettings = $this->organization->settings['smartbill'] ?? [];
        $username = $smartbillSettings['username'] ?? null;
        $token = $smartbillSettings['token'] ?? null;
        $cif = $smartbillSettings['cif'] ?? null;

        if (!$username || !$token || !$cif) {
            throw new Exception('Smartbill credentials not configured for this organization');
        }

        $this->smartbillService = new SmartbillService($username, $token, $cif);
    }

    /**
     * Import invoices for a date range
     */
    public function importInvoices($fromDate, $toDate, $downloadPdfs = true, $preview = false)
    {
        $this->stats = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'clients_created' => 0,
            'pdfs_downloaded' => 0,
        ];

        try {
            $page = 1;
            $perPage = 50;
            $hasMore = true;

            while ($hasMore) {
                $response = $this->smartbillService->listInvoices($fromDate, $toDate, $page, $perPage);

                if (!isset($response['list']) || !is_array($response['list'])) {
                    Log::warning('Unexpected response from Smartbill API', ['response' => $response]);
                    break;
                }

                $invoices = $response['list'];
                $this->stats['total'] += count($invoices);

                foreach ($invoices as $invoice) {
                    try {
                        $this->processInvoice($invoice, $downloadPdfs, $preview);
                    } catch (Exception $e) {
                        $this->stats['errors']++;
                        Log::error('Error processing invoice', [
                            'invoice' => $invoice,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                // Check if there are more pages
                $hasMore = count($invoices) === $perPage;
                $page++;
            }

            return [
                'success' => true,
                'stats' => $this->stats,
            ];
        } catch (Exception $e) {
            Log::error('Error importing invoices', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'stats' => $this->stats,
            ];
        }
    }

    /**
     * Process a single invoice
     */
    protected function processInvoice($invoice, $downloadPdf = true, $preview = false)
    {
        $seriesName = $invoice['seriesName'] ?? null;
        $number = $invoice['number'] ?? null;
        $clientCif = $invoice['client']['cif'] ?? $invoice['client']['vatCode'] ?? null;

        if (!$seriesName || !$number) {
            $this->stats['skipped']++;
            Log::warning('Invoice missing required fields', ['invoice' => $invoice]);
            return;
        }

        // Check if invoice already exists
        $existing = FinancialRevenue::withoutGlobalScope('user_scope')
            ->where('organization_id', $this->organization->id)
            ->where('smartbill_invoice_number', $number)
            ->where('smartbill_series', $seriesName)
            ->first();

        // Find or create client
        $client = null;
        if ($clientCif) {
            $client = $this->findOrCreateClient($invoice['client'], $preview);
        }

        // Parse invoice date
        $invoiceDate = isset($invoice['issueDate']) ? Carbon::parse($invoice['issueDate']) : now();

        // Calculate total amount
        $total = $invoice['total'] ?? 0;

        // Prepare revenue data
        $revenueData = [
            'organization_id' => $this->organization->id,
            'user_id' => $this->userId,
            'document_name' => "Factura {$seriesName}-{$number}",
            'amount' => $total,
            'currency' => $invoice['currency'] ?? 'RON',
            'occurred_at' => $invoiceDate,
            'client_id' => $client?->id,
            'year' => $invoiceDate->year,
            'month' => $invoiceDate->month,
            'note' => $this->buildNoteFromInvoice($invoice),
            'smartbill_invoice_number' => $number,
            'smartbill_series' => $seriesName,
            'smartbill_client_cif' => $clientCif,
            'smartbill_imported_at' => now(),
            'smartbill_raw_data' => $invoice,
        ];

        if ($preview) {
            // In preview mode, just log what would be done
            Log::info('Preview: Would ' . ($existing ? 'update' : 'create') . ' invoice', $revenueData);
            if ($existing) {
                $this->stats['updated']++;
            } else {
                $this->stats['created']++;
            }
            return;
        }

        DB::transaction(function () use ($existing, $revenueData, $seriesName, $number, $downloadPdf) {
            if ($existing) {
                // Update existing revenue
                $existing->update($revenueData);
                $revenue = $existing;
                $this->stats['updated']++;
            } else {
                // Create new revenue
                $revenue = FinancialRevenue::withoutGlobalScope('user_scope')->create($revenueData);
                $this->stats['created']++;
            }

            // Download and attach PDF
            if ($downloadPdf) {
                $this->downloadAndAttachPdf($revenue, $seriesName, $number);
            }
        });
    }

    /**
     * Find or create a client based on Smartbill data
     */
    protected function findOrCreateClient($clientData, $preview = false)
    {
        $cif = $clientData['cif'] ?? $clientData['vatCode'] ?? null;
        $name = $clientData['name'] ?? null;

        if (!$cif && !$name) {
            return null;
        }

        // Try to find existing client by CIF or name
        $query = Client::withoutGlobalScope('user_scope')
            ->where('organization_id', $this->organization->id);

        if ($cif) {
            $query->where('tax_id', $cif);
        } else {
            $query->where('name', $name);
        }

        $client = $query->first();

        if ($client) {
            return $client;
        }

        // Client doesn't exist - create new one
        if ($preview) {
            Log::info('Preview: Would create client', $clientData);
            $this->stats['clients_created']++;
            return null;
        }

        $clientCreateData = [
            'organization_id' => $this->organization->id,
            'user_id' => $this->userId,
            'name' => $this->formatClientName($name),
            'type' => 'business',
            'tax_id' => $cif,
            'email' => $clientData['email'] ?? null,
            'phone' => $clientData['phone'] ?? null,
            'address' => $this->buildAddressFromClient($clientData),
            'city' => $clientData['city'] ?? null,
            'country' => $clientData['country'] ?? 'România',
        ];

        $client = Client::withoutGlobalScope('user_scope')->create($clientCreateData);
        $this->stats['clients_created']++;

        return $client;
    }

    /**
     * Build address from client data
     */
    protected function buildAddressFromClient($clientData)
    {
        $parts = array_filter([
            $clientData['address'] ?? null,
            $clientData['city'] ?? null,
            $clientData['county'] ?? null,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Build note from invoice data
     */
    protected function buildNoteFromInvoice($invoice)
    {
        $notes = [];

        if (isset($invoice['client']['name'])) {
            $notes[] = "Client: {$invoice['client']['name']}";
        }

        if (isset($invoice['dueDate'])) {
            $notes[] = "Scadență: {$invoice['dueDate']}";
        }

        if (isset($invoice['observations']) && !empty($invoice['observations'])) {
            $notes[] = "Observații: {$invoice['observations']}";
        }

        return implode("\n", $notes);
    }

    /**
     * Download and attach PDF to revenue
     */
    protected function downloadAndAttachPdf($revenue, $seriesName, $number)
    {
        try {
            $pdfContent = $this->smartbillService->downloadInvoicePdf($seriesName, $number);

            if (!$pdfContent) {
                Log::warning('Could not download PDF for invoice', [
                    'series' => $seriesName,
                    'number' => $number,
                ]);
                return;
            }

            // Create filename matching convention: "Factura SAD0XXX.pdf"
            $invoiceNumber = str_pad($number, 4, '0', STR_PAD_LEFT);
            $filename = "Factura {$seriesName}{$invoiceNumber}.pdf";

            // Determine year/month from revenue
            $year = $revenue->occurred_at->year;
            $month = $revenue->occurred_at->month;
            $monthName = $this->getRomanianMonthName($month);

            // Store in same structure as regular revenue files: /year/MonthName/Incasari/filename
            $path = "{$year}/{$monthName}/Incasari/{$filename}";

            // Store PDF using the financial disk
            Storage::disk('financial')->put($path, $pdfContent);

            // Check if file attachment already exists
            $existingFile = FinancialFile::where('entity_type', FinancialRevenue::class)
                ->where('entity_id', $revenue->id)
                ->where('file_name', $filename)
                ->first();

            if (!$existingFile) {
                // Create file record
                FinancialFile::create([
                    'entity_type' => FinancialRevenue::class,
                    'entity_id' => $revenue->id,
                    'organization_id' => $this->organization->id,
                    'file_name' => $filename,
                    'file_path' => $path,
                    'file_type' => 'application/pdf',
                    'file_size' => strlen($pdfContent),
                    'uploaded_by' => $this->userId,
                ]);

                $this->stats['pdfs_downloaded']++;
            }
        } catch (Exception $e) {
            Log::error('Error downloading PDF', [
                'series' => $seriesName,
                'number' => $number,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get import statistics
     */
    public function getStats()
    {
        return $this->stats;
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
}
