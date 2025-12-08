<?php

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubscriptionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any subscriptions.
     */
    public function viewAny(User $user): bool
    {
        return $user->organization_id !== null;
    }

    /**
     * Determine whether the user can view the subscription.
     * Organization-scoped: any user in the org can view.
     */
    public function view(User $user, Subscription $subscription): bool
    {
        return $subscription->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can create subscriptions.
     */
    public function create(User $user): bool
    {
        return $user->organization_id !== null;
    }

    /**
     * Determine whether the user can update the subscription.
     * Organization-scoped: any user in the org can update.
     */
    public function update(User $user, Subscription $subscription): bool
    {
        return $subscription->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can delete the subscription.
     * Organization-scoped: any user in the org can delete.
     */
    public function delete(User $user, Subscription $subscription): bool
    {
        return $subscription->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can restore the subscription.
     */
    public function restore(User $user, Subscription $subscription): bool
    {
        return $subscription->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can permanently delete the subscription.
     */
    public function forceDelete(User $user, Subscription $subscription): bool
    {
        return $subscription->organization_id === $user->organization_id;
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
