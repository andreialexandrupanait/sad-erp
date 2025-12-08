<?php

namespace App\Console\Commands;

use App\Models\BankingCredential;
use App\Services\Banking\TransactionImportService;
use Illuminate\Console\Command;

class ImportHistoricalTransactions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'banking:import-historical
                            {credential : The credential ID to import for}
                            {--from= : Start date (Y-m-d format, default from config)}
                            {--to= : End date (Y-m-d format, default today)}';

    /**
     * The console command description.
     */
    protected $description = 'Import historical bank transactions for a credential';

    /**
     * Execute the console command.
     */
    public function handle(TransactionImportService $importService): int
    {
        $credentialId = $this->argument('credential');
        $credential = BankingCredential::find($credentialId);

        if (!$credential) {
            $this->error("Credential ID {$credentialId} not found");
            return self::FAILURE;
        }

        if (!$credential->canSync()) {
            $this->error("Credential {$credential->id} cannot sync (status: {$credential->status})");
            return self::FAILURE;
        }

        $startDate = $this->option('from') ?? config('banking.sync.historical_start_date', '2019-10-01');
        $endDate = $this->option('to') ?? now()->format('Y-m-d');

        $this->info("Starting historical import for {$credential->account_iban}");
        $this->info("Date range: {$startDate} to {$endDate}");

        if (!$this->confirm('This may take a while and consume API quota. Continue?', true)) {
            $this->info('Import cancelled');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar();
        $bar->start();

        try {
            $syncLogs = $importService->syncHistoricalTransactions($credential, $startDate);

            $bar->finish();
            $this->newLine(2);

            // Summary
            $totalNew = 0;
            $totalUpdated = 0;
            $totalFetched = 0;

            foreach ($syncLogs as $log) {
                $totalNew += $log->transactions_new;
                $totalUpdated += $log->transactions_updated;
                $totalFetched += $log->transactions_fetched;
            }

            $this->info('Historical import completed!');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Sync batches', count($syncLogs)],
                    ['Total fetched', $totalFetched],
                    ['New transactions', $totalNew],
                    ['Updated transactions', $totalUpdated],
                ]
            );

            return self::SUCCESS;

        } catch (\Exception $e) {
            $bar->finish();
            $this->newLine(2);
            $this->error('Historical import failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
