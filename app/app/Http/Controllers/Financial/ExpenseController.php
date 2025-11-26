<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\FinancialExpense;
use App\Models\FinancialFile;
use App\Models\SettingOption;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        // Get filter values from request or session, with defaults
        $year = $request->get('year', session('financial.filters.year', now()->year));
        $month = $request->get('month', session('financial.filters.month', now()->month));
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

        $expenses = FinancialExpense::with(['category', 'files'])
            ->withCount('files')
            ->forYear($year)
            ->when($month, fn($q) => $q->where('month', $month))
            ->when($currency, fn($q) => $q->where('currency', $currency))
            ->when($categoryId, fn($q) => $q->where('category_option_id', $categoryId))
            ->when($search, fn($q) => $q->where(function($query) use ($search) {
                $query->where('document_name', 'like', "%{$search}%")
                      ->orWhere('note', 'like', "%{$search}%");
            }))
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        // Widget 1: Calculate FILTERED totals (respects ALL filters including month)
        $filteredTotals = FinancialExpense::forYear($year)
            ->when($month, fn($q) => $q->where('month', $month))
            ->when($currency, fn($q) => $q->where('currency', $currency))
            ->when($categoryId, fn($q) => $q->where('category_option_id', $categoryId))
            ->select('currency', DB::raw('SUM(amount) as total'))
            ->groupBy('currency')
            ->get()
            ->mapWithKeys(fn($item) => [$item->currency => $item->total]);

        // Widget 2: Calculate YEARLY totals (all currencies, always full year)
        $yearTotals = FinancialExpense::forYear($year)
            ->when($categoryId, fn($q) => $q->where('category_option_id', $categoryId))
            ->select('currency', DB::raw('SUM(amount) as total'))
            ->groupBy('currency')
            ->get()
            ->mapWithKeys(fn($item) => [$item->currency => $item->total]);

        // Count total records
        $recordCount = FinancialExpense::forYear($year)
            ->when($month, fn($q) => $q->where('month', $month))
            ->when($currency, fn($q) => $q->where('currency', $currency))
            ->when($categoryId, fn($q) => $q->where('category_option_id', $categoryId))
            ->when($search, fn($q) => $q->where(function($query) use ($search) {
                $query->where('document_name', 'like', "%{$search}%")
                      ->orWhere('note', 'like', "%{$search}%");
            }))
            ->count();

        // Category breakdown for current filter
        $categoryBreakdown = FinancialExpense::forYear($year)
            ->when($month, fn($q) => $q->where('month', $month))
            ->when($currency, fn($q) => $q->where('currency', $currency))
            ->whereNotNull('category_option_id')
            ->select('category_option_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category_option_id')
            ->with('category')
            ->get()
            ->sortByDesc('total')
            ->take(5);

        $categories = SettingOption::rootCategories()->with('children')->get();
        $currencies = SettingOption::currencies()->get();

        // Available years - show all years from 2019 to present
        $currentYear = now()->year;
        $availableYears = collect(range(2019, $currentYear))->reverse()->values();

        // Get months with transactions for the selected year (with transaction count and total amount)
        $monthsWithTransactions = FinancialExpense::forYear($year)
            ->when($currency, fn($q) => $q->where('currency', $currency))
            ->when($categoryId, fn($q) => $q->where('category_option_id', $categoryId))
            ->select('month', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('month')
            ->get()
            ->mapWithKeys(fn($item) => [$item->month => [
                'count' => $item->count,
                'total' => $item->total
            ]]);

        return view('financial.expenses.index', compact(
            'expenses',
            'filteredTotals',
            'yearTotals',
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
        $categories = SettingOption::rootCategories()->with('children')->get();
        $currencies = SettingOption::currencies()->get();
        return view('financial.expenses.create', compact('categories', 'currencies'));
    }

    public function store(Request $request)
    {
        $validCurrencies = SettingOption::currencies()->pluck('value')->toArray();

        $validated = $request->validate([
            'document_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'currency' => ['required', Rule::in($validCurrencies)],
            'occurred_at' => 'required|date',
            'category_option_id' => 'nullable|exists:settings_options,id',
            'note' => 'nullable|string',
            'files.*' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,zip,rar',
        ]);

        $expense = FinancialExpense::create($validated);

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
                'message' => 'Expense created successfully!',
                'expense' => $expense->load('category', 'files'),
            ], 201);
        }

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
        $expense->load('files');
        $categories = SettingOption::rootCategories()->with('children')->get();
        $currencies = SettingOption::currencies()->get();
        return view('financial.expenses.edit', compact('expense', 'categories', 'currencies'));
    }

    public function update(Request $request, FinancialExpense $expense)
    {
        $validCurrencies = SettingOption::currencies()->pluck('value')->toArray();

        $validated = $request->validate([
            'document_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'currency' => ['required', Rule::in($validCurrencies)],
            'occurred_at' => 'required|date',
            'category_option_id' => 'nullable|exists:settings_options,id',
            'note' => 'nullable|string',
            'files.*' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,zip,rar',
            'delete_files' => 'nullable|array',
            'delete_files.*' => 'integer|exists:financial_files,id',
        ]);

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
                'message' => 'Expense updated successfully!',
                'expense' => $expense->fresh()->load('category', 'files'),
            ]);
        }

        return redirect()->route('financial.expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(FinancialExpense $expense)
    {
        $expense->delete();

        return redirect()->route('financial.expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }

    /**
     * Upload a file and attach it to the expense
     */
    private function uploadFile($file, FinancialExpense $expense)
    {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();

        // Get year and month from expense
        $year = $expense->year;
        $month = $expense->month;
        $monthName = romanian_month($month);
        $tip = 'Plati'; // Expense files folder

        // Generate file name: DD.MM - Document Name.ext
        $date = $expense->occurred_at;
        $day = $date->format('d');
        $monthNum = $date->format('m');
        $documentName = $expense->document_name;

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

}
