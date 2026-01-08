<?php

namespace App\Policies;

use App\Models\FinancialRevenue;
use App\Models\User;

class FinancialRevenuePolicy
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
    public function view(User $user, FinancialRevenue $revenue): bool
    {
        return $this->isSameOrganization($user, $revenue);
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
     * Organization-scoped: any user in the org can update.
     */
    public function update(User $user, FinancialRevenue $revenue): bool
    {
        return $this->isSameOrganization($user, $revenue);
    }

    /**
     * Determine whether the user can delete the model.
     * Organization-scoped: any user in the org can delete.
     */
    public function delete(User $user, FinancialRevenue $revenue): bool
    {
        return $this->isSameOrganization($user, $revenue);
    }

    /**
     * Determine whether the user can restore the model.
     * Organization-scoped: any user in the org can restore.
     */
    public function restore(User $user, FinancialRevenue $revenue): bool
    {
        return $this->isSameOrganization($user, $revenue);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, FinancialRevenue $revenue): bool
    {
        if (!$this->isSameOrganization($user, $revenue)) {
            return false;
        }

        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can import revenues.
     */
    public function import(User $user): bool
    {
        return $user->organization_id !== null;
    }

    /**
     * Determine whether the user can export revenues.
     */
    public function export(User $user): bool
    {
        return $user->organization_id !== null;
    }

    /**
     * Check if user belongs to the same organization as the revenue.
     */
    private function isSameOrganization(User $user, FinancialRevenue $revenue): bool
    {
        return $user->organization_id === $revenue->organization_id;
    }

    /**
     * Check if user is an admin.
     */
    private function isAdmin(User $user): bool
    {
        return $user->role === 'admin' || $user->role === 'superadmin';
    }
}
