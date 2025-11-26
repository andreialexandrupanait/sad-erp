<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Client;
use App\Models\SettingOption;
use App\Http\Requests\Domain\StoreDomainRequest;
use App\Http\Requests\Domain\UpdateDomainRequest;
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

        $domains = $query->paginate(15)->withQueryString();

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
        $registrars = SettingOption::domainRegistrars()->get();
        $statuses = SettingOption::domainStatuses()->get();

        return view('domains.create', compact('clients', 'registrars', 'statuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDomainRequest $request)
    {
        $domain = Domain::create($request->validated());

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Domain created successfully.'),
                'domain' => $domain->load('client'),
            ], 201);
        }

        return redirect()->route('domains.show', $domain)
            ->with('success', __('Domain created successfully.'));
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
        $registrars = SettingOption::domainRegistrars()->get();
        $statuses = SettingOption::domainStatuses()->get();

        return view('domains.edit', compact('domain', 'clients', 'registrars', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDomainRequest $request, Domain $domain)
    {
        $domain->update($request->validated());

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Domain updated successfully.'),
                'domain' => $domain->load('client'),
            ]);
        }

        return redirect()->route('domains.show', $domain)
            ->with('success', __('Domain updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Domain $domain)
    {
        $domain->delete();

        return redirect()->route('domains.index')
            ->with('success', __('Domain deleted successfully.'));
    }
}
