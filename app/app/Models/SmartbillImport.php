<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmartbillImport extends Model
{
    protected $fillable = [
        'organization_id',
        'user_id',
        'file_name',
        'file_path',
        'status',
        'options',
        'stats',
        'errors',
        'total_rows',
        'processed_rows',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'options' => 'array',
        'stats' => 'array',
        'errors' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the organization that owns this import
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the user who initiated this import
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get only running imports
     */
    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    /**
     * Scope to get only completed imports
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get only failed imports
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentageAttribute(): int
    {
        if ($this->total_rows === 0) {
            return 0;
        }
        return (int) round(($this->processed_rows / $this->total_rows) * 100);
    }

    /**
     * Update progress
     */
    public function incrementProgress(int $count = 1): void
    {
        $this->increment('processed_rows', $count);
    }

    /**
     * Add error message
     */
    public function addError(string $error): void
    {
        $errors = $this->errors ?? [];
        $errors[] = $error;
        $this->update(['errors' => $errors]);
    }

    /**
     * Update stats
     */
    public function updateStats(array $stats): void
    {
        $this->update(['stats' => array_merge($this->stats ?? [], $stats)]);
    }
}
