<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesBulkActions;
use App\Http\Requests\Credential\StoreCredentialRequest;
use App\Http\Requests\Credential\UpdateCredentialRequest;
use App\Mail\CredentialsMail;
use App\Models\ApplicationSetting;
use App\Services\NomenclatureService;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
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
                $q->where('access_credentials.site_name', 'like', "%{$search}%")
                  ->orWhere('access_credentials.platform', 'like', "%{$search}%")
                  ->orWhere('access_credentials.username', 'like', "%{$search}%")
                  ->orWhere('access_credentials.url', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Apply client filter
        if ($request->filled('client_id')) {
            $query->where('access_credentials.client_id', $request->client_id);
        }

        // Use proper join instead of subquery for secure sorting
        // Join with clients table to get client name for sorting
        $query->leftJoin('clients', 'access_credentials.client_id', '=', 'clients.id')
            ->select('access_credentials.*')
            ->selectRaw('COALESCE(NULLIF(access_credentials.site_name, ""), clients.name) as display_name');

        // Group credentials by site_name or client name
        return $query
            ->orderBy('display_name', 'ASC')
            ->orderBy('access_credentials.credential_type')
            ->orderBy('access_credentials.platform')
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

        // Log the access for audit purposes (security audit trail)
        Log::channel('audit')->info('Password revealed for credential (confirmed)', [
            'action' => 'password_reveal_confirmed',
            'credential_id' => $credential->id,
            'site_name' => $credential->site_name,
            'platform' => $credential->platform,
            'client_id' => $credential->client_id,
            'user_id' => auth()->id(),
            'user_email' => auth()->user()->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
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

        // Track access in database
        $credential->trackAccess();

        // Log the access for audit purposes (security audit trail)
        Log::channel('audit')->info('Password accessed for credential', [
            'action' => 'password_access',
            'credential_id' => $credential->id,
            'site_name' => $credential->site_name,
            'platform' => $credential->platform,
            'client_id' => $credential->client_id,
            'user_id' => auth()->id(),
            'user_email' => auth()->user()->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ]);

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

    /**
     * Export credentials for a specific site as CSV.
     */
    public function exportSite(string $siteName)
    {
        $siteName = urldecode($siteName);

        $credentials = Credential::with('client')
            ->where(function ($query) use ($siteName) {
                $query->where('site_name', $siteName)
                    ->orWhereHas('client', function ($q) use ($siteName) {
                        $q->where('name', $siteName);
                    });
            })
            ->orderBy('credential_type')
            ->orderBy('platform')
            ->get();

        if ($credentials->isEmpty()) {
            abort(404, __('No credentials found for this site.'));
        }

        // Sanitize filename
        $safeSiteName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $siteName);
        $filename = "credentials_{$safeSiteName}_" . date("Y-m-d_His") . ".csv";

        $headers = [
            "Content-Type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($credentials) {
            $file = fopen("php://output", "w");
            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ["Platform", "Username", "Password", "URL", "Notes"]);

            foreach ($credentials as $credential) {
                fputcsv($file, [
                    $credential->platform,
                    $credential->username ?? "",
                    $credential->password ?? "",
                    $credential->url ?? "",
                    $credential->notes ?? "",
                ]);
            }

            fclose($file);
        };

        // Log the export action
        Log::info('Site credentials exported', [
            'site_name' => $siteName,
            'credentials_count' => $credentials->count(),
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
        ]);

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Send credentials for a specific site via email.
     */
    public function emailSite(Request $request, string $siteName)
    {
        $siteName = urldecode($siteName);

        $validated = $request->validate([
            'email' => 'required|email',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:2000',
        ]);

        $credentials = Credential::with('client')
            ->where(function ($query) use ($siteName) {
                $query->where('site_name', $siteName)
                    ->orWhereHas('client', function ($q) use ($siteName) {
                        $q->where('name', $siteName);
                    });
            })
            ->orderBy('credential_type')
            ->orderBy('platform')
            ->get();

        if ($credentials->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => __('No credentials found for this site.'),
            ], 404);
        }

        try {
            // Configure SMTP from database settings if enabled
            $this->configureSmtpFromDatabase();

            $subject = $validated['subject'] ?? __('Access Credentials for :site', ['site' => $siteName]);

            Mail::to($validated['email'])->send(new CredentialsMail(
                siteName: $siteName,
                credentials: $credentials,
                customMessage: $validated['message'] ?? null,
                subject: $subject
            ));

            // Log the email action
            Log::info('Site credentials sent via email', [
                'site_name' => $siteName,
                'recipient' => $validated['email'],
                'credentials_count' => $credentials->count(),
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => __('Credentials sent successfully to :email', ['email' => $validated['email']]),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send credentials email', [
                'site_name' => $siteName,
                'recipient' => $validated['email'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Failed to send email. Please try again.'),
            ], 500);
        }
    }

    /**
     * Configure SMTP from database settings if enabled.
     */
    protected function configureSmtpFromDatabase(): void
    {
        $smtpEnabled = ApplicationSetting::get('smtp_enabled', false);

        if ($smtpEnabled) {
            $smtpHost = ApplicationSetting::get('smtp_host');
            $smtpPort = ApplicationSetting::get('smtp_port', 587);
            $smtpUsername = ApplicationSetting::get('smtp_username');
            $smtpPassword = ApplicationSetting::get('smtp_password');
            $smtpEncryption = ApplicationSetting::get('smtp_encryption', 'tls');
            $fromEmail = ApplicationSetting::get('smtp_from_email');
            $fromName = ApplicationSetting::get('smtp_from_name', config('app.name'));

            // Decrypt password if encrypted
            if ($smtpPassword) {
                try {
                    $smtpPassword = decrypt($smtpPassword);
                } catch (\Exception $e) {
                    // Password might not be encrypted
                }
            }

            // Configure SMTP on the fly
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $smtpHost,
                'mail.mailers.smtp.port' => (int) $smtpPort,
                'mail.mailers.smtp.username' => $smtpUsername,
                'mail.mailers.smtp.password' => $smtpPassword,
                'mail.mailers.smtp.encryption' => $smtpEncryption === 'none' ? null : $smtpEncryption,
                'mail.from.address' => $fromEmail ?: $smtpUsername,
                'mail.from.name' => $fromName,
            ]);

            Log::info('SMTP configured from database settings for credentials email', ['host' => $smtpHost]);
        }
    }
}
