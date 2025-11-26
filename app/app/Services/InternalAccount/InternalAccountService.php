<?php

namespace App\Services\InternalAccount;

use App\Models\InternalAccount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class InternalAccountService
{
    /**
     * Get paginated internal accounts with filters applied.
     */
    public function getPaginatedAccounts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = InternalAccount::with('user');

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['ownership'])) {
            if ($filters['ownership'] === 'mine') {
                $query->ownedByMe();
            } elseif ($filters['ownership'] === 'team') {
                $query->teamAccessible(true);
            }
        }

        $sortBy = $filters['sort'] ?? 'created_at';
        $sortDir = $filters['dir'] ?? 'desc';

        $allowedSorts = ['account_name', 'url', 'username', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        return $query->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get statistics for internal accounts.
     */
    public function getStatistics(): array
    {
        return [
            'total_accounts' => InternalAccount::count(),
            'my_accounts' => InternalAccount::ownedByMe()->count(),
            'team_accounts' => InternalAccount::teamAccessible(true)->count(),
        ];
    }

    /**
     * Create a new internal account.
     */
    public function create(array $data): InternalAccount
    {
        return InternalAccount::create($data);
    }

    /**
     * Update an existing internal account.
     */
    public function update(InternalAccount $account, array $data): InternalAccount
    {
        // Only update password if a new one is provided
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $account->update($data);
        return $account->fresh();
    }

    /**
     * Delete an internal account (soft delete).
     */
    public function delete(InternalAccount $account): bool
    {
        return $account->delete();
    }

    /**
     * Reveal password and log the access.
     */
    public function revealPassword(InternalAccount $account): ?string
    {
        // Log the access for audit purposes
        Log::info('Password revealed for internal account', [
            'account_id' => $account->id,
            'account_name' => $account->account_name,
            'user_id' => auth()->id(),
            'user_email' => auth()->user()->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $account->password;
    }

    /**
     * Get all accounts owned by the current user.
     */
    public function getMyAccounts(): Collection
    {
        return InternalAccount::ownedByMe()
            ->orderBy('account_name')
            ->get();
    }

    /**
     * Get all team-accessible accounts.
     */
    public function getTeamAccounts(): Collection
    {
        return InternalAccount::teamAccessible(true)
            ->with('user')
            ->orderBy('account_name')
            ->get();
    }

    /**
     * Share an account with the team.
     */
    public function shareWithTeam(InternalAccount $account): InternalAccount
    {
        $account->update(['team_accessible' => true]);
        return $account->fresh();
    }

    /**
     * Unshare an account from the team.
     */
    public function unshareFromTeam(InternalAccount $account): InternalAccount
    {
        $account->update(['team_accessible' => false]);
        return $account->fresh();
    }

    /**
     * Check if the current user can access the account.
     */
    public function canAccess(InternalAccount $account): bool
    {
        return $account->isAccessible();
    }

    /**
     * Check if the current user owns the account.
     */
    public function isOwner(InternalAccount $account): bool
    {
        return $account->isOwner();
    }
}
