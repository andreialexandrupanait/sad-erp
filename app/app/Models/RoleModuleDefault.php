<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class RoleModuleDefault extends Model
{
    protected $fillable = [
        'role',
        'module_id',
        'can_view',
        'can_create',
        'can_update',
        'can_delete',
        'can_export',
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_create' => 'boolean',
        'can_update' => 'boolean',
        'can_delete' => 'boolean',
        'can_export' => 'boolean',
    ];

    /**
     * Get the module this default applies to.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Get defaults for a specific role (cached).
     */
    public static function getForRole(string $role): array
    {
        return Cache::remember(
            "role:{$role}:module_defaults",
            3600,
            function () use ($role) {
                return self::where('role', $role)
                    ->with('module')
                    ->get()
                    ->keyBy('module.slug')
                    ->map(fn ($default) => [
                        'can_view' => $default->can_view,
                        'can_create' => $default->can_create,
                        'can_update' => $default->can_update,
                        'can_delete' => $default->can_delete,
                        'can_export' => $default->can_export,
                    ])
                    ->toArray();
            }
        );
    }

    /**
     * Clear cache for a specific role.
     */
    public static function clearCacheForRole(string $role): void
    {
        Cache::forget("role:{$role}:module_defaults");
    }

    /**
     * Clear cache for all roles.
     */
    public static function clearAllCache(): void
    {
        foreach (['superadmin', 'admin', 'manager', 'user'] as $role) {
            self::clearCacheForRole($role);
        }
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::saved(function (self $default) {
            self::clearCacheForRole($default->role);
        });

        static::deleted(function (self $default) {
            self::clearCacheForRole($default->role);
        });
    }
}
