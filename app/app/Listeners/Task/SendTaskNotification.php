<?php

namespace App\Listeners\Task;

use App\Events\Task\TaskAssigned;
use App\Events\Task\TaskCreated;
use App\Events\Task\TaskStatusChanged;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendTaskNotification implements ShouldQueue
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
        // If task is assigned to someone, notify them
        if ($event->task->assigned_to) {
            $assignee = User::find($event->task->assigned_to);
            if ($assignee) {
                // TODO: Implement notification
                // $assignee->notify(new TaskCreatedNotification($event->task));
            }
        }
    }

    /**
     * Handle TaskAssigned event
     */
    public function handleTaskAssigned(TaskAssigned $event): void
    {
        // Notify the newly assigned user
        if ($event->newAssigneeId) {
            $assignee = User::find($event->newAssigneeId);
            if ($assignee) {
                // TODO: Implement notification
                // $assignee->notify(new TaskAssignedNotification($event->task));
            }
        }

        // Optionally notify the previous assignee that they were unassigned
        if ($event->oldAssigneeId) {
            $oldAssignee = User::find($event->oldAssigneeId);
            if ($oldAssignee) {
                // TODO: Implement notification
                // $oldAssignee->notify(new TaskUnassignedNotification($event->task));
            }
        }
    }

    /**
     * Handle TaskStatusChanged event
     */
    public function handleTaskStatusChanged(TaskStatusChanged $event): void
    {
        // Notify assigned user and task creator about status change
        $usersToNotify = collect([
            $event->task->assigned_to,
            $event->task->user_id,
        ])->filter()->unique();

        foreach ($usersToNotify as $userId) {
            $user = User::find($userId);
            if ($user) {
                // TODO: Implement notification
                // $user->notify(new TaskStatusChangedNotification($event->task, $event->oldStatusId, $event->newStatusId));
            }
        }
    }
}
