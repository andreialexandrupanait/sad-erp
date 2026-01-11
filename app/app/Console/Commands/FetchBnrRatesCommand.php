<?php

namespace App\Console\Commands;

use App\Services\Currency\BnrExchangeRateService;
use Illuminate\Console\Command;

class FetchBnrRatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bnr:fetch-rates {--force : Force fetch even if already fetched today}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch exchange rates from National Bank of Romania (BNR)';

    /**
     * Execute the console command.
     */
    public function handle(BnrExchangeRateService $bnrService): int
    {
        $this->info('Fetching BNR exchange rates...');

        // Check if we already have today's rate
        if (!$this->option('force') && $bnrService->hasTodayRate()) {
            $this->info('Today\'s rates already fetched. Use --force to refetch.');
            return self::SUCCESS;
        }

        $result = $bnrService->fetchTodayRates();

        if ($result['success']) {
            $this->info('Rates fetched successfully for date: ' . $result['date']);
            $this->table(
                ['Currency', 'Rate (RON)'],
                collect($result['rates'])
                    ->filter(fn($rate, $currency) => in_array($currency, ['EUR', 'USD', 'GBP']))
                    ->map(fn($rate, $currency) => [$currency, number_format($rate, 4)])
                    ->values()
                    ->toArray()
            );
            return self::SUCCESS;
        }

        $this->error('Failed to fetch rates: ' . ($result['error'] ?? 'Unknown error'));
        return self::FAILURE;
    }
}
