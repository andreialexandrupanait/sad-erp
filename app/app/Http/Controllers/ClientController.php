<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientSetting;
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
        $query = Client::with(['status']);

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

        // Sort based on view mode
        if ($viewMode === 'kanban') {
            $query->ordered();
        } else {
            $sortField = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            $query->orderBy($sortField, $sortDirection);
        }

        // For kanban view, group by status
        if ($viewMode === 'kanban') {
            $clients = $query->get()->groupBy('status_id');
        } else {
            $clients = $query->paginate(15)->withQueryString();
        }

        // Note: $clientStatuses is now automatically available via SettingsComposer
        return view('clients.index', compact('clients', 'viewMode'));
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
                Rule::unique('clients')->where(function ($query) {
                    return $query->where('user_id', auth()->id());
                }),
            ],
            'registration_number' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'vat_payer' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'status_id' => 'nullable|exists:client_settings,id',
            'order_index' => 'nullable|integer',
        ]);

        // Convert checkbox to boolean
        $validated['vat_payer'] = $request->has('vat_payer');

        $client = Client::create($validated);

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Client created successfully!',
                'client' => $client->load('status'),
            ], 201);
        }

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Client created successfully!');
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
                Rule::unique('clients')->where(function ($query) {
                    return $query->where('user_id', auth()->id());
                })->ignore($client->id),
            ],
            'registration_number' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'vat_payer' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'status_id' => 'nullable|exists:client_settings,id',
            'order_index' => 'nullable|integer',
        ]);

        // Convert checkbox to boolean
        $validated['vat_payer'] = $request->has('vat_payer');

        $client->update($validated);

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Client updated successfully!',
                'client' => $client->fresh()->load('status'),
            ]);
        }

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Client updated successfully!');
    }

    /**
     * Update client status (for AJAX requests)
     */
    public function updateStatus(Request $request, Client $client)
    {
        $validated = $request->validate([
            'status_id' => 'required|exists:client_settings,id',
        ]);

        $client->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Client status updated successfully!',
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
            ->with('success', "Client '{$clientName}' deleted successfully!");
    }
}
