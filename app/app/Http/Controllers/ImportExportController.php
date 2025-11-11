<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

// Models
use App\Models\Client;
use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;
use App\Models\Subscription;
use App\Models\Domain;
use App\Models\InternalAccount;
use App\Models\AccessCredential;
use App\Models\SettingOption;

class ImportExportController extends Controller
{
    /**
     * Show the centralized import/export page
     */
    public function index()
    {
        return view('import-export.index');
    }

    // ==================== IMPORT ====================

    /**
     * Show import form for a specific module
     */
    public function showImportForm($module)
    {
        $this->validateModule($module);

        return view('import-export.import', compact('module'));
    }

    /**
     * Process import for a specific module
     */
    public function import(Request $request, $module)
    {
        $this->validateModule($module);

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $method = 'import' . ucfirst($module);

        if (!method_exists($this, $method)) {
            return back()->with('error', 'Import not supported for this module');
        }

        return $this->$method($request);
    }

    /**
     * Download import template for a specific module
     */
    public function downloadTemplate($module)
    {
        $this->validateModule($module);

        $method = 'downloadTemplate' . ucfirst($module);

        if (!method_exists($this, $method)) {
            abort(404, 'Template not available for this module');
        }

        return $this->$method();
    }

    // ==================== EXPORT ====================

    /**
     * Export data for a specific module
     */
    public function export(Request $request, $module)
    {
        $this->validateModule($module);

        $method = 'export' . ucfirst($module);

        if (!method_exists($this, $method)) {
            return back()->with('error', 'Export not supported for this module');
        }

        return $this->$method($request);
    }

    // ==================== CLIENTS ====================

    private function importClients(Request $request)
    {
        $file = $request->file('csv_file');
        $csvData = array_map('str_getcsv', file($file->getRealPath()));
        $header = array_shift($csvData);
        $header = array_map('trim', $header);

        $defaultStatus = \App\Models\SettingOption::clientStatuses()->first();
        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($csvData as $index => $row) {
            $rowNumber = $index + 2;
            if (empty(array_filter($row))) continue;

            $data = array_combine($header, $row);

            $validator = Validator::make($data, [
                'name' => 'required|string|max:255',
                'tax_id' => ['nullable', 'string', 'max:100'],
            ]);

            if ($validator->fails()) {
                $errors[] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                $skipped++;
                continue;
            }

            if (!empty($data['tax_id'])) {
                $exists = Client::where('tax_id', trim($data['tax_id']))
                    ->where('user_id', auth()->id())
                    ->exists();
                if ($exists) {
                    $errors[] = "Row {$rowNumber}: Client with tax_id '{$data['tax_id']}' already exists";
                    $skipped++;
                    continue;
                }
            }

            try {
                Client::create([
                    'name' => trim($data['name'] ?? ''),
                    'company_name' => trim($data['company_name'] ?? $data['company'] ?? ''),
                    'tax_id' => trim($data['tax_id'] ?? $data['cui'] ?? ''),
                    'registration_number' => trim($data['registration_number'] ?? $data['reg_number'] ?? ''),
                    'contact_person' => trim($data['contact_person'] ?? ''),
                    'email' => trim($data['email'] ?? ''),
                    'phone' => trim($data['phone'] ?? ''),
                    'address' => trim($data['address'] ?? ''),
                    'vat_payer' => in_array(strtolower(trim($data['vat_payer'] ?? '')), ['yes', 'da', '1', 'true']),
                    'notes' => trim($data['notes'] ?? ''),
                    'status_id' => $defaultStatus ? $defaultStatus->id : null,
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                $skipped++;
            }
        }

        return redirect()->route('import-export.index')
            ->with('success', "Clients: {$imported} imported, {$skipped} skipped")
            ->with('import_errors', $errors);
    }

    private function downloadTemplateClients()
    {
        $filename = 'clients_template_' . date('Y-m-d') . '.csv';
        return $this->streamCsv($filename, function($file) {
            fputcsv($file, ['name', 'company_name', 'tax_id', 'registration_number', 'contact_person', 'email', 'phone', 'address', 'vat_payer', 'notes']);
            fputcsv($file, ['Client Name', 'Company SRL', 'RO12345678', 'J40/1234/2020', 'John Doe', 'contact@example.com', '+40712345678', 'Str. Example 123, Bucharest', 'yes', 'Important client']);
        });
    }

    private function exportClients(Request $request)
    {
        $search = $request->get('search');
        $statusId = $request->get('status_id');

        $clients = Client::with('status')
            ->when($search, fn($q) => $q->search($search))
            ->when($statusId, fn($q) => $q->where('status_id', $statusId))
            ->orderBy('name')
            ->get();

        $filename = 'clients_export_' . date('Y-m-d') . '.csv';
        return $this->streamCsv($filename, function($file) use ($clients) {
            fputcsv($file, ['name', 'company_name', 'tax_id', 'registration_number', 'contact_person', 'email', 'phone', 'address', 'vat_payer', 'status', 'notes']);
            foreach ($clients as $client) {
                fputcsv($file, [
                    $client->name,
                    $client->company_name,
                    $client->tax_id,
                    $client->registration_number,
                    $client->contact_person,
                    $client->email,
                    $client->phone,
                    $client->address,
                    $client->vat_payer ? 'yes' : 'no',
                    $client->status?->label ?? '',
                    $client->notes,
                ]);
            }
        });
    }

    // ==================== REVENUES ====================

    private function importRevenues(Request $request)
    {
        $file = $request->file('csv_file');
        $csvData = array_map('str_getcsv', file($file->getRealPath()));
        $header = array_shift($csvData);
        $header = array_map('trim', $header);

        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($csvData as $index => $row) {
            $rowNumber = $index + 2;
            if (empty(array_filter($row))) continue;

            $data = array_combine($header, $row);

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
                $clientId = null;
                if (!empty($data['client_name'] ?? $data['client'] ?? '')) {
                    $clientName = trim($data['client_name'] ?? $data['client']);
                    $client = Client::where('name', 'like', "%{$clientName}%")->first();
                    $clientId = $client?->id;
                }

                $occurredAt = Carbon::parse($data['occurred_at']);

                FinancialRevenue::create([
                    'document_name' => trim($data['document_name']),
                    'amount' => (float) $data['amount'],
                    'currency' => strtoupper(trim($data['currency'])),
                    'occurred_at' => $occurredAt,
                    'year' => $occurredAt->year,
                    'month' => $occurredAt->month,
                    'client_id' => $clientId,
                    'note' => trim($data['note'] ?? ''),
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                $skipped++;
            }
        }

        return redirect()->route('import-export.index')
            ->with('success', "Revenues: {$imported} imported, {$skipped} skipped")
            ->with('import_errors', $errors);
    }

    private function downloadTemplateRevenues()
    {
        $filename = 'revenues_template_' . date('Y-m-d') . '.csv';
        return $this->streamCsv($filename, function($file) {
            fputcsv($file, ['document_name', 'amount', 'currency', 'occurred_at', 'client_name', 'note']);
            fputcsv($file, ['Factura #2025001', '1500.00', 'RON', date('Y-m-d'), 'Example Client', 'Monthly service fee']);
        });
    }

    private function exportRevenues(Request $request)
    {
        $year = $request->get('year', now()->year);

        $revenues = FinancialRevenue::with('client')
            ->forYear($year)
            ->orderBy('occurred_at')
            ->get();

        $filename = 'revenues_export_' . date('Y-m-d') . '.csv';
        return $this->streamCsv($filename, function($file) use ($revenues) {
            fputcsv($file, ['document_name', 'amount', 'currency', 'occurred_at', 'client_name', 'note', 'year', 'month']);
            foreach ($revenues as $revenue) {
                fputcsv($file, [
                    $revenue->document_name,
                    $revenue->amount,
                    $revenue->currency,
                    $revenue->occurred_at->format('Y-m-d'),
                    $revenue->client?->name ?? '',
                    $revenue->note ?? '',
                    $revenue->year,
                    $revenue->month,
                ]);
            }
        });
    }

    // ==================== EXPENSES ====================

    private function importExpenses(Request $request)
    {
        $file = $request->file('csv_file');
        $csvData = array_map('str_getcsv', file($file->getRealPath()));
        $header = array_shift($csvData);
        $header = array_map('trim', $header);

        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($csvData as $index => $row) {
            $rowNumber = $index + 2;
            if (empty(array_filter($row))) continue;

            $data = array_combine($header, $row);

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
                $categoryId = null;
                if (!empty($data['category'] ?? '')) {
                    $categoryLabel = trim($data['category']);
                    $category = SettingOption::active()->ordered()
                        ->where('name', 'like', "%{$categoryLabel}%")
                        ->first();
                    $categoryId = $category?->id;
                }

                $occurredAt = Carbon::parse($data['occurred_at']);

                \App\Models\FinancialExpense::create([
                    'document_name' => trim($data['document_name']),
                    'amount' => (float) $data['amount'],
                    'currency' => strtoupper(trim($data['currency'])),
                    'occurred_at' => $occurredAt,
                    'year' => $occurredAt->year,
                    'month' => $occurredAt->month,
                    'category_option_id' => $categoryId,
                    'note' => trim($data['note'] ?? ''),
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                $skipped++;
            }
        }

        return redirect()->route('import-export.index')
            ->with('success', "Expenses: {$imported} imported, {$skipped} skipped")
            ->with('import_errors', $errors);
    }

    private function downloadTemplateExpenses()
    {
        $filename = 'expenses_template_' . date('Y-m-d') . '.csv';
        return $this->streamCsv($filename, function($file) {
            fputcsv($file, ['document_name', 'amount', 'currency', 'occurred_at', 'category', 'note']);
            fputcsv($file, ['Factura Hosting #123', '250.00', 'RON', date('Y-m-d'), 'Cloud & Hosting', 'Monthly server cost']);
        });
    }

    private function exportExpenses(Request $request)
    {
        $year = $request->get('year', now()->year);

        $expenses = \App\Models\FinancialExpense::with('category')
            ->forYear($year)
            ->orderBy('occurred_at')
            ->get();

        $filename = 'expenses_export_' . date('Y-m-d') . '.csv';
        return $this->streamCsv($filename, function($file) use ($expenses) {
            fputcsv($file, ['document_name', 'amount', 'currency', 'occurred_at', 'category', 'note', 'year', 'month']);
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
        });
    }

    // ==================== HELPERS ====================

    private function validateModule($module)
    {
        $validModules = ['clients', 'revenues', 'expenses', 'subscriptions', 'domains', 'credentials', 'accounts'];

        if (!in_array($module, $validModules)) {
            abort(404, 'Invalid module');
        }
    }

    private function streamCsv($filename, $callback)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function() use ($callback) {
            $file = fopen('php://output', 'w');
            $callback($file);
            fclose($file);
        }, 200, $headers);
    }
}
