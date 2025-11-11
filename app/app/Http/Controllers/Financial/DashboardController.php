<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;
use App\Models\SettingOption;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month');

        // Build query for revenues
        $revenuesQuery = FinancialRevenue::query()->forYear($year);
        $expensesQuery = FinancialExpense::query()->forYear($year);

        if ($month) {
            $revenuesQuery->where('month', $month);
            $expensesQuery->where('month', $month);
        }

        // Calculate totals by currency
        $revenueTotals = (clone $revenuesQuery)
            ->select('currency', DB::raw('SUM(amount) as total'))
            ->groupBy('currency')
            ->get()
            ->mapWithKeys(fn($item) => [$item->currency => $item->total]);

        $expenseTotals = (clone $expensesQuery)
            ->select('currency', DB::raw('SUM(amount) as total'))
            ->groupBy('currency')
            ->get()
            ->mapWithKeys(fn($item) => [$item->currency => $item->total]);

        // Calculate profit by currency
        $currencies = collect(['RON', 'EUR']);
        $profitTotals = $currencies->mapWithKeys(function($currency) use ($revenueTotals, $expenseTotals) {
            $revenue = $revenueTotals->get($currency, 0);
            $expense = $expenseTotals->get($currency, 0);
            return [$currency => $revenue - $expense];
        });

        // Monthly data for charts (last 12 months or current year)
        $monthlyRevenues = FinancialRevenue::forYear($year)
            ->select('month', 'currency', DB::raw('SUM(amount) as total'))
            ->groupBy('month', 'currency')
            ->orderBy('month')
            ->get()
            ->groupBy('currency');

        $monthlyExpenses = FinancialExpense::forYear($year)
            ->select('month', 'currency', DB::raw('SUM(amount) as total'))
            ->groupBy('month', 'currency')
            ->orderBy('month')
            ->get()
            ->groupBy('currency');

        // Expenses by category
        $expensesByCategory = FinancialExpense::forYear($year)
            ->when($month, fn($q) => $q->where('month', $month))
            ->with('category')
            ->select('category_option_id', DB::raw('SUM(amount) as total'), 'currency')
            ->groupBy('category_option_id', 'currency')
            ->get()
            ->groupBy('currency');

        // Recent transactions
        $recentRevenues = FinancialRevenue::with('client')
            ->forYear($year)
            ->when($month, fn($q) => $q->where('month', $month))
            ->latest('occurred_at')
            ->take(5)
            ->get();

        $recentExpenses = FinancialExpense::with('category')
            ->forYear($year)
            ->when($month, fn($q) => $q->where('month', $month))
            ->latest('occurred_at')
            ->take(5)
            ->get();

        // Available years for filter
        $availableYears = FinancialRevenue::select(DB::raw('DISTINCT year'))
            ->union(FinancialExpense::select(DB::raw('DISTINCT year')))
            ->orderByDesc('year')
            ->pluck('year');

        return view('financial.dashboard', compact(
            'year',
            'month',
            'revenueTotals',
            'expenseTotals',
            'profitTotals',
            'monthlyRevenues',
            'monthlyExpenses',
            'expensesByCategory',
            'recentRevenues',
            'recentExpenses',
            'availableYears',
            'currencies'
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
