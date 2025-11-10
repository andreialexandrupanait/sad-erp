<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class FinancialSetting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'option_type',
        'option_label',
        'option_value',
        'color_class',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    protected static function booted()
    {
        // Auto-fill organization_id
        static::creating(function ($setting) {
            if (Auth::check()) {
                $setting->organization_id = $setting->organization_id ?? Auth::user()->organization_id;
            }
        });

        // Global scope for organization isolation
        static::addGlobalScope('organization', function (Builder $query) {
            if (Auth::check()) {
                $query->where('organization_id', Auth::user()->organization_id);
            }
        });
    }

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function expenses()
    {
        return $this->hasMany(FinancialExpense::class, 'category_option_id');
    }

    // Scopes
    public function scopeExpenseCategories($query)
    {
        return $query->where('option_type', 'expense_category')->orderBy('sort_order');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Helper methods
    public function getBadgeClassAttribute()
    {
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

        return $colorMap[$this->color_class] ?? $colorMap['slate'];
    }

    public static function getAvailableColors()
    {
        return [
            'slate' => 'Slate',
            'blue' => 'Blue',
            'green' => 'Green',
            'red' => 'Red',
            'yellow' => 'Yellow',
            'purple' => 'Purple',
            'orange' => 'Orange',
            'pink' => 'Pink',
        ];
    }
}
