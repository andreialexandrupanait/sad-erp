<?php

namespace App\Services\Financial;

use App\Models\FinancialRevenue;
use App\Services\Concerns\HasOrganizationCache;
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
    use HasOrganizationCache;

    private const CACHE_TTL = 600; // 10 minutes

    /**
     * Get yearly revenue totals by currency
     * RON = sum of all amounts (including EUR converted to RON)
     * EUR = sum of amount_eur for EUR records (original EUR values)
     *
     * @param int $year
     * @return Collection
     */
    public function getYearlyTotals(int $year): Collection
    {
        return Cache::remember(
            $this->cacheKey("financial.revenues.totals.{$year}"),
            self::CACHE_TTL,
            function() use ($year) {
                // Total RON = sum of all amount fields (EUR records have RON in amount)
                $totalRon = FinancialRevenue::forYear($year)->sum("amount");
                
                // Total EUR = sum of amount_eur for EUR currency records
                $totalEur = FinancialRevenue::forYear($year)
                    ->where("currency", "EUR")
                    ->whereNotNull("amount_eur")
                    ->sum("amount_eur");
                
                return collect([
                    "RON" => $totalRon,
                    "EUR" => $totalEur,
                ]);
            }
        );
    }

    /**
     * Get monthly revenue data for a year (all currencies, amounts in RON)
     *
     * @param int $year
     * @return Collection
     */
    public function getMonthlyData(int $year): Collection
    {
        return Cache::remember(
            $this->cacheKey("financial.revenues.monthly.{$year}.all"),
            self::CACHE_TTL,
            fn() => FinancialRevenue::forYear($year)
                ->select("month", DB::raw("SUM(amount) as total"))
                ->groupBy("month")
                ->orderBy("month")
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
            function () use ($year, $limit) {
                // Get aggregated data
                $results = FinancialRevenue::forYear($year)
                    ->whereNotNull('client_id')
                    ->select('client_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
                    ->groupBy('client_id')
                    ->orderByDesc('total')
                    ->limit($limit)
                    ->get();

                // Load clients separately to avoid N+1
                $clientIds = $results->pluck('client_id')->filter();
                $clients = \App\Models\Client::whereIn('id', $clientIds)->get()->keyBy('id');

                // Map clients to results
                $results->each(fn($r) => $r->setRelation('client', $clients->get($r->client_id)));

                return $results;
            }
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
