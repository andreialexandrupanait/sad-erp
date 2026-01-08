<?php

namespace App\Policies;

use App\Models\Domain;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DomainPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any domains.
     */
    public function viewAny(User $user): bool
    {
        return $user->organization_id !== null;
    }

    /**
     * Determine whether the user can view the domain.
     */
    public function view(User $user, Domain $domain): bool
    {
        return $domain->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can create domains.
     */
    public function create(User $user): bool
    {
        return $user->organization_id !== null;
    }

    /**
     * Determine whether the user can update the domain.
     */
    public function update(User $user, Domain $domain): bool
    {
        return $domain->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can delete the domain.
     */
    public function delete(User $user, Domain $domain): bool
    {
        return $domain->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can restore the domain.
     */
    public function restore(User $user, Domain $domain): bool
    {
        return $domain->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can permanently delete the domain.
     */
    public function forceDelete(User $user, Domain $domain): bool
    {
        return $domain->organization_id === $user->organization_id;
    }

    public function bulkUpdate(User $user): bool
    {
        return $user->organization_id  !==  null;
    }

    public function bulkDelete(User $user): bool
    {
        return $user->organization_id  !==  null;
    }
}
