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
     */
    public function view(User $user, BankingCredential $bankingCredential): bool
    {
        return $bankingCredential->organization_id === $user->organization_id;
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
     */
    public function update(User $user, BankingCredential $bankingCredential): bool
    {
        return $bankingCredential->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can delete the banking credential.
     */
    public function delete(User $user, BankingCredential $bankingCredential): bool
    {
        return $bankingCredential->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can restore the banking credential.
     */
    public function restore(User $user, BankingCredential $bankingCredential): bool
    {
        return $bankingCredential->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can permanently delete the banking credential.
     */
    public function forceDelete(User $user, BankingCredential $bankingCredential): bool
    {
        return $bankingCredential->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can sync transactions for the banking credential.
     */
    public function sync(User $user, BankingCredential $bankingCredential): bool
    {
        return $bankingCredential->organization_id === $user->organization_id
               && $bankingCredential->status === 'active';
    }

    /**
     * Determine whether the user can revoke consent for the banking credential.
     */
    public function revokeConsent(User $user, BankingCredential $bankingCredential): bool
    {
        return $bankingCredential->organization_id === $user->organization_id;
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
