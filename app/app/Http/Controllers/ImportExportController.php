<?php

namespace App\Http\Controllers;

use App\Services\Import\CsvImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

// Models
use App\Models\Client;
use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;
use App\Models\Subscription;
use App\Models\Domain;
use App\Models\InternalAccount;
use App\Models\SettingOption;

class ImportExportController extends Controller
{
    protected CsvImportService $csvImporter;

    public function __construct(CsvImportService $csvImporter)
    {
        $this->csvImporter = $csvImporter;
    }
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

        // SECURITY: Whitelist allowed import methods to prevent arbitrary method invocation
        $allowedImportMethods = [
            'clients' => 'importClients',
            'revenues' => 'importRevenues',
            'expenses' => 'importExpenses',
            'subscriptions' => 'importSubscriptions',
            'domains' => 'importDomains',
            'credentials' => 'importCredentials',
            'accounts' => 'importAccounts',
        ];

        if (!isset($allowedImportMethods[$module])) {
            return back()->with('error', 'Import not supported for this module');
        }

        $method = $allowedImportMethods[$module];

        return $this->$method($request);
    }

    /**
     * Download import template for a specific module
     */
    public function downloadTemplate($module)
    {
        $this->validateModule($module);

        // SECURITY: Whitelist allowed template methods
        $allowedTemplateMethods = [
            'clients' => 'downloadTemplateClients',
            'revenues' => 'downloadTemplateRevenues',
            'expenses' => 'downloadTemplateExpenses',
            'subscriptions' => 'downloadTemplateSubscriptions',
            'domains' => 'downloadTemplateDomains',
            'credentials' => 'downloadTemplateCredentials',
            'accounts' => 'downloadTemplateAccounts',
        ];

        if (!isset($allowedTemplateMethods[$module])) {
            abort(404, 'Template not available for this module');
        }

        $method = $allowedTemplateMethods[$module];

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

        // SECURITY: Whitelist allowed export methods
        $allowedExportMethods = [
            'clients' => 'exportClients',
            'revenues' => 'exportRevenues',
            'expenses' => 'exportExpenses',
            'subscriptions' => 'exportSubscriptions',
            'domains' => 'exportDomains',
            'credentials' => 'exportCredentials',
            'accounts' => 'exportAccounts',
        ];

        if (!isset($allowedExportMethods[$module])) {
            return back()->with('error', 'Export not supported for this module');
        }

        $method = $allowedExportMethods[$module];

        if (!method_exists($this, $method)) {
            return back()->with('error', 'Export not supported for this module');
        }

        // AUDIT: Log all data exports for compliance and security tracking
        $startTime = microtime(true);
        Log::channel('audit')->info('Data export initiated', [
            'module' => $module,
            'user_id' => auth()->id(),
            'user_email' => auth()->user()?->email,
            'organization_id' => auth()->user()?->organization_id,
            'ip_address' => $request->ip(),
            'filters' => $request->except(['_token']),
        ]);

        try {
            $result = $this->$method($request);

            $duration = round((microtime(true) - $startTime) * 1000, 2);
            Log::channel('audit')->info('Data export completed', [
                'module' => $module,
                'user_id' => auth()->id(),
                'duration_ms' => $duration,
            ]);

            return $result;
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            Log::channel('audit')->error('Data export failed', [
                'module' => $module,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'duration_ms' => $duration,
            ]);
            throw $e;
        }
    }

    // ==================== CLIENTS ====================

    private function importClients(Request $request)
    {
        $defaultStatus = SettingOption::clientStatuses()->first();

        $result = $this->csvImporter->import(
            $request->file('csv_file'),
            [
                'name' => 'required|string|max:255',
                'tax_id' => ['nullable', 'string', 'max:100'],
            ],
            function($data, $rowNumber) use ($defaultStatus) {
                Client::create([
                    'name' => $this->csvImporter->getValue($data, 'name'),
                    'company_name' => $this->csvImporter->getValue($data, ['company_name', 'company']),
                    'tax_id' => $this->csvImporter->getValue($data, ['tax_id', 'cui']),
                    'registration_number' => $this->csvImporter->getValue($data, ['registration_number', 'reg_number']),
                    'contact_person' => $this->csvImporter->getValue($data, 'contact_person'),
                    'email' => $this->csvImporter->getValue($data, 'email'),
                    'phone' => $this->csvImporter->getValue($data, 'phone'),
                    'address' => $this->csvImporter->getValue($data, 'address'),
                    'vat_payer' => $this->csvImporter->getBooleanValue($data, 'vat_payer'),
                    'notes' => $this->csvImporter->getValue($data, 'notes'),
                    'status_id' => $defaultStatus?->id,
                ]);
            },
            function($data, $rowNumber) {
                $taxId = $this->csvImporter->getValue($data, ['tax_id', 'cui']);
                if ($taxId && Client::where('tax_id', $taxId)->where('user_id', auth()->id())->exists()) {
                    $this->csvImporter->addDuplicateError($rowNumber, "Client with tax_id '{$taxId}' already exists");
                    return true;
                }
                return false;
            }
        );

        return redirect()->route('import-export.index')
            ->with('success', $this->csvImporter->getSuccessMessage('Clients'))
            ->with('import_errors', $result['errors']);
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
        $result = $this->csvImporter->import(
            $request->file('csv_file'),
            [
                'document_name' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0',
                'currency' => 'required|in:RON,EUR',
                'occurred_at' => 'required|date',
            ],
            function($data, $rowNumber) {
                $clientId = null;
                $clientName = $this->csvImporter->getValue($data, ['client_name', 'client']);
                if ($clientName) {
                    $client = Client::where('name', 'like', "%{$clientName}%")->first();
                    $clientId = $client?->id;
                }

                $occurredAt = Carbon::parse($data['occurred_at']);

                FinancialRevenue::create([
                    'document_name' => $this->csvImporter->getValue($data, 'document_name'),
                    'amount' => (float) $data['amount'],
                    'currency' => strtoupper($this->csvImporter->getValue($data, 'currency')),
                    'occurred_at' => $occurredAt,
                    'year' => $occurredAt->year,
                    'month' => $occurredAt->month,
                    'client_id' => $clientId,
                    'note' => $this->csvImporter->getValue($data, 'note'),
                ]);
            }
        );

        return redirect()->route('import-export.index')
            ->with('success', $this->csvImporter->getSuccessMessage('Revenues'))
            ->with('import_errors', $result['errors']);
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
        $result = $this->csvImporter->import(
            $request->file('csv_file'),
            [
                'document_name' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0',
                'currency' => 'required|in:RON,EUR',
                'occurred_at' => 'required|date',
            ],
            function($data, $rowNumber) {
                $categoryId = null;
                $categoryLabel = $this->csvImporter->getValue($data, 'category');
                if ($categoryLabel) {
                    $category = SettingOption::active()->ordered()
                        ->where('name', 'like', "%{$categoryLabel}%")
                        ->first();
                    $categoryId = $category?->id;
                }

                $occurredAt = Carbon::parse($data['occurred_at']);

                FinancialExpense::create([
                    'document_name' => $this->csvImporter->getValue($data, 'document_name'),
                    'amount' => (float) $data['amount'],
                    'currency' => strtoupper($this->csvImporter->getValue($data, 'currency')),
                    'occurred_at' => $occurredAt,
                    'year' => $occurredAt->year,
                    'month' => $occurredAt->month,
                    'category_option_id' => $categoryId,
                    'note' => $this->csvImporter->getValue($data, 'note'),
                ]);
            }
        );

        return redirect()->route('import-export.index')
            ->with('success', $this->csvImporter->getSuccessMessage('Expenses'))
            ->with('import_errors', $result['errors']);
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
