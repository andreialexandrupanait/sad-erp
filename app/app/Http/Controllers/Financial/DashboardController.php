<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;
use App\Models\SettingOption;
use App\Charts\MonthlyFinancialChart;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get year from request or session, default to current year
        $year = $request->get('year', session('financial.filters.year', now()->year));

        // Store year in session for consistency across financial pages
        session(['financial.filters.year' => $year]);

        // Calculate yearly totals by currency
        $yearlyRevenueRON = FinancialRevenue::forYear($year)->where('currency', 'RON')->sum('amount');
        $yearlyRevenueEUR = FinancialRevenue::forYear($year)->where('currency', 'EUR')->sum('amount');
        $yearlyRevenueTotal = $yearlyRevenueRON + $yearlyRevenueEUR;

        $yearlyExpenseRON = FinancialExpense::forYear($year)->where('currency', 'RON')->sum('amount');
        $yearlyExpenseEUR = FinancialExpense::forYear($year)->where('currency', 'EUR')->sum('amount');
        $yearlyExpenseTotal = $yearlyExpenseRON + $yearlyExpenseEUR;

        $yearlyProfitRON = $yearlyRevenueRON - $yearlyExpenseRON;
        $yearlyProfitEUR = $yearlyRevenueEUR - $yearlyExpenseEUR;
        $yearlyProfitTotal = $yearlyRevenueTotal - $yearlyExpenseTotal;

        // Monthly data for charts (12 months, RON only)
        $monthlyRevenuesData = FinancialRevenue::forYear($year)
            ->where('currency', 'RON')
            ->select('month', DB::raw('SUM(amount) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->mapWithKeys(fn($item) => [$item->month => $item->total]);

        $monthlyExpensesData = FinancialExpense::forYear($year)
            ->where('currency', 'RON')
            ->select('month', DB::raw('SUM(amount) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->mapWithKeys(fn($item) => [$item->month => $item->total]);

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

        // Available years for filter
        $availableYears = FinancialRevenue::select(DB::raw('DISTINCT year'))
            ->union(FinancialExpense::select(DB::raw('DISTINCT year')))
            ->orderByDesc('year')
            ->pluck('year');

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
            'availableYears'
        ));
    }

    public function yearlyReport(Request $request, $year)
    {
        // Get all revenues and expenses for the year
        $revenues = FinancialRevenue::with('client')
            ->forYear($year)
            ->orderBy('occurred_at')
            ->get();

        $expenses = FinancialExpense::with('category')
            ->forYear($year)
            ->orderBy('occurred_at')
            ->get();

        // Calculate monthly summaries
        $monthlySummary = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthRevenues = $revenues->where('month', $month);
            $monthExpenses = $expenses->where('month', $month);

            $monthlySummary[$month] = [
                'revenues_ron' => $monthRevenues->where('currency', 'RON')->sum('amount'),
                'revenues_eur' => $monthRevenues->where('currency', 'EUR')->sum('amount'),
                'expenses_ron' => $monthExpenses->where('currency', 'RON')->sum('amount'),
                'expenses_eur' => $monthExpenses->where('currency', 'EUR')->sum('amount'),
            ];
        }

        return view('financial.yearly-report', compact('year', 'revenues', 'expenses', 'monthlySummary'));
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
}
