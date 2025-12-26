<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesBulkActions;
use App\Http\Requests\Credential\StoreCredentialRequest;
use App\Http\Requests\Credential\UpdateCredentialRequest;
use App\Services\NomenclatureService;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Gate;
use App\Models\Credential;
use App\Models\Client;
use Illuminate\Http\Request;

class CredentialController extends Controller
{
    use HandlesBulkActions;

    protected NomenclatureService $nomenclatureService;

    public function __construct(NomenclatureService $nomenclatureService)
    {
        $this->nomenclatureService = $nomenclatureService;
        $this->authorizeResource(Credential::class, 'credential');
    }

    /**
     * Display a listing of the resource.
     * Single unified view: credentials grouped by site/client with adjacent platforms
     */
    public function index(Request $request)
    {
        $credentialsBySite = $this->getFilteredCredentials($request);

        // Return only the partial for AJAX requests
        if ($request->ajax() || $request->has('ajax')) {
            return view('credentials.partials.credentials-list', compact('credentialsBySite'));
        }

        // Get clients for filters (only for full page load)
        $clients = Client::select('id', 'name')->orderBy('name')->get();

        return view('credentials.index', compact('credentialsBySite', 'clients'));
    }

    /**
     * Get filtered and grouped credentials
     */
    protected function getFilteredCredentials(Request $request)
    {
        $query = Credential::with(['client', 'client.status']);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('site_name', 'like', "%{$search}%")
                  ->orWhere('platform', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Apply client filter
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Group credentials by site_name or client name
        return $query
            ->orderByRaw('COALESCE(NULLIF(site_name, ""), (SELECT name FROM clients WHERE clients.id = access_credentials.client_id)) ASC')
            ->orderBy('credential_type')
            ->orderBy('platform')
            ->get()
            ->groupBy(function ($credential) {
                // Use site_name if set, otherwise use client name
                if ($credential->site_name && $credential->site_name !== '') {
                    return $credential->site_name;
                }
                return $credential->client?->name ?? __('No Client');
            });
    }

    /**
     * Get credentials for a specific site (AJAX endpoint for slide-over panel)
     */
    public function siteCredentials(Request $request, string $siteName)
    {
        $siteName = urldecode($siteName);

        $siteInfo = Credential::getSiteInfo($siteName);

        if (!$siteInfo) {
            return response()->json(['error' => 'Site not found'], 404);
        }

        $credentialsByType = Credential::getCredentialsForSite($siteName);

        // Transform credentials for JSON response
        $credentials = [];
        foreach ($credentialsByType as $type => $typeCredentials) {
            $credentials[$type] = $typeCredentials->map(function ($credential) {
                return [
                    'id' => $credential->id,
                    'platform' => $credential->platform,
                    'credential_type' => $credential->credential_type,
                    'type_label' => $credential->type_label,
                    'type_badge_color' => $credential->type_badge_color,
                    'username' => $credential->username,
                    'url' => $credential->url,
                    'website' => $credential->website,
                    'quick_login_url' => $credential->quick_login_url,
                    'notes' => $credential->notes,
                    'last_accessed_at' => $credential->last_accessed_at?->format('Y-m-d H:i'),
                    'access_count' => $credential->access_count,
                ];
            })->values();
        }

        return response()->json([
            'site_name' => $siteInfo->site_name,
            'client' => $siteInfo->client ? [
                'id' => $siteInfo->client->id,
                'name' => $siteInfo->client->name,
                'company' => $siteInfo->client->company,
                'display_name' => $siteInfo->client->display_name ?? $siteInfo->client->name,
            ] : null,
            'website' => $siteInfo->website,
            'credentials' => $credentials,
            'types' => Credential::CREDENTIAL_TYPES,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = Client::select('id', 'name')->orderBy('name')->get();
        $platforms = $this->nomenclatureService->getAccessPlatforms();
        $credentialTypes = Credential::CREDENTIAL_TYPES;
        $sites = Credential::getUniqueSites();

        return view('credentials.create', compact('clients', 'platforms', 'credentialTypes', 'sites'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCredentialRequest $request)
    {
        $data = $request->validated();
        // Default credential_type if not provided
        if (empty($data['credential_type'])) {
            $data['credential_type'] = 'admin-panel';
        }
        $credential = Credential::create($data);

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Credential created successfully.'),
                'credential' => $credential->load('client'),
            ], 201);
        }

        return redirect()->route('credentials.show', $credential)
            ->with('success', __('Credential created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Credential $credential)
    {
        $credential->load('client');

        return view('credentials.show', compact('credential'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Credential $credential)
    {
        $clients = Client::select('id', 'name')->orderBy('name')->get();
        $platforms = $this->nomenclatureService->getAccessPlatforms();
        $credentialTypes = Credential::CREDENTIAL_TYPES;
        $sites = Credential::getUniqueSites();

        return view('credentials.edit', compact('credential', 'clients', 'platforms', 'credentialTypes', 'sites'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCredentialRequest $request, Credential $credential)
    {
        $validated = $request->validated();

        // Only update password if a new one is provided
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $credential->update($validated);

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Credential updated successfully.'),
                'credential' => $credential->fresh()->load('client'),
            ]);
        }

        return redirect()->route('credentials.show', $credential)
            ->with('success', __('Credential updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Credential $credential)
    {
        $credential->delete();

        return redirect()->route('credentials.index')
            ->with('success', __('Credential deleted successfully.'));
    }

    /**
     * Reveal password (returns JSON for AJAX) - requires password confirmation
     */
    public function revealPassword(Credential $credential)
    {
        // Authorize access to this credential
        Gate::authorize('view', $credential);

        // Track access in database
        $credential->trackAccess();

        // Log the access for audit purposes
        \Illuminate\Support\Facades\Log::info('Password revealed for credential', [
            'credential_id' => $credential->id,
            'platform' => $credential->platform,
            'client_id' => $credential->client_id,
            'user_id' => auth()->id(),
            'user_email' => auth()->user()->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json([
            'password' => $credential->password,
        ]);
    }

    /**
     * Get password for display in list view (no password confirmation required)
     */
    public function getPassword(Credential $credential)
    {
        // Authorize access to this credential
        Gate::authorize('view', $credential);

        return response()->json([
            'password' => $credential->password,
        ]);
    }

    protected function getBulkModelClass(): string
    {
        return \App\Models\Credential::class;
    }

    protected function getExportEagerLoads(): array
    {
        return ['client'];
    }

    protected function exportToCsv($credentials)
    {
        $filename = "credentials_export_" . date("Y-m-d_His") . ".csv";

        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($credentials) {
            $file = fopen("php://output", "w");
            fputcsv($file, ["Client", "Site", "Type", "Platform", "URL", "Username", "Notes"]);

            foreach ($credentials as $credential) {
                fputcsv($file, [
                    $credential->client?->name ?? "N/A",
                    $credential->site_name ?? "",
                    $credential->type_label,
                    $credential->platform,
                    $credential->url ?? "",
                    $credential->username ?? "",
                    $credential->notes ?? "",
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
