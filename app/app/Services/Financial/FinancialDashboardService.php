<?php

namespace App\Services\Financial;

use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * Service for financial dashboard calculations and aggregations.
 *
 * Handles caching, data aggregation, and analytics for financial reports.
 */
class FinancialDashboardService
{
    // Cache TTL in seconds (10 minutes)
    private const CACHE_TTL = 600;

    /**
     * Romanian month names (short).
     */
    private const SHORT_MONTHS = ['Ian', 'Feb', 'Mar', 'Apr', 'Mai', 'Iun', 'Iul', 'Aug', 'Sep', 'Oct', 'Noi', 'Dec'];

    /**
     * Romanian month names (full).
     */
    private const FULL_MONTHS = ['Ianuarie', 'Februarie', 'Martie', 'Aprilie', 'Mai', 'Iunie', 'Iulie', 'August', 'Septembrie', 'Octombrie', 'Noiembrie', 'Decembrie'];

    /**
     * Get yearly revenue totals by currency.
     */
    public function getYearlyRevenueTotals(int $year): Collection
    {
        return Cache::remember(
            "financial.revenues.totals.{$year}",
            self::CACHE_TTL,
            fn() => FinancialRevenue::forYear($year)
                ->select('currency', DB::raw('SUM(amount) as total'))
                ->groupBy('currency')
                ->pluck('total', 'currency')
        );
    }

    /**
     * Get yearly expense totals by currency.
     */
    public function getYearlyExpenseTotals(int $year): Collection
    {
        return Cache::remember(
            "financial.expenses.totals.{$year}",
            self::CACHE_TTL,
            fn() => FinancialExpense::forYear($year)
                ->select('currency', DB::raw('SUM(amount) as total'))
                ->groupBy('currency')
                ->pluck('total', 'currency')
        );
    }

    /**
     * Get monthly revenue data for a year (RON only).
     */
    public function getMonthlyRevenueData(int $year): Collection
    {
        return Cache::remember(
            "financial.revenues.monthly.{$year}",
            self::CACHE_TTL,
            fn() => FinancialRevenue::forYear($year)
                ->where('currency', 'RON')
                ->select('month', DB::raw('SUM(amount) as total'))
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->mapWithKeys(fn($item) => [$item->month => $item->total])
        );
    }

    /**
     * Get monthly expense data for a year (RON only).
     */
    public function getMonthlyExpenseData(int $year): Collection
    {
        return Cache::remember(
            "financial.expenses.monthly.{$year}",
            self::CACHE_TTL,
            fn() => FinancialExpense::forYear($year)
                ->where('currency', 'RON')
                ->select('month', DB::raw('SUM(amount) as total'))
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->mapWithKeys(fn($item) => [$item->month => $item->total])
        );
    }

    /**
     * Get category breakdown for expenses.
     */
    public function getExpenseCategoryBreakdown(int $year, int $limit = 8): Collection
    {
        return Cache::remember(
            "financial.expenses.categories.{$year}",
            self::CACHE_TTL,
            fn() => FinancialExpense::forYear($year)
                ->whereNotNull('category_option_id')
                ->select('category_option_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
                ->groupBy('category_option_id')
                ->with('category')
                ->get()
                ->sortByDesc('total')
                ->take($limit)
        );
    }

    /**
     * Prepare chart data for all 12 months.
     */
    public function prepareChartData(Collection $monthlyRevenues, Collection $monthlyExpenses): array
    {
        $chartRevenuesRON = [];
        $chartExpensesRON = [];

        for ($month = 1; $month <= 12; $month++) {
            $revenueAmount = $monthlyRevenues->get($month, 0);
            $expenseAmount = $monthlyExpenses->get($month, 0);

            $chartRevenuesRON[] = [
                'month' => self::SHORT_MONTHS[$month - 1],
                'amount' => $revenueAmount,
                'formatted' => number_format($revenueAmount, 2),
            ];

            $chartExpensesRON[] = [
                'month' => self::SHORT_MONTHS[$month - 1],
                'amount' => $expenseAmount,
                'formatted' => number_format($expenseAmount, 2),
            ];
        }

        return [
            'revenues' => $chartRevenuesRON,
            'expenses' => $chartExpensesRON,
        ];
    }

    /**
     * Calculate common max value for chart scaling.
     */
    public function calculateChartMaxValue(array $chartRevenues, array $chartExpenses): float
    {
        $maxRevenueAmount = collect($chartRevenues)->max('amount') ?: 0;
        $maxExpenseAmount = collect($chartExpenses)->max('amount') ?: 0;
        $commonMaxValue = max($maxRevenueAmount, $maxExpenseAmount);

        // Add 10% padding for better visualization
        return $commonMaxValue * 1.1;
    }

    /**
     * Build monthly breakdown table data.
     */
    public function buildMonthlyBreakdown(Collection $monthlyRevenues, Collection $monthlyExpenses): array
    {
        $monthlyBreakdown = [];

        for ($month = 1; $month <= 12; $month++) {
            $revenueRON = $monthlyRevenues->get($month, 0);
            $expenseRON = $monthlyExpenses->get($month, 0);

            $monthlyBreakdown[] = [
                'month' => $month,
                'month_name' => self::FULL_MONTHS[$month - 1],
                'revenues_ron' => $revenueRON,
                'expenses_ron' => $expenseRON,
                'profit_ron' => $revenueRON - $expenseRON,
            ];
        }

        return $monthlyBreakdown;
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
            'financial.available_years',
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
            "financial.cashflow.revenues.{$year}",
            self::CACHE_TTL,
            fn() => FinancialRevenue::forYear($year)
                ->select('month', 'currency', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
                ->groupBy('month', 'currency')
                ->get()
        );

        // Get monthly expenses by currency
        $monthlyExpenses = Cache::remember(
            "financial.cashflow.expenses.{$year}",
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
                'month_name' => self::FULL_MONTHS[$month - 1],
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
     */
    public function prepareCashflowChartData(array $cashflowData, int $year): array
    {
        $monthsToShow = ($year == now()->year) ? now()->month : 12;

        return [
            'labels' => collect($cashflowData)->pluck('month_name')->take($monthsToShow)->toArray(),
            'revenues' => collect($cashflowData)->pluck('revenue_ron')->take($monthsToShow)->toArray(),
            'expenses' => collect($cashflowData)->pluck('expense_ron')->take($monthsToShow)->toArray(),
            'net' => collect($cashflowData)->pluck('net_ron')->take($monthsToShow)->toArray(),
            'balance' => collect($cashflowData)->pluck('balance_ron')->take($monthsToShow)->toArray(),
        ];
    }

    /**
     * Get yearly report data.
     */
    public function getYearlyReportData(): array
    {
        $availableYears = $this->getAvailableYearsFromData();

        // Revenue aggregations grouped by year and currency
        $revenueAggregates = Cache::remember(
            'financial.revenues.yearly_aggregates',
            self::CACHE_TTL,
            fn() => FinancialRevenue::select(
                'year',
                'currency',
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as invoice_count'),
                DB::raw('COUNT(DISTINCT client_id) as client_count')
            )
                ->groupBy('year', 'currency')
                ->get()
                ->groupBy('year')
        );

        // Expense aggregations grouped by year and currency
        $expenseAggregates = Cache::remember(
            'financial.expenses.yearly_aggregates',
            self::CACHE_TTL,
            fn() => FinancialExpense::select(
                'year',
                'currency',
                DB::raw('SUM(amount) as total')
            )
                ->groupBy('year', 'currency')
                ->get()
                ->groupBy('year')
        );

        // Build yearly summary
        $yearlySummary = [];
        foreach ($availableYears as $year) {
            $yearRevenues = $revenueAggregates->get($year, collect());
            $yearExpenses = $expenseAggregates->get($year, collect());

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
     */
    public function getAnalytics(array $yearlySummary, Collection $availableYears): array
    {
        $bestYear = collect($yearlySummary)->sortByDesc('profit_ron')->keys()->first();
        $worstYear = collect($yearlySummary)->sortBy('profit_ron')->keys()->first();

        // Top clients by revenue (all time)
        $topClients = FinancialRevenue::with('client')
            ->whereNotNull('client_id')
            ->select('client_id', DB::raw('SUM(amount) as total'))
            ->groupBy('client_id')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        // Expense breakdown by category (all time)
        $expenseByCategory = FinancialExpense::with('category')
            ->whereNotNull('category_option_id')
            ->select('category_option_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category_option_id')
            ->orderByDesc('total')
            ->take(10)
            ->get();

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
     */
    public function clearCache(?int $year = null): void
    {
        if ($year) {
            Cache::forget("financial.revenues.totals.{$year}");
            Cache::forget("financial.expenses.totals.{$year}");
            Cache::forget("financial.revenues.monthly.{$year}");
            Cache::forget("financial.expenses.monthly.{$year}");
            Cache::forget("financial.expenses.categories.{$year}");
            Cache::forget("financial.cashflow.revenues.{$year}");
            Cache::forget("financial.cashflow.expenses.{$year}");
        }

        Cache::forget('financial.available_years');
        Cache::forget('financial.revenues.yearly_aggregates');
        Cache::forget('financial.expenses.yearly_aggregates');
    }
}
