<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Services\Financial\FinancialDashboardService;
use App\Charts\MonthlyFinancialChart;
use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected FinancialDashboardService $dashboardService;

    public function __construct(FinancialDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request)
    {
        // Get year from request or session, default to current year
        $year = $request->get('year', session('financial.filters.year', now()->year));
        session(['financial.filters.year' => $year]);

        // Get revenue and expense totals
        $revenueTotals = $this->dashboardService->getYearlyRevenueTotals($year);
        $expenseTotals = $this->dashboardService->getYearlyExpenseTotals($year);

        $yearlyRevenueRON = $revenueTotals->get('RON', 0);
        $yearlyRevenueEUR = $revenueTotals->get('EUR', 0);
        $yearlyRevenueTotal = $yearlyRevenueRON + $yearlyRevenueEUR;

        $yearlyExpenseRON = $expenseTotals->get('RON', 0);
        $yearlyExpenseEUR = $expenseTotals->get('EUR', 0);
        $yearlyExpenseTotal = $yearlyExpenseRON + $yearlyExpenseEUR;

        $yearlyProfitRON = $yearlyRevenueRON - $yearlyExpenseRON;
        $yearlyProfitEUR = $yearlyRevenueEUR - $yearlyExpenseEUR;
        $yearlyProfitTotal = $yearlyRevenueTotal - $yearlyExpenseTotal;

        // Get monthly data
        $monthlyRevenuesData = $this->dashboardService->getMonthlyRevenueData($year);
        $monthlyExpensesData = $this->dashboardService->getMonthlyExpenseData($year);

        // Prepare chart data
        $chartData = $this->dashboardService->prepareChartData($monthlyRevenuesData, $monthlyExpensesData);
        $chartRevenuesRON = $chartData['revenues'];
        $chartExpensesRON = $chartData['expenses'];

        // Calculate common max value for both charts
        $commonMaxValue = $this->dashboardService->calculateChartMaxValue($chartRevenuesRON, $chartExpensesRON);

        // Create charts
        $revenueChart = MonthlyFinancialChart::createMonthlyChart($chartRevenuesRON, 'revenue', $commonMaxValue);
        $expenseChart = MonthlyFinancialChart::createMonthlyChart($chartExpensesRON, 'expense', $commonMaxValue);

        // Build monthly breakdown table
        $monthlyBreakdown = $this->dashboardService->buildMonthlyBreakdown($monthlyRevenuesData, $monthlyExpensesData);

        // Get available years and category breakdown
        $availableYears = $this->dashboardService->getAvailableYears();
        $categoryBreakdown = $this->dashboardService->getExpenseCategoryBreakdown($year);

        // Budget thresholds
        $budgetThresholds = auth()->user()->getBudgetThresholds();

        // Profit margin
        $profitMargin = $this->dashboardService->calculateProfitMargin($yearlyRevenueRON, $yearlyProfitRON);

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
     * Save budget thresholds for the current user.
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

        $availableYears = $this->dashboardService->getAvailableYears();
        $cashflowData = $this->dashboardService->getCashflowData($year);
        $totals = $this->dashboardService->calculateCashflowTotals($cashflowData);
        $chartData = $this->dashboardService->prepareCashflowChartData($cashflowData, $year);

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
        $reportData = $this->dashboardService->getYearlyReportData();
        $availableYears = $reportData['available_years'];
        $yearlySummary = $reportData['yearly_summary'];
        $sortedYears = $reportData['sorted_years'];

        $totals = $this->dashboardService->calculateAllTimeTotals($yearlySummary);
        $analytics = $this->dashboardService->getAnalytics($yearlySummary, $availableYears);

        // Prepare chart data
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

        $callback = function () use ($revenues, $expenses) {
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
        $reportData = $this->dashboardService->getYearlyReportData();
        $availableYears = $reportData['available_years'];
        $yearlySummary = $reportData['yearly_summary'];

        $filename = 'financial_history_all_years.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($yearlySummary, $availableYears) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

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
