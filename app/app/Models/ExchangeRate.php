<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ExchangeRate extends Model
{
    protected $fillable = [
        'organization_id',
        'from_currency',
        'to_currency',
        'rate',
        'effective_date',
        'source',
    ];

    protected $casts = [
        'rate' => 'decimal:6',
        'effective_date' => 'date',
    ];

    protected static function booted()
    {
        // Auto-fill organization_id
        static::creating(function ($rate) {
            if (Auth::check() && !$rate->organization_id) {
                $rate->organization_id = Auth::user()->organization_id;
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

    /**
     * Get the exchange rate for a currency pair on a specific date
     * Returns the most recent rate on or before the given date
     */
    public static function getRate(string $from, string $to, ?Carbon $date = null): ?float
    {
        // Same currency, no conversion needed
        if (strtoupper($from) === strtoupper($to)) {
            return 1.0;
        }

        $date = $date ?? now();

        // Try direct rate first
        $rate = static::where('from_currency', strtoupper($from))
            ->where('to_currency', strtoupper($to))
            ->where('effective_date', '<=', $date)
            ->orderBy('effective_date', 'desc')
            ->first();

        if ($rate) {
            return (float) $rate->rate;
        }

        // Try inverse rate
        $inverseRate = static::where('from_currency', strtoupper($to))
            ->where('to_currency', strtoupper($from))
            ->where('effective_date', '<=', $date)
            ->orderBy('effective_date', 'desc')
            ->first();

        if ($inverseRate && $inverseRate->rate > 0) {
            return 1.0 / (float) $inverseRate->rate;
        }

        return null;
    }

    /**
     * Convert an amount between currencies
     */
    public static function convert(float $amount, string $from, string $to, ?Carbon $date = null): ?array
    {
        $rate = static::getRate($from, $to, $date);

        if ($rate === null) {
            return null;
        }

        return [
            'original_amount' => $amount,
            'original_currency' => strtoupper($from),
            'converted_amount' => round($amount * $rate, 2),
            'target_currency' => strtoupper($to),
            'exchange_rate' => $rate,
            'conversion_date' => $date ?? now(),
        ];
    }

    /**
     * Get all available rates to a target currency
     */
    public static function getAvailableRates(string $targetCurrency): array
    {
        $rates = [];
        $targetCurrency = strtoupper($targetCurrency);

        // Get direct rates
        $directRates = static::where('to_currency', $targetCurrency)
            ->orderBy('effective_date', 'desc')
            ->get()
            ->unique('from_currency');

        foreach ($directRates as $rate) {
            $rates[$rate->from_currency] = [
                'rate' => (float) $rate->rate,
                'effective_date' => $rate->effective_date,
                'source' => $rate->source,
            ];
        }

        // Get inverse rates (for currencies we don't have direct rates for)
        $inverseRates = static::where('from_currency', $targetCurrency)
            ->orderBy('effective_date', 'desc')
            ->get()
            ->unique('to_currency');

        foreach ($inverseRates as $rate) {
            if (!isset($rates[$rate->to_currency]) && $rate->rate > 0) {
                $rates[$rate->to_currency] = [
                    'rate' => 1.0 / (float) $rate->rate,
                    'effective_date' => $rate->effective_date,
                    'source' => $rate->source . ' (inverse)',
                ];
            }
        }

        return $rates;
    }

    /**
     * Check if a rate exists for a currency pair
     */
    public static function hasRate(string $from, string $to): bool
    {
        return static::getRate($from, $to) !== null;
    }

    /**
     * Set or update a rate for today.
     * Uses transaction with locking to prevent race conditions.
     */
    public static function setRate(string $from, string $to, float $rate, string $source = 'manual'): static
    {
        return DB::transaction(function () use ($from, $to, $rate, $source) {
            $orgId = Auth::user()->organization_id;
            $fromCurrency = strtoupper($from);
            $toCurrency = strtoupper($to);
            $date = now()->toDateString();

            // Try to find and lock existing rate
            $existing = static::lockForUpdate()
                ->where('organization_id', $orgId)
                ->where('from_currency', $fromCurrency)
                ->where('to_currency', $toCurrency)
                ->where('effective_date', $date)
                ->first();

            if ($existing) {
                $existing->update([
                    'rate' => $rate,
                    'source' => $source,
                ]);
                return $existing;
            }

            return static::create([
                'organization_id' => $orgId,
                'from_currency' => $fromCurrency,
                'to_currency' => $toCurrency,
                'effective_date' => $date,
                'rate' => $rate,
                'source' => $source,
            ]);
        });
    }
}
