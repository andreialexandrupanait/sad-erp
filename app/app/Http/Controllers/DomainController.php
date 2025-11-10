<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Client;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Domain::with('client');

        // Search
        if ($request->has('search') && $request->search != '') {
            $query->search($request->search);
        }

        // Filter by client
        if ($request->has('client_id') && $request->client_id != '') {
            $query->client($request->client_id);
        }

        // Filter by registrar
        if ($request->has('registrar') && $request->registrar != '') {
            $query->registrar($request->registrar);
        }

        // Filter by expiry status
        if ($request->has('expiry_status') && $request->expiry_status != '') {
            $query->expiryStatus($request->expiry_status);
        }

        // Sort
        $sortBy = $request->get('sort', 'expiry_date');
        $sortOrder = $request->get('dir', 'asc');

        // Validate sort column
        $allowedSortColumns = ['domain_name', 'registrar', 'expiry_date', 'annual_cost', 'created_at'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'expiry_date';
        }

        $query->orderBy($sortBy, $sortOrder);

        $domains = $query->paginate(15);

        // Get data for filters
        $clients = Client::orderBy('name')->get();
        $registrars = setting_options('domain_registrars'); // Use dynamic settings

        // Statistics
        $stats = Domain::getStatistics();

        // Count active filters
        $activeFilters = collect([
            $request->search,
            $request->client_id,
            $request->registrar,
            $request->expiry_status,
        ])->filter()->count();

        return view('domains.index', compact('domains', 'clients', 'registrars', 'stats', 'activeFilters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = Client::orderBy('name')->get();
        $registrars = setting_options('domain_registrars'); // Use dynamic settings

        return view('domains.create', compact('clients', 'registrars'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'domain_name' => 'required|string|max:255|unique:domains,domain_name',
            'client_id' => 'nullable|exists:clients,id',
            'registrar' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Expiring,Expired,Suspended',
            'registration_date' => 'nullable|date',
            'expiry_date' => 'required|date',
            'annual_cost' => 'nullable|numeric|min:0',
            'auto_renew' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        // Ensure boolean value
        $validated['auto_renew'] = $request->has('auto_renew');

        // Normalize domain name
        $validated['domain_name'] = strtolower(trim($validated['domain_name']));

        $domain = Domain::create($validated);

        return redirect()->route('domains.show', $domain)
            ->with('success', 'Domain added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Domain $domain)
    {
        $domain->load('client');

        return view('domains.show', compact('domain'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Domain $domain)
    {
        $clients = Client::orderBy('name')->get();
        $registrars = setting_options('domain_registrars'); // Use dynamic settings

        return view('domains.edit', compact('domain', 'clients', 'registrars'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Domain $domain)
    {
        $validated = $request->validate([
            'domain_name' => 'required|string|max:255|unique:domains,domain_name,' . $domain->id,
            'client_id' => 'nullable|exists:clients,id',
            'registrar' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Expiring,Expired,Suspended',
            'registration_date' => 'nullable|date',
            'expiry_date' => 'required|date',
            'annual_cost' => 'nullable|numeric|min:0',
            'auto_renew' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        // Ensure boolean value
        $validated['auto_renew'] = $request->has('auto_renew');

        // Normalize domain name
        $validated['domain_name'] = strtolower(trim($validated['domain_name']));

        $domain->update($validated);

        return redirect()->route('domains.show', $domain)
            ->with('success', 'Domain updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Domain $domain)
    {
        $domain->delete();

        return redirect()->route('domains.index')
            ->with('success', 'Domain deleted successfully.');
    }
}
