<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;
use App\Models\Client;
use App\Models\FinancialSetting;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ImportController extends Controller
{
    // ==================== REVENUE IMPORT/EXPORT ====================

    public function showRevenueImportForm()
    {
        return view('financial.revenues.import');
    }

    public function importRevenues(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $csvData = array_map('str_getcsv', file($file->getRealPath()));

        // Get header row
        $header = array_shift($csvData);
        $header = array_map('trim', $header);

        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($csvData as $index => $row) {
            $rowNumber = $index + 2;

            if (empty(array_filter($row))) continue;

            $data = array_combine($header, $row);

            // Validate required fields
            $validator = Validator::make($data, [
                'document_name' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0',
                'currency' => 'required|in:RON,EUR',
                'occurred_at' => 'required|date',
            ]);

            if ($validator->fails()) {
                $errors[] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                $skipped++;
                continue;
            }

            try {
                // Find client by name if provided
                $clientId = null;
                if (!empty($data['client_name'] ?? $data['client'] ?? '')) {
                    $clientName = trim($data['client_name'] ?? $data['client']);
                    $client = Client::where('name', 'like', "%{$clientName}%")->first();
                    $clientId = $client?->id;
                }

                $occurredAt = Carbon::parse($data['occurred_at']);

                $revenueData = [
                    'document_name' => trim($data['document_name']),
                    'amount' => (float) $data['amount'],
                    'currency' => strtoupper(trim($data['currency'])),
                    'occurred_at' => $occurredAt,
                    'year' => $occurredAt->year,
                    'month' => $occurredAt->month,
                    'client_id' => $clientId,
                    'note' => trim($data['note'] ?? ''),
                ];

                FinancialRevenue::create($revenueData);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                $skipped++;
            }
        }

        return redirect()
            ->route('financial.venituri.index')
            ->with('success', "Import completed: {$imported} revenues imported, {$skipped} skipped")
            ->with('import_errors', $errors);
    }

    public function downloadRevenueTemplate()
    {
        $filename = 'revenue_import_template_' . date('Y-m-d') . '.csv';

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
                'client_name',
                'note'
            ]);

            // Example row
            fputcsv($file, [
                'Factura #2025001',
                '1500.00',
                'RON',
                date('Y-m-d'),
                'Example Client SRL',
                'Monthly retainer fee'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportRevenues(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month');
        $currency = $request->get('currency');
        $clientId = $request->get('client_id');

        $revenues = FinancialRevenue::with('client')
            ->forYear($year)
            ->when($month, fn($q) => $q->where('month', $month))
            ->when($currency, fn($q) => $q->where('currency', $currency))
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->orderBy('occurred_at')
            ->get();

        $filename = 'revenues_export_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($revenues) {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, [
                'document_name',
                'amount',
                'currency',
                'occurred_at',
                'client_name',
                'client_tax_id',
                'note',
                'year',
                'month'
            ]);

            foreach ($revenues as $revenue) {
                fputcsv($file, [
                    $revenue->document_name,
                    $revenue->amount,
                    $revenue->currency,
                    $revenue->occurred_at->format('Y-m-d'),
                    $revenue->client?->name ?? '',
                    $revenue->client?->tax_id ?? '',
                    $revenue->note ?? '',
                    $revenue->year,
                    $revenue->month,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ==================== EXPENSE IMPORT/EXPORT ====================

    public function showExpenseImportForm()
    {
        return view('financial.expenses.import');
    }

    public function importExpenses(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $csvData = array_map('str_getcsv', file($file->getRealPath()));

        // Get header row
        $header = array_shift($csvData);
        $header = array_map('trim', $header);

        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($csvData as $index => $row) {
            $rowNumber = $index + 2;

            if (empty(array_filter($row))) continue;

            $data = array_combine($header, $row);

            // Validate required fields
            $validator = Validator::make($data, [
                'document_name' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0',
                'currency' => 'required|in:RON,EUR',
                'occurred_at' => 'required|date',
            ]);

            if ($validator->fails()) {
                $errors[] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                $skipped++;
                continue;
            }

            try {
                // Find category by label if provided
                $categoryId = null;
                if (!empty($data['category'] ?? '')) {
                    $categoryLabel = trim($data['category']);
                    $category = FinancialSetting::expenseCategories()
                        ->where('option_label', 'like', "%{$categoryLabel}%")
                        ->first();
                    $categoryId = $category?->id;
                }

                $occurredAt = Carbon::parse($data['occurred_at']);

                $expenseData = [
                    'document_name' => trim($data['document_name']),
                    'amount' => (float) $data['amount'],
                    'currency' => strtoupper(trim($data['currency'])),
                    'occurred_at' => $occurredAt,
                    'year' => $occurredAt->year,
                    'month' => $occurredAt->month,
                    'category_option_id' => $categoryId,
                    'note' => trim($data['note'] ?? ''),
                ];

                FinancialExpense::create($expenseData);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                $skipped++;
            }
        }

        return redirect()
            ->route('financial.cheltuieli.index')
            ->with('success', "Import completed: {$imported} expenses imported, {$skipped} skipped")
            ->with('import_errors', $errors);
    }

    public function downloadExpenseTemplate()
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

    public function exportExpenses(Request $request)
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
                    $expense->category?->option_label ?? '',
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
