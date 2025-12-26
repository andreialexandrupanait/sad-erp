<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contract_id',
        'offer_item_id',
        'service_id',
        'title',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'discount_percent',
        'total_price',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function offerItem()
    {
        return $this->belongsTo(OfferItem::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Create ContractItem from OfferItem.
     * Preserves all data for self-contained contract.
     */
    public static function fromOfferItem(OfferItem $offerItem, int $sortOrder = 0): static
    {
        return new static([
            'offer_item_id' => $offerItem->id,
            'service_id' => $offerItem->service_id,
            'title' => $offerItem->title ?? $offerItem->name ?? ($offerItem->service?->name ?? __('Service')),
            'description' => $offerItem->description ?? '',
            'quantity' => $offerItem->quantity ?? 1,
            'unit' => $offerItem->unit ?? 'buc',
            'unit_price' => $offerItem->unit_price ?? 0,
            'discount_percent' => $offerItem->discount_percent ?? 0,
            'total_price' => $offerItem->total_price ?? $offerItem->total ?? 0,
            'sort_order' => $sortOrder,
        ]);
    }

    /**
     * Get the display name for this item.
     * Returns title if set, otherwise description.
     */
    public function getNameAttribute(): string
    {
        return $this->title ?? $this->description ?? __('Service');
    }

    /**
     * Calculate total price based on quantity, unit price and discount.
     */
    public function calculateTotal(): float
    {
        $subtotal = $this->quantity * $this->unit_price;
        $discount = $subtotal * ($this->discount_percent / 100);
        return round($subtotal - $discount, 2);
    }

    /**
     * Boot method to auto-calculate total.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            if (is_null($item->total_price)) {
                $item->total_price = $item->calculateTotal();
            }
        });
    }
}
