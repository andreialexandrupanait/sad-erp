<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class FinancialExpense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'user_id',
        'document_name',
        'amount',
        'currency',
        'occurred_at',
        'category_option_id',
        'year',
        'month',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'occurred_at' => 'date',
        'year' => 'integer',
        'month' => 'integer',
    ];

    protected static function booted()
    {
        // Auto-fill organization_id and user_id
        static::creating(function ($expense) {
            if (Auth::check()) {
                $expense->organization_id = $expense->organization_id ?? Auth::user()->organization_id;
                $expense->user_id = $expense->user_id ?? Auth::id();
            }

            // Auto-calculate year and month from occurred_at
            if ($expense->occurred_at) {
                $date = is_string($expense->occurred_at)
                    ? \Carbon\Carbon::parse($expense->occurred_at)
                    : $expense->occurred_at;
                $expense->year = $date->year;
                $expense->month = $date->month;
            }
        });

        // Global scope for organization and user isolation
        static::addGlobalScope('user_scope', function (Builder $query) {
            if (Auth::check()) {
                $query->where('organization_id', Auth::user()->organization_id)
                      ->where('user_id', Auth::id());
            }
        });
    }

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(SettingOption::class, 'category_option_id');
    }

    public function files()
    {
        return $this->morphMany(FinancialFile::class, 'entity');
    }

    // Scopes
    public function scopeForYear($query, $year)
    {
        return $query->where('year', $year);
    }

    public function scopeForMonth($query, $year, $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    public function scopeByCurrency($query, $currency)
    {
        return $query->where('currency', $currency);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_option_id', $categoryId);
    }

    // Helper methods
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }

    public function getMonthNameAttribute()
    {
        return \Carbon\Carbon::create()->setMonth($this->month)->translatedFormat('F');
    }

    public function getCategoryColorAttribute()
    {
        return $this->category ? $this->category->color_class : 'slate';
    }
}
