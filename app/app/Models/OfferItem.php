<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferItem extends Model
{
    use HasFactory;

    /**
     * Flag to defer total calculation during bulk operations.
     * Set to true before bulk save, then call Offer::calculateTotals() once at the end.
     */
    public static bool $deferTotalCalculation = false;

    protected $fillable = [
        'offer_id',
        'service_id',
        'type',
        'is_selected',
        'title',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'discount_percent',
        'currency',
        'total_price',
        'original_currency',
        'original_unit_price',
        'exchange_rate',
        'is_recurring',
        'billing_cycle',
        'custom_cycle_days',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'total_price' => 'decimal:2',
        'original_unit_price' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'is_recurring' => 'boolean',
        'is_selected' => 'boolean',
        'custom_cycle_days' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Boot function
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-calculate total_price with discount
        static::saving(function ($item) {
            $subtotal = $item->quantity * $item->unit_price;
            $discount = $subtotal * (($item->discount_percent ?? 0) / 100);
            $item->total_price = $subtotal - $discount;
        });

        // Recalculate offer totals after save (unless deferred for bulk operations)
        static::saved(function ($item) {
            if (!static::$deferTotalCalculation && $item->offer) {
                $item->offer->calculateTotals();
            }
        });

        // Recalculate offer totals after delete (unless deferred for bulk operations)
        static::deleted(function ($item) {
            if (!static::$deferTotalCalculation && $item->offer) {
                $item->offer->calculateTotals();
            }
        });
    }

    /**
     * Perform a bulk operation with deferred total calculation.
     * Usage: OfferItem::withDeferredCalculation(function() { ... bulk operations ... }, $offer);
     */
    public static function withDeferredCalculation(callable $callback, ?Offer $offer = null): mixed
    {
        static::$deferTotalCalculation = true;

        try {
            $result = $callback();
        } finally {
            static::$deferTotalCalculation = false;

            // Calculate totals once at the end if offer provided
            if ($offer) {
                $offer->calculateTotals();
            }
        }

        return $result;
    }

    /**
     * Relationships
     */
    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get unit label
     */
    public function getUnitLabelAttribute()
    {
        $units = [
            'buc' => __('pcs'),
            'ora' => __('hour'),
            'luna' => __('month'),
            'an' => __('year'),
            'proiect' => __('project'),
        ];

        return $units[$this->unit] ?? $this->unit;
    }

    /**
     * Get billing cycle label
     */
    public function getBillingCycleLabelAttribute()
    {
        if (!$this->is_recurring) {
            return __('One-time');
        }

        $cycles = [
            'monthly' => __('Monthly'),
            'yearly' => __('Yearly'),
            'custom' => $this->custom_cycle_days
                ? trans_choice('Every :count day|Every :count days', $this->custom_cycle_days, ['count' => $this->custom_cycle_days])
                : __('Custom'),
        ];

        return $cycles[$this->billing_cycle] ?? ucfirst($this->billing_cycle);
    }

    /**
     * Available units
     */
    public static function getUnits()
    {
        return [
            'buc' => __('Pieces (buc)'),
            'ora' => __('Hours'),
            'luna' => __('Months'),
            'an' => __('Years'),
            'proiect' => __('Projects'),
        ];
    }

    /**
     * Available billing cycles
     */
    public static function getBillingCycles()
    {
        return [
            'monthly' => __('Monthly'),
            'yearly' => __('Yearly'),
            'custom' => __('Custom'),
        ];
    }

    /**
     * Create from service
     */
    public static function fromService(Service $service, float $quantity = 1)
    {
        return new static([
            'service_id' => $service->id,
            'title' => $service->name,
            'description' => $service->description,
            'quantity' => $quantity,
            'unit' => $service->unit ?? 'buc',
            'unit_price' => $service->default_rate,
            'original_currency' => $service->currency,
            'original_unit_price' => $service->default_rate,
            'exchange_rate' => 1.0,
            'is_recurring' => false,
        ]);
    }

    /**
     * Create from service with currency conversion
     */
    public static function fromServiceWithConversion(Service $service, string $targetCurrency, float $quantity = 1): static
    {
        $item = new static([
            'service_id' => $service->id,
            'title' => $service->name,
            'description' => $service->description,
            'quantity' => $quantity,
            'unit' => $service->unit ?? 'buc',
            'original_currency' => $service->currency ?? 'RON',
            'original_unit_price' => $service->default_rate,
            'is_recurring' => false,
        ]);

        $serviceCurrency = strtoupper($service->currency ?? 'RON');
        $targetCurrency = strtoupper($targetCurrency);

        if ($serviceCurrency !== $targetCurrency) {
            $rate = ExchangeRate::getRate($serviceCurrency, $targetCurrency);

            if ($rate !== null) {
                $item->exchange_rate = $rate;
                $item->unit_price = round($service->default_rate * $rate, 2);
            } else {
                // No rate available - use original price (user will need to adjust)
                $item->exchange_rate = null;
                $item->unit_price = $service->default_rate;
            }
        } else {
            $item->exchange_rate = 1.0;
            $item->unit_price = $service->default_rate;
        }

        return $item;
    }

    /**
     * Check if this item was converted from a different currency
     */
    public function wasConverted(): bool
    {
        return $this->original_currency
            && $this->offer
            && strtoupper($this->original_currency) !== strtoupper($this->offer->currency ?? 'RON');
    }

    /**
     * Get original amount formatted
     */
    public function getOriginalAmountFormattedAttribute(): ?string
    {
        if (!$this->original_currency || !$this->original_unit_price) {
            return null;
        }

        return number_format($this->original_unit_price, 2, ',', '.') . ' ' . $this->original_currency;
    }
}
