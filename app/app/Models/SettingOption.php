<?php

namespace App\Models;

use App\Helpers\SettingsHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class SettingOption extends Model implements Sortable
{
    use SoftDeletes, SortableTrait;

    protected $table = 'settings_options';

    protected $fillable = [
        'organization_id',
        'parent_id',
        'category',
        'label',
        'value',
        'color_class',
        'sort_order',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public $sortable = [
        'order_column_name' => 'sort_order',
        'sort_when_creating' => true,
    ];

    protected static function booted()
    {
        parent::booted();

        // Auto-fill organization_id
        static::creating(function ($setting) {
            if (Auth::check() && Auth::user()->organization_id) {
                $setting->organization_id = $setting->organization_id ?? Auth::user()->organization_id;
            }
        });

        // Clear cache when settings are modified
        static::saved(function ($setting) {
            SettingsHelper::clearCache($setting->category, $setting->organization_id);
        });

        static::deleted(function ($setting) {
            SettingsHelper::clearCache($setting->category, $setting->organization_id);
        });

        // Global scope for organization isolation
        static::addGlobalScope('organization', function (Builder $query) {
            if (Auth::check() && Auth::user()->organization_id) {
                $query->where(function ($q) {
                    $q->where('organization_id', Auth::user()->organization_id)
                      ->orWhereNull('organization_id'); // Allow global options
                });
            }
        });
    }

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function parent()
    {
        return $this->belongsTo(SettingOption::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(SettingOption::class, 'parent_id')->orderBy('sort_order');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'status_id');
    }

    // Scopes by category
    public function scopeClientStatuses($query)
    {
        return $query->where('category', 'client_statuses')
                     ->where('is_active', true)
                     ->orderBy('sort_order');
    }

    public function scopeDomainRegistrars($query)
    {
        return $query->where('category', 'domain_registrars')
                     ->where('is_active', true)
                     ->orderBy('sort_order');
    }

    public function scopeDomainStatuses($query)
    {
        return $query->where('category', 'domain_statuses')
                     ->where('is_active', true)
                     ->orderBy('sort_order');
    }

    public function scopeBillingCycles($query)
    {
        return $query->where('category', 'billing_cycles')
                     ->where('is_active', true)
                     ->orderBy('sort_order');
    }

    public function scopeSubscriptionStatuses($query)
    {
        return $query->where('category', 'subscription_statuses')
                     ->where('is_active', true)
                     ->orderBy('sort_order');
    }

    public function scopePaymentMethods($query)
    {
        return $query->where('category', 'payment_methods')
                     ->where('is_active', true)
                     ->orderBy('sort_order');
    }

    public function scopeAccessPlatforms($query)
    {
        return $query->where('category', 'access_platforms')
                     ->where('is_active', true)
                     ->orderBy('sort_order');
    }

    public function scopeExpenseCategories($query)
    {
        return $query->where('category', 'expense_categories')
                     ->where('is_active', true)
                     ->orderBy('sort_order');
    }

    public function scopeCurrencies($query)
    {
        return $query->where('category', 'currencies')
                     ->where('is_active', true)
                     ->orderBy('sort_order');
    }

    public function scopeDashboardQuickActions($query)
    {
        return $query->where('category', 'dashboard_quick_actions')
                     ->where('is_active', true)
                     ->orderBy('sort_order');
    }

    public function scopeTaskStatuses($query)
    {
        return $query->where('category', 'task_statuses')
                     ->where('is_active', true)
                     ->orderBy('sort_order');
    }

    public function scopeTaskPriorities($query)
    {
        return $query->where('category', 'task_priorities')
                     ->where('is_active', true)
                     ->orderBy('sort_order');
    }

    // Alias for backward compatibility
    public function scopePlatforms($query)
    {
        return $this->scopeAccessPlatforms($query);
    }

    public function scopeRootCategories($query)
    {
        return $query->where('category', 'expense_categories')
                     ->whereNull('parent_id')
                     ->where('is_active', true)
                     ->orderBy('sort_order');
    }

    // General scopes
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Helper methods

    /**
     * Alias for label - provides backward compatibility
     */
    public function getNameAttribute()
    {
        return $this->label;
    }

    /**
     * Generate a URL-friendly slug from the label
     */
    public function getSlugAttribute(): string
    {
        return \Illuminate\Support\Str::slug($this->label);
    }

    public function getBadgeClassAttribute()
    {
        if (!$this->color_class) {
            return 'bg-slate-100 text-slate-700 border-slate-300';
        }

        $colorMap = [
            'slate' => 'bg-slate-100 text-slate-700 border-slate-300',
            'blue' => 'bg-blue-100 text-blue-700 border-blue-300',
            'green' => 'bg-green-100 text-green-700 border-green-300',
            'red' => 'bg-red-100 text-red-700 border-red-300',
            'yellow' => 'bg-yellow-100 text-yellow-700 border-yellow-300',
            'purple' => 'bg-purple-100 text-purple-700 border-purple-300',
            'orange' => 'bg-orange-100 text-orange-700 border-orange-300',
            'pink' => 'bg-pink-100 text-pink-700 border-pink-300',
        ];

        // If color_class is a hex color, return it directly
        if (str_starts_with($this->color_class, '#')) {
            return 'border-gray-300';
        }

        return $colorMap[$this->color_class] ?? $colorMap['slate'];
    }

    /**
     * Generate a light background color from the main color
     */
    public function getColorBackgroundAttribute()
    {
        if (!$this->color_class || !str_starts_with($this->color_class, '#')) {
            return '#F3F4F6'; // Default gray-100
        }

        // Convert hex to lighter shade for background (90% lighter)
        return $this->adjustBrightness($this->color_class, 0.9);
    }

    /**
     * Generate a dark text color from the main color
     */
    public function getColorTextAttribute()
    {
        if (!$this->color_class || !str_starts_with($this->color_class, '#')) {
            return '#1F2937'; // Default gray-800
        }

        // Convert hex to darker shade for text (40% darker)
        return $this->adjustBrightness($this->color_class, -0.4);
    }

    /**
     * Adjust the brightness of a hex color
     *
     * @param string $hex Hex color code (e.g., '#3B82F6')
     * @param float $percent Percentage to adjust (-1.0 to 1.0, negative darkens, positive lightens)
     * @return string Adjusted hex color
     */
    private function adjustBrightness($hex, $percent)
    {
        // Remove # if present
        $hex = ltrim($hex, '#');

        // Convert to RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Adjust brightness
        if ($percent > 0) {
            // Lighten: move towards white (255)
            $r = min(255, $r + (255 - $r) * $percent);
            $g = min(255, $g + (255 - $g) * $percent);
            $b = min(255, $b + (255 - $b) * $percent);
        } else {
            // Darken: move towards black (0)
            $r = max(0, $r + $r * $percent);
            $g = max(0, $g + $g * $percent);
            $b = max(0, $b + $b * $percent);
        }

        return sprintf("#%02x%02x%02x", round($r), round($g), round($b));
    }
}
