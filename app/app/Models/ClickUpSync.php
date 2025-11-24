<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClickUpSync extends Model
{
    protected $table = 'clickup_syncs';

    protected $fillable = [
        'organization_id',
        'user_id',
        'sync_type',
        'clickup_workspace_id',
        'clickup_list_id',
        'status',
        'options',
        'stats',
        'errors',
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
     * Get the organization that owns this sync
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the user who initiated this sync
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get only running syncs
     */
    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    /**
     * Scope to get only completed syncs
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get only failed syncs
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
