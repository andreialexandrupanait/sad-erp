<?php

namespace App\Http\Controllers;

use App\Models\InternalAccount;
use App\Http\Requests\InternalAccount\StoreInternalAccountRequest;
use App\Http\Requests\InternalAccount\UpdateInternalAccountRequest;
use Illuminate\Http\Request;

class InternalAccountController extends Controller
{
    /**
     * Apply authorization for resource controller
     */
    public function __construct()
    {
        $this->authorizeResource(InternalAccount::class, 'internal_account');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Validate all filter parameters for security
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'ownership' => 'nullable|string|in:mine,team',
            'sort' => 'nullable|string|in:account_name,url,username,created_at,updated_at',
            'dir' => 'nullable|string|in:asc,desc',
        ]);

        $query = InternalAccount::with('user');

        // Search
        if (!empty($validated['search'])) {
            $query->search($validated['search']);
        }

        // Filter by ownership
        if (!empty($validated['ownership'])) {
            if ($validated['ownership'] === 'mine') {
                $query->ownedByMe();
            } elseif ($validated['ownership'] === 'team') {
                $query->teamAccessible(true);
            }
        }

        // Sort (already validated above)
        $sortBy = $validated['sort'] ?? 'created_at';
        $sortOrder = $validated['dir'] ?? 'desc';

        $query->orderBy($sortBy, $sortOrder);

        $accounts = $query->paginate(15)->withQueryString();

        // Statistics
        $stats = [
            'total_accounts' => InternalAccount::count(),
            'my_accounts' => InternalAccount::ownedByMe()->count(),
            'team_accounts' => InternalAccount::teamAccessible(true)->count(),
        ];

        return view('internal-accounts.index', compact('accounts', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('internal-accounts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInternalAccountRequest $request)
    {
        $account = InternalAccount::create($request->validated());

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Internal account created successfully.'),
                'account' => $account->load('user'),
            ], 201);
        }

        return redirect()->route('internal-accounts.show', $account)
            ->with('success', __('Internal account created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(InternalAccount $internalAccount)
    {
        // Check if user has access
        if (!$internalAccount->isAccessible()) {
            abort(403, __('You do not have permission to view this account.'));
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
            abort(403, __('Only the account owner can edit this record.'));
        }

        return view('internal-accounts.edit', compact('internalAccount'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInternalAccountRequest $request, InternalAccount $internalAccount)
    {
        $validated = $request->validated();

        // Only update password if a new one is provided
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $internalAccount->update($validated);

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Internal account updated successfully.'),
                'account' => $internalAccount->fresh()->load('user'),
            ]);
        }

        return redirect()->route('internal-accounts.show', $internalAccount)
            ->with('success', __('Internal account updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InternalAccount $internalAccount)
    {
        // Only owner can delete
        if (!$internalAccount->isOwner()) {
            abort(403, __('Only the account owner can delete this record.'));
        }

        $internalAccount->delete();

        return redirect()->route('internal-accounts.index')
            ->with('success', __('Internal account deleted successfully.'));
    }

    /**
     * Bulk delete internal accounts
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:internal_accounts,id',
        ]);

        $deleted = 0;
        $skipped = 0;

        foreach ($validated['ids'] as $id) {
            $account = InternalAccount::find($id);

            if (!$account) {
                $skipped++;
                continue;
            }

            // Only owner can delete their accounts
            if (!$account->isOwner()) {
                $skipped++;
                continue;
            }

            $account->delete();
            $deleted++;
        }

        return response()->json([
            'success' => true,
            'message' => __(':deleted account(s) deleted successfully.', ['deleted' => $deleted])
                . ($skipped > 0 ? ' ' . __(':skipped skipped (not owner).', ['skipped' => $skipped]) : ''),
            'deleted' => $deleted,
            'skipped' => $skipped,
        ]);
    }

    /**
     * Reveal password (returns JSON for AJAX)
     */
    public function revealPassword(InternalAccount $internalAccount)
    {
        // Check if user has access to this account
        if (!$internalAccount->isAccessible()) {
            return response()->json([
                'message' => __('You do not have permission to view this password.'),
            ], 403);
        }

        // Log the access for audit purposes
        \Illuminate\Support\Facades\Log::info('Password revealed for internal account', [
            'account_id' => $internalAccount->id,
            'account_name' => $internalAccount->account_name,
            'user_id' => auth()->id(),
            'user_email' => auth()->user()->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Return password with security headers to prevent caching
        return response()->json([
            'password' => $internalAccount->password,
        ])->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
