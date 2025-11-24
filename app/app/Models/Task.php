<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Task extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'list_id',
        'organization_id',
        'user_id',
        'assigned_to',
        'service_id',
        'status_id',
        'priority_id',
        'parent_task_id',
        'name',
        'description',
        'due_date',
        'start_date',
        'time_tracked',
        'time_estimate',
        'amount',
        'total_amount',
        'position',
        'date_closed',
    ];

    protected $casts = [
        'list_id' => 'integer',
        'organization_id' => 'integer',
        'user_id' => 'integer',
        'assigned_to' => 'integer',
        'service_id' => 'integer',
        'status_id' => 'integer',
        'priority_id' => 'integer',
        'parent_task_id' => 'integer',
        'time_tracked' => 'integer',
        'time_estimate' => 'integer',
        'amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'position' => 'integer',
        'due_date' => 'date',
        'start_date' => 'date',
        'date_closed' => 'datetime',
    ];

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($task) {
            if (Auth::check()) {
                $task->organization_id = $task->organization_id ?? Auth::user()->organization_id;
                $task->user_id = $task->user_id ?? Auth::id();
            }
        });

        // Auto-calculate total_amount before saving
        static::saving(function ($task) {
            // Calculate: total_amount = (time_tracked / 60) * amount
            if ($task->time_tracked && $task->amount) {
                $task->total_amount = ($task->time_tracked / 60) * $task->amount;
            } else {
                $task->total_amount = 0;
            }

            // Auto-set date_closed when status changes to "Done"
            if ($task->isDirty('status_id') && $task->status) {
                $statusName = $task->status->name ?? '';
                if (in_array(strtolower($statusName), ['done', 'completed', 'closed'])) {
                    $task->date_closed = now();
                } elseif ($task->date_closed) {
                    // Clear date_closed if status changed from Done to something else
                    $task->date_closed = null;
                }
            }
        });

        // Only scope by organization - users within same org can collaborate
        static::addGlobalScope('organization_scope', function (Builder $query) {
            if (Auth::check()) {
                $query->where('organization_id', Auth::user()->organization_id);
            }
        });
    }

    // Relationships
    public function list()
    {
        return $this->belongsTo(TaskList::class, 'list_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Multiple assignees (new ClickUp-style feature)
     */
    public function assignees()
    {
        return $this->belongsToMany(User::class, 'task_assignees')
                    ->withTimestamps()
                    ->withPivot('assigned_by', 'assigned_at')
                    ->orderBy('task_assignees.assigned_at');
    }

    /**
     * Task watchers/followers
     */
    public function watchers()
    {
        return $this->belongsToMany(User::class, 'task_watchers')
                    ->withTimestamps()
                    ->withPivot('watched_at')
                    ->orderBy('task_watchers.watched_at');
    }

    public function service()
    {
        return $this->belongsTo(TaskService::class, 'service_id');
    }

    public function status()
    {
        return $this->belongsTo(SettingOption::class, 'status_id');
    }

    public function customFieldValues()
    {
        return $this->hasMany(TaskCustomFieldValue::class);
    }

    public function priority()
    {
        return $this->belongsTo(SettingOption::class, 'priority_id');
    }

    // Subtask relationships
    public function parentTask()
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function subtasks()
    {
        return $this->hasMany(Task::class, 'parent_task_id')->ordered();
    }

    // Comments and attachments
    public function comments()
    {
        return $this->hasMany(TaskComment::class)->with('user')->latest();
    }

    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class)->with('user')->latest();
    }

    /**
     * Task checklists
     */
    public function checklists()
    {
        return $this->hasMany(TaskChecklist::class)->ordered()->with('items');
    }

    /**
     * Task tags
     */
    public function tags()
    {
        return $this->belongsToMany(TaskTag::class, 'task_tag_assignments', 'task_id', 'tag_id')
                    ->withTimestamps();
    }

    /**
     * Tasks that this task depends on (blocking tasks)
     */
    public function dependencies()
    {
        return $this->hasMany(TaskDependency::class, 'task_id')->with('dependsOnTask');
    }

    /**
     * Tasks that depend on this task (blocked tasks)
     */
    public function dependents()
    {
        return $this->hasMany(TaskDependency::class, 'depends_on_task_id')->with('task');
    }

    /**
     * Activity log for this task
     */
    public function activities()
    {
        return $this->hasMany(TaskActivity::class)->with('user')->latest('created_at');
    }

    /**
     * Time entries for this task
     */
    public function timeEntries()
    {
        return $this->hasMany(TaskTimeEntry::class)->with('user')->latest();
    }

    /**
     * Add a tag to this task
     */
    public function addTag(TaskTag $tag): void
    {
        if (!$this->tags()->where('tag_id', $tag->id)->exists()) {
            $this->tags()->attach($tag->id);
        }
    }

    /**
     * Remove a tag from this task
     */
    public function removeTag(TaskTag $tag): void
    {
        $this->tags()->detach($tag->id);
    }

    /**
     * Add a dependency (this task depends on another task)
     */
    public function addDependency(int $dependsOnTaskId, string $type = 'blocks'): bool
    {
        // Check for circular dependencies
        if (TaskDependency::wouldCreateCircularDependency($this->id, $dependsOnTaskId)) {
            return false;
        }

        // Check if dependency already exists
        if ($this->dependencies()->where('depends_on_task_id', $dependsOnTaskId)->exists()) {
            return false;
        }

        $this->dependencies()->create([
            'depends_on_task_id' => $dependsOnTaskId,
            'dependency_type' => $type,
        ]);

        return true;
    }

    /**
     * Remove a dependency
     */
    public function removeDependency(int $dependsOnTaskId): void
    {
        $this->dependencies()->where('depends_on_task_id', $dependsOnTaskId)->delete();
    }

    /**
     * Check if this task is blocked (has incomplete dependencies)
     */
    public function isBlocked(): bool
    {
        return $this->dependencies()
                    ->whereHas('dependsOnTask', function ($query) {
                        $query->whereHas('status', function ($q) {
                            $q->whereNotIn('name', ['Done', 'Completed', 'Closed']);
                        });
                    })
                    ->exists();
    }

    /**
     * Get count of incomplete dependencies
     */
    public function getIncompleteDependenciesCount(): int
    {
        return $this->dependencies()
                    ->whereHas('dependsOnTask', function ($query) {
                        $query->whereHas('status', function ($q) {
                            $q->whereNotIn('name', ['Done', 'Completed', 'Closed']);
                        });
                    })
                    ->count();
    }

    /**
     * Get checklist progress summary as "3/10"
     */
    public function getChecklistProgressAttribute(): string
    {
        $totalItems = 0;
        $completedItems = 0;

        foreach ($this->checklists as $checklist) {
            $totalItems += $checklist->items->count();
            $completedItems += $checklist->items->where('is_completed', true)->count();
        }

        if ($totalItems === 0) {
            return '';
        }

        return "{$completedItems}/{$totalItems}";
    }

    // Helper methods for assignees and watchers
    /**
     * Assign a user to this task
     */
    public function assignUser($userId)
    {
        if (!$this->assignees->contains($userId)) {
            $this->assignees()->attach($userId, [
                'assigned_by' => Auth::id(),
                'assigned_at' => now(),
            ]);

            // Auto-add as watcher
            $this->addWatcher($userId);
        }
    }

    /**
     * Remove an assignee from this task
     */
    public function removeAssignee($userId)
    {
        $this->assignees()->detach($userId);
    }

    /**
     * Sync all assignees
     */
    public function syncAssignees(array $userIds)
    {
        $this->assignees()->sync($userIds);

        // Auto-add all assignees as watchers
        foreach ($userIds as $userId) {
            $this->addWatcher($userId);
        }
    }

    /**
     * Add a watcher to this task
     */
    public function addWatcher($userId)
    {
        if (!$this->watchers->contains($userId)) {
            $this->watchers()->attach($userId, [
                'watched_at' => now(),
            ]);
        }
    }

    /**
     * Remove a watcher from this task
     */
    public function removeWatcher($userId)
    {
        $this->watchers()->detach($userId);
    }

    /**
     * Check if user is watching this task
     */
    public function isWatchedBy($userId)
    {
        return $this->watchers->contains($userId);
    }

    /**
     * Check if user is assigned to this task (including multi-assignees)
     */
    public function isAssignedTo($userId)
    {
        return $this->assigned_to === $userId || $this->assignees->contains($userId);
    }

    // Scopes
    public function scopeOrdered($query)
    {
        return $query->orderBy('position')->orderBy('due_date');
    }

    /**
     * Tasks created by the current user
     */
    public function scopeCreatedByMe($query)
    {
        return $query->where('user_id', Auth::id());
    }

    /**
     * Tasks assigned to the current user
     */
    public function scopeAssignedToMe($query)
    {
        return $query->where('assigned_to', Auth::id());
    }

    /**
     * Tasks accessible by the current user (created by OR assigned to)
     */
    public function scopeAccessibleByMe($query)
    {
        return $query->where(function($q) {
            $q->where('user_id', Auth::id())
              ->orWhere('assigned_to', Auth::id());
        });
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeWithStatus($query, $statusId)
    {
        return $query->where('status_id', $statusId);
    }

    public function scopeDueSoon($query, $days = 7)
    {
        return $query->where('due_date', '<=', now()->addDays($days))
                     ->where('due_date', '>=', now());
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now());
    }

    // Accessors for Planning Fields

    /**
     * Format time estimate as "2h 30m"
     */
    public function getTimeEstimateFormattedAttribute(): string
    {
        if (!$this->time_estimate) {
            return '';
        }

        $hours = floor($this->time_estimate / 60);
        $minutes = $this->time_estimate % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}m";
        }
    }

    /**
     * Calculate estimate variance (tracked - estimate)
     * Positive = over estimate, Negative = under estimate
     */
    public function getEstimateVarianceAttribute(): ?int
    {
        if (!$this->time_estimate) {
            return null;
        }

        return $this->time_tracked - $this->time_estimate;
    }

    /**
     * Get estimate variance percentage
     * Returns percentage over/under estimate
     */
    public function getEstimateVariancePercentAttribute(): ?float
    {
        if (!$this->time_estimate || $this->time_estimate === 0) {
            return null;
        }

        return ($this->estimate_variance / $this->time_estimate) * 100;
    }
}
