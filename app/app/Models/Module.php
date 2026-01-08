<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Module extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'icon',
        'route_prefix',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get user permissions for this module.
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(UserModulePermission::class);
    }

    /**
     * Get role default permissions for this module.
     */
    public function roleDefaults(): HasMany
    {
        return $this->hasMany(RoleModuleDefault::class);
    }

    /**
     * Get all active modules (cached for performance).
     */
    public static function getAllCached(): Collection
    {
        return Cache::remember('modules:all', 3600, function () {
            return self::where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        });
    }

    /**
     * Clear the modules cache.
     */
    public static function clearCache(): void
    {
        Cache::forget('modules:all');
    }

    /**
     * Find module by slug.
     */
    public static function findBySlug(string $slug): ?self
    {
        return self::getAllCached()->firstWhere('slug', $slug);
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::saved(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });
    }
}
