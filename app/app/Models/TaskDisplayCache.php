<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskDisplayCache extends Model
{
    protected $table = 'task_display_cache';

    public $timestamps = false; // Only has updated_at

    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'task_id',
        'organization_id',
        'status_id',
        'list_id',
        'task_name',
        'list_name',
        'client_name',
        'service_name',
        'assignee_name',
        'priority_label',
        'status_label',
        'status_color',
        'time_tracked',
        'total_amount',
        'due_date',
        'position',
    ];

    protected $casts = [
        'time_tracked' => 'integer',
        'total_amount' => 'decimal:2',
        'due_date' => 'date',
        'position' => 'integer',
        'updated_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Rebuild cache for a specific task
     */
    public static function rebuildForTask(Task $task): void
    {
        self::updateOrCreate(
            ['task_id' => $task->id],
            [
                'organization_id' => $task->organization_id,
                'status_id' => $task->status_id,
                'list_id' => $task->list_id,
                'task_name' => $task->name,
                'list_name' => $task->list?->name,
                'client_name' => $task->list?->client?->name,
                'service_name' => $task->service?->name,
                'assignee_name' => $task->assignedUser?->name,
                'priority_label' => $task->priority?->label,
                'status_label' => $task->status?->label,
                'status_color' => $task->status?->color_class,
                'time_tracked' => $task->time_tracked,
                'total_amount' => $task->total_amount,
                'due_date' => $task->due_date,
                'position' => $task->position,
            ]
        );
    }

    /**
     * Rebuild cache for all tasks (run in background)
     */
    public static function rebuildAll(): void
    {
        // Truncate and rebuild
        self::truncate();

        Task::with(['list.client', 'service', 'status', 'assignedUser', 'priority'])
            ->chunk(500, function ($tasks) {
                foreach ($tasks as $task) {
                    self::rebuildForTask($task);
                }
            });
    }
}
