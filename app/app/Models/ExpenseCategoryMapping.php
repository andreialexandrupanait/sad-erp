<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseCategoryMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'pattern',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get all active mappings for an organization
     */
    public static function getActiveMappings(?int $organizationId = null): array
    {
        $organizationId = $organizationId ?? auth()->user()?->organization_id;

        if (!$organizationId) {
            return [];
        }

        return static::where('organization_id', $organizationId)
            ->where('is_active', true)
            ->pluck('category', 'pattern')
            ->toArray();
    }

    /**
     * Find category for a description based on pattern matching
     */
    public static function findCategoryForDescription(string $description, ?int $organizationId = null): ?string
    {
        $mappings = static::getActiveMappings($organizationId);

        foreach ($mappings as $pattern => $category) {
            if (stripos($description, $pattern) !== false) {
                return $category;
            }
        }

        return null;
    }

    /**
     * Add or update a mapping
     */
    public static function saveMapping(string $pattern, string $category, ?int $organizationId = null): self
    {
        $organizationId = $organizationId ?? auth()->user()?->organization_id;

        return static::updateOrCreate(
            [
                'organization_id' => $organizationId,
                'pattern' => $pattern,
            ],
            [
                'category' => $category,
                'is_active' => true,
            ]
        );
    }
}
