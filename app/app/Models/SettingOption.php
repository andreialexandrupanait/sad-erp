<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SettingOption extends Model
{
    protected $fillable = [
        'group_id',
        'label',
        'value',
        'color',
        'description',
        'order',
        'is_active',
        'is_default',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'order' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the group this option belongs to
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(SettingGroup::class, 'group_id');
    }

    /**
     * Scope for active options
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered options
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Get the display color with fallback
     */
    public function getDisplayColor(): string
    {
        return $this->color ?? '#6b7280'; // Default slate-500
    }
}
