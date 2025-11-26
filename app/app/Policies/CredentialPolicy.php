<?php

namespace App\Policies;

use App\Models\Credential;
use App\Models\User;

class CredentialPolicy
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
    public function view(User $user, Credential $credential): bool
    {
        return $this->isSameOrganization($user, $credential);
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
    public function update(User $user, Credential $credential): bool
    {
        return $this->isSameOrganization($user, $credential);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Credential $credential): bool
    {
        if (!$this->isSameOrganization($user, $credential)) {
            return false;
        }

        // Only admins and managers can delete credentials
        return $user->canManage();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Credential $credential): bool
    {
        if (!$this->isSameOrganization($user, $credential)) {
            return false;
        }

        return $user->canManage();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Credential $credential): bool
    {
        if (!$this->isSameOrganization($user, $credential)) {
            return false;
        }

        return $user->isAdmin();
    }

    /**
     * Determine whether the user can reveal the password.
     */
    public function revealPassword(User $user, Credential $credential): bool
    {
        return $this->isSameOrganization($user, $credential);
    }

    /**
     * Check if user belongs to the same organization as the credential.
     */
    private function isSameOrganization(User $user, Credential $credential): bool
    {
        return $user->organization_id === $credential->organization_id;
    }
}
