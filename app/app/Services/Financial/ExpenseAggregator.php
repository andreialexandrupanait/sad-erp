<?php

namespace App\Services\Financial;

use App\Models\FinancialExpense;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * Expense Aggregator Service
 *
 * Handles all expense-related aggregations, calculations, and data retrieval
 * with caching support.
 */
class ExpenseAggregator
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
     * Get yearly expense totals by currency
     * RON = sum of all amounts (including EUR converted to RON)
     * EUR = sum of amount_eur for EUR records (original EUR values)
     *
     * @param int $year
     * @return Collection
     */
    public function getYearlyTotals(int $year): Collection
    {
        return Cache::remember(
            $this->cacheKey("financial.expenses.totals.{$year}"),
            self::CACHE_TTL,
            function() use ($year) {
                // Total RON = sum of all amount fields (EUR records have RON in amount)
                $totalRon = FinancialExpense::forYear($year)->sum("amount");
                
                // Total EUR = sum of amount_eur for EUR currency records
                $totalEur = FinancialExpense::forYear($year)
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
     * Get monthly expense data for a year (specific currency)
     *
     * @param int $year
     * @param string $currency
     * @return Collection
     */
    /**
     * Get monthly expense data for a year (all currencies, amounts in RON)
     *
     * @param int $year
     * @return Collection
     */
    public function getMonthlyData(int $year): Collection
    {
        return Cache::remember(
            $this->cacheKey("financial.expenses.monthly.{$year}.all"),
            self::CACHE_TTL,
            fn() => FinancialExpense::forYear($year)
                ->select("month", DB::raw("SUM(amount) as total"))
                ->groupBy("month")
                ->orderBy("month")
                ->get()
                ->mapWithKeys(fn($item) => [$item->month => $item->total])
        );
    }

    /**
     * Get category breakdown for expenses
     *
     * @param int $year
     * @param int $limit
     * @return Collection
     */
    public function getCategoryBreakdown(int $year, int $limit = 8): Collection
    {
        return Cache::remember(
            $this->cacheKey("financial.expenses.categories.{$year}"),
            self::CACHE_TTL,
            fn() => FinancialExpense::forYear($year)
                ->whereNotNull('category_option_id')
                ->select('category_option_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
                ->groupBy('category_option_id')
                ->orderByDesc('total')
                ->limit($limit)
                ->with('category')
                ->get()
        );
    }

    /**
     * Get expense totals for all years grouped by year and currency
     *
     * @return Collection
     */
    public function getAllYearsTotals(): Collection
    {
        return Cache::remember(
            $this->cacheKey("financial.expenses.all_years"),
            self::CACHE_TTL,
            fn() => FinancialExpense::select('year', 'currency', DB::raw('SUM(amount) as total'))
                ->groupBy('year', 'currency')
                ->orderBy('year', 'desc')
                ->get()
                ->groupBy('year')
        );
    }

    /**
     * Get expense count for a specific year
     *
     * @param int $year
     * @return int
     */
    public function getYearlyCount(int $year): int
    {
        return Cache::remember(
            $this->cacheKey("financial.expenses.count.{$year}"),
            self::CACHE_TTL,
            fn() => FinancialExpense::forYear($year)->count()
        );
    }

    /**
     * Clear expense caches
     *
     * @param int|null $year
     * @return void
     */
    public function clearCache(?int $year = null): void
    {
        if ($year) {
            Cache::forget($this->cacheKey("financial.expenses.totals.{$year}"));
            Cache::forget($this->cacheKey("financial.expenses.monthly.{$year}.RON"));
            Cache::forget($this->cacheKey("financial.expenses.monthly.{$year}.EUR"));
            Cache::forget($this->cacheKey("financial.expenses.monthly.{$year}.USD"));
            Cache::forget($this->cacheKey("financial.expenses.categories.{$year}"));
            Cache::forget($this->cacheKey("financial.expenses.count.{$year}"));
        } else {
            Cache::forget($this->cacheKey("financial.expenses.all_years"));
        }
    }
}
