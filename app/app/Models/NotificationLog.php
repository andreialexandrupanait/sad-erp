<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    protected $fillable = [
        'organization_id',
        'user_id',
        'notification_type',
        'channel',
        'entity_type',
        'entity_id',
        'payload',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
    ];

    /**
     * Get the organization that owns the notification log.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the user who triggered the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by organization.
     */
    public function scopeForOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Scope to filter by entity.
     */
    public function scopeForEntity($query, string $entityType, int $entityId)
    {
        return $query->where('entity_type', $entityType)
                     ->where('entity_id', $entityId);
    }

    /**
     * Scope to filter by notification type.
     */
    public function scopeOfType($query, string $notificationType)
    {
        return $query->where('notification_type', $notificationType);
    }

    /**
     * Scope to filter by channel.
     */
    public function scopeViaChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get successfully sent notifications.
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope to get failed notifications.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to get skipped notifications.
     */
    public function scopeSkipped($query)
    {
        return $query->where('status', 'skipped');
    }

    /**
     * Check if a notification was already sent for a specific entity and type.
     *
     * @param string $notificationType
     * @param string $entityType
     * @param int $entityId
     * @param int|null $organizationId
     * @param \Carbon\Carbon|null $onDate Check if sent on a specific date
     * @param string|null $channel Optional channel filter
     * @return bool
     */
    public static function wasAlreadySent(
        string $notificationType,
        string $entityType,
        int $entityId,
        ?int $organizationId = null,
        $onDate = null,
        ?string $channel = null
    ): bool {
        $query = self::where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->where('notification_type', $notificationType)
            ->where('status', 'sent');

        if ($organizationId !== null) {
            $query->where('organization_id', $organizationId);
        }

        if ($onDate !== null) {
            $query->whereDate('sent_at', $onDate);
        }

        if ($channel !== null) {
            $query->where('channel', $channel);
        }

        return $query->exists();
    }
}
