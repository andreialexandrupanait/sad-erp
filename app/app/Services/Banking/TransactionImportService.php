<?php

namespace App\Services\Banking;

use App\Models\BankingCredential;
use App\Models\BankSyncLog;
use App\Models\BankTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionImportService
{
    protected BancaTransilvaniaService $btService;

    public function __construct(BancaTransilvaniaService $btService)
    {
        $this->btService = $btService;
    }

    /**
     * Sync transactions for a banking credential
     */
    public function syncTransactions(
        BankingCredential $credential,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        string $syncType = 'scheduled'
    ): BankSyncLog {
        // Determine date range
        if (!$dateFrom) {
            $dateFrom = $credential->sync_from_date
                ?? now()->subDays(config('banking.sync.default_sync_range_days', 90));
        }

        if (!$dateTo) {
            $dateTo = now();
        }

        $dateFrom = is_string($dateFrom) ? Carbon::parse($dateFrom) : $dateFrom;
        $dateTo = is_string($dateTo) ? Carbon::parse($dateTo) : $dateTo;

        // Create sync log
        $syncLog = BankSyncLog::startSync(
            $credential,
            $syncType,
            $dateFrom->format('Y-m-d'),
            $dateTo->format('Y-m-d')
        );

        try {
            // Get account information
            $accounts = $this->btService->getAccounts($credential);

            if (empty($accounts['accounts'])) {
                throw new \Exception('No accounts found');
            }

            // Use first account (in future, support multiple accounts)
            $account = $accounts['accounts'][0];
            $accountId = $account['resourceId'] ?? $account['id'] ?? null;

            if (!$accountId) {
                throw new \Exception('Account ID not found in API response');
            }

            $stats = [
                'fetched' => 0,
                'new' => 0,
                'updated' => 0,
                'duplicate' => 0,
                'matches_auto' => 0,
                'matches_manual' => 0,
                'matches_total' => 0,
            ];

            // Fetch transactions in chunks (PSD2 API may paginate)
            $continuationKey = null;
            $maxIterations = 100; // Safety limit
            $iteration = 0;

            do {
                $syncLog->incrementApiCall();

                $response = $this->btService->getTransactions(
                    $credential,
                    $accountId,
                    $dateFrom->format('Y-m-d'),
                    $dateTo->format('Y-m-d'),
                    $continuationKey
                );

                $transactions = $response['transactions']['booked'] ?? [];
                $pendingTransactions = $response['transactions']['pending'] ?? [];

                // Process booked transactions in a single transaction for efficiency
                DB::transaction(function () use ($credential, $transactions, $pendingTransactions, &$stats) {
                    // Process booked transactions
                    foreach ($transactions as $txData) {
                        $result = $this->importTransaction($credential, $txData, 'booked');
                        $stats[$result]++;
                        $stats['fetched']++;
                    }

                    // Optionally process pending transactions (mark them as pending status)
                    foreach ($pendingTransactions as $txData) {
                        $result = $this->importTransaction($credential, $txData, 'pending');
                        $stats[$result]++;
                        $stats['fetched']++;
                    }
                });

                // Check for pagination
                $continuationKey = $response['transactions']['_links']['next'] ?? null;
                $iteration++;

                // Add delay to respect rate limits
                if ($continuationKey && config('banking.sync.request_delay_ms')) {
                    usleep(config('banking.sync.request_delay_ms') * 1000);
                }

            } while ($continuationKey && $iteration < $maxIterations);

            // Mark sync as successful
            $credential->markSyncSuccess();
            $syncLog->markCompleted($stats);

            Log::info('Transaction sync completed', [
                'credential_id' => $credential->id,
                'stats' => $stats,
            ]);

            return $syncLog;

        } catch (\Exception $e) {
            Log::error('Transaction sync failed', [
                'credential_id' => $credential->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $credential->markSyncFailure($e->getMessage());
            $syncLog->markFailed($e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            throw $e;
        }
    }

    /**
     * Import a single transaction
     */
    protected function importTransaction(
        BankingCredential $credential,
        array $txData,
        string $status = 'booked'
    ): string {
        // Extract transaction ID
        $transactionId = $txData['transactionId']
            ?? $txData['entryReference']
            ?? $txData['endToEndId']
            ?? null;

        if (!$transactionId) {
            Log::warning('Transaction without ID skipped', ['data' => $txData]);
            return 'duplicate'; // Skip transactions without ID
        }

        // Check if transaction already exists
        $existing = BankTransaction::where('transaction_id', $transactionId)
            ->where('banking_credential_id', $credential->id)
            ->first();

        // Determine transaction type (incoming/outgoing)
        $amount = (float) ($txData['transactionAmount']['amount'] ?? 0);
        $type = $amount >= 0 ? 'incoming' : 'outgoing';
        $amount = abs($amount);

        // Parse dates
        $bookingDate = $this->parseDate($txData['bookingDate'] ?? $txData['valueDate'] ?? now());
        $valueDate = $this->parseDate($txData['valueDate'] ?? $txData['bookingDate'] ?? now());

        // Extract debtor/creditor information
        $debtorName = $txData['debtorName'] ?? $txData['debtor']['name'] ?? null;
        $debtorAccount = $txData['debtorAccount']['iban'] ?? null;
        $creditorName = $txData['creditorName'] ?? $txData['creditor']['name'] ?? null;
        $creditorAccount = $txData['creditorAccount']['iban'] ?? null;

        $transactionData = [
            'organization_id' => $credential->organization_id,
            'user_id' => $credential->user_id,
            'banking_credential_id' => $credential->id,
            'transaction_id' => $transactionId,
            'entry_reference' => $txData['entryReference'] ?? null,
            'booking_date' => $bookingDate,
            'value_date' => $valueDate,
            'type' => $type,
            'amount' => $amount,
            'currency' => $txData['transactionAmount']['currency'] ?? $credential->currency,
            'description' => $txData['additionalInformation'] ?? $txData['remittanceInformationUnstructured'] ?? null,
            'debtor_name' => $debtorName,
            'debtor_account' => $debtorAccount,
            'creditor_name' => $creditorName,
            'creditor_account' => $creditorAccount,
            'remittance_information' => $txData['remittanceInformationUnstructured'] ?? null,
            'status' => $status === 'pending' ? 'pending' : 'processed',
            'raw_data' => $txData,
        ];

        if ($existing) {
            // Update existing transaction
            $existing->update($transactionData);
            return 'updated';
        } else {
            // Create new transaction
            BankTransaction::create($transactionData);
            return 'new';
        }
    }

    /**
     * Parse date from various formats
     */
    protected function parseDate($date): Carbon
    {
        if ($date instanceof Carbon) {
            return $date;
        }

        if (is_string($date)) {
            return Carbon::parse($date);
        }

        return now();
    }

    /**
     * Sync historical transactions (from a specific start date)
     */
    public function syncHistoricalTransactions(
        BankingCredential $credential,
        ?string $startDate = null
    ): array {
        $startDate = $startDate ?? config('banking.sync.historical_start_date', '2019-10-01');
        $startDate = Carbon::parse($startDate);
        $endDate = now();

        $syncLogs = [];
        $maxDaysPerRequest = config('banking.sync.max_days_per_request', 90);

        // Split into chunks to avoid API limits
        $currentStart = $startDate->copy();

        while ($currentStart->isBefore($endDate)) {
            $currentEnd = $currentStart->copy()->addDays($maxDaysPerRequest);

            if ($currentEnd->isAfter($endDate)) {
                $currentEnd = $endDate->copy();
            }

            Log::info('Syncing historical chunk', [
                'from' => $currentStart->format('Y-m-d'),
                'to' => $currentEnd->format('Y-m-d'),
            ]);

            try {
                $syncLog = $this->syncTransactions(
                    $credential,
                    $currentStart->format('Y-m-d'),
                    $currentEnd->format('Y-m-d'),
                    'historical'
                );

                $syncLogs[] = $syncLog;

                // Add delay between chunks
                if (config('banking.sync.request_delay_ms')) {
                    usleep(config('banking.sync.request_delay_ms') * 1000);
                }

            } catch (\Exception $e) {
                Log::error('Historical sync chunk failed', [
                    'from' => $currentStart->format('Y-m-d'),
                    'to' => $currentEnd->format('Y-m-d'),
                    'error' => $e->getMessage(),
                ]);

                // Continue with next chunk even if this one fails
            }

            $currentStart = $currentEnd->copy()->addDay();
        }

        return $syncLogs;
    }

    /**
     * Get sync statistics for a credential
     */
    public function getSyncStatistics(BankingCredential $credential): array
    {
        return [
            'total_transactions' => $credential->transactions()->count(),
            'incoming_transactions' => $credential->transactions()->incoming()->count(),
            'outgoing_transactions' => $credential->transactions()->outgoing()->count(),
            'unmatched_transactions' => $credential->transactions()->unmatched()->count(),
            'matched_transactions' => $credential->transactions()->matched()->count(),
            'last_sync' => $credential->last_successful_sync_at,
            'sync_logs_count' => $credential->syncLogs()->count(),
            'successful_syncs' => $credential->syncLogs()->success()->count(),
            'failed_syncs' => $credential->syncLogs()->failed()->count(),
            'total_amount_incoming' => $credential->transactions()->incoming()->sum('amount'),
            'total_amount_outgoing' => $credential->transactions()->outgoing()->sum('amount'),
        ];
    }
}
