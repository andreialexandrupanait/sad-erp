<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SettingGroup extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'key',
        'description',
        'order',
        'has_colors',
        'is_active',
    ];

    protected $casts = [
        'has_colors' => 'boolean',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the category this group belongs to
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(SettingCategory::class, 'category_id');
    }

    /**
     * Get all options for this group
     */
    public function options(): HasMany
    {
        return $this->hasMany(SettingOption::class, 'group_id')->orderBy('order');
    }

    /**
     * Get active options only
     */
    public function activeOptions(): HasMany
    {
        return $this->options()->where('is_active', true);
    }

    /**
     * Scope for active groups
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered groups
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Get options as key-value pairs for dropdowns
     */
    public function getOptionsForDropdown(): array
    {
        return $this->activeOptions()
            ->pluck('label', 'value')
            ->toArray();
    }
}
