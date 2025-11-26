<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;
use App\Models\SettingOption;
use App\Charts\MonthlyFinancialChart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // Cache TTL in seconds (10 minutes)
    private const CACHE_TTL = 600;

    public function index(Request $request)
    {
        // Get year from request or session, default to current year
        $year = $request->get('year', session('financial.filters.year', now()->year));

        // Store year in session for consistency across financial pages
        session(['financial.filters.year' => $year]);

        // CACHED: Revenue totals by currency for the year
        $revenueTotals = Cache::remember(
            "financial.revenues.totals.{$year}",
            self::CACHE_TTL,
            fn() => FinancialRevenue::forYear($year)
                ->select('currency', DB::raw('SUM(amount) as total'))
                ->groupBy('currency')
                ->pluck('total', 'currency')
        );

        $yearlyRevenueRON = $revenueTotals->get('RON', 0);
        $yearlyRevenueEUR = $revenueTotals->get('EUR', 0);
        $yearlyRevenueTotal = $yearlyRevenueRON + $yearlyRevenueEUR;

        // CACHED: Expense totals by currency for the year
        $expenseTotals = Cache::remember(
            "financial.expenses.totals.{$year}",
            self::CACHE_TTL,
            fn() => FinancialExpense::forYear($year)
                ->select('currency', DB::raw('SUM(amount) as total'))
                ->groupBy('currency')
                ->pluck('total', 'currency')
        );

        $yearlyExpenseRON = $expenseTotals->get('RON', 0);
        $yearlyExpenseEUR = $expenseTotals->get('EUR', 0);
        $yearlyExpenseTotal = $yearlyExpenseRON + $yearlyExpenseEUR;

        $yearlyProfitRON = $yearlyRevenueRON - $yearlyExpenseRON;
        $yearlyProfitEUR = $yearlyRevenueEUR - $yearlyExpenseEUR;
        $yearlyProfitTotal = $yearlyRevenueTotal - $yearlyExpenseTotal;

        // CACHED: Monthly revenue data for charts (RON only)
        $monthlyRevenuesData = Cache::remember(
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

        // CACHED: Monthly expense data for charts (RON only)
        $monthlyExpensesData = Cache::remember(
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

        // Prepare chart data for all 12 months (RON only)
        $romanianMonths = ['Ian', 'Feb', 'Mar', 'Apr', 'Mai', 'Iun', 'Iul', 'Aug', 'Sep', 'Oct', 'Noi', 'Dec'];
        $chartRevenuesRON = [];
        $chartExpensesRON = [];

        for ($month = 1; $month <= 12; $month++) {
            $revenueAmount = $monthlyRevenuesData->get($month, 0);
            $expenseAmount = $monthlyExpensesData->get($month, 0);

            $chartRevenuesRON[] = [
                'month' => $romanianMonths[$month - 1],
                'amount' => $revenueAmount,
                'formatted' => number_format($revenueAmount, 2),
            ];

            $chartExpensesRON[] = [
                'month' => $romanianMonths[$month - 1],
                'amount' => $expenseAmount,
                'formatted' => number_format($expenseAmount, 2),
            ];
        }

        // Calculate common max value for both charts to ensure proper scaling
        $maxRevenueAmount = collect($chartRevenuesRON)->max('amount') ?: 0;
        $maxExpenseAmount = collect($chartExpensesRON)->max('amount') ?: 0;
        $commonMaxValue = max($maxRevenueAmount, $maxExpenseAmount);

        // Add 10% padding to the max value for better visualization
        $commonMaxValue = $commonMaxValue * 1.1;

        // Create Chart.js charts using the MonthlyFinancialChart class
        $revenueChart = MonthlyFinancialChart::createMonthlyChart($chartRevenuesRON, 'revenue', $commonMaxValue);
        $expenseChart = MonthlyFinancialChart::createMonthlyChart($chartExpensesRON, 'expense', $commonMaxValue);

        // Monthly breakdown table data (all 12 months with RON values)
        $monthlyBreakdown = [];
        $fullMonthNames = ['Ianuarie', 'Februarie', 'Martie', 'Aprilie', 'Mai', 'Iunie', 'Iulie', 'August', 'Septembrie', 'Octombrie', 'Noiembrie', 'Decembrie'];

        for ($month = 1; $month <= 12; $month++) {
            $revenueRON = $monthlyRevenuesData->get($month, 0);
            $expenseRON = $monthlyExpensesData->get($month, 0);

            $monthlyBreakdown[] = [
                'month' => $month,
                'month_name' => $fullMonthNames[$month - 1],
                'revenues_ron' => $revenueRON,
                'expenses_ron' => $expenseRON,
                'profit_ron' => $revenueRON - $expenseRON,
            ];
        }

        // Available years for filter - show all years from 2019 to present
        $currentYear = now()->year;
        $availableYears = collect(range(2019, $currentYear))->reverse()->values();

        // CACHED: Category breakdown for expense categories (top 8)
        $categoryBreakdown = Cache::remember(
            "financial.expenses.categories.{$year}",
            self::CACHE_TTL,
            fn() => FinancialExpense::forYear($year)
                ->whereNotNull('category_option_id')
                ->select('category_option_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
                ->groupBy('category_option_id')
                ->with('category')
                ->get()
                ->sortByDesc('total')
                ->take(8)
        );

        // Budget thresholds for visual indicators
        $budgetThresholds = auth()->user()->getBudgetThresholds();

        // Calculate profit margin for current year
        $profitMargin = $yearlyRevenueRON > 0
            ? round(($yearlyProfitRON / $yearlyRevenueRON) * 100, 1)
            : 0;

        return view('financial.dashboard', compact(
            'year',
            'yearlyRevenueRON',
            'yearlyRevenueEUR',
            'yearlyRevenueTotal',
            'yearlyExpenseRON',
            'yearlyExpenseEUR',
            'yearlyExpenseTotal',
            'yearlyProfitRON',
            'yearlyProfitEUR',
            'yearlyProfitTotal',
            'revenueChart',
            'expenseChart',
            'commonMaxValue',
            'monthlyBreakdown',
            'availableYears',
            'categoryBreakdown',
            'budgetThresholds',
            'profitMargin'
        ));
    }

    /**
     * Save budget thresholds for the current user
     */
    public function saveBudgetThresholds(Request $request)
    {
        $validated = $request->validate([
            'expense_budget_ron' => 'nullable|numeric|min:0',
            'expense_budget_eur' => 'nullable|numeric|min:0',
            'revenue_target_ron' => 'nullable|numeric|min:0',
            'revenue_target_eur' => 'nullable|numeric|min:0',
            'profit_margin_min' => 'nullable|numeric|min:0|max:100',
        ]);

        auth()->user()->saveBudgetThresholds($validated);

        return back()->with('success', __('Budget thresholds saved successfully.'));
    }

    public function cashflow(Request $request)
    {
        $year = $request->get('year', session('financial.filters.year', now()->year));
        session(['financial.filters.year' => $year]);

        // Available years - show all years from 2019 to present
        $currentYear = now()->year;
        $availableYears = collect(range(2019, $currentYear))->reverse()->values();

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

        // Build cashflow data for all 12 months
        $romanianMonths = ['Ianuarie', 'Februarie', 'Martie', 'Aprilie', 'Mai', 'Iunie', 'Iulie', 'August', 'Septembrie', 'Octombrie', 'Noiembrie', 'Decembrie'];
        $cashflowData = [];
        $runningBalanceRON = 0;
        $runningBalanceEUR = 0;

        // Pre-index revenues and expenses by month+currency for O(1) lookup
        $revenueIndex = $monthlyRevenues->groupBy(fn($item) => $item->month . '_' . $item->currency);
        $expenseIndex = $monthlyExpenses->groupBy(fn($item) => $item->month . '_' . $item->currency);
        $revenueCountIndex = $monthlyRevenues->groupBy('month');
        $expenseCountIndex = $monthlyExpenses->groupBy('month');

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
                'month_name' => $romanianMonths[$month - 1],
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

        // Calculate totals
        $totals = [
            'revenue_ron' => collect($cashflowData)->sum('revenue_ron'),
            'revenue_eur' => collect($cashflowData)->sum('revenue_eur'),
            'expense_ron' => collect($cashflowData)->sum('expense_ron'),
            'expense_eur' => collect($cashflowData)->sum('expense_eur'),
            'net_ron' => collect($cashflowData)->sum('net_ron'),
            'net_eur' => collect($cashflowData)->sum('net_eur'),
        ];

        // Prepare chart data
        $chartData = [
            'labels' => collect($cashflowData)->pluck('month_name')->take(now()->year == $year ? now()->month : 12)->toArray(),
            'revenues' => collect($cashflowData)->pluck('revenue_ron')->take(now()->year == $year ? now()->month : 12)->toArray(),
            'expenses' => collect($cashflowData)->pluck('expense_ron')->take(now()->year == $year ? now()->month : 12)->toArray(),
            'net' => collect($cashflowData)->pluck('net_ron')->take(now()->year == $year ? now()->month : 12)->toArray(),
            'balance' => collect($cashflowData)->pluck('balance_ron')->take(now()->year == $year ? now()->month : 12)->toArray(),
        ];

        return view('financial.cashflow', compact(
            'year',
            'availableYears',
            'cashflowData',
            'totals',
            'chartData'
        ));
    }

    public function yearlyReport(Request $request)
    {
        // CACHED: Available years from both revenues and expenses
        $availableYears = Cache::remember(
            'financial.available_years',
            self::CACHE_TTL,
            function() {
                $revenueYears = FinancialRevenue::selectRaw('DISTINCT year')->pluck('year');
                $expenseYears = FinancialExpense::selectRaw('DISTINCT year')->pluck('year');
                $years = $revenueYears->merge($expenseYears)->unique()->sortDesc()->values();
                return $years->isEmpty() ? collect([now()->year]) : $years;
            }
        );

        // CACHED: Revenue aggregations grouped by year and currency
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

        // CACHED: Expense aggregations grouped by year and currency
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

        // Build multi-year summary data from aggregated results
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

            // Get invoice and client counts from RON data (or any currency data)
            $invoiceCount = $yearRevenues->sum('invoice_count');
            $clientCount = $yearRevenues->max('client_count') ?? 0;

            $yearlySummary[$year] = [
                'revenue_ron' => $revenueRON,
                'revenue_eur' => $revenueEUR,
                'expense_ron' => $expenseRON,
                'expense_eur' => $expenseEUR,
                'profit_ron' => $profitRON,
                'profit_eur' => $profitEUR,
                'margin_percent' => $revenueRON > 0 ? round(($profitRON / $revenueRON) * 100, 1) : 0,
                'client_count' => $clientCount,
                'invoice_count' => $invoiceCount,
            ];
        }

        // Calculate YoY growth for each year
        $sortedYears = collect($yearlySummary)->keys()->sort()->values();
        foreach ($sortedYears as $index => $year) {
            $prevYear = $year - 1;
            if (isset($yearlySummary[$prevYear]) && $yearlySummary[$prevYear]['revenue_ron'] > 0) {
                $yearlySummary[$year]['yoy_growth'] = round(
                    (($yearlySummary[$year]['revenue_ron'] - $yearlySummary[$prevYear]['revenue_ron'])
                    / $yearlySummary[$prevYear]['revenue_ron']) * 100, 1
                );
            } else {
                $yearlySummary[$year]['yoy_growth'] = null;
            }
        }

        // Calculate totals across all years
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
        $totals['margin_percent'] = $totals['revenue_ron'] > 0
            ? round(($totals['profit_ron'] / $totals['revenue_ron']) * 100, 1)
            : 0;

        // Smart Analytics
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
        $topClientRevenue = $topClients->first()?->total ?? 0;
        $clientDependencyRisk = $totals['revenue_ron'] > 0
            ? ($topClientRevenue / $totals['revenue_ron']) > 0.3
            : false;
        $topClientPercentage = $totals['revenue_ron'] > 0
            ? round(($topClientRevenue / $totals['revenue_ron']) * 100, 1)
            : 0;

        // Find expense spikes (months with expenses > 2x average)
        $expenseSpikes = [];
        foreach ($availableYears as $year) {
            $monthlyExpenses = FinancialExpense::forYear($year)
                ->byCurrency('RON')
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

        $analytics = [
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

        // Prepare chart data (sorted by year ascending)
        $chartData = [
            'labels' => $sortedYears->toArray(),
            'revenues' => $sortedYears->map(fn($y) => $yearlySummary[$y]['revenue_ron'])->toArray(),
            'expenses' => $sortedYears->map(fn($y) => $yearlySummary[$y]['expense_ron'])->toArray(),
            'profits' => $sortedYears->map(fn($y) => $yearlySummary[$y]['profit_ron'])->toArray(),
        ];

        return view('financial.yearly-report', compact(
            'availableYears',
            'yearlySummary',
            'totals',
            'analytics',
            'chartData'
        ));
    }

    public function exportCsv(Request $request, $year)
    {
        $revenues = FinancialRevenue::with('client')
            ->forYear($year)
            ->orderBy('occurred_at')
            ->get();

        $expenses = FinancialExpense::with('category')
            ->forYear($year)
            ->orderBy('occurred_at')
            ->get();

        $filename = "financial_report_{$year}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($revenues, $expenses) {
            $file = fopen('php://output', 'w');

            // Revenues section
            fputcsv($file, ['REVENUES']);
            fputcsv($file, ['Date', 'Document', 'Amount', 'Currency', 'Client', 'Note']);

            foreach ($revenues as $revenue) {
                fputcsv($file, [
                    $revenue->occurred_at->format('Y-m-d'),
                    $revenue->document_name,
                    $revenue->amount,
                    $revenue->currency,
                    $revenue->client?->name ?? '-',
                    $revenue->note ?? '',
                ]);
            }

            fputcsv($file, []); // Empty line

            // Expenses section
            fputcsv($file, ['EXPENSES']);
            fputcsv($file, ['Date', 'Document', 'Amount', 'Currency', 'Category', 'Note']);

            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->occurred_at->format('Y-m-d'),
                    $expense->document_name,
                    $expense->amount,
                    $expense->currency,
                    $expense->category?->name ?? '-',
                    $expense->note ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportAllYearsCsv(Request $request)
    {
        // Get all available years
        $revenueYears = FinancialRevenue::selectRaw('DISTINCT year')->pluck('year');
        $expenseYears = FinancialExpense::selectRaw('DISTINCT year')->pluck('year');
        $availableYears = $revenueYears->merge($expenseYears)->unique()->sort()->values();

        // OPTIMIZED: Single query for all revenue aggregations grouped by year and currency
        $revenueAggregates = FinancialRevenue::select(
                'year',
                'currency',
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as invoice_count'),
                DB::raw('COUNT(DISTINCT client_id) as client_count')
            )
            ->groupBy('year', 'currency')
            ->get()
            ->groupBy('year');

        // OPTIMIZED: Single query for all expense aggregations grouped by year and currency
        $expenseAggregates = FinancialExpense::select(
                'year',
                'currency',
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('year', 'currency')
            ->get()
            ->groupBy('year');

        // Build yearly summary data from aggregated results
        $yearlySummary = [];
        foreach ($availableYears as $year) {
            $yearRevenues = $revenueAggregates->get($year, collect());
            $yearExpenses = $expenseAggregates->get($year, collect());

            $revenueRON = $yearRevenues->where('currency', 'RON')->first()?->total ?? 0;
            $revenueEUR = $yearRevenues->where('currency', 'EUR')->first()?->total ?? 0;
            $expenseRON = $yearExpenses->where('currency', 'RON')->first()?->total ?? 0;
            $expenseEUR = $yearExpenses->where('currency', 'EUR')->first()?->total ?? 0;

            $yearlySummary[$year] = [
                'revenue_ron' => $revenueRON,
                'revenue_eur' => $revenueEUR,
                'expense_ron' => $expenseRON,
                'expense_eur' => $expenseEUR,
                'profit_ron' => $revenueRON - $expenseRON,
                'profit_eur' => $revenueEUR - $expenseEUR,
                'margin_percent' => $revenueRON > 0 ? round((($revenueRON - $expenseRON) / $revenueRON) * 100, 1) : 0,
                'client_count' => $yearRevenues->max('client_count') ?? 0,
                'invoice_count' => $yearRevenues->sum('invoice_count'),
            ];
        }

        $filename = 'financial_history_all_years.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($yearlySummary, $availableYears) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header row
            fputcsv($file, [
                'An',
                'Venituri RON',
                'Venituri EUR',
                'Cheltuieli RON',
                'Cheltuieli EUR',
                'Profit RON',
                'Profit EUR',
                'Marja Profit %',
                'Clienti',
                'Facturi'
            ]);

            // Data rows
            foreach ($availableYears as $year) {
                $data = $yearlySummary[$year];
                fputcsv($file, [
                    $year,
                    $data['revenue_ron'],
                    $data['revenue_eur'],
                    $data['expense_ron'],
                    $data['expense_eur'],
                    $data['profit_ron'],
                    $data['profit_eur'],
                    $data['margin_percent'],
                    $data['client_count'],
                    $data['invoice_count'],
                ]);
            }

            // Totals row
            fputcsv($file, [
                'TOTAL',
                collect($yearlySummary)->sum('revenue_ron'),
                collect($yearlySummary)->sum('revenue_eur'),
                collect($yearlySummary)->sum('expense_ron'),
                collect($yearlySummary)->sum('expense_eur'),
                collect($yearlySummary)->sum('profit_ron'),
                collect($yearlySummary)->sum('profit_eur'),
                '-',
                '-',
                collect($yearlySummary)->sum('invoice_count'),
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
