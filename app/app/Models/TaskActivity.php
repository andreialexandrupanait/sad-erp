<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskActivity extends Model
{
    public $timestamps = false; // We only use created_at

    protected $fillable = [
        'task_id',
        'user_id',
        'action',
        'field_changed',
        'old_value',
        'new_value',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'task_id' => 'integer',
        'user_id' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * The task this activity belongs to
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * The user who performed this action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log a task creation
     */
    public static function logCreated(Task $task, int $userId): void
    {
        self::create([
            'task_id' => $task->id,
            'user_id' => $userId,
            'action' => 'created',
            'new_value' => $task->name,
        ]);
    }

    /**
     * Log a field change
     */
    public static function logFieldChange(Task $task, int $userId, string $field, $oldValue, $newValue, array $metadata = []): void
    {
        // Don't log if values are the same
        if ($oldValue === $newValue) {
            return;
        }

        // Determine action based on field
        $action = match($field) {
            'status_id' => 'status_changed',
            'priority_id' => 'priority_changed',
            'assigned_to' => 'assigned',
            'due_date', 'start_date' => 'date_changed',
            default => 'updated'
        };

        self::create([
            'task_id' => $task->id,
            'user_id' => $userId,
            'action' => $action,
            'field_changed' => $field,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Log assignee addition
     */
    public static function logAssigneeAdded(Task $task, int $userId, User $assignee): void
    {
        self::create([
            'task_id' => $task->id,
            'user_id' => $userId,
            'action' => 'assignee_added',
            'new_value' => $assignee->name,
            'metadata' => ['assignee_id' => $assignee->id],
        ]);
    }

    /**
     * Log assignee removal
     */
    public static function logAssigneeRemoved(Task $task, int $userId, User $assignee): void
    {
        self::create([
            'task_id' => $task->id,
            'user_id' => $userId,
            'action' => 'assignee_removed',
            'old_value' => $assignee->name,
            'metadata' => ['assignee_id' => $assignee->id],
        ]);
    }

    /**
     * Log watcher addition
     */
    public static function logWatcherAdded(Task $task, int $userId, User $watcher): void
    {
        self::create([
            'task_id' => $task->id,
            'user_id' => $userId,
            'action' => 'watcher_added',
            'new_value' => $watcher->name,
            'metadata' => ['watcher_id' => $watcher->id],
        ]);
    }

    /**
     * Log watcher removal
     */
    public static function logWatcherRemoved(Task $task, int $userId, User $watcher): void
    {
        self::create([
            'task_id' => $task->id,
            'user_id' => $userId,
            'action' => 'watcher_removed',
            'old_value' => $watcher->name,
            'metadata' => ['watcher_id' => $watcher->id],
        ]);
    }

    /**
     * Log comment addition
     */
    public static function logCommentAdded(Task $task, int $userId): void
    {
        self::create([
            'task_id' => $task->id,
            'user_id' => $userId,
            'action' => 'comment_added',
        ]);
    }

    /**
     * Log attachment addition
     */
    public static function logAttachmentAdded(Task $task, int $userId, string $filename): void
    {
        self::create([
            'task_id' => $task->id,
            'user_id' => $userId,
            'action' => 'attachment_added',
            'new_value' => $filename,
        ]);
    }

    /**
     * Log tag addition
     */
    public static function logTagAdded(Task $task, int $userId, TaskTag $tag): void
    {
        self::create([
            'task_id' => $task->id,
            'user_id' => $userId,
            'action' => 'tag_added',
            'new_value' => $tag->name,
            'metadata' => ['tag_id' => $tag->id, 'tag_color' => $tag->color],
        ]);
    }

    /**
     * Log tag removal
     */
    public static function logTagRemoved(Task $task, int $userId, TaskTag $tag): void
    {
        self::create([
            'task_id' => $task->id,
            'user_id' => $userId,
            'action' => 'tag_removed',
            'old_value' => $tag->name,
            'metadata' => ['tag_id' => $tag->id, 'tag_color' => $tag->color],
        ]);
    }

    /**
     * Log dependency addition
     */
    public static function logDependencyAdded(Task $task, int $userId, Task $dependsOnTask): void
    {
        self::create([
            'task_id' => $task->id,
            'user_id' => $userId,
            'action' => 'dependency_added',
            'new_value' => $dependsOnTask->name,
            'metadata' => ['depends_on_task_id' => $dependsOnTask->id],
        ]);
    }

    /**
     * Log dependency removal
     */
    public static function logDependencyRemoved(Task $task, int $userId, Task $dependsOnTask): void
    {
        self::create([
            'task_id' => $task->id,
            'user_id' => $userId,
            'action' => 'dependency_removed',
            'old_value' => $dependsOnTask->name,
            'metadata' => ['depends_on_task_id' => $dependsOnTask->id],
        ]);
    }

    /**
     * Get a human-readable description of the activity
     */
    public function getDescriptionAttribute(): string
    {
        $userName = $this->user->name ?? 'Unknown';

        return match($this->action) {
            'created' => "{$userName} created this task",
            'status_changed' => "{$userName} changed status from {$this->old_value} to {$this->new_value}",
            'priority_changed' => "{$userName} changed priority from {$this->old_value} to {$this->new_value}",
            'assigned' => $this->new_value
                ? "{$userName} assigned to {$this->new_value}"
                : "{$userName} removed assignee {$this->old_value}",
            'assignee_added' => "{$userName} added {$this->new_value} as assignee",
            'assignee_removed' => "{$userName} removed {$this->old_value} as assignee",
            'watcher_added' => "{$userName} added {$this->new_value} as watcher",
            'watcher_removed' => "{$userName} removed {$this->old_value} as watcher",
            'date_changed' => "{$userName} changed {$this->field_changed} from {$this->old_value} to {$this->new_value}",
            'comment_added' => "{$userName} added a comment",
            'attachment_added' => "{$userName} added attachment {$this->new_value}",
            'tag_added' => "{$userName} added tag {$this->new_value}",
            'tag_removed' => "{$userName} removed tag {$this->old_value}",
            'dependency_added' => "{$userName} added dependency on {$this->new_value}",
            'dependency_removed' => "{$userName} removed dependency on {$this->old_value}",
            'updated' => "{$userName} updated {$this->field_changed}",
            default => "{$userName} performed {$this->action}",
        };
    }
}
