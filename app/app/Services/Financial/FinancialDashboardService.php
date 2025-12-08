<?php

namespace App\Services\Financial;

use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * Financial Dashboard Service
 *
 * Orchestrates financial dashboard calculations and aggregations.
 * Delegates to specialized aggregators for revenue, expenses, and chart data.
 */
class FinancialDashboardService
{
    // Cache TTL in seconds (10 minutes)
    private const CACHE_TTL = 600;

    protected RevenueAggregator $revenueAggregator;
    protected ExpenseAggregator $expenseAggregator;
    protected ChartDataBuilder $chartBuilder;

    public function __construct(
        RevenueAggregator $revenueAggregator,
        ExpenseAggregator $expenseAggregator,
        ChartDataBuilder $chartBuilder
    ) {
        $this->revenueAggregator = $revenueAggregator;
        $this->expenseAggregator = $expenseAggregator;
        $this->chartBuilder = $chartBuilder;
    }

    /**
     * Get cache key with organization prefix.
     */
    private function cacheKey(string $key): string
    {
        $orgId = auth()->user()->organization_id ?? 'default';
        return "org.{$orgId}.{$key}";
    }

    /**
     * Get yearly revenue totals by currency.
     * Delegates to RevenueAggregator.
     */
    public function getYearlyRevenueTotals(int $year): Collection
    {
        return $this->revenueAggregator->getYearlyTotals($year);
    }

    /**
     * Get yearly expense totals by currency.
     * Delegates to ExpenseAggregator.
     */
    public function getYearlyExpenseTotals(int $year): Collection
    {
        return $this->expenseAggregator->getYearlyTotals($year);
    }

    /**
     * Get monthly revenue data for a year (RON only).
     * Delegates to RevenueAggregator.
     */
    public function getMonthlyRevenueData(int $year): Collection
    {
        return $this->revenueAggregator->getMonthlyData($year);
    }

    /**
     * Get monthly expense data for a year (RON only).
     * Delegates to ExpenseAggregator.
     */
    public function getMonthlyExpenseData(int $year): Collection
    {
        return $this->expenseAggregator->getMonthlyData($year);
    }

    /**
     * Get category breakdown for expenses.
     * Delegates to ExpenseAggregator.
     */
    public function getExpenseCategoryBreakdown(int $year, int $limit = 8): Collection
    {
        return $this->expenseAggregator->getCategoryBreakdown($year, $limit);
    }

    /**
     * Prepare chart data for all 12 months.
     * Delegates to ChartDataBuilder.
     */
    public function prepareChartData(Collection $monthlyRevenues, Collection $monthlyExpenses): array
    {
        return $this->chartBuilder->prepareChartData($monthlyRevenues, $monthlyExpenses);
    }

    /**
     * Calculate common max value for chart scaling.
     * Delegates to ChartDataBuilder.
     */
    public function calculateChartMaxValue(array $chartRevenues, array $chartExpenses): float
    {
        return $this->chartBuilder->calculateChartMaxValue($chartRevenues, $chartExpenses);
    }

    /**
     * Build monthly breakdown table data.
     * Delegates to ChartDataBuilder.
     */
    public function buildMonthlyBreakdown(Collection $monthlyRevenues, Collection $monthlyExpenses): array
    {
        return $this->chartBuilder->buildMonthlyBreakdown($monthlyRevenues, $monthlyExpenses);
    }

    /**
     * Get available years for filter (2019 to present).
     */
    public function getAvailableYears(): Collection
    {
        $currentYear = now()->year;
        return collect(range(2019, $currentYear))->reverse()->values();
    }

    /**
     * Get available years from database records.
     */
    public function getAvailableYearsFromData(): Collection
    {
        return Cache::remember(
            $this->cacheKey('financial.available_years'),
            self::CACHE_TTL,
            function () {
                $revenueYears = FinancialRevenue::selectRaw('DISTINCT year')->pluck('year');
                $expenseYears = FinancialExpense::selectRaw('DISTINCT year')->pluck('year');
                $years = $revenueYears->merge($expenseYears)->unique()->sortDesc()->values();
                return $years->isEmpty() ? collect([now()->year]) : $years;
            }
        );
    }

    /**
     * Calculate profit margin percentage.
     */
    public function calculateProfitMargin(float $revenue, float $profit): float
    {
        return $revenue > 0 ? round(($profit / $revenue) * 100, 1) : 0;
    }

    /**
     * Get cashflow data for a year.
     */
    public function getCashflowData(int $year): array
    {
        // Get monthly revenues by currency
        $monthlyRevenues = Cache::remember(
            $this->cacheKey("financial.cashflow.revenues.{$year}"),
            self::CACHE_TTL,
            fn() => FinancialRevenue::forYear($year)
                ->select('month', 'currency', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
                ->groupBy('month', 'currency')
                ->get()
        );

        // Get monthly expenses by currency
        $monthlyExpenses = Cache::remember(
            $this->cacheKey("financial.cashflow.expenses.{$year}"),
            self::CACHE_TTL,
            fn() => FinancialExpense::forYear($year)
                ->select('month', 'currency', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
                ->groupBy('month', 'currency')
                ->get()
        );

        // Pre-index for O(1) lookup
        $revenueIndex = $monthlyRevenues->groupBy(fn($item) => $item->month . '_' . $item->currency);
        $expenseIndex = $monthlyExpenses->groupBy(fn($item) => $item->month . '_' . $item->currency);
        $revenueCountIndex = $monthlyRevenues->groupBy('month');
        $expenseCountIndex = $monthlyExpenses->groupBy('month');

        $cashflowData = [];
        $runningBalanceRON = 0;
        $runningBalanceEUR = 0;

        for ($month = 1; $month <= 12; $month++) {
            $revenueRON = $revenueIndex->get("{$month}_RON")?->first()?->total ?? 0;
            $revenueEUR = $revenueIndex->get("{$month}_EUR")?->first()?->total ?? 0;
            $expenseRON = $expenseIndex->get("{$month}_RON")?->first()?->total ?? 0;
            $expenseEUR = $expenseIndex->get("{$month}_EUR")?->first()?->total ?? 0;

            $netRON = $revenueRON - $expenseRON;
            $netEUR = $revenueEUR - $expenseEUR;
            $runningBalanceRON += $netRON;
            $runningBalanceEUR += $netEUR;

            $revenueCount = $revenueCountIndex->get($month)?->sum('count') ?? 0;
            $expenseCount = $expenseCountIndex->get($month)?->sum('count') ?? 0;

            $cashflowData[] = [
                'month' => $month,
                'month_name' => $this->chartBuilder->getFullMonthName($month),
                'revenue_ron' => $revenueRON,
                'revenue_eur' => $revenueEUR,
                'expense_ron' => $expenseRON,
                'expense_eur' => $expenseEUR,
                'net_ron' => $netRON,
                'net_eur' => $netEUR,
                'balance_ron' => $runningBalanceRON,
                'balance_eur' => $runningBalanceEUR,
                'revenue_count' => $revenueCount,
                'expense_count' => $expenseCount,
                'is_current_month' => $month == now()->month && $year == now()->year,
                'is_future' => ($year == now()->year && $month > now()->month) || $year > now()->year,
            ];
        }

        return $cashflowData;
    }

    /**
     * Calculate cashflow totals from data.
     */
    public function calculateCashflowTotals(array $cashflowData): array
    {
        $collection = collect($cashflowData);

        return [
            'revenue_ron' => $collection->sum('revenue_ron'),
            'revenue_eur' => $collection->sum('revenue_eur'),
            'expense_ron' => $collection->sum('expense_ron'),
            'expense_eur' => $collection->sum('expense_eur'),
            'net_ron' => $collection->sum('net_ron'),
            'net_eur' => $collection->sum('net_eur'),
        ];
    }

    /**
     * Prepare cashflow chart data.
     * Delegates to ChartDataBuilder.
     */
    public function prepareCashflowChartData(array $cashflowData, int $year): array
    {
        return $this->chartBuilder->prepareCashflowChartData($cashflowData, $year);
    }

    /**
     * Get yearly report data.
     * Orchestrates aggregators to build comprehensive yearly summary.
     */
    public function getYearlyReportData(): array
    {
        $availableYears = $this->getAvailableYearsFromData();

        // Get all years revenue totals
        $allYearsRevenue = $this->revenueAggregator->getAllYearsTotals();
        $allYearsExpense = $this->expenseAggregator->getAllYearsTotals();

        // Build yearly summary
        $yearlySummary = [];
        foreach ($availableYears as $year) {
            $yearRevenues = $allYearsRevenue->get($year, collect());
            $yearExpenses = $allYearsExpense->get($year, collect());

            $revenueRON = $yearRevenues->where('currency', 'RON')->first()?->total ?? 0;
            $revenueEUR = $yearRevenues->where('currency', 'EUR')->first()?->total ?? 0;
            $expenseRON = $yearExpenses->where('currency', 'RON')->first()?->total ?? 0;
            $expenseEUR = $yearExpenses->where('currency', 'EUR')->first()?->total ?? 0;
            $profitRON = $revenueRON - $expenseRON;
            $profitEUR = $revenueEUR - $expenseEUR;

            $yearlySummary[$year] = [
                'revenue_ron' => $revenueRON,
                'revenue_eur' => $revenueEUR,
                'expense_ron' => $expenseRON,
                'expense_eur' => $expenseEUR,
                'profit_ron' => $profitRON,
                'profit_eur' => $profitEUR,
                'margin_percent' => $this->calculateProfitMargin($revenueRON, $profitRON),
                'client_count' => $yearRevenues->max('client_count') ?? 0,
                'invoice_count' => $yearRevenues->sum('invoice_count'),
            ];
        }

        // Calculate YoY growth
        $sortedYears = collect($yearlySummary)->keys()->sort()->values();
        foreach ($sortedYears as $year) {
            $prevYear = $year - 1;
            if (isset($yearlySummary[$prevYear]) && $yearlySummary[$prevYear]['revenue_ron'] > 0) {
                $yearlySummary[$year]['yoy_growth'] = round(
                    (($yearlySummary[$year]['revenue_ron'] - $yearlySummary[$prevYear]['revenue_ron'])
                        / $yearlySummary[$prevYear]['revenue_ron']) * 100,
                    1
                );
            } else {
                $yearlySummary[$year]['yoy_growth'] = null;
            }
        }

        return [
            'available_years' => $availableYears,
            'yearly_summary' => $yearlySummary,
            'sorted_years' => $sortedYears,
        ];
    }

    /**
     * Calculate totals across all years.
     */
    public function calculateAllTimeTotals(array $yearlySummary): array
    {
        $totals = [
            'revenue_ron' => collect($yearlySummary)->sum('revenue_ron'),
            'revenue_eur' => collect($yearlySummary)->sum('revenue_eur'),
            'expense_ron' => collect($yearlySummary)->sum('expense_ron'),
            'expense_eur' => collect($yearlySummary)->sum('expense_eur'),
            'profit_ron' => collect($yearlySummary)->sum('profit_ron'),
            'profit_eur' => collect($yearlySummary)->sum('profit_eur'),
            'client_count' => FinancialRevenue::whereNotNull('client_id')->distinct('client_id')->count('client_id'),
            'invoice_count' => FinancialRevenue::count(),
        ];

        $totals['margin_percent'] = $this->calculateProfitMargin($totals['revenue_ron'], $totals['profit_ron']);

        return $totals;
    }

    /**
     * Get analytics data for yearly report.
     * Orchestrates aggregators and adds business intelligence.
     */
    public function getAnalytics(array $yearlySummary, Collection $availableYears): array
    {
        $bestYear = collect($yearlySummary)->sortByDesc('profit_ron')->keys()->first();
        $worstYear = collect($yearlySummary)->sortBy('profit_ron')->keys()->first();

        // Get top clients from revenue aggregator (all time)
        $topClients = Cache::remember(
            $this->cacheKey('financial.analytics.top_clients'),
            self::CACHE_TTL,
            fn() => FinancialRevenue::with('client')
                ->whereNotNull('client_id')
                ->select('client_id', DB::raw('SUM(amount) as total'))
                ->groupBy('client_id')
                ->orderByDesc('total')
                ->take(10)
                ->get()
        );

        // Get expense category breakdown (all time)
        $expenseByCategory = Cache::remember(
            $this->cacheKey('financial.analytics.expense_categories'),
            self::CACHE_TTL,
            fn() => FinancialExpense::with('category')
                ->whereNotNull('category_option_id')
                ->select('category_option_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
                ->groupBy('category_option_id')
                ->orderByDesc('total')
                ->take(10)
                ->get()
        );

        // Client dependency risk (>30% from single client)
        $totalRevenueRON = collect($yearlySummary)->sum('revenue_ron');
        $topClientRevenue = $topClients->first()?->total ?? 0;
        $clientDependencyRisk = $totalRevenueRON > 0 ? ($topClientRevenue / $totalRevenueRON) > 0.3 : false;
        $topClientPercentage = $totalRevenueRON > 0 ? round(($topClientRevenue / $totalRevenueRON) * 100, 1) : 0;

        // Find expense spikes
        $expenseSpikes = $this->findExpenseSpikes($availableYears);

        return [
            'best_year' => $bestYear,
            'best_year_profit' => $yearlySummary[$bestYear]['profit_ron'] ?? 0,
            'worst_year' => $worstYear,
            'worst_year_profit' => $yearlySummary[$worstYear]['profit_ron'] ?? 0,
            'top_clients' => $topClients,
            'expense_by_category' => $expenseByCategory,
            'client_dependency_risk' => $clientDependencyRisk,
            'top_client_percentage' => $topClientPercentage,
            'expense_spikes' => collect($expenseSpikes)->sortByDesc('multiplier')->take(5),
        ];
    }

    /**
     * Find expense spikes (months with expenses > 2x average).
     */
    private function findExpenseSpikes(Collection $availableYears): array
    {
        $expenseSpikes = [];

        foreach ($availableYears as $year) {
            $monthlyExpenses = FinancialExpense::forYear($year)
                ->where('currency', 'RON')
                ->select('month', DB::raw('SUM(amount) as total'))
                ->groupBy('month')
                ->get()
                ->pluck('total', 'month');

            $avgExpense = $monthlyExpenses->avg() ?: 0;

            foreach ($monthlyExpenses as $month => $amount) {
                if ($avgExpense > 0 && $amount > ($avgExpense * 2)) {
                    $expenseSpikes[] = [
                        'year' => $year,
                        'month' => $month,
                        'amount' => $amount,
                        'multiplier' => round($amount / $avgExpense, 1),
                    ];
                }
            }
        }

        return $expenseSpikes;
    }

    /**
     * Clear all financial dashboard caches.
     * Delegates to all aggregators and clears orchestration-level caches.
     */
    public function clearCache(?int $year = null): void
    {
        // Delegate to aggregators
        $this->revenueAggregator->clearCache($year);
        $this->expenseAggregator->clearCache($year);

        // Clear cashflow caches (not in aggregators)
        if ($year) {
            Cache::forget($this->cacheKey("financial.cashflow.revenues.{$year}"));
            Cache::forget($this->cacheKey("financial.cashflow.expenses.{$year}"));
        }

        // Clear orchestration-level caches
        Cache::forget($this->cacheKey('financial.available_years'));
        Cache::forget($this->cacheKey('financial.analytics.top_clients'));
        Cache::forget($this->cacheKey('financial.analytics.expense_categories'));
    }
}
