<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Display a listing of the clients
     */
    public function index(Request $request)
    {
        $query = Client::query();

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Paginate
        $clients = $query->paginate(15);

        return view('clients.index', compact('clients'));
    }

    /**
     * Show the form for creating a new client
     */
    public function create()
    {
        return view('clients.create');
    }

    /**
     * Store a newly created client in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $client = Client::create($validated);

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
        $client->load(['offers', 'contracts', 'subscriptions']);

        // Get statistics
        $stats = [
            'total_offers' => $client->offers()->count(),
            'total_contracts' => $client->contracts()->count(),
            'active_subscriptions' => $client->subscriptions()->where('status', 'active')->count(),
            'total_revenue' => $client->revenues()->sum('amount'),
        ];

        return view('clients.show', compact('client', 'stats'));
    }

    /**
     * Show the form for editing the specified client
     */
    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    /**
     * Update the specified client in database
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $client->update($validated);

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Client updated successfully!');
    }

    /**
     * Remove the specified client from database
     */
    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client deleted successfully!');
    }
}
