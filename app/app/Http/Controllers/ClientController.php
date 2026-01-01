<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesBulkActions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Models\Client;
use App\Services\NomenclatureService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    use HandlesBulkActions;

    protected NomenclatureService $nomenclatureService;

    public function __construct(NomenclatureService $nomenclatureService)
    {
        $this->nomenclatureService = $nomenclatureService;
        $this->authorizeResource(Client::class, 'client');
    }

    /**
     * Display a listing of the clients.
     */
    public function index(Request $request): View|JsonResponse
    {
        // For AJAX requests, return JSON
        if ($request->wantsJson() || $request->ajax()) {
            return $this->indexJson($request);
        }

        // For initial page load, return view with statuses for filter pills
        $statuses = $this->nomenclatureService->getClientStatuses();

        // Parse initial filters from URL for server-side rendering fallback
        $initialFilters = $this->parseUrlFilters($request);

        // Get initial clients data for immediate render (no loading state)
        $initialData = $this->getInitialClientsData($request, $statuses);

        return view('clients.index', [
            'statuses' => $statuses,
            'initialFilters' => $initialFilters,
            'initialClients' => $initialData['clients'],
            'initialPagination' => $initialData['pagination'],
            'initialStatusCounts' => $initialData['status_counts'],
        ]);
    }

    /**
     * Return clients data as JSON for AJAX requests.
     */
    private function indexJson(Request $request): JsonResponse
    {
        $query = Client::with(['status'])
            ->withCount(['revenues as invoices_count']);

        // Multi-status filter (comma-separated slugs)
        if ($request->filled('status')) {
            $statusSlugs = array_filter(explode(',', $request->status));
            if (!empty($statusSlugs)) {
                // Get status IDs from slugs
                $allStatuses = $this->nomenclatureService->getClientStatuses();
                $statusIds = $allStatuses->filter(function ($status) use ($statusSlugs) {
                    return in_array($status->slug, $statusSlugs);
                })->pluck('id')->toArray();

                if (!empty($statusIds)) {
                    $query->whereIn('status_id', $statusIds);
                }
            }
        }

        // Search functionality (q parameter for cleaner URLs)
        if ($request->filled('q')) {
            $query->search($request->q);
        }

        // Sort (format: "column" or "column:direction")
        $sort = $request->get('sort', 'name:asc');
        [$column, $direction] = $this->parseSort($sort);
        $query->orderBy($column, $direction);

        // Pagination
        $perPage = $request->get('limit', 25);
        $allowedPerPage = [10, 25, 50, 100];
        $perPage = in_array((int) $perPage, $allowedPerPage) ? (int) $perPage : 25;

        $clients = $query->paginate($perPage);

        // Get status counts for filter pills
        $statusCounts = $this->getStatusCounts($request);

        return response()->json([
            'clients' => $clients->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'slug' => $client->slug ?? null,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'contact_person' => $client->contact_person,
                    'company_name' => $client->company_name,
                    'tax_id' => $client->tax_id,
                    'total_incomes' => $client->total_incomes,
                    'invoices_count' => $client->invoices_count,
                    'status_id' => $client->status_id,
                    'status' => $client->status ? [
                        'id' => $client->status->id,
                        'name' => $client->status->name,
                        'slug' => $client->status->slug,
                        'color_background' => $client->status->color_background,
                        'color_text' => $client->status->color_text,
                    ] : null,
                    'created_at' => $client->created_at?->toISOString(),
                    'updated_at' => $client->updated_at?->toISOString(),
                ];
            }),
            'pagination' => [
                'total' => $clients->total(),
                'per_page' => $clients->perPage(),
                'current_page' => $clients->currentPage(),
                'last_page' => $clients->lastPage(),
                'from' => $clients->firstItem(),
                'to' => $clients->lastItem(),
            ],
            'status_counts' => $statusCounts,
        ]);
    }

    /**
     * Parse sort parameter (format: "column" or "column:direction").
     */
    private function parseSort(string $sort): array
    {
        $parts = explode(':', $sort);
        $column = $parts[0];
        $direction = $parts[1] ?? null;

        // Map frontend names to DB columns
        $columnMap = [
            'name' => 'name',
            'revenue' => 'total_incomes',
            'status' => 'status_id',
            'created' => 'created_at',
            'email' => 'email',
            // Also allow direct DB column names
            'total_incomes' => 'total_incomes',
            'status_id' => 'status_id',
            'created_at' => 'created_at',
        ];

        // Default direction per column (desc for metrics/dates)
        $defaultDesc = ['revenue', 'total_incomes', 'created', 'created_at'];

        // If no direction specified, use default
        if ($direction === null) {
            $direction = in_array($column, $defaultDesc) ? 'desc' : 'asc';
        }

        return [
            $columnMap[$column] ?? 'name',
            in_array($direction, ['asc', 'desc']) ? $direction : 'asc',
        ];
    }

    /**
     * Get status counts for filter pills.
     */
    private function getStatusCounts(Request $request): array
    {
        // Base counts without any status filter
        $counts = Client::selectRaw('status_id, COUNT(*) as count')
            ->groupBy('status_id')
            ->pluck('count', 'status_id')
            ->toArray();

        // Total count
        $total = array_sum($counts);

        return [
            'total' => $total,
            'by_status' => $counts,
        ];
    }

    /**
     * Parse URL filters for initial server-side rendering.
     */
    private function parseUrlFilters(Request $request): array
    {
        return [
            'status' => $request->filled('status') ? explode(',', $request->status) : [],
            'q' => $request->get('q', ''),
            'sort' => $request->get('sort', 'name:asc'),
            'page' => (int) $request->get('page', 1),
        ];
    }

    /**
     * Get initial clients data for server-side rendering (no loading flash).
     */
    private function getInitialClientsData(Request $request, $statuses): array
    {
        $query = Client::with(['status'])
            ->withCount(['revenues as invoices_count']);

        // Multi-status filter (comma-separated slugs)
        if ($request->filled('status')) {
            $statusSlugs = array_filter(explode(',', $request->status));
            if (!empty($statusSlugs)) {
                $statusIds = $statuses->filter(function ($status) use ($statusSlugs) {
                    return in_array($status->slug, $statusSlugs);
                })->pluck('id')->toArray();

                if (!empty($statusIds)) {
                    $query->whereIn('status_id', $statusIds);
                }
            }
        }

        // Search functionality
        if ($request->filled('q')) {
            $query->search($request->q);
        }

        // Sort
        $sort = $request->get('sort', 'name:asc');
        [$column, $direction] = $this->parseSort($sort);
        $query->orderBy($column, $direction);

        // Pagination
        $perPage = 25;
        $clients = $query->paginate($perPage);

        // Get status counts
        $statusCounts = $this->getStatusCounts($request);

        return [
            'clients' => $clients->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'slug' => $client->slug ?? null,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'contact_person' => $client->contact_person,
                    'company_name' => $client->company_name,
                    'tax_id' => $client->tax_id,
                    'total_incomes' => $client->total_incomes,
                    'invoices_count' => $client->invoices_count,
                    'status_id' => $client->status_id,
                    'status' => $client->status ? [
                        'id' => $client->status->id,
                        'name' => $client->status->name,
                        'slug' => $client->status->slug,
                        'color_background' => $client->status->color_background,
                        'color_text' => $client->status->color_text,
                    ] : null,
                    'created_at' => $client->created_at?->toISOString(),
                    'updated_at' => $client->updated_at?->toISOString(),
                ];
            })->toArray(),
            'pagination' => [
                'total' => $clients->total(),
                'per_page' => $clients->perPage(),
                'current_page' => $clients->currentPage(),
                'last_page' => $clients->lastPage(),
                'from' => $clients->firstItem(),
                'to' => $clients->lastItem(),
            ],
            'status_counts' => $statusCounts,
        ];
    }

    /**
     * Show the form for creating a new client.
     */
    public function create(): View
    {
        // Note: $clientStatuses is now automatically available via SettingsComposer
        return view('clients.create');
    }

    /**
     * Store a newly created client in database.
     */
    public function store(StoreClientRequest $request): JsonResponse|RedirectResponse
    {
        $client = Client::create($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Client created successfully.'),
                'client' => $client->load('status'),
            ], 201);
        }

        return redirect()
            ->route('clients.show', $client)
            ->with('success', __('Client created successfully.'));
    }

    /**
     * Display the specified client.
     */
    public function show(Client $client, Request $request): View
    {
        // Get sorting parameters
        $sortBy = $request->get('sort', 'occurred_at');
        $sortDir = $request->get('dir', 'desc');

        // Whitelist allowed sort columns for security
        $allowedSortColumns = ['occurred_at', 'document_name', 'amount'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'occurred_at';
        }

        // Validate sort direction
        $sortDir = in_array($sortDir, ['asc', 'desc']) ? $sortDir : 'desc';

        // Load relationships - revenues ordered by request with files
        $client->load([
            'status',
            'revenues' => function($query) use ($sortBy, $sortDir) {
                $query->with('files')->orderBy($sortBy, $sortDir);
            },
            'domains',
            'accessCredentials'
        ]);

        // Get active tab from request (default: overview)
        $activeTab = request()->get('tab', 'overview');

        // Get statistics
        $stats = [
            'total_revenue' => $client->total_revenue,
            'active_domains' => $client->active_domains_count,
            'credentials_count' => $client->credentials_count,
            'invoices_count' => $client->revenues->count(),
        ];

        return view('clients.show', compact('client', 'stats', 'activeTab'));
    }

    /**
     * Show the form for editing the specified client.
     */
    public function edit(Client $client): View
    {
        // Note: $clientStatuses is now automatically available via SettingsComposer
        return view('clients.edit', compact('client'));
    }

    /**
     * Update the specified client in database.
     */
    public function update(UpdateClientRequest $request, Client $client): JsonResponse|RedirectResponse
    {
        $client->update($request->validated());

        // Clear credentials cache for this client when status changes
        Cache::forget("credentials_client_{$client->id}");

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Client updated successfully.'),
                'client' => $client->fresh()->load('status'),
            ]);
        }

        return redirect()
            ->route('clients.show', $client)
            ->with('success', __('Client updated successfully.'));
    }

    /**
     * Update client status (for AJAX requests).
     */
    public function updateStatus(Request $request, Client $client): JsonResponse
    {
        $validated = $request->validate([
            'status_id' => 'nullable|exists:settings_options,id',
        ]);

        // Update the status
        $client->status_id = $validated['status_id'] ?? null;
        $client->save();

        // Clear credentials cache for this client when status changes
        Cache::forget("credentials_client_{$client->id}");

        return response()->json([
            'success' => true,
            'message' => __('Client status updated successfully!'),
        ]);
    }

    /**
     * Update client order index (for kanban reordering).
     */
    public function reorder(Request $request, Client $client): JsonResponse
    {
        $validated = $request->validate([
            'order_index' => 'required|integer|min:0',
        ]);

        $client->update($validated);

        return response()->json([
            'success' => true,
            'message' => __('Client order updated successfully.'),
        ]);
    }

    /**
     * Remove the specified client from database.
     */
    public function destroy(Client $client): RedirectResponse
    {
        $clientName = $client->name;
        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('success', __('Client deleted successfully.'));
    }

    protected function getBulkModelClass(): string
    {
        return Client::class;
    }

    protected function getExportEagerLoads(): array
    {
        return ['status'];
    }

    protected function exportToCsv($clients)
    {
        $filename = "clients_export_" . date("Y-m-d_His") . ".csv";

        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($clients) {
            $file = fopen("php://output", "w");

            fputcsv($file, ["Name", "Email", "Phone", "Company", "Tax ID", "Status", "Total Income"]);

            foreach ($clients as $client) {
                fputcsv($file, [
                    $client->name,
                    $client->email,
                    $client->phone,
                    $client->company_name,
                    $client->tax_id,
                    $client->status?->name ?? "N/A",
                    $client->total_incomes,
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
