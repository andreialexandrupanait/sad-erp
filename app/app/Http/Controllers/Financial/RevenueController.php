<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\FinancialRevenue;
use App\Models\FinancialFile;
use App\Models\Client;
use App\Models\SettingOption;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RevenueController extends Controller
{
    public function index(Request $request)
    {
        // Get filter values from request or session, with defaults
        $year = $request->get('year', session('financial.filters.year', now()->year));
        $month = $request->get('month', session('financial.filters.month', now()->month));
        $currency = $request->get('currency', session('financial.filters.currency'));
        $clientId = $request->get('client_id', session('financial.filters.client_id'));

        // Store filter values in session for persistence
        session([
            'financial.filters.year' => $year,
            'financial.filters.month' => $month,
            'financial.filters.currency' => $currency,
            'financial.filters.client_id' => $clientId,
        ]);

        $revenues = FinancialRevenue::with(['client', 'files'])
            ->withCount('files')
            ->forYear($year)
            ->when($month, fn($q) => $q->where('month', $month))
            ->when($currency, fn($q) => $q->where('currency', $currency))
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->latest('occurred_at')
            ->paginate(15);

        // Widget 1: Calculate FILTERED totals (respects ALL filters including month)
        $filteredTotals = FinancialRevenue::forYear($year)
            ->when($month, fn($q) => $q->where('month', $month))
            ->when($currency, fn($q) => $q->where('currency', $currency))
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->select('currency', DB::raw('SUM(amount) as total'))
            ->groupBy('currency')
            ->get()
            ->mapWithKeys(fn($item) => [$item->currency => $item->total]);

        // Widget 2: Calculate YEARLY totals (RON only, always full year)
        $yearTotalsRonOnly = FinancialRevenue::forYear($year)
            ->where('currency', 'RON')
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->sum('amount');

        // Count total records
        $recordCount = FinancialRevenue::forYear($year)
            ->when($month, fn($q) => $q->where('month', $month))
            ->when($currency, fn($q) => $q->where('currency', $currency))
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->count();

        $clients = Client::orderBy('name')->get();
        $currencies = SettingOption::currencies()->get();

        // Available years
        $availableYears = FinancialRevenue::select(DB::raw('DISTINCT year'))
            ->orderByDesc('year')
            ->pluck('year');

        // Get months with transactions for the selected year (with transaction count and total amount)
        $monthsWithTransactions = FinancialRevenue::forYear($year)
            ->when($currency, fn($q) => $q->where('currency', $currency))
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->select('month', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('month')
            ->get()
            ->mapWithKeys(fn($item) => [$item->month => [
                'count' => $item->count,
                'total' => $item->total
            ]]);

        return view('financial.revenues.index', compact(
            'revenues',
            'filteredTotals',
            'yearTotalsRonOnly',
            'recordCount',
            'year',
            'month',
            'currency',
            'clientId',
            'clients',
            'currencies',
            'availableYears',
            'monthsWithTransactions'
        ));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        $currencies = SettingOption::currencies()->get();
        return view('financial.revenues.create', compact('clients', 'currencies'));
    }

    public function store(Request $request)
    {
        $validCurrencies = SettingOption::currencies()->pluck('value')->toArray();

        $validated = $request->validate([
            'document_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'currency' => ['required', Rule::in($validCurrencies)],
            'occurred_at' => 'required|date',
            'client_id' => 'nullable|exists:clients,id',
            'note' => 'nullable|string',
            'files.*' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,zip,rar',
        ]);

        $revenue = FinancialRevenue::create($validated);

        // Handle file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $this->uploadFile($file, $revenue);
            }
        }

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Revenue created successfully!',
                'revenue' => $revenue->load('client', 'files'),
            ], 201);
        }

        return redirect()->route('financial.revenues.index')
            ->with('success', 'Revenue added successfully.');
    }

    public function show(FinancialRevenue $revenue)
    {
        $revenue->load('client', 'files');
        return view('financial.revenues.show', compact('revenue'));
    }

    public function edit(FinancialRevenue $revenue)
    {
        $revenue->load('files');
        $clients = Client::orderBy('name')->get();
        $currencies = SettingOption::currencies()->get();
        return view('financial.revenues.edit', compact('revenue', 'clients', 'currencies'));
    }

    public function update(Request $request, FinancialRevenue $revenue)
    {
        $validCurrencies = SettingOption::currencies()->pluck('value')->toArray();

        $validated = $request->validate([
            'document_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'currency' => ['required', Rule::in($validCurrencies)],
            'occurred_at' => 'required|date',
            'client_id' => 'nullable|exists:clients,id',
            'note' => 'nullable|string',
            'files.*' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,zip,rar',
            'delete_files' => 'nullable|array',
            'delete_files.*' => 'integer|exists:financial_files,id',
        ]);

        // Update year and month based on occurred_at
        $date = \Carbon\Carbon::parse($validated['occurred_at']);
        $validated['year'] = $date->year;
        $validated['month'] = $date->month;

        $revenue->update($validated);

        // Handle file deletions
        if ($request->has('delete_files')) {
            foreach ($request->input('delete_files') as $fileId) {
                $file = FinancialFile::find($fileId);
                if ($file && $file->entity_id === $revenue->id && $file->entity_type === FinancialRevenue::class) {
                    // Delete physical file
                    if (Storage::exists($file->file_path)) {
                        Storage::delete($file->file_path);
                    }
                    // Delete database record
                    $file->delete();
                }
            }
        }

        // Handle new file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $this->uploadFile($file, $revenue);
            }
        }

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Revenue updated successfully!',
                'revenue' => $revenue->fresh()->load('client', 'files'),
            ]);
        }

        return redirect()->route('financial.revenues.index')
            ->with('success', 'Revenue updated successfully.');
    }

    public function destroy(FinancialRevenue $revenue)
    {
        $revenue->delete();

        return redirect()->route('financial.revenues.index')
            ->with('success', 'Revenue deleted successfully.');
    }

    /**
     * Upload a file and attach it to the revenue
     */
    private function uploadFile($file, FinancialRevenue $revenue)
    {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();

        // Get year and month from revenue
        $year = $revenue->year;
        $month = $revenue->month;
        $monthName = $this->getRomanianMonthName($month);
        $tip = 'Incasari'; // Revenue files folder

        // Generate file name: DD.MM - Document Name.ext
        $date = $revenue->occurred_at;
        $day = $date->format('d');
        $monthNum = $date->format('m');
        $documentName = $revenue->document_name;

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
            'entity_type' => FinancialRevenue::class,
            'entity_id' => $revenue->id,
            'an' => $year,
            'luna' => $month,
            'tip' => 'incasare',
        ]);
    }

    /**
     * Get Romanian month name
     */
    private function getRomanianMonthName($monthNumber)
    {
        $months = [
            1 => 'Ianuarie',
            2 => 'Februarie',
            3 => 'Martie',
            4 => 'Aprilie',
            5 => 'Mai',
            6 => 'Iunie',
            7 => 'Iulie',
            8 => 'August',
            9 => 'Septembrie',
            10 => 'Octombrie',
            11 => 'Noiembrie',
            12 => 'Decembrie',
        ];

        return $months[$monthNumber] ?? 'Unknown';
    }

    /**
     * Sanitize filename for storage
     */
    private function sanitizeFileName($filename)
    {
        // Remove accents
        $filename = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $filename);
        // Keep only alphanumeric, dash, underscore
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '-', $filename);
        // Remove consecutive dashes
        $filename = preg_replace('/-+/', '-', $filename);
        // Trim dashes from ends
        $filename = trim($filename, '-');

        return $filename ?: 'file';
    }
}
