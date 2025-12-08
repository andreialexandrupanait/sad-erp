<?php

namespace App\Services\Financial;

use App\Models\FinancialRevenue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * Revenue Aggregator Service
 *
 * Handles all revenue-related aggregations, calculations, and data retrieval
 * with caching support.
 */
class RevenueAggregator
{
    private const CACHE_TTL = 600; // 10 minutes

    /**
     * Get cache key with organization prefix
     */
    private function cacheKey(string $key): string
    {
        $orgId = auth()->user()->organization_id ?? 'default';
        return "org.{$orgId}.{$key}";
    }

    /**
     * Get yearly revenue totals by currency
     *
     * @param int $year
     * @return Collection
     */
    public function getYearlyTotals(int $year): Collection
    {
        return Cache::remember(
            $this->cacheKey("financial.revenues.totals.{$year}"),
            self::CACHE_TTL,
            fn() => FinancialRevenue::forYear($year)
                ->select('currency', DB::raw('SUM(amount) as total'))
                ->groupBy('currency')
                ->pluck('total', 'currency')
        );
    }

    /**
     * Get monthly revenue data for a year (specific currency)
     *
     * @param int $year
     * @param string $currency
     * @return Collection
     */
    public function getMonthlyData(int $year, string $currency = 'RON'): Collection
    {
        return Cache::remember(
            $this->cacheKey("financial.revenues.monthly.{$year}.{$currency}"),
            self::CACHE_TTL,
            fn() => FinancialRevenue::forYear($year)
                ->where('currency', $currency)
                ->select('month', DB::raw('SUM(amount) as total'))
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->mapWithKeys(fn($item) => [$item->month => $item->total])
        );
    }

    /**
     * Get revenue totals for all years grouped by year and currency
     *
     * @return Collection
     */
    public function getAllYearsTotals(): Collection
    {
        return Cache::remember(
            $this->cacheKey("financial.revenues.all_years"),
            self::CACHE_TTL,
            fn() => FinancialRevenue::select('year', 'currency', DB::raw('SUM(amount) as total'))
                ->groupBy('year', 'currency')
                ->orderBy('year', 'desc')
                ->get()
                ->groupBy('year')
        );
    }

    /**
     * Get revenue count for a specific year
     *
     * @param int $year
     * @return int
     */
    public function getYearlyCount(int $year): int
    {
        return Cache::remember(
            $this->cacheKey("financial.revenues.count.{$year}"),
            self::CACHE_TTL,
            fn() => FinancialRevenue::forYear($year)->count()
        );
    }

    /**
     * Get client revenue breakdown for a year
     *
     * @param int $year
     * @param int $limit
     * @return Collection
     */
    public function getClientBreakdown(int $year, int $limit = 10): Collection
    {
        return Cache::remember(
            $this->cacheKey("financial.revenues.clients.{$year}"),
            self::CACHE_TTL,
            fn() => FinancialRevenue::forYear($year)
                ->whereNotNull('client_id')
                ->select('client_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
                ->groupBy('client_id')
                ->with('client')
                ->get()
                ->sortByDesc('total')
                ->take($limit)
        );
    }

    /**
     * Clear revenue caches
     *
     * @param int|null $year
     * @return void
     */
    public function clearCache(?int $year = null): void
    {
        if ($year) {
            Cache::forget($this->cacheKey("financial.revenues.totals.{$year}"));
            Cache::forget($this->cacheKey("financial.revenues.monthly.{$year}.RON"));
            Cache::forget($this->cacheKey("financial.revenues.monthly.{$year}.EUR"));
            Cache::forget($this->cacheKey("financial.revenues.monthly.{$year}.USD"));
            Cache::forget($this->cacheKey("financial.revenues.count.{$year}"));
            Cache::forget($this->cacheKey("financial.revenues.clients.{$year}"));
        } else {
            Cache::forget($this->cacheKey("financial.revenues.all_years"));
        }
    }
}
