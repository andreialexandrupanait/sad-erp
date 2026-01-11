<?php

namespace App\Console\Commands;

use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;
use App\Models\Subscription;
use App\Services\Currency\BnrExchangeRateService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Migrate existing EUR records to the new currency structure.
 *
 * For records where:
 * - currency = 'EUR'
 * - amount_eur IS NULL (not yet migrated)
 *
 * Strategy:
 * 1. Check smartbill_raw_data for RON value
 * 2. If found: Set amount_eur = current amount, amount = RON value from raw data
 * 3. If not found and --use-bnr: Convert using BNR historical rate
 * 4. If not found and no --use-bnr: Log for manual review, leave unchanged
 *
 * Safety: Always run with --dry-run first!
 */
class MigrateEurRevenuesCommand extends Command
{
    protected $signature = 'currency:migrate-eur
        {--dry-run : Preview changes without applying}
        {--batch=100 : Number of records to process per batch}
        {--type=all : Type to migrate: revenues, expenses, subscriptions, or all}
        {--use-bnr : Use BNR historical rates for records without RON value}
        {--force : Skip confirmation prompt}';

    protected $description = 'Migrate existing EUR records to the new currency structure (RON primary, EUR reference)';

    private int $totalProcessed = 0;
    private int $totalMigrated = 0;
    private int $totalSkipped = 0;
    private int $totalConvertedBnr = 0;
    private array $skippedRecords = [];
    private ?BnrExchangeRateService $bnrService = null;

    public function handle(BnrExchangeRateService $bnrService): int
    {
        $this->bnrService = $bnrService;

        $dryRun = $this->option('dry-run');
        $batchSize = (int) $this->option('batch');
        $type = $this->option('type');
        $useBnr = $this->option('use-bnr');

        $this->info($dryRun ? '=== DRY RUN MODE ===' : '=== MIGRATION MODE ===');
        if ($useBnr) {
            $this->warn('Using BNR historical rates for conversion.');
        }
        $this->newLine();

        if (!$dryRun && !$this->option('force')) {
            if (!$this->confirm('This will modify existing records. Continue?')) {
                $this->warn('Migration cancelled.');
                return self::SUCCESS;
            }
        }

        // Migrate based on type
        if ($type === 'all' || $type === 'revenues') {
            $this->migrateRevenues($dryRun, $batchSize, $useBnr);
        }

        if ($type === 'all' || $type === 'expenses') {
            $this->migrateExpenses($dryRun, $batchSize, $useBnr);
        }

        if ($type === 'all' || $type === 'subscriptions') {
            $this->migrateSubscriptions($dryRun, $batchSize);
        }

        // Summary
        $this->newLine();
        $this->info('=== SUMMARY ===');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Processed', $this->totalProcessed],
                ['From SmartBill data', $this->totalMigrated - $this->totalConvertedBnr],
                ['Converted via BNR', $this->totalConvertedBnr],
                ['Skipped (manual review)', $this->totalSkipped],
            ]
        );

        // Show skipped records
        if (!empty($this->skippedRecords)) {
            $this->newLine();
            $this->warn('Records requiring manual review:');
            $this->table(
                ['Type', 'ID', 'Document', 'EUR Amount', 'Date', 'Reason'],
                array_slice($this->skippedRecords, 0, 20)
            );

            if (count($this->skippedRecords) > 20) {
                $this->warn('... and ' . (count($this->skippedRecords) - 20) . ' more.');
            }

            Log::warning('EUR migration skipped records', [
                'count' => count($this->skippedRecords),
                'records' => $this->skippedRecords,
            ]);
        }

        return self::SUCCESS;
    }

    private function migrateRevenues(bool $dryRun, int $batchSize, bool $useBnr): void
    {
        $this->info('Processing Financial Revenues...');

        $query = FinancialRevenue::withoutGlobalScopes()
            ->where('currency', 'EUR')
            ->whereNull('amount_eur');

        $total = $query->count();
        $this->info("Found {$total} EUR revenues to process.");

        if ($total === 0) {
            return;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->chunkById($batchSize, function ($revenues) use ($dryRun, $bar, $useBnr) {
            foreach ($revenues as $revenue) {
                $this->processRevenue($revenue, $dryRun, $useBnr);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
    }

    private function processRevenue(FinancialRevenue $revenue, bool $dryRun, bool $useBnr): void
    {
        $this->totalProcessed++;

        // Try to get RON value from smartbill_raw_data
        $ronValue = $this->extractRonFromSmartbillData($revenue->smartbill_raw_data);
        $source = 'smartbill';

        // If no RON value and --use-bnr, try BNR conversion
        if ($ronValue === null && $useBnr && $revenue->occurred_at) {
            $rate = $this->bnrService->getHistoricalRate('EUR', 'RON', Carbon::parse($revenue->occurred_at));
            if ($rate) {
                $ronValue = round($revenue->amount * $rate, 2);
                $source = 'bnr';
                $this->totalConvertedBnr++;
            }
        }

        if ($ronValue !== null) {
            // Found RON value - migrate
            if (!$dryRun) {
                $revenue->update([
                    'amount_eur' => $revenue->amount,
                    'amount' => $ronValue,
                    'exchange_rate' => $ronValue / $revenue->amount,
                ]);

                Log::info('Migrated EUR revenue', [
                    'id' => $revenue->id,
                    'eur' => $revenue->amount,
                    'ron' => $ronValue,
                    'source' => $source,
                ]);
            }

            $this->totalMigrated++;
        } else {
            // No RON value found - skip
            $this->totalSkipped++;
            $this->skippedRecords[] = [
                'Revenue',
                $revenue->id,
                substr($revenue->document_name ?? '-', 0, 30),
                $revenue->amount,
                $revenue->occurred_at?->format('Y-m-d') ?? '-',
                $useBnr ? 'No BNR rate available' : 'Use --use-bnr to convert',
            ];
        }
    }

    private function migrateExpenses(bool $dryRun, int $batchSize, bool $useBnr): void
    {
        $this->info('Processing Financial Expenses...');

        $query = FinancialExpense::withoutGlobalScopes()
            ->where('currency', 'EUR')
            ->whereNull('amount_eur');

        $total = $query->count();
        $this->info("Found {$total} EUR expenses to process.");

        if ($total === 0) {
            return;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->chunkById($batchSize, function ($expenses) use ($dryRun, $bar, $useBnr) {
            foreach ($expenses as $expense) {
                $this->processExpense($expense, $dryRun, $useBnr);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
    }

    private function processExpense(FinancialExpense $expense, bool $dryRun, bool $useBnr): void
    {
        $this->totalProcessed++;

        $ronValue = null;
        $source = null;

        // For expenses, try BNR conversion if enabled
        if ($useBnr && $expense->occurred_at) {
            $rate = $this->bnrService->getHistoricalRate('EUR', 'RON', Carbon::parse($expense->occurred_at));
            if ($rate) {
                $ronValue = round($expense->amount * $rate, 2);
                $source = 'bnr';
                $this->totalConvertedBnr++;
            }
        }

        if ($ronValue !== null) {
            if (!$dryRun) {
                $expense->update([
                    'amount_eur' => $expense->amount,
                    'amount' => $ronValue,
                    'exchange_rate' => $ronValue / $expense->amount,
                ]);

                Log::info('Migrated EUR expense', [
                    'id' => $expense->id,
                    'eur' => $expense->amount,
                    'ron' => $ronValue,
                    'source' => $source,
                ]);
            }

            $this->totalMigrated++;
        } else {
            $this->totalSkipped++;
            $this->skippedRecords[] = [
                'Expense',
                $expense->id,
                substr($expense->document_name ?? '-', 0, 30),
                $expense->amount,
                $expense->occurred_at?->format('Y-m-d') ?? '-',
                $useBnr ? 'No BNR rate available' : 'Use --use-bnr to convert',
            ];
        }
    }

    private function migrateSubscriptions(bool $dryRun, int $batchSize): void
    {
        $this->info('Processing Subscriptions...');

        $query = Subscription::withoutGlobalScopes()
            ->where('currency', 'EUR')
            ->whereNull('price_eur');

        $total = $query->count();
        $this->info("Found {$total} EUR subscriptions to process.");

        if ($total === 0) {
            return;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->chunkById($batchSize, function ($subscriptions) use ($dryRun, $bar) {
            foreach ($subscriptions as $subscription) {
                $this->processSubscription($subscription, $dryRun);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
    }

    private function processSubscription(Subscription $subscription, bool $dryRun): void
    {
        $this->totalProcessed++;

        // For subscriptions, we just set price_eur = price (keep as EUR)
        // User can later decide to convert to RON if needed
        if (!$dryRun) {
            $subscription->update([
                'price_eur' => $subscription->price,
            ]);

            Log::info('Migrated EUR subscription', [
                'id' => $subscription->id,
                'vendor' => $subscription->vendor_name,
                'price_eur' => $subscription->price,
            ]);
        }

        $this->totalMigrated++;
    }

    /**
     * Extract RON value from SmartBill raw data JSON.
     */
    private function extractRonFromSmartbillData($rawData): ?float
    {
        if (empty($rawData)) {
            return null;
        }

        $data = is_string($rawData) ? json_decode($rawData, true) : $rawData;

        if (!is_array($data)) {
            return null;
        }

        $ronFields = [
            'Total Value(RON)',
            'total_value_ron',
            'totalValueRon',
            'value_ron',
            'ron_value',
            'total_ron',
        ];

        foreach ($ronFields as $field) {
            if (isset($data[$field]) && is_numeric($data[$field])) {
                return (float) $data[$field];
            }
        }

        return null;
    }
}
