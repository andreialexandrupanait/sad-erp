<?php

namespace App\Listeners\Task;

use App\Events\Task\TaskAssigned;
use App\Events\Task\TaskCreated;
use App\Events\Task\TaskDeleted;
use App\Events\Task\TaskStatusChanged;
use App\Events\Task\TaskUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogTaskActivity implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle TaskCreated event
     */
    public function handleTaskCreated(TaskCreated $event): void
    {
        Log::info('Task created', [
            'task_id' => $event->task->id,
            'task_name' => $event->task->name,
            'list_id' => $event->task->list_id,
            'user_id' => $event->task->user_id,
            'organization_id' => $event->task->organization_id,
        ]);
    }

    /**
     * Handle TaskUpdated event
     */
    public function handleTaskUpdated(TaskUpdated $event): void
    {
        Log::info('Task updated', [
            'task_id' => $event->task->id,
            'task_name' => $event->task->name,
            'changes' => $event->changes,
            'organization_id' => $event->task->organization_id,
        ]);
    }

    /**
     * Handle TaskDeleted event
     */
    public function handleTaskDeleted(TaskDeleted $event): void
    {
        Log::info('Task deleted', [
            'task_id' => $event->taskId,
            'list_id' => $event->listId,
            'organization_id' => $event->organizationId,
        ]);
    }

    /**
     * Handle TaskStatusChanged event
     */
    public function handleTaskStatusChanged(TaskStatusChanged $event): void
    {
        Log::info('Task status changed', [
            'task_id' => $event->task->id,
            'task_name' => $event->task->name,
            'old_status_id' => $event->oldStatusId,
            'new_status_id' => $event->newStatusId,
            'organization_id' => $event->task->organization_id,
        ]);
    }

    /**
     * Handle TaskAssigned event
     */
    public function handleTaskAssigned(TaskAssigned $event): void
    {
        Log::info('Task assigned', [
            'task_id' => $event->task->id,
            'task_name' => $event->task->name,
            'old_assignee_id' => $event->oldAssigneeId,
            'new_assignee_id' => $event->newAssigneeId,
            'organization_id' => $event->task->organization_id,
        ]);
    }
}
