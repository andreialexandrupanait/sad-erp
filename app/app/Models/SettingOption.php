<?php

namespace App\Models;

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
}
