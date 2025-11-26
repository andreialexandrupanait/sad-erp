<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskTimeEntry extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'description',
        'minutes',
        'billable',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'task_id' => 'integer',
        'user_id' => 'integer',
        'minutes' => 'integer',
        'billable' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /**
     * The task this time entry belongs to
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * The user who logged this time
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Format duration as "2h 30m"
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->minutes) {
            return '0m';
        }

        $hours = floor($this->minutes / 60);
        $mins = $this->minutes % 60;

        if ($hours > 0 && $mins > 0) {
            return "{$hours}h {$mins}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$mins}m";
        }
    }

    /**
     * Calculate duration from started_at and ended_at
     */
    public static function calculateMinutesFromTimestamps($startedAt, $endedAt): int
    {
        if (!$startedAt || !$endedAt) {
            return 0;
        }

        $start = is_string($startedAt) ? new \DateTime($startedAt) : $startedAt;
        $end = is_string($endedAt) ? new \DateTime($endedAt) : $endedAt;

        $diff = $end->getTimestamp() - $start->getTimestamp();
        return max(0, intval($diff / 60)); // Convert seconds to minutes
    }

    /**
     * Scope to filter billable entries
     */
    public function scopeBillable($query)
    {
        return $query->where('billable', true);
    }

    /**
     * Scope to filter non-billable entries
     */
    public function scopeNonBillable($query)
    {
        return $query->where('billable', false);
    }

    /**
     * Scope to filter by user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
