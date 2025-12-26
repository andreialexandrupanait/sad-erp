<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'offer_id',
        'version_number',
        'snapshot',
        'changes_summary',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'changes_summary' => 'array',
    ];

    /**
     * Relationships
     */
    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get a specific field from the snapshot.
     */
    public function getSnapshotField(string $field, $default = null)
    {
        return $this->snapshot[$field] ?? $default;
    }

    /**
     * Compare this version with another version.
     * Returns an array of changed fields.
     */
    public function compareWith(OfferVersion $other): array
    {
        $changes = [];
        $fieldsToCompare = ['title', 'introduction', 'terms', 'subtotal', 'discount_amount', 'discount_percent', 'total', 'valid_until'];

        foreach ($fieldsToCompare as $field) {
            $thisValue = $this->snapshot[$field] ?? null;
            $otherValue = $other->snapshot[$field] ?? null;

            if ($thisValue !== $otherValue) {
                $changes[$field] = [
                    'old' => $thisValue,
                    'new' => $otherValue,
                ];
            }
        }

        // Compare items
        $thisItems = $this->snapshot['items'] ?? [];
        $otherItems = $other->snapshot['items'] ?? [];

        if (json_encode($thisItems) !== json_encode($otherItems)) {
            $changes['items'] = [
                'old_count' => count($thisItems),
                'new_count' => count($otherItems),
            ];
        }

        return $changes;
    }

    /**
     * Get formatted date of version creation.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('d/m/Y H:i');
    }
}
