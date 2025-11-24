<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskChecklistItem extends Model
{
    protected $fillable = [
        'checklist_id',
        'text',
        'is_completed',
        'assigned_to',
        'position',
        'completed_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'position' => 'integer',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the checklist that owns the item
     */
    public function checklist(): BelongsTo
    {
        return $this->belongsTo(TaskChecklist::class, 'checklist_id');
    }

    /**
     * Get the assigned user
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Toggle the completion status
     */
    public function toggle(): void
    {
        $this->is_completed = !$this->is_completed;
        $this->completed_at = $this->is_completed ? now() : null;
        $this->save();
    }

    /**
     * Mark as complete
     */
    public function complete(): void
    {
        $this->is_completed = true;
        $this->completed_at = now();
        $this->save();
    }

    /**
     * Mark as incomplete
     */
    public function uncomplete(): void
    {
        $this->is_completed = false;
        $this->completed_at = null;
        $this->save();
    }
}
