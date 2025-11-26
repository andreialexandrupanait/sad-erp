<?php

namespace App\Policies;

use App\Models\InternalAccount;
use App\Models\User;

class InternalAccountPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->organization_id !== null;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, InternalAccount $internalAccount): bool
    {
        if (!$this->isSameOrganization($user, $internalAccount)) {
            return false;
        }

        // Owner can always view
        if ($this->isOwner($user, $internalAccount)) {
            return true;
        }

        // Team members can view if account is team-accessible
        return $internalAccount->team_accessible;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->organization_id !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, InternalAccount $internalAccount): bool
    {
        if (!$this->isSameOrganization($user, $internalAccount)) {
            return false;
        }

        // Only owner can update
        return $this->isOwner($user, $internalAccount);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, InternalAccount $internalAccount): bool
    {
        if (!$this->isSameOrganization($user, $internalAccount)) {
            return false;
        }

        // Only owner can delete
        return $this->isOwner($user, $internalAccount);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, InternalAccount $internalAccount): bool
    {
        if (!$this->isSameOrganization($user, $internalAccount)) {
            return false;
        }

        return $this->isOwner($user, $internalAccount);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, InternalAccount $internalAccount): bool
    {
        if (!$this->isSameOrganization($user, $internalAccount)) {
            return false;
        }

        return $this->isOwner($user, $internalAccount) || $user->isAdmin();
    }

    /**
     * Determine whether the user can reveal the password.
     */
    public function revealPassword(User $user, InternalAccount $internalAccount): bool
    {
        // Same logic as view
        return $this->view($user, $internalAccount);
    }

    /**
     * Check if user owns the account.
     */
    private function isOwner(User $user, InternalAccount $internalAccount): bool
    {
        return $user->id === $internalAccount->user_id;
    }

    /**
     * Check if user belongs to the same organization.
     */
    private function isSameOrganization(User $user, InternalAccount $internalAccount): bool
    {
        return $user->organization_id === $internalAccount->organization_id;
    }
}
