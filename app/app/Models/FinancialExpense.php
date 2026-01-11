<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class FinancialExpense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'user_id',
        'created_by',
        'document_name',
        'amount',
        'amount_eur',
        'currency',
        'exchange_rate',
        'occurred_at',
        'category_option_id',
        'year',
        'month',
        'note',
        'source_file_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_eur' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'occurred_at' => 'date',
        'year' => 'integer',
        'month' => 'integer',
    ];

    protected static function booted()
    {
        // Auto-fill organization_id and created_by
        static::creating(function ($expense) {
            if (Auth::check()) {
                $expense->organization_id = $expense->organization_id ?? Auth::user()->organization_id;
                $expense->created_by = $expense->created_by ?? Auth::id();
                // Keep user_id for backwards compatibility
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

        // Global scope for organization isolation only (not user-specific)
        static::addGlobalScope('organization', function (Builder $query) {
            if (Auth::check() && Auth::user()->organization_id) {
                $query->where('financial_expenses.organization_id', Auth::user()->organization_id);
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function category()
    {
        return $this->belongsTo(SettingOption::class, 'category_option_id');
    }

    public function files()
    {
        return $this->morphMany(FinancialFile::class, 'entity');
    }

    public function matchedBankTransaction()
    {
        return $this->hasOne(BankTransaction::class, 'matched_expense_id');
    }

    public function sourceFile()
    {
        return $this->belongsTo(FinancialFile::class, 'source_file_id');
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
        return $query->where('currency',
        'exchange_rate', $currency);
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
