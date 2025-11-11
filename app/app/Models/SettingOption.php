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

    /**
     * Accessor for 'name' - backwards compatibility with ClientSetting
     * Maps 'label' to 'name'
     */
    public function getNameAttribute(): string
    {
        return $this->label;
    }

    /**
     * Accessor for 'color_background' - backwards compatibility
     * Generates a light background color from the main color
     */
    public function getColorBackgroundAttribute(): string
    {
        if (!$this->color) {
            return '#F3F4F6'; // slate-100
        }

        // Convert hex to RGB and lighten it
        $hex = ltrim($this->color, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Lighten by mixing with white (90% white, 10% color)
        $r = round($r * 0.1 + 255 * 0.9);
        $g = round($g * 0.1 + 255 * 0.9);
        $b = round($b * 0.1 + 255 * 0.9);

        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }

    /**
     * Accessor for 'color_text' - backwards compatibility
     * Returns a dark version of the color for text
     */
    public function getColorTextAttribute(): string
    {
        if (!$this->color) {
            return '#374151'; // slate-700
        }

        // Convert hex to RGB and darken it
        $hex = ltrim($this->color, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Darken by 60%
        $r = round($r * 0.4);
        $g = round($g * 0.4);
        $b = round($b * 0.4);

        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }
}
