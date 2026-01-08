<?php

namespace App\Http\Controllers\Traits;

use App\Models\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Trait for caching common dropdown queries to reduce database load.
 *
 * Provides cached versions of frequently-used dropdown data like
 * clients list, which are queried on many pages.
 */
trait CachedDropdowns
{
    /**
     * Get cached client dropdown list for select inputs.
     *
     * Caches client list for 5 minutes, keyed by organization.
     * Returns only id and name for dropdowns.
     *
     * @return Collection
     */
    protected function getCachedClientDropdown(): Collection
    {
        $orgId = auth()->user()?->organization_id ?? 0;
        $cacheKey = "dropdown:clients:{$orgId}";
        $ttl = config('erp.cache.client_dropdown_ttl', 300);

        return Cache::remember($cacheKey, $ttl, function () {
            return Client::select('id', 'name')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Clear the cached client dropdown.
     *
     * Call this when clients are created/updated/deleted.
     */
    protected static function clearCachedClientDropdown(): void
    {
        $orgId = auth()->user()?->organization_id ?? 0;
        Cache::forget("dropdown:clients:{$orgId}");
    }
}
