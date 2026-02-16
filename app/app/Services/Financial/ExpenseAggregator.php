<?php

namespace App\Services\Financial;

use App\Models\FinancialExpense;
use App\Services\Concerns\HasOrganizationCache;
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
    use HasOrganizationCache;

    private const CACHE_TTL = 600; // 10 minutes

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
            function () use ($year, $limit) {
                // Get aggregated data grouped by parent category
                // Uses COALESCE to group by parent_id if it exists, otherwise by the category's own id
                $results = FinancialExpense::forYear($year)
                    ->whereNotNull('category_option_id')
                    ->join('settings_options', 'financial_expenses.category_option_id', '=', 'settings_options.id')
                    ->select(
                        DB::raw('COALESCE(settings_options.parent_id, settings_options.id) as parent_category_id'),
                        DB::raw('SUM(financial_expenses.amount) as total'),
                        DB::raw('COUNT(*) as count')
                    )
                    ->groupBy(DB::raw('COALESCE(settings_options.parent_id, settings_options.id)'))
                    ->orderByDesc('total')
                    ->limit($limit)
                    ->get();

                // Load parent categories separately to avoid N+1
                $parentCategoryIds = $results->pluck('parent_category_id')->filter();
                $categories = \App\Models\SettingOption::whereIn('id', $parentCategoryIds)->get()->keyBy('id');

                // Map parent categories to results and set category_option_id for compatibility
                $results->each(function($r) use ($categories) {
                    $r->category_option_id = $r->parent_category_id;
                    $r->setRelation('category', $categories->get($r->parent_category_id));
                });

                return $results;
            }
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
