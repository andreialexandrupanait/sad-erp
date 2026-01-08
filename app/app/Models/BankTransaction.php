<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class BankTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'user_id',
        'banking_credential_id',
        'transaction_id',
        'entry_reference',
        'booking_date',
        'value_date',
        'type',
        'amount',
        'currency',
        'description',
        'debtor_name',
        'debtor_account',
        'creditor_name',
        'creditor_account',
        'remittance_information',
        'match_status',
        'match_confidence',
        'matched_revenue_id',
        'matched_expense_id',
        'matched_at',
        'matched_by_user_id',
        'match_notes',
        'raw_data',
        'status',
        'notes',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'value_date' => 'date',
        'amount' => 'decimal:2',
        'match_confidence' => 'decimal:2',
        'matched_at' => 'datetime',
        'raw_data' => 'array',
    ];

    protected static function booted()
    {
        // Auto-fill organization_id
        static::creating(function ($transaction) {
            if (Auth::check()) {
                $transaction->organization_id = $transaction->organization_id ?? Auth::user()->organization_id;
                $transaction->user_id = $transaction->user_id ?? Auth::id();
            }
        });

        // Global scope for organization isolation
        static::addGlobalScope('organization', function (Builder $query) {
            if (Auth::check() && Auth::user()->organization_id) {
                $query->where('bank_transactions.organization_id', Auth::user()->organization_id);
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

    public function bankingCredential()
    {
        return $this->belongsTo(BankingCredential::class);
    }

    public function matchedRevenue()
    {
        return $this->belongsTo(FinancialRevenue::class, 'matched_revenue_id');
    }

    public function matchedExpense()
    {
        return $this->belongsTo(FinancialExpense::class, 'matched_expense_id');
    }

    public function matchedByUser()
    {
        return $this->belongsTo(User::class, 'matched_by_user_id');
    }

    // Scopes
    public function scopeUnmatched($query)
    {
        return $query->where('match_status', 'unmatched');
    }

    public function scopeMatched($query)
    {
        return $query->whereIn('match_status', ['auto_matched', 'manual_matched']);
    }

    public function scopeIncoming($query)
    {
        return $query->where('type', 'incoming');
    }

    public function scopeOutgoing($query)
    {
        return $query->where('type', 'outgoing');
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('booking_date', [$startDate, $endDate]);
    }

    public function scopeByCurrency($query, $currency)
    {
        return $query->where('currency', $currency);
    }

    // Accessors
    public function getIsMatchedAttribute()
    {
        return in_array($this->match_status, ['auto_matched', 'manual_matched']);
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }

    // Methods
    public function matchToRevenue(FinancialRevenue $revenue, float $confidence = null, string $notes = null)
    {
        $this->update([
            'matched_revenue_id' => $revenue->id,
            'match_status' => $confidence >= 90 ? 'auto_matched' : 'manual_matched',
            'match_confidence' => $confidence,
            'matched_at' => now(),
            'matched_by_user_id' => Auth::id(),
            'match_notes' => $notes,
        ]);
    }

    public function matchToExpense(FinancialExpense $expense, float $confidence = null, string $notes = null)
    {
        $this->update([
            'matched_expense_id' => $expense->id,
            'match_status' => $confidence >= 90 ? 'auto_matched' : 'manual_matched',
            'match_confidence' => $confidence,
            'matched_at' => now(),
            'matched_by_user_id' => Auth::id(),
            'match_notes' => $notes,
        ]);
    }

    public function unmatch()
    {
        $this->update([
            'matched_revenue_id' => null,
            'matched_expense_id' => null,
            'match_status' => 'unmatched',
            'match_confidence' => null,
            'matched_at' => null,
            'matched_by_user_id' => null,
            'match_notes' => null,
        ]);
    }

    public function ignore(string $notes = null)
    {
        $this->update([
            'match_status' => 'ignored',
            'match_notes' => $notes,
        ]);
    }
}
