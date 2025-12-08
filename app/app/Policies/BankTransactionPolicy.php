<?php

namespace App\Policies;

use App\Models\BankTransaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BankTransactionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any bank transactions.
     */
    public function viewAny(User $user): bool
    {
        return $user->organization_id !== null;
    }

    /**
     * Determine whether the user can view the bank transaction.
     */
    public function view(User $user, BankTransaction $transaction): bool
    {
        return $transaction->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can create bank transactions.
     */
    public function create(User $user): bool
    {
        return $user->organization_id !== null;
    }

    /**
     * Determine whether the user can update the bank transaction.
     */
    public function update(User $user, BankTransaction $transaction): bool
    {
        return $transaction->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can delete the bank transaction.
     */
    public function delete(User $user, BankTransaction $transaction): bool
    {
        return $transaction->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can restore the bank transaction.
     */
    public function restore(User $user, BankTransaction $transaction): bool
    {
        return $transaction->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can permanently delete the bank transaction.
     */
    public function forceDelete(User $user, BankTransaction $transaction): bool
    {
        return $transaction->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can match the bank transaction.
     */
    public function match(User $user, BankTransaction $transaction): bool
    {
        return $transaction->organization_id === $user->organization_id
               && $transaction->match_status !== 'matched';
    }

    /**
     * Determine whether the user can unmatch the bank transaction.
     */
    public function unmatch(User $user, BankTransaction $transaction): bool
    {
        return $transaction->organization_id === $user->organization_id
               && $transaction->match_status === 'matched';
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
