<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Financial\Concerns\ManagesImports;
use App\Http\Controllers\Traits\SafeJsonResponse;
use App\Models\FinancialExpense;
use App\Services\Financial\ExpenseImportService;
use Illuminate\Http\Request;

class ExpenseImportController extends Controller
{
    use SafeJsonResponse, ManagesImports;

    protected ExpenseImportService $expenseImportService;

    public function __construct(ExpenseImportService $expenseImportService)
    {
        $this->expenseImportService = $expenseImportService;
    }

    /**
     * Show expense import form
     */
    public function showForm()
    {
        $this->authorize('import', FinancialExpense::class);

        return view('financial.expenses.import');
    }

    /**
     * Import expenses from uploaded file
     */
    public function import(Request $request)
    {
        $this->authorize('import', FinancialExpense::class);

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        try {
            $file = $request->file('csv_file');
            $csvData = $this->expenseImportService->parseFile($file->getRealPath());

            // Use the service for import (fixes N+1 query on category lookup)
            $stats = $this->expenseImportService->import(
                $csvData,
                auth()->user()->organization_id,
                auth()->id()
            );

            return redirect()
                ->route('financial.cheltuieli.index')
                ->with('success', "Import completed: {$stats['imported']} expenses imported, {$stats['skipped']} skipped")
                ->with('import_errors', $stats['errors']);
        } catch (\Exception $e) {
            return redirect()
                ->route('financial.expenses.import')
                ->with('error', 'Import failed. Please check your file format.');
        }
    }

    /**
     * Download CSV template for expense import
     */
    public function downloadTemplate()
    {
        $filename = 'expense_import_template_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, [
                'document_name',
                'amount',
                'currency',
                'occurred_at',
                'category',
                'note'
            ]);

            // Example row
            fputcsv($file, [
                'Factura Hosting #12345',
                '250.00',
                'RON',
                date('Y-m-d'),
                'Cloud & Hosting',
                'Monthly server hosting'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export expenses to CSV
     */
    public function export(Request $request)
    {
        $this->authorize('export', FinancialExpense::class);

        $year = $request->get('year', now()->year);
        $month = $request->get('month');
        $currency = $request->get('currency');
        $categoryId = $request->get('category_id');

        $expenses = FinancialExpense::with('category')
            ->where('organization_id', auth()->user()->organization_id)
            ->forYear($year)
            ->when($month, fn($q) => $q->where('month', $month))
            ->when($currency, fn($q) => $q->where('currency', $currency))
            ->when($categoryId, fn($q) => $q->where('category_option_id', $categoryId))
            ->orderBy('occurred_at')
            ->get();

        $filename = 'expenses_export_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($expenses) {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, [
                'document_name',
                'amount',
                'currency',
                'occurred_at',
                'category',
                'note',
                'year',
                'month'
            ]);

            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->document_name,
                    $expense->amount,
                    $expense->currency,
                    $expense->occurred_at->format('Y-m-d'),
                    $expense->category?->name ?? '',
                    $expense->note ?? '',
                    $expense->year,
                    $expense->month,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
