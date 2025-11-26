<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\SettingOption;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    /**
     * Display a listing of the clients
     */
    public function index(Request $request)
    {
        $query = Client::with(['status'])
            ->withCount(['revenues as invoices_count']);

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->filled('status_id')) {
            $query->byStatus($request->status_id);
        }

        // Get view mode (default: table)
        $viewMode = $request->get('view', 'table');

        // Sort - default to name ascending for better UX
        if ($viewMode === 'kanban') {
            $query->ordered();
        } else {
            $sortBy = $request->get('sort', 'name');
            $sortDir = $request->get('dir', 'asc');

            // Whitelist allowed sort columns for security
            $allowedSortColumns = ['name', 'status_id', 'total_incomes', 'created_at', 'email'];
            if (!in_array($sortBy, $allowedSortColumns)) {
                $sortBy = 'name';
            }

            // Validate sort direction
            $sortDir = in_array($sortDir, ['asc', 'desc']) ? $sortDir : 'asc';

            $query->orderBy($sortBy, $sortDir);
        }

        // Get status counts for filter pills
        $statusCounts = Client::selectRaw('status_id, COUNT(*) as count')
            ->groupBy('status_id')
            ->pluck('count', 'status_id')
            ->toArray();

        // For kanban view, group by status
        if ($viewMode === 'kanban') {
            $clients = $query->get()->groupBy('status_id');
        } else {
            // Default to 100 clients per page
            $clients = $query->paginate(100)->withQueryString();
        }

        return view('clients.index', compact('clients', 'viewMode', 'statusCounts'));
    }

    /**
     * Show the form for creating a new client
     */
    public function create()
    {
        // Note: $clientStatuses is now automatically available via SettingsComposer
        return view('clients.create');
    }

    /**
     * Store a newly created client in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'tax_id' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('clients')->where(function ($query) use ($request) {
                    // Only check uniqueness if tax_id is not NULL or '-'
                    $taxId = $request->input('tax_id');
                    if ($taxId !== null && $taxId !== '' && $taxId !== '-') {
                        return $query->where('user_id', auth()->id())
                                     ->where('tax_id', '!=', '-')
                                     ->whereNotNull('tax_id');
                    }
                    return $query->whereRaw('1 = 0'); // Never match if tax_id is NULL or '-'
                }),
            ],
            'registration_number' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'vat_payer' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'status_id' => 'nullable|exists:settings_options,id',
            'order_index' => 'nullable|integer',
        ]);

        // Convert checkbox to boolean
        $validated['vat_payer'] = $request->has('vat_payer');

        // Sanitize text inputs to prevent XSS
        $validated['name'] = sanitize_input($validated['name']);
        if (!empty($validated['company_name'])) {
            $validated['company_name'] = sanitize_input($validated['company_name']);
        }
        if (!empty($validated['contact_person'])) {
            $validated['contact_person'] = sanitize_input($validated['contact_person']);
        }

        $client = Client::create($validated);

        // Return JSON for AJAX requests
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
     * Display the specified client
     */
    public function show(Client $client)
    {
        // Load relationships
        $client->load(['status', 'revenues', 'domains', 'accessCredentials']);

        // Get active tab from request (default: overview)
        $activeTab = request()->get('tab', 'overview');

        // Get statistics
        $stats = [
            'total_revenue' => $client->total_revenue,
            'active_domains' => $client->active_domains_count,
            'credentials_count' => $client->credentials_count,
        ];

        return view('clients.show', compact('client', 'stats', 'activeTab'));
    }

    /**
     * Show the form for editing the specified client
     */
    public function edit(Client $client)
    {
        // Note: $clientStatuses is now automatically available via SettingsComposer
        return view('clients.edit', compact('client'));
    }

    /**
     * Update the specified client in database
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'tax_id' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('clients')->where(function ($query) use ($request) {
                    // Only check uniqueness if tax_id is not NULL or '-'
                    $taxId = $request->input('tax_id');
                    if ($taxId !== null && $taxId !== '' && $taxId !== '-') {
                        return $query->where('user_id', auth()->id())
                                     ->where('tax_id', '!=', '-')
                                     ->whereNotNull('tax_id');
                    }
                    return $query->whereRaw('1 = 0'); // Never match if tax_id is NULL or '-'
                })->ignore($client->id),
            ],
            'registration_number' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'vat_payer' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'status_id' => 'nullable|exists:settings_options,id',
            'order_index' => 'nullable|integer',
        ]);

        // Convert checkbox to boolean
        $validated['vat_payer'] = $request->has('vat_payer');

        // Sanitize text inputs to prevent XSS
        $validated['name'] = sanitize_input($validated['name']);
        if (!empty($validated['company_name'])) {
            $validated['company_name'] = sanitize_input($validated['company_name']);
        }
        if (!empty($validated['contact_person'])) {
            $validated['contact_person'] = sanitize_input($validated['contact_person']);
        }

        $client->update($validated);

        // Return JSON for AJAX requests
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
     * Update client status (for AJAX requests)
     */
    public function updateStatus(Request $request, Client $client)
    {
        $validated = $request->validate([
            'status_id' => 'nullable|exists:settings_options,id',
        ]);

        // Update the status
        $client->status_id = $validated['status_id'] ?? null;
        $client->save();

        return response()->json([
            'success' => true,
            'message' => __('Client status updated successfully!'),
        ]);
    }

    /**
     * Update client order index (for kanban reordering)
     */
    public function reorder(Request $request, Client $client)
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
     * Remove the specified client from database
     */
    public function destroy(Client $client)
    {
        $clientName = $client->name;
        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('success', __('Client deleted successfully.'));
    }
}
