<?php

namespace App\Services;

use App\Models\Client;
use App\Models\FinancialRevenue;
use App\Models\FinancialFile;
use App\Models\Organization;
use App\Services\Financial\Import\ClientMatcher;
use App\Services\Financial\RevenueAggregator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class SmartbillImporter
{
    /**
     * Romanian month names - static to avoid recreating on every call
     */
    protected const ROMANIAN_MONTHS = [
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

    protected $smartbillService;
    protected $organization;
    protected $userId;
    protected ClientMatcher $clientMatcher;
    protected $stats = [
        'total' => 0,
        'created' => 0,
        'updated' => 0,
        'skipped' => 0,
        'errors' => 0,
        'clients_created' => 0,
        'pdfs_downloaded' => 0,
    ];

    /**
     * Track client IDs that need their totals updated after bulk import.
     */
    protected array $affectedClientIds = [];

    public function __construct(Organization $organization, $userId, ?ClientMatcher $clientMatcher = null)
    {
        $this->organization = $organization;
        $this->userId = $userId;
        $this->clientMatcher = $clientMatcher ?? app(ClientMatcher::class);

        // Get Smartbill credentials from organization settings (using encrypted getter)
        $smartbillSettings = $this->organization->getSmartbillSettings();
        $username = $smartbillSettings['username'] ?? null;
        $token = $smartbillSettings['token'] ?? null;
        $cif = $smartbillSettings['cif'] ?? null;

        if (!$username || !$token || !$cif) {
            throw new Exception('Smartbill credentials not configured for this organization');
        }

        $this->smartbillService = new SmartbillService($username, $token, $cif);

        // Load client index for fast lookups
        $this->clientMatcher->loadIndex();
    }

    /**
     * Import invoices for a date range
     */
    public function importInvoices(string $fromDate, string $toDate, bool $downloadPdfs = true, bool $preview = false): array
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
        $this->affectedClientIds = [];

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

                // Process in smaller chunks to prevent memory exhaustion
                // Wrap in transaction and disable events to prevent redundant cache clearing
                $chunks = array_chunk($invoices, 25);
                foreach ($chunks as $chunk) {
                    try {
                        DB::transaction(function () use ($chunk, $downloadPdfs, $preview) {
                            // Disable observer events during bulk import
                            // Observers clear cache on each save, which is inefficient for bulk operations
                            FinancialRevenue::withoutEvents(function () use ($chunk, $downloadPdfs, $preview) {
                                foreach ($chunk as $invoice) {
                                    try {
                                        $this->processInvoice($invoice, $downloadPdfs, $preview);
                                    } catch (\Illuminate\Database\QueryException $e) {
                                        // Database errors should rollback the entire chunk
                                        throw $e;
                                    } catch (Exception $e) {
                                        // Application errors are logged but don't rollback
                                        $this->stats['errors']++;
                                        Log::error('Error processing invoice', [
                                            'invoice' => $invoice,
                                            'error' => $e->getMessage(),
                                        ]);
                                    }
                                }
                            });
                        });
                    } catch (\Illuminate\Database\QueryException $e) {
                        // Database error rolled back the chunk - log and continue with next chunk
                        $this->stats['errors'] += count($chunk);
                        Log::error('Database error processing chunk - rolled back', [
                            'error' => $e->getMessage(),
                            'chunk_size' => count($chunk),
                        ]);
                    }
                    // Free memory between chunks
                    gc_collect_cycles();
                }

                // Check if there are more pages
                $hasMore = count($invoices) === $perPage;
                $page++;
            }

            // Update client totals for all affected clients (batch update since observers are disabled)
            $this->updateAffectedClientTotals();

            // Clear dashboard caches after successful import
            $this->clearFinancialCaches();

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
     * Clear financial caches after import to ensure fresh data.
     */
    protected function clearFinancialCaches(): void
    {
        try {
            // Clear revenue aggregator cache
            $revenueAggregator = app(RevenueAggregator::class);
            $revenueAggregator->clearCache();

            // Clear dashboard-related caches
            $orgId = $this->organization->id;
            $currentYear = now()->year;

            // Clear specific cache keys that might be affected
            Cache::forget("financial_dashboard_{$orgId}_{$currentYear}");
            Cache::forget("dashboard_metrics_{$orgId}");

            Log::info('Cleared financial caches after Smartbill import', [
                'organization_id' => $orgId,
            ]);
        } catch (Exception $e) {
            // Cache clearing should not fail the import
            Log::warning('Failed to clear financial caches after import', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update total_incomes for all clients affected by the bulk import.
     *
     * Since observers are disabled during bulk import, we batch-update
     * client totals at the end instead of updating after each revenue.
     */
    protected function updateAffectedClientTotals(): void
    {
        $clientIds = array_keys($this->affectedClientIds);

        if (empty($clientIds)) {
            return;
        }

        try {
            // Use a single bulk UPDATE with subquery instead of 3N individual queries
            // This updates all affected clients in one database operation
            DB::statement("
                UPDATE clients
                SET total_incomes = (
                    SELECT COALESCE(SUM(amount), 0)
                    FROM financial_revenues
                    WHERE financial_revenues.client_id = clients.id
                    AND financial_revenues.organization_id = ?
                )
                WHERE id IN (" . implode(',', array_fill(0, count($clientIds), '?')) . ")
            ", array_merge([$this->organization->id], $clientIds));

            Log::info('Updated client totals after bulk import', [
                'clients_updated' => count($clientIds),
                'organization_id' => $this->organization->id,
            ]);
        } catch (Exception $e) {
            // Client total update should not fail the entire import
            Log::error('Failed to update client totals after import', [
                'client_ids' => $clientIds,
                'error' => $e->getMessage(),
            ]);
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

            // Track affected client for batch total update
            if ($revenue->client_id) {
                $this->affectedClientIds[$revenue->client_id] = true;
            }

            // Download and attach PDF
            if ($downloadPdf) {
                $this->downloadAndAttachPdf($revenue, $seriesName, $number);
            }
        });
    }

    /**
     * Find or create a client based on Smartbill data.
     * Delegates to ClientMatcher with Smartbill-specific data mapping.
     */
    protected function findOrCreateClient($clientData, $preview = false)
    {
        $cif = $clientData['cif'] ?? $clientData['vatCode'] ?? null;
        $name = $clientData['name'] ?? null;

        if (!$cif && !$name) {
            return null;
        }

        // Map Smartbill client data to ClientMatcher format
        $mappedData = [
            'cif_client' => $cif,
            'client_name' => $name,
            'client_address' => $this->buildAddressFromClient($clientData),
            'client_contact' => $clientData['email'] ?? $clientData['phone'] ?? null,
        ];

        // Use ClientMatcher to find or create
        $clientId = $this->clientMatcher->findOrCreate($mappedData, $preview);

        // Sync stats from ClientMatcher
        $this->stats['clients_created'] += $this->clientMatcher->stats['clients_created'];

        if (!$clientId) {
            return null;
        }

        // Return the actual Client model
        return Client::find($clientId);
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
    public function getStats(): array
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
        return self::ROMANIAN_MONTHS[$monthNumber] ?? 'Unknown';
    }
}
