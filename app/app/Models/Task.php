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
        'time_tracked',
        'amount',
        'total_amount',
        'position',
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
        'amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'position' => 'integer',
        'due_date' => 'date',
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
}
