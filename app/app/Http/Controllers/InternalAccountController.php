<?php

namespace App\Http\Controllers;

use App\Models\InternalAccount;
use Illuminate\Http\Request;

class InternalAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = InternalAccount::with('user');

        // Search
        if ($request->has('search') && $request->search != '') {
            $query->search($request->search);
        }

        // Filter by platform
        if ($request->has('platform') && $request->platform != '') {
            $query->platform($request->platform);
        }

        // Filter by ownership
        if ($request->has('ownership') && $request->ownership != '') {
            if ($request->ownership === 'mine') {
                $query->ownedByMe();
            } elseif ($request->ownership === 'team') {
                $query->teamAccessible(true);
            }
        }

        // Sort
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('dir', 'desc');

        // Validate sort column
        $allowedSortColumns = ['nume_cont_aplicatie', 'platforma', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        $query->orderBy($sortBy, $sortOrder);

        $accounts = $query->paginate(15);

        // Get platforms for filter
        $platforms = setting_options('access_platforms');

        // Statistics
        $stats = [
            'total_accounts' => InternalAccount::count(),
            'my_accounts' => InternalAccount::ownedByMe()->count(),
            'team_accounts' => InternalAccount::teamAccessible(true)->count(),
            'unique_platforms' => InternalAccount::distinct('platforma')->count('platforma'),
        ];

        return view('internal-accounts.index', compact('accounts', 'platforms', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $platforms = setting_options('access_platforms');
        return view('internal-accounts.create', compact('platforms'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nume_cont_aplicatie' => 'required|string|max:255',
            'platforma' => 'required|string|max:255',
            'url' => 'nullable|url|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:500',
            'accesibil_echipei' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        // Ensure boolean value
        $validated['accesibil_echipei'] = $request->has('accesibil_echipei');

        $account = InternalAccount::create($validated);

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Internal account created successfully!',
                'account' => $account->load('user'),
            ], 201);
        }

        return redirect()->route('internal-accounts.show', $account)
            ->with('success', 'Internal account created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(InternalAccount $internalAccount)
    {
        // Check if user has access
        if (!$internalAccount->isAccessible()) {
            abort(403, 'You do not have permission to view this account.');
        }

        $internalAccount->load('user');

        return view('internal-accounts.show', compact('internalAccount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InternalAccount $internalAccount)
    {
        // Only owner can edit
        if (!$internalAccount->isOwner()) {
            abort(403, 'Only the account owner can edit this record.');
        }

        $platforms = setting_options('access_platforms');

        return view('internal-accounts.edit', compact('internalAccount', 'platforms'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InternalAccount $internalAccount)
    {
        // Only owner can update
        if (!$internalAccount->isOwner()) {
            abort(403, 'Only the account owner can update this record.');
        }

        $validated = $request->validate([
            'nume_cont_aplicatie' => 'required|string|max:255',
            'platforma' => 'required|string|max:255',
            'url' => 'nullable|url|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:500',
            'accesibil_echipei' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        // Ensure boolean value
        $validated['accesibil_echipei'] = $request->has('accesibil_echipei');

        // Only update password if a new one is provided
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $internalAccount->update($validated);

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Internal account updated successfully!',
                'account' => $internalAccount->fresh()->load('user'),
            ]);
        }

        return redirect()->route('internal-accounts.show', $internalAccount)
            ->with('success', 'Internal account updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InternalAccount $internalAccount)
    {
        // Only owner can delete
        if (!$internalAccount->isOwner()) {
            abort(403, 'Only the account owner can delete this record.');
        }

        $internalAccount->delete();

        return redirect()->route('internal-accounts.index')
            ->with('success', 'Internal account deleted successfully.');
    }
}
