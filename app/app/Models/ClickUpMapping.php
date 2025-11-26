<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClickUpMapping extends Model
{
    protected $table = 'clickup_mappings';

    protected $fillable = [
        'organization_id',
        'entity_type',
        'clickup_id',
        'laravel_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the organization that owns this mapping
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get or create a mapping
     *
     * @param int $organizationId
     * @param string $entityType
     * @param string $clickupId
     * @param int $laravelId
     * @param array|null $metadata
     * @return static
     */
    public static function createMapping($organizationId, $entityType, $clickupId, $laravelId, $metadata = null)
    {
        return static::updateOrCreate(
            [
                'organization_id' => $organizationId,
                'entity_type' => $entityType,
                'clickup_id' => $clickupId,
            ],
            [
                'laravel_id' => $laravelId,
                'metadata' => $metadata,
            ]
        );
    }

    /**
     * Get Laravel ID from ClickUp ID
     *
     * @param int $organizationId
     * @param string $entityType
     * @param string $clickupId
     * @return int|null
     */
    public static function getLaravelId($organizationId, $entityType, $clickupId)
    {
        $mapping = static::where('organization_id', $organizationId)
            ->where('entity_type', $entityType)
            ->where('clickup_id', $clickupId)
            ->first();

        return $mapping ? $mapping->laravel_id : null;
    }

    /**
     * Get ClickUp ID from Laravel ID
     *
     * @param int $organizationId
     * @param string $entityType
     * @param int $laravelId
     * @return string|null
     */
    public static function getClickUpId($organizationId, $entityType, $laravelId)
    {
        $mapping = static::where('organization_id', $organizationId)
            ->where('entity_type', $entityType)
            ->where('laravel_id', $laravelId)
            ->first();

        return $mapping ? $mapping->clickup_id : null;
    }
}
