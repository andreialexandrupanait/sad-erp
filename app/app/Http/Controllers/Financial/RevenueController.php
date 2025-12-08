<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Concerns\HandlesBulkActions;
use App\Http\Requests\Financial\StoreRevenueRequest;
use App\Http\Requests\Financial\UpdateRevenueRequest;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinancialRevenue;
use App\Models\FinancialFile;
use App\Models\Client;
use App\Models\SettingOption;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RevenueController extends Controller
{
    use HandlesBulkActions;
    public function index(Request $request)
    {
        // Get filter values from request or session, with defaults
        $year = $request->get('year', session('financial.filters.year', now()->year));
        $month = $request->get('month', session('financial.filters.month', now()->month));
        $currency = $request->get('currency', session('financial.filters.currency'));
        $clientId = $request->get('client_id', session('financial.filters.client_id'));
        $search = $request->get('search', '');

        // Store filter values in session for persistence
        session([
            'financial.filters.year' => $year,
            'financial.filters.month' => $month,
            'financial.filters.currency' => $currency,
            'financial.filters.client_id' => $clientId,
        ]);

        // Sorting
        $sortBy = $request->get('sort', 'occurred_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedColumns = ['occurred_at', 'amount', 'document_name', 'client_id', 'currency', 'created_at'];
        if (!in_array($sortBy, $allowedColumns)) {
            $sortBy = 'occurred_at';
        }

        $perPage = $request->get('per_page', 50);

        $revenues = FinancialRevenue::with(['client', 'files'])
            ->withCount('files')
            ->forYear($year)
            ->when($month, fn($q) => $q->where('month', $month))
            ->when($currency, fn($q) => $q->where('currency', $currency))
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->when($search, fn($q) => $q->where(function($query) use ($search) {
                $query->where('document_name', 'like', "%{$search}%")
                      ->orWhere('note', 'like', "%{$search}%")
                      ->orWhereHas('client', fn($q) => $q->where('name', 'like', "%{$search}%"));
            }))
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        // Widget 1: Calculate FILTERED totals (respects ALL filters including month)
        $filteredTotals = FinancialRevenue::forYear($year)
            ->when($month, fn($q) => $q->where('month', $month))
            ->when($currency, fn($q) => $q->where('currency', $currency))
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->select('currency', DB::raw('SUM(amount) as total'))
            ->groupBy('currency')
            ->get()
            ->mapWithKeys(fn($item) => [$item->currency => $item->total]);

        // Widget 2: Calculate YEARLY totals (all currencies, always full year)
        $yearTotals = FinancialRevenue::forYear($year)
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->select('currency', DB::raw('SUM(amount) as total'))
            ->groupBy('currency')
            ->get()
            ->mapWithKeys(fn($item) => [$item->currency => $item->total]);

        // Count total records
        $recordCount = FinancialRevenue::forYear($year)
            ->when($month, fn($q) => $q->where('month', $month))
            ->when($currency, fn($q) => $q->where('currency', $currency))
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->when($search, fn($q) => $q->where(function($query) use ($search) {
                $query->where('document_name', 'like', "%{$search}%")
                      ->orWhere('note', 'like', "%{$search}%")
                      ->orWhereHas('client', fn($q) => $q->where('name', 'like', "%{$search}%"));
            }))
            ->count();

        $clients = Client::orderBy('name')->get();
        $currencies = SettingOption::currencies()->get();

        // Available years - show all years from 2019 to present
        $currentYear = now()->year;
        $availableYears = collect(range(2019, $currentYear))->reverse()->values();

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
            'yearTotals',
            'recordCount',
            'year',
            'month',
            'currency',
            'clientId',
            'search',
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

    public function store(StoreRevenueRequest $request)
    {
        $revenue = FinancialRevenue::create($request->validated());

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
                'message' => __('messages.revenue_created'),
                'revenue' => $revenue->load('client', 'files'),
            ], 201);
        }

        return redirect()->route('financial.revenues.index')
            ->with('success', __('messages.revenue_added'));
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

    public function update(UpdateRevenueRequest $request, FinancialRevenue $revenue)
    {
        $validated = $request->validated();

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
                $this->uploadFile($file, $revenue);
            }
        }

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('messages.revenue_updated'),
                'revenue' => $revenue->fresh()->load('client', 'files'),
            ]);
        }

        return redirect()->route('financial.revenues.index')
            ->with('success', __('messages.revenue_updated'));
    }

    public function destroy(FinancialRevenue $revenue)
    {
        $revenue->delete();

        return redirect()->route('financial.revenues.index')
            ->with('success', __('messages.revenue_deleted'));
    }

    /**
     * Upload a file and attach it to the revenue
     */
    private function uploadFile($file, FinancialRevenue $revenue)
    {
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());

        // Validate extension against allowed types (defense in depth)
        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar'];
        if (!in_array($extension, $allowedExtensions)) {
            throw new \InvalidArgumentException('Invalid file extension');
        }

        // Get year and month from revenue
        $year = $revenue->year;
        $month = $revenue->month;
        $monthName = romanian_month($month);
        $tip = 'Incasari'; // Revenue files folder

        // Sanitize document name to prevent path traversal and special characters
        $documentName = Str::slug($revenue->document_name, ' ');
        $documentName = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $documentName);
        $documentName = trim($documentName) ?: 'document';

        // Always add "Factura " prefix
        $newFileName = "Factura {$documentName}.{$extension}";

        // Storage path: /year/month_name/type/
        $storagePath = "{$year}/{$monthName}/{$tip}";

        // Check if file already exists and add suffix if needed
        $finalFileName = $newFileName;
        $counter = 1;
        while (\Storage::disk('financial')->exists("{$storagePath}/{$finalFileName}")) {
            $finalFileName = "{$documentName} ({$counter}).{$extension}";
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


    protected function getBulkModelClass(): string
    {
        return FinancialRevenue::class;
    }

    protected function getExportEagerLoads(): array
    {
        return ['client', 'category'];
    }

    protected function exportToCsv($revenues)
    {
        $filename = "revenues_export_" . date("Y-m-d_His") . ".csv";

        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($revenues) {
            $file = fopen("php://output", "w");
            fputcsv($file, ["Document", "Client", "Amount", "Currency", "Date", "Category"]);

            foreach ($revenues as $revenue) {
                fputcsv($file, [
                    $revenue->document_name,
                    $revenue->client?->name ?? "N/A",
                    $revenue->amount,
                    $revenue->currency,
                    $revenue->occurred_at?->format("Y-m-d") ?? "N/A",
                    $revenue->category?->name ?? "N/A",
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
