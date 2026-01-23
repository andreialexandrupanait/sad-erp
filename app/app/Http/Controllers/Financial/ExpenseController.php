<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Concerns\HandlesBulkActions;
use App\Http\Requests\Financial\StoreExpenseRequest;
use App\Http\Requests\Financial\UpdateExpenseRequest;
use App\Services\Financial\QueryBuilderService;
use App\Services\NomenclatureService;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinancialExpense;
use App\Models\FinancialFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExpenseController extends Controller
{
    use HandlesBulkActions;

    protected QueryBuilderService $queryBuilder;
    protected NomenclatureService $nomenclatureService;

    public function __construct(
        QueryBuilderService $queryBuilder,
        NomenclatureService $nomenclatureService
    ) {
        $this->queryBuilder = $queryBuilder;
        $this->nomenclatureService = $nomenclatureService;
        $this->authorizeResource(FinancialExpense::class, 'expense');
    }

    public function index(Request $request)
    {
        // Get filter values from request or session, with defaults
        $year = $request->get('year', session('financial.filters.year', now()->year));
        // Handle clear_month parameter to show all months
        if ($request->has('clear_month')) {
            $month = null;
            session()->forget('financial.filters.month');
        } else {
            // Default to current month if no month filter is set
            $month = $request->get('month', session('financial.filters.month', now()->month));
        }
        $currency = $request->get('currency', session('financial.filters.currency'));
        $categoryId = $request->get('category_id', session('financial.filters.category_id'));
        $search = $request->get('search', '');

        // Store filter values in session for persistence
        session([
            'financial.filters.year' => $year,
            'financial.filters.month' => $month,
            'financial.filters.currency' => $currency,
            'financial.filters.category_id' => $categoryId,
        ]);

        // Sorting
        $sortBy = $request->get('sort', 'occurred_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedColumns = ['occurred_at', 'amount', 'document_name', 'category_option_id', 'currency', 'created_at'];
        if (!in_array($sortBy, $allowedColumns)) {
            $sortBy = 'occurred_at';
        }

        $perPage = $request->get('per_page', 50);

        // Prepare filters array
        $filters = [
            'year' => $year,
            'month' => $month,
            'currency' => $currency,
            'category_option_id' => $categoryId,
            'search' => $search,
        ];

        // Build main paginated query using query builder service
        $expenses = $this->queryBuilder->buildPaginatedQuery(
            FinancialExpense::class,
            $filters,
            $sortBy,
            $sortDir,
            $perPage,
            ['category', 'files'],
            ['files']
        );

        // Widget 1: Calculate FILTERED totals (respects ALL filters including month)
        $filteredQuery = FinancialExpense::query();
        $this->queryBuilder->applyFilters($filteredQuery, $filters);
        $filteredTotals = $this->queryBuilder->calculateFilteredTotals($filteredQuery);

        // Widget 2: Calculate YEARLY totals (all currencies, always full year)
        $yearTotals = $this->queryBuilder->calculateYearlyTotals(
            FinancialExpense::class,
            $year,
            $categoryId ? ["category_option_id" => $categoryId] : []
        );

        // Total RON for filtered results (sum of all amounts)
        $filteredTotalRon = (clone $filteredQuery)->sum("amount");

        // Total RON (sum of all amounts - for display widget)
        $yearTotalRon = FinancialExpense::forYear($year)
            ->when($categoryId, fn($q) => $q->where("category_option_id", $categoryId))
            ->sum("amount");

        // Count total records
        $recordCount = $this->queryBuilder->countFiltered(FinancialExpense::class, $filters);

        // Category breakdown for current filter
        $categoryBreakdownQuery = FinancialExpense::forYear($year);
        if ($month) {
            $categoryBreakdownQuery->where('month', $month);
        }
        if ($currency) {
            $categoryBreakdownQuery->where('currency', $currency);
        }
        $categoryBreakdown = $this->queryBuilder->getCategoryBreakdown($categoryBreakdownQuery);

        $categories = $this->nomenclatureService->getExpenseCategories();
        $currencies = $this->nomenclatureService->getCurrencies();

        // Available years
        $availableYears = $this->queryBuilder->getAvailableYears();

        // Get months with transactions for the selected year
        $monthsQuery = FinancialExpense::forYear($year);
        if ($currency) {
            $monthsQuery->where('currency', $currency);
        }
        if ($categoryId) {
            $monthsQuery->where('category_option_id', $categoryId);
        }
        $monthsWithTransactions = $this->queryBuilder->getMonthsWithTransactions($monthsQuery);

        return view('financial.expenses.index', compact(
            'expenses',
            'filteredTotals', 'filteredTotalRon',
            'yearTotals', 'yearTotalRon',
            'recordCount',
            'categoryBreakdown',
            'year',
            'month',
            'currency',
            'categoryId',
            'search',
            'categories',
            'currencies',
            'availableYears',
            'monthsWithTransactions'
        ));
    }

    public function create()
    {
        $categories = $this->nomenclatureService->getExpenseCategories();
        $currencies = $this->nomenclatureService->getCurrencies();
        return view('financial.expenses.create', compact('categories', 'currencies'));
    }

    public function store(StoreExpenseRequest $request)
    {
        $expense = FinancialExpense::create($request->validated());

        // Handle file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $this->uploadFile($file, $expense);
            }
        }

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('messages.expense_created'),
                'expense' => $expense->load('category', 'files'),
            ], 201);
        }

        return redirect()->route('financial.expenses.index')
            ->with('success', __('messages.expense_added'));
    }

    public function show(FinancialExpense $expense)
    {
        $expense->load('category', 'files');
        return view('financial.expenses.show', compact('expense'));
    }

    public function edit(FinancialExpense $expense)
    {
        $expense->load('files');
        $categories = $this->nomenclatureService->getExpenseCategories();
        $currencies = $this->nomenclatureService->getCurrencies();
        return view('financial.expenses.edit', compact('expense', 'categories', 'currencies'));
    }

    public function update(UpdateExpenseRequest $request, FinancialExpense $expense)
    {
        $validated = $request->validated();

        // Update year and month based on occurred_at
        $date = \Carbon\Carbon::parse($validated['occurred_at']);
        $validated['year'] = $date->year;
        $validated['month'] = $date->month;

        $expense->update($validated);

        // Handle file deletions
        if ($request->has('delete_files')) {
            foreach ($request->input('delete_files') as $fileId) {
                $file = FinancialFile::find($fileId);
                if ($file && $file->entity_id === $expense->id && $file->entity_type === FinancialExpense::class) {
                    // Delete physical file from 'financial' disk
                    if (Storage::disk('financial')->exists($file->file_path)) {
                        Storage::disk('financial')->delete($file->file_path);
                    }
                    // Delete database record
                    $file->delete();
                }
            }
        }

        // Handle new file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $this->uploadFile($file, $expense);
            }
        }

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('messages.expense_updated'),
                'expense' => $expense->fresh()->load('category', 'files'),
            ]);
        }

        return redirect()->route('financial.expenses.index')
            ->with('success', __('messages.expense_updated'));
    }

    public function destroy(FinancialExpense $expense)
    {
        $expense->delete();

        return redirect()->route('financial.expenses.index')
            ->with('success', __('messages.expense_deleted'));
    }

    /**
     * Upload a file and attach it to the expense
     */
    private function uploadFile($file, FinancialExpense $expense)
    {
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());

        // Validate extension against allowed types (defense in depth)
        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar'];
        if (!in_array($extension, $allowedExtensions)) {
            throw new \InvalidArgumentException('Invalid file extension');
        }

        // Get year and month from expense
        $year = $expense->year;
        $month = $expense->month;
        $monthName = romanian_month($month);
        $tip = 'Plati'; // Expense files folder

        // Generate file name: DD.MM - Document Name.ext
        $date = $expense->occurred_at;
        $day = $date->format('d');
        $monthNum = $date->format('m');

        // Sanitize document name to prevent path traversal and special characters
        $documentName = Str::slug($expense->document_name, ' ');
        $documentName = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $documentName);
        $documentName = trim($documentName) ?: 'document';

        $newFileName = "{$day}.{$monthNum} - {$documentName}.{$extension}";

        // Storage path: /year/month_name/type/
        $storagePath = "{$year}/{$monthName}/{$tip}";

        // Check if file already exists and add suffix if needed
        $finalFileName = $newFileName;
        $counter = 1;
        while (\Storage::disk('financial')->exists("{$storagePath}/{$finalFileName}")) {
            $finalFileName = "{$day}.{$monthNum} - {$documentName} ({$counter}).{$extension}";
            $counter++;
        }

        // Show warning if duplicate exists
        if ($counter > 1) {
            session()->flash('warning', "A file with this name already exists. Saved as: {$finalFileName}");
        }

        // Store file using the 'financial' disk
        $path = $file->storeAs($storagePath, $finalFileName, 'financial');

        // Create database record
        FinancialFile::create([
            'file_name' => $finalFileName,
            'file_path' => $path,
            'file_type' => $file->getClientMimeType(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'entity_type' => FinancialExpense::class,
            'entity_id' => $expense->id,
            'an' => $year,
            'luna' => $month,
            'tip' => 'plata',
        ]);
    }


    protected function getBulkModelClass(): string
    {
        return \App\Models\FinancialExpense::class;
    }

    protected function getExportEagerLoads(): array
    {
        return ['category'];
    }

    protected function exportToCsv($expenses)
    {
        $filename = "expenses_export_" . date("Y-m-d_His") . ".csv";

        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($expenses) {
            $file = fopen("php://output", "w");
            fputcsv($file, ["Document Name", "Amount", "Currency", "Date", "Category", "Note"]);

            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->document_name ?? "N/A",
                    $expense->amount,
                    $expense->currency,
                    $expense->occurred_at?->format("Y-m-d") ?? "N/A",
                    $expense->category?->label ?? $expense->category?->name ?? "N/A",
                    $expense->note ?? "",
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
