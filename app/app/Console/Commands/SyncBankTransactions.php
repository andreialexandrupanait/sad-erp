<?php

namespace App\Console\Commands;

use App\Jobs\Banking\SyncBankTransactionsJob;
use App\Models\BankingCredential;
use Illuminate\Console\Command;

class SyncBankTransactions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'banking:sync-transactions
                            {--credential= : Specific credential ID to sync}
                            {--all : Sync all active credentials}
                            {--force : Force sync even if recently synced}';

    /**
     * The console command description.
     */
    protected $description = 'Sync bank transactions from connected accounts';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('credential')) {
            return $this->syncSpecificCredential((int) $this->option('credential'));
        }

        if ($this->option('all') || !$this->option('credential')) {
            return $this->syncAllCredentials();
        }

        return self::SUCCESS;
    }

    /**
     * Sync a specific credential
     */
    protected function syncSpecificCredential(int $credentialId): int
    {
        $credential = BankingCredential::find($credentialId);

        if (!$credential) {
            $this->error("Credential ID {$credentialId} not found");
            return self::FAILURE;
        }

        if (!$credential->canSync() && !$this->option('force')) {
            $this->warn("Credential {$credential->id} cannot sync (status: {$credential->status})");
            return self::FAILURE;
        }

        $this->info("Dispatching sync job for credential {$credential->id} ({$credential->account_iban})");

        SyncBankTransactionsJob::dispatch($credential, null, null, 'manual');

        $this->info('Sync job dispatched successfully');

        return self::SUCCESS;
    }

    /**
     * Sync all active credentials that need syncing
     */
    protected function syncAllCredentials(): int
    {
        $credentials = BankingCredential::needingSync()->get();

        if ($credentials->isEmpty()) {
            $this->info('No credentials need syncing');
            return self::SUCCESS;
        }

        $this->info("Found {$credentials->count()} credentials needing sync");

        $dispatched = 0;

        foreach ($credentials as $credential) {
            if (!$credential->canSync() && !$this->option('force')) {
                $this->warn("Skipping credential {$credential->id} (cannot sync)");
                continue;
            }

            $this->line("Dispatching sync for {$credential->account_iban}...");

            SyncBankTransactionsJob::dispatch($credential, null, null, 'scheduled');

            $dispatched++;
        }

        $this->info("Dispatched {$dispatched} sync jobs");

        return self::SUCCESS;
    }
}
