<?php

namespace App\Policies;

use App\Models\FinancialExpense;
use App\Models\User;

class FinancialExpensePolicy
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
    public function view(User $user, FinancialExpense $expense): bool
    {
        return $this->isSameOrganization($user, $expense);
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
    public function update(User $user, FinancialExpense $expense): bool
    {
        return $this->isSameOrganization($user, $expense);
    }

    /**
     * Determine whether the user can delete the model.
     * Organization-scoped: any user in the org can delete.
     */
    public function delete(User $user, FinancialExpense $expense): bool
    {
        return $this->isSameOrganization($user, $expense);
    }

    /**
     * Determine whether the user can restore the model.
     * Organization-scoped: any user in the org can restore.
     */
    public function restore(User $user, FinancialExpense $expense): bool
    {
        return $this->isSameOrganization($user, $expense);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, FinancialExpense $expense): bool
    {
        if (!$this->isSameOrganization($user, $expense)) {
            return false;
        }

        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can import expenses.
     */
    public function import(User $user): bool
    {
        return $user->organization_id !== null;
    }

    /**
     * Determine whether the user can export expenses.
     */
    public function export(User $user): bool
    {
        return $user->organization_id !== null;
    }

    /**
     * Check if user belongs to the same organization as the expense.
     */
    private function isSameOrganization(User $user, FinancialExpense $expense): bool
    {
        return $user->organization_id === $expense->organization_id;
    }

    /**
     * Check if user is an admin.
     */
    private function isAdmin(User $user): bool
    {
        return $user->role === 'admin' || $user->role === 'superadmin';
    }
}
