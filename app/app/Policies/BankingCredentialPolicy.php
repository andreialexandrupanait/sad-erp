<?php

namespace App\Policies;

use App\Models\BankingCredential;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BankingCredentialPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any banking credentials.
     */
    public function viewAny(User $user): bool
    {
        return $user->organization_id !== null;
    }

    /**
     * Determine whether the user can view the banking credential.
     * Banking credentials are user-private (not just org-scoped) due to sensitive OAuth tokens.
     */
    public function view(User $user, BankingCredential $bankingCredential): bool
    {
        return $bankingCredential->user_id === $user->id;
    }

    /**
     * Determine whether the user can create banking credentials.
     */
    public function create(User $user): bool
    {
        return $user->organization_id !== null;
    }

    /**
     * Determine whether the user can update the banking credential.
     * Banking credentials are user-private (not just org-scoped) due to sensitive OAuth tokens.
     */
    public function update(User $user, BankingCredential $bankingCredential): bool
    {
        return $bankingCredential->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the banking credential.
     * Banking credentials are user-private (not just org-scoped) due to sensitive OAuth tokens.
     */
    public function delete(User $user, BankingCredential $bankingCredential): bool
    {
        return $bankingCredential->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the banking credential.
     * Banking credentials are user-private (not just org-scoped) due to sensitive OAuth tokens.
     */
    public function restore(User $user, BankingCredential $bankingCredential): bool
    {
        return $bankingCredential->user_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the banking credential.
     * Banking credentials are user-private (not just org-scoped) due to sensitive OAuth tokens.
     */
    public function forceDelete(User $user, BankingCredential $bankingCredential): bool
    {
        return $bankingCredential->user_id === $user->id;
    }

    /**
     * Determine whether the user can sync transactions for the banking credential.
     * Banking credentials are user-private (not just org-scoped) due to sensitive OAuth tokens.
     */
    public function sync(User $user, BankingCredential $bankingCredential): bool
    {
        return $bankingCredential->user_id === $user->id
               && $bankingCredential->status === 'active';
    }

    /**
     * Determine whether the user can revoke consent for the banking credential.
     * Banking credentials are user-private (not just org-scoped) due to sensitive OAuth tokens.
     */
    public function revokeConsent(User $user, BankingCredential $bankingCredential): bool
    {
        return $bankingCredential->user_id === $user->id;
    }

    /**
     * Determine whether the user can perform bulk operations.
     */
    public function bulkUpdate(User $user): bool
    {
        return $user->organization_id !== null;
    }

    /**
     * Determine whether the user can perform bulk delete.
     */
    public function bulkDelete(User $user): bool
    {
        return $user->organization_id !== null;
    }
}
