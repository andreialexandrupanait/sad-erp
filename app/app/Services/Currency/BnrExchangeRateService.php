<?php

namespace App\Services\Currency;

use App\Models\ExchangeRate;
use App\Models\Organization;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * BNR Exchange Rate Service
 *
 * Fetches exchange rates from the National Bank of Romania (BNR) XML feed.
 * Stores rates in the exchange_rates table with source = 'bnr'.
 */
class BnrExchangeRateService
{
    /**
     * BNR XML feed URL
     */
    protected const BNR_URL = 'https://www.bnr.ro/nbrfxrates.xml';

    /**
     * Cache TTL for rates (1 hour)
     */
    protected const CACHE_TTL = 3600;

    /**
     * Fetch and store today's rates from BNR
     */
    public function fetchTodayRates(?int $organizationId = null): array
    {
        try {
            $response = Http::retry(3, 100, throw: false)
                ->timeout(10)
                ->get(self::BNR_URL);

            if (!$response->successful()) {
                Log::error('BNR rate fetch failed', ['status' => $response->status()]);
                return ['success' => false, 'error' => 'Failed to fetch BNR rates (status: ' . $response->status() . ')'];
            }

            $xml = simplexml_load_string($response->body());
            if (!$xml) {
                Log::error('BNR XML parse failed');
                return ['success' => false, 'error' => 'Failed to parse BNR XML'];
            }

            // Get the date from BNR
            $cube = $xml->Body->Cube;
            $rateDate = (string) $cube['date'];

            // Parse rates
            $rates = [];
            foreach ($cube->Rate as $rate) {
                $currency = (string) $rate['currency'];
                $multiplier = (int) ($rate['multiplier'] ?? 1);
                $value = (float) $rate / $multiplier;

                $rates[$currency] = $value;
            }

            // Store EUR rate (primary interest)
            if (isset($rates['EUR'])) {
                $this->storeRate('EUR', 'RON', $rates['EUR'], $rateDate, $organizationId);
            }

            // Also store USD and GBP if available
            if (isset($rates['USD'])) {
                $this->storeRate('USD', 'RON', $rates['USD'], $rateDate, $organizationId);
            }

            if (isset($rates['GBP'])) {
                $this->storeRate('GBP', 'RON', $rates['GBP'], $rateDate, $organizationId);
            }

            // Clear cache
            $this->clearCache();

            Log::info('BNR rates fetched successfully', [
                'date' => $rateDate,
                'EUR' => $rates['EUR'] ?? null,
            ]);

            return [
                'success' => true,
                'date' => $rateDate,
                'rates' => $rates,
            ];

        } catch (\Exception $e) {
            Log::error('BNR rate fetch exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get rate for a specific date, fetching from BNR if not in database
     */
    public function getRate(string $from, string $to, ?Carbon $date = null, ?int $organizationId = null): ?float
    {
        $date = $date ?? now();
        $cacheKey = "bnr_rate_{$from}_{$to}_" . $date->format('Y-m-d');

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($from, $to, $date, $organizationId) {
            // First try to get from database
            $rate = ExchangeRate::withoutGlobalScopes()
                ->when($organizationId, fn($q) => $q->where('organization_id', $organizationId))
                ->where('from_currency', strtoupper($from))
                ->where('to_currency', strtoupper($to))
                ->where('effective_date', '<=', $date)
                ->orderBy('effective_date', 'desc')
                ->first();

            if ($rate) {
                return (float) $rate->rate;
            }

            // If not found and date is today, try fetching from BNR
            if ($date->isToday()) {
                $result = $this->fetchTodayRates($organizationId);
                if ($result['success'] && isset($result['rates'][strtoupper($from)])) {
                    return (float) $result['rates'][strtoupper($from)];
                }
            }

            return null;
        });
    }

    /**
     * Get EUR to RON rate for a specific date
     */
    public function getEurRate(?Carbon $date = null, ?int $organizationId = null): ?float
    {
        return $this->getRate('EUR', 'RON', $date, $organizationId);
    }

    /**
     * Convert EUR to RON
     */
    public function convertEurToRon(float $eurAmount, ?Carbon $date = null, ?int $organizationId = null): ?array
    {
        $rate = $this->getEurRate($date, $organizationId);

        if ($rate === null) {
            return null;
        }

        return [
            'amount_eur' => $eurAmount,
            'amount_ron' => round($eurAmount * $rate, 2),
            'exchange_rate' => $rate,
            'date' => $date ?? now(),
        ];
    }

    /**
     * Store a rate in the database
     */
    protected function storeRate(string $from, string $to, float $rate, string $date, ?int $organizationId = null): ExchangeRate
    {
        // If no organization provided, store for all organizations
        if ($organizationId) {
            return ExchangeRate::withoutGlobalScopes()->updateOrCreate(
                [
                    'organization_id' => $organizationId,
                    'from_currency' => strtoupper($from),
                    'to_currency' => strtoupper($to),
                    'effective_date' => $date,
                ],
                [
                    'rate' => $rate,
                    'source' => 'bnr',
                ]
            );
        }

        // Store for all organizations
        $organizations = Organization::all();
        $storedRate = null;

        foreach ($organizations as $org) {
            $storedRate = ExchangeRate::withoutGlobalScopes()->updateOrCreate(
                [
                    'organization_id' => $org->id,
                    'from_currency' => strtoupper($from),
                    'to_currency' => strtoupper($to),
                    'effective_date' => $date,
                ],
                [
                    'rate' => $rate,
                    'source' => 'bnr',
                ]
            );
        }

        return $storedRate;
    }

    /**
     * Clear rate cache
     */
    protected function clearCache(): void
    {
        // Clear all BNR rate cache keys
        Cache::forget('bnr_rate_EUR_RON_' . now()->format('Y-m-d'));
        Cache::forget('bnr_rate_USD_RON_' . now()->format('Y-m-d'));
        Cache::forget('bnr_rate_GBP_RON_' . now()->format('Y-m-d'));
    }

    /**
     * Get historical rate (with fallback to previous day if not available)
     */
    public function getHistoricalRate(string $from, string $to, Carbon $date, ?int $organizationId = null): ?float
    {
        // Try to get rate for the exact date
        $rate = ExchangeRate::withoutGlobalScopes()
            ->when($organizationId, fn($q) => $q->where('organization_id', $organizationId))
            ->where('from_currency', strtoupper($from))
            ->where('to_currency', strtoupper($to))
            ->where('effective_date', '<=', $date)
            ->orderBy('effective_date', 'desc')
            ->first();

        return $rate ? (float) $rate->rate : null;
    }

    /**
     * Check if we have a rate for today
     */
    public function hasTodayRate(string $from = 'EUR', string $to = 'RON', ?int $organizationId = null): bool
    {
        return ExchangeRate::withoutGlobalScopes()
            ->when($organizationId, fn($q) => $q->where('organization_id', $organizationId))
            ->where('from_currency', strtoupper($from))
            ->where('to_currency', strtoupper($to))
            ->where('effective_date', now()->toDateString())
            ->exists();
    }
}
