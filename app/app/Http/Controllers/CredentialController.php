<?php

namespace App\Http\Controllers;

use App\Models\Credential;
use App\Models\Client;
use App\Models\SettingOption;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CredentialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Credential::with('client');

        // Search
        if ($request->has('search') && $request->search != '') {
            $query->search($request->search);
        }

        // Filter by platform
        if ($request->has('platform') && $request->platform != '') {
            $query->platform($request->platform);
        }

        // Filter by client
        if ($request->has('client_id') && $request->client_id != '') {
            $query->client($request->client_id);
        }

        // Sort
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');

        // Validate sort column
        $allowedSortColumns = ['client_id', 'platform', 'username', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        $query->orderBy($sortBy, $sortDir);

        $credentials = $query->paginate(15)->withQueryString();

        // Get clients and platforms for filters
        $clients = Client::orderBy('name')->get();
        $platforms = SettingOption::accessPlatforms()->get();

        return view('credentials.index', compact('credentials', 'clients', 'platforms'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = Client::orderBy('name')->get();
        $platforms = SettingOption::accessPlatforms()->get();

        return view('credentials.create', compact('clients', 'platforms'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validPlatforms = SettingOption::accessPlatforms()->pluck('value')->toArray();

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'platform' => ['required', Rule::in($validPlatforms)],
            'url' => 'nullable|url|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
        ]);

        $credential = Credential::create($validated);

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
        $clients = Client::orderBy('name')->get();
        $platforms = SettingOption::accessPlatforms()->get();

        return view('credentials.edit', compact('credential', 'clients', 'platforms'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Credential $credential)
    {
        $validPlatforms = SettingOption::accessPlatforms()->pluck('value')->toArray();

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'platform' => ['required', Rule::in($validPlatforms)],
            'url' => 'nullable|url|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
        ]);

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
     * Reveal password (returns JSON for AJAX)
     */
    public function revealPassword(Credential $credential)
    {
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
}
