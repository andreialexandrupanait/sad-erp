<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class FinancialRevenue extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'user_id',
        'document_name',
        'amount',
        'currency',
        'occurred_at',
        'client_id',
        'year',
        'month',
        'note',
        'smartbill_invoice_number',
        'smartbill_series',
        'smartbill_client_cif',
        'smartbill_imported_at',
        'smartbill_raw_data',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'occurred_at' => 'date',
        'year' => 'integer',
        'month' => 'integer',
        'smartbill_imported_at' => 'datetime',
        'smartbill_raw_data' => 'array',
    ];

    protected static function booted()
    {
        // Auto-fill organization_id and user_id
        static::creating(function ($revenue) {
            if (Auth::check()) {
                $revenue->organization_id = $revenue->organization_id ?? Auth::user()->organization_id;
                $revenue->user_id = $revenue->user_id ?? Auth::id();
            }

            // Auto-calculate year and month from occurred_at
            if ($revenue->occurred_at) {
                $date = is_string($revenue->occurred_at)
                    ? \Carbon\Carbon::parse($revenue->occurred_at)
                    : $revenue->occurred_at;
                $revenue->year = $date->year;
                $revenue->month = $date->month;
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

    public function client()
    {
        return $this->belongsTo(Client::class);
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

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
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
}
