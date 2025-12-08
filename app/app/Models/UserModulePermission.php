<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserModulePermission extends Model
{
    protected $fillable = [
        'user_id',
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
     * Get the user that owns this permission.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the module this permission applies to.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        // Clear user's permission cache when permissions change
        static::saved(function (self $permission) {
            $permission->user?->clearPermissionCache();
        });

        static::deleted(function (self $permission) {
            $permission->user?->clearPermissionCache();
        });
    }
}
