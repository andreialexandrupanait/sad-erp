<?php

namespace App\Http\View\Composers;

use App\Models\ClientSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Settings View Composer
 *
 * Efficiently provides settings data to views using caching.
 * This prevents N+1 queries and reduces database load.
 *
 * Usage: Settings are automatically available in specified views
 * Cache is invalidated when settings are modified
 */
class SettingsComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        // Only fetch if user is authenticated
        if (!auth()->check()) {
            $view->with('clientStatuses', collect([]));
            return;
        }

        $userId = auth()->id();

        // Cache key unique to this user
        $cacheKey = "user.{$userId}.client_statuses";

        // Get statuses from cache or database (cache for 1 hour)
        $clientStatuses = Cache::remember($cacheKey, 3600, function () {
            return ClientSetting::active()
                ->ordered()
                ->get();
        });

        // Make available to view
        $view->with('clientStatuses', $clientStatuses);
    }

    /**
     * Clear cache for a specific user
     */
    public static function clearCache(?int $userId = null): void
    {
        $userId = $userId ?? auth()->id();

        if ($userId) {
            Cache::forget("user.{$userId}.client_statuses");
        }
    }

    /**
     * Clear cache for all users (use after seeding or mass updates)
     */
    public static function clearAllCache(): void
    {
        Cache::flush();
    }
}
