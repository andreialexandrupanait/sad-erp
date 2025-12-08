<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can view any clients.
     */
    public function viewAny(User $user): bool
    {
        return $user->organization_id !== null;
    }

    /**
     * Determine if user can view a specific client.
     * Organization-scoped: any user in the org can view.
     */
    public function view(User $user, Client $client): bool
    {
        return $client->organization_id === $user->organization_id;
    }

    /**
     * Determine if user can create clients.
     */
    public function create(User $user): bool
    {
        return $user->organization_id !== null;
    }

    /**
     * Determine if user can update a client.
     * Organization-scoped: any user in the org can update.
     */
    public function update(User $user, Client $client): bool
    {
        return $client->organization_id === $user->organization_id;
    }

    /**
     * Determine if user can delete a client.
     * Organization-scoped: any user in the org can delete.
     */
    public function delete(User $user, Client $client): bool
    {
        return $client->organization_id === $user->organization_id;
    }

    /**
     * Determine if user can restore a soft-deleted client.
     */
    public function restore(User $user, Client $client): bool
    {
        return $client->organization_id === $user->organization_id;
    }

    /**
     * Determine if user can force delete a client.
     */
    public function forceDelete(User $user, Client $client): bool
    {
        return $client->organization_id === $user->organization_id;
    }

    /**
     * Determine if user can perform bulk updates.
     */
    public function bulkUpdate(User $user): bool
    {
        return $user->organization_id !== null;
    }

    /**
     * Determine if user can perform bulk deletes.
     */
    public function bulkDelete(User $user): bool
    {
        return $user->organization_id !== null;
    }
}
