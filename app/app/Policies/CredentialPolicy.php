<?php

namespace App\Policies;

use App\Models\Credential;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CredentialPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any credentials.
     */
    public function viewAny(User $user): bool
    {
        return $user->organization_id !== null;
    }

    /**
     * Determine whether the user can view the credential.
     */
    public function view(User $user, Credential $credential): bool
    {
        return $credential->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can create credentials.
     */
    public function create(User $user): bool
    {
        return $user->organization_id !== null;
    }

    /**
     * Determine whether the user can update the credential.
     */
    public function update(User $user, Credential $credential): bool
    {
        return $credential->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can delete the credential.
     */
    public function delete(User $user, Credential $credential): bool
    {
        return $credential->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can restore the credential.
     */
    public function restore(User $user, Credential $credential): bool
    {
        return $credential->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can permanently delete the credential.
     */
    public function forceDelete(User $user, Credential $credential): bool
    {
        return $credential->organization_id === $user->organization_id;
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
