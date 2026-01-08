<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        Log::info('User created', [
            'user_id' => $user->id,
            'email' => $user->email,
            'organization_id' => $user->organization_id,
            'role' => $user->role,
        ]);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Clear user-specific caches when user is updated
        $this->clearUserCaches($user);

        // If role changed, log it
        if ($user->isDirty('role')) {
            Log::info('User role changed', [
                'user_id' => $user->id,
                'old_role' => $user->getOriginal('role'),
                'new_role' => $user->role,
            ]);
        }

        // If organization changed, log it (important for multi-tenancy)
        if ($user->isDirty('organization_id')) {
            Log::warning('User organization changed', [
                'user_id' => $user->id,
                'old_org' => $user->getOriginal('organization_id'),
                'new_org' => $user->organization_id,
            ]);
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Clear user-specific caches
        $this->clearUserCaches($user);

        Log::info('User deleted', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        Log::info('User restored', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        // Clear all user-related caches
        $this->clearUserCaches($user);

        Log::warning('User permanently deleted', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    /**
     * Clear all caches related to this user.
     */
    protected function clearUserCaches(User $user): void
    {
        // Clear module permissions cache
        Cache::forget("user.{$user->id}.modules");

        // Clear user services cache
        Cache::forget("user.{$user->id}.services");

        // Clear any organization-level caches this user might affect
        if ($user->organization_id) {
            // Organization users cache
            Cache::forget("org.{$user->organization_id}.users");

            // If this is an admin, clear admin-related caches
            if (in_array($user->role, ['admin', 'superadmin'])) {
                Cache::forget("org.{$user->organization_id}.admins");
            }
        }
    }
}
