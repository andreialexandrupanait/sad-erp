<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesBulkActions;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Models\Client;
use App\Services\Client\ClientQueryService;
use App\Services\NomenclatureService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    use HandlesBulkActions;

    public function __construct(
        protected NomenclatureService $nomenclatureService,
        protected ClientQueryService $clientQueryService
    ) {
        $this->authorizeResource(Client::class, 'client');
    }

    /**
     * Display a listing of the clients.
     */
    public function index(Request $request): View|JsonResponse
    {
        // For AJAX requests, return JSON
        if ($request->wantsJson() || $request->ajax()) {
            $clients = $this->clientQueryService->getPaginatedClients($request);
            return response()->json(
                $this->clientQueryService->transformPaginatedResponse($clients)
            );
        }

        // For initial page load, return view with statuses for filter pills
        $statuses = $this->nomenclatureService->getClientStatuses();
        $initialFilters = $this->clientQueryService->parseUrlFilters($request);
        $initialData = $this->clientQueryService->getInitialData($request, $statuses);

        return view('clients.index', [
            'statuses' => $statuses,
            'initialFilters' => $initialFilters,
            'initialClients' => $initialData['clients'],
            'initialPagination' => $initialData['pagination'],
            'initialStatusCounts' => $initialData['status_counts'],
        ]);
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
