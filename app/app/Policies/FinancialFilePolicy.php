<?php

namespace App\Policies;

use App\Models\FinancialFile;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FinancialFilePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any financial files.
     */
    public function viewAny(User $user): bool
    {
        return $user->organization_id !== null;
    }

    /**
     * Determine whether the user can view the financial file.
     */
    public function view(User $user, FinancialFile $file): bool
    {
        return $file->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can create financial files.
     */
    public function create(User $user): bool
    {
        return $user->organization_id !== null;
    }

    /**
     * Determine whether the user can update the financial file.
     */
    public function update(User $user, FinancialFile $file): bool
    {
        return $file->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can delete the financial file.
     */
    public function delete(User $user, FinancialFile $file): bool
    {
        return $file->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can restore the financial file.
     */
    public function restore(User $user, FinancialFile $file): bool
    {
        return $file->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can permanently delete the financial file.
     */
    public function forceDelete(User $user, FinancialFile $file): bool
    {
        return $file->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can download the financial file.
     */
    public function download(User $user, FinancialFile $file): bool
    {
        return $file->organization_id === $user->organization_id;
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
