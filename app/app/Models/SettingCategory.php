<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SettingCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the setting groups for this category
     */
    public function groups(): HasMany
    {
        return $this->hasMany(SettingGroup::class, 'category_id')->orderBy('order');
    }

    /**
     * Get active groups only
     */
    public function activeGroups(): HasMany
    {
        return $this->groups()->where('is_active', true);
    }

    /**
     * Scope for active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered categories
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
