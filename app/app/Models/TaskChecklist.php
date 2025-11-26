<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskChecklist extends Model
{
    protected $fillable = [
        'task_id',
        'name',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    /**
     * Get the task that owns the checklist
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the checklist items
     */
    public function items(): HasMany
    {
        return $this->hasMany(TaskChecklistItem::class, 'checklist_id')->orderBy('position');
    }

    /**
     * Scope to order by position
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    /**
     * Get checklist progress as "3/5" format
     */
    public function getProgressAttribute(): string
    {
        $total = $this->items()->count();
        $completed = $this->items()->where('is_completed', true)->count();
        return "{$completed}/{$total}";
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentageAttribute(): int
    {
        $total = $this->items()->count();
        if ($total === 0) {
            return 0;
        }
        $completed = $this->items()->where('is_completed', true)->count();
        return (int) round(($completed / $total) * 100);
    }
}
