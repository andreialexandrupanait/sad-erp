<?php

namespace App\Jobs\Banking;

use App\Models\BankingCredential;
use App\Services\Banking\TransactionImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncBankTransactionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public BankingCredential $credential;
    public ?string $dateFrom;
    public ?string $dateTo;
    public string $syncType;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 3600; // 1 hour

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        BankingCredential $credential,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        string $syncType = 'manual'
    ) {
        $this->credential = $credential;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->syncType = $syncType;
    }

    /**
     * Execute the job.
     */
    public function handle(TransactionImportService $importService): void
    {
        Log::info('Starting bank transaction sync job', [
            'credential_id' => $this->credential->id,
            'sync_type' => $this->syncType,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
        ]);

        try {
            // Check if credential can sync
            if (!$this->credential->canSync()) {
                Log::warning('Credential cannot sync', [
                    'credential_id' => $this->credential->id,
                    'status' => $this->credential->status,
                    'consent_valid' => $this->credential->isConsentValid(),
                ]);
                return;
            }

            // Perform sync
            $syncLog = $importService->syncTransactions(
                $this->credential,
                $this->dateFrom,
                $this->dateTo,
                $this->syncType
            );

            Log::info('Bank transaction sync job completed', [
                'credential_id' => $this->credential->id,
                'sync_log_id' => $syncLog->id,
                'transactions_fetched' => $syncLog->transactions_fetched,
                'transactions_new' => $syncLog->transactions_new,
            ]);

        } catch (\Exception $e) {
            Log::error('Bank transaction sync job failed', [
                'credential_id' => $this->credential->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Bank transaction sync job permanently failed', [
            'credential_id' => $this->credential->id,
            'error' => $exception->getMessage(),
        ]);

        // Mark credential as error if job fails permanently
        $this->credential->markSyncFailure('Job failed after ' . $this->tries . ' attempts: ' . $exception->getMessage());
    }
}
