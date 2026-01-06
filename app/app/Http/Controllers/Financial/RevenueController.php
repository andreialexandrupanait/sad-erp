<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Concerns\HandlesBulkActions;
use App\Http\Requests\Financial\StoreRevenueRequest;
use App\Http\Requests\Financial\UpdateRevenueRequest;
use App\Services\Financial\QueryBuilderService;
use App\Services\NomenclatureService;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinancialRevenue;
use App\Models\FinancialFile;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RevenueController extends Controller
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
        $this->authorizeResource(FinancialRevenue::class, 'revenue');
    }
    public function index(Request $request)
    {
        // Validate all filter parameters for security
        $validated = $request->validate([
            'year' => 'nullable|integer|min:2000|max:2100',
            'month' => 'nullable|integer|min:1|max:12',
            'currency' => 'nullable|string|in:RON,EUR,USD',
            'client_id' => 'nullable|integer|exists:clients,id',
            'search' => 'nullable|string|max:255',
            'sort' => 'nullable|string|in:occurred_at,amount,document_name,client_id,currency,created_at',
            'dir' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:10|max:100',
        ]);

        // Get filter values from validated request or session, with defaults
        $year = $validated['year'] ?? session('financial.filters.year', now()->year);
        $month = $validated['month'] ?? session('financial.filters.month', now()->month);
        $currency = $validated['currency'] ?? session('financial.filters.currency');
        $clientId = $validated['client_id'] ?? session('financial.filters.client_id');
        $search = $validated['search'] ?? '';

        // Store filter values in session for persistence
        session([
            'financial.filters.year' => $year,
            'financial.filters.month' => $month,
            'financial.filters.currency' => $currency,
            'financial.filters.client_id' => $clientId,
        ]);

        // Sorting (already validated above)
        $sortBy = $validated['sort'] ?? 'occurred_at';
        $sortDir = $validated['dir'] ?? 'desc';

        $perPage = $validated['per_page'] ?? 50;

        // Prepare filters array
        $filters = [
            'year' => $year,
            'month' => $month,
            'currency' => $currency,
            'client_id' => $clientId,
            'search' => $search,
            'searchFields' => ['client' => ['column' => 'name']],
        ];

        // Build main paginated query using query builder service
        $revenues = $this->queryBuilder->buildPaginatedQuery(
            FinancialRevenue::class,
            $filters,
            $sortBy,
            $sortDir,
            $perPage,
            ['client', 'files'],
            ['files']
        );

        // Widget 1: Calculate FILTERED totals (respects ALL filters including month)
        $filteredQuery = FinancialRevenue::query();
        $this->queryBuilder->applyFilters($filteredQuery, $filters);
        $filteredTotals = $this->queryBuilder->calculateFilteredTotals($filteredQuery);

        // Widget 2: Calculate YEARLY totals (all currencies, always full year)
        $yearTotals = $this->queryBuilder->calculateYearlyTotals(
            FinancialRevenue::class,
            $year,
            $clientId ? ['client_id' => $clientId] : []
        );

        // Count total records
        $recordCount = $this->queryBuilder->countFiltered(FinancialRevenue::class, $filters);

        $clients = Client::select('id', 'name')->orderBy('name')->get();
        $currencies = $this->nomenclatureService->getCurrencies();

        // Available years
        $availableYears = $this->queryBuilder->getAvailableYears();

        // Get months with transactions for the selected year
        $monthsQuery = FinancialRevenue::forYear($year);
        if ($currency) {
            $monthsQuery->where('currency', $currency);
        }
        if ($clientId) {
            $monthsQuery->where('client_id', $clientId);
        }
        $monthsWithTransactions = $this->queryBuilder->getMonthsWithTransactions($monthsQuery);

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
        $clients = Client::select('id', 'name')->orderBy('name')->get();
        $currencies = $this->nomenclatureService->getCurrencies();
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
        $clients = Client::select('id', 'name')->orderBy('name')->get();
        $currencies = $this->nomenclatureService->getCurrencies();
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
