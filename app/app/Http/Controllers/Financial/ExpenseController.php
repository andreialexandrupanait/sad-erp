<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinancialExpense;
use App\Models\FinancialSetting;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month');
        $currency = $request->get('currency');
        $categoryId = $request->get('category_id');

        $expenses = FinancialExpense::with('category')
            ->forYear($year)
            ->when($month, fn($q) => $q->where('month', $month))
            ->when($currency, fn($q) => $q->where('currency', $currency))
            ->when($categoryId, fn($q) => $q->where('category_option_id', $categoryId))
            ->latest('occurred_at')
            ->paginate(15);

        // Calculate totals by currency for current filter
        $totals = FinancialExpense::forYear($year)
            ->when($month, fn($q) => $q->where('month', $month))
            ->when($currency, fn($q) => $q->where('currency', $currency))
            ->when($categoryId, fn($q) => $q->where('category_option_id', $categoryId))
            ->select('currency', DB::raw('SUM(amount) as total'))
            ->groupBy('currency')
            ->get()
            ->mapWithKeys(fn($item) => [$item->currency => $item->total]);

        $categories = FinancialSetting::expenseCategories()->get();

        // Available years
        $availableYears = FinancialExpense::select(DB::raw('DISTINCT year'))
            ->orderByDesc('year')
            ->pluck('year');

        return view('financial.expenses.index', compact(
            'expenses',
            'totals',
            'year',
            'month',
            'currency',
            'categoryId',
            'categories',
            'availableYears'
        ));
    }

    public function create()
    {
        $categories = FinancialSetting::expenseCategories()->get();
        return view('financial.expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|in:RON,EUR',
            'occurred_at' => 'required|date',
            'category_option_id' => 'nullable|exists:financial_settings,id',
            'note' => 'nullable|string',
        ]);

        FinancialExpense::create($validated);

        return redirect()->route('financial.expenses.index')
            ->with('success', 'Expense added successfully.');
    }

    public function show(FinancialExpense $expense)
    {
        $expense->load('category', 'files');
        return view('financial.expenses.show', compact('expense'));
    }

    public function edit(FinancialExpense $expense)
    {
        $categories = FinancialSetting::expenseCategories()->get();
        return view('financial.expenses.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, FinancialExpense $expense)
    {
        $validated = $request->validate([
            'document_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|in:RON,EUR',
            'occurred_at' => 'required|date',
            'category_option_id' => 'nullable|exists:financial_settings,id',
            'note' => 'nullable|string',
        ]);

        // Update year and month based on occurred_at
        $date = \Carbon\Carbon::parse($validated['occurred_at']);
        $validated['year'] = $date->year;
        $validated['month'] = $date->month;

        $expense->update($validated);

        return redirect()->route('financial.expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(FinancialExpense $expense)
    {
        $expense->delete();

        return redirect()->route('financial.expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }
}
