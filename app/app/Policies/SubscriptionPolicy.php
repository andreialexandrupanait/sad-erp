<?php

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;

class SubscriptionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Subscription $subscription): bool
    {
        // User can view their own subscriptions
        // Admins can view all subscriptions in their organization
        return $this->isOwner($user, $subscription) || $this->isOrgAdmin($user, $subscription);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Subscription $subscription): bool
    {
        return $this->isOwner($user, $subscription) || $this->isOrgAdmin($user, $subscription);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Subscription $subscription): bool
    {
        return $this->isOwner($user, $subscription) || $this->isOrgAdmin($user, $subscription);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Subscription $subscription): bool
    {
        return $this->isOwner($user, $subscription) || $this->isOrgAdmin($user, $subscription);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Subscription $subscription): bool
    {
        return $this->isOrgAdmin($user, $subscription);
    }

    /**
     * Determine whether the user can renew the subscription.
     */
    public function renew(User $user, Subscription $subscription): bool
    {
        return $this->isOwner($user, $subscription) || $this->isOrgAdmin($user, $subscription);
    }

    /**
     * Check if user owns the subscription.
     */
    private function isOwner(User $user, Subscription $subscription): bool
    {
        return $user->id === $subscription->user_id;
    }

    /**
     * Check if user is an admin in the same organization.
     */
    private function isOrgAdmin(User $user, Subscription $subscription): bool
    {
        // Get the subscription owner to check their organization
        $subscriptionOwner = $subscription->user;
        if (!$subscriptionOwner) {
            return false;
        }

        return $user->organization_id === $subscriptionOwner->organization_id && $user->isAdmin();
    }
}
