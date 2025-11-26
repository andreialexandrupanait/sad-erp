<?php

namespace App\Policies;

use App\Models\Domain;
use App\Models\User;

class DomainPolicy
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
    public function view(User $user, Domain $domain): bool
    {
        return $this->isSameOrganization($user, $domain);
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
    public function update(User $user, Domain $domain): bool
    {
        return $this->isSameOrganization($user, $domain);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Domain $domain): bool
    {
        if (!$this->isSameOrganization($user, $domain)) {
            return false;
        }

        // Only admins and managers can delete
        return $user->canManage();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Domain $domain): bool
    {
        if (!$this->isSameOrganization($user, $domain)) {
            return false;
        }

        return $user->canManage();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Domain $domain): bool
    {
        if (!$this->isSameOrganization($user, $domain)) {
            return false;
        }

        return $user->isAdmin();
    }

    /**
     * Check if user belongs to the same organization as the domain.
     */
    private function isSameOrganization(User $user, Domain $domain): bool
    {
        return $user->organization_id === $domain->organization_id;
    }
}
