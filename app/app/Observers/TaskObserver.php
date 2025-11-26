<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\TaskActivity;
use Illuminate\Support\Facades\Auth;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        if (Auth::check()) {
            TaskActivity::logCreated($task, Auth::id());
        }
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        if (!Auth::check()) {
            return;
        }

        $userId = Auth::id();
        $changes = $task->getChanges();

        // Track which fields changed
        $fieldsToTrack = [
            'name',
            'description',
            'status_id',
            'priority_id',
            'assigned_to',
            'due_date',
            'start_date',
            'time_tracked',
            'time_estimate',
            'list_id',
            'service_id',
        ];

        foreach ($fieldsToTrack as $field) {
            if (array_key_exists($field, $changes)) {
                $oldValue = $task->getOriginal($field);
                $newValue = $changes[$field];

                // Get human-readable values for relationships
                $metadata = [];

                if ($field === 'status_id') {
                    $oldStatus = \App\Models\SettingOption::find($oldValue);
                    $newStatus = \App\Models\SettingOption::find($newValue);
                    $oldValue = $oldStatus?->label ?? $oldValue;
                    $newValue = $newStatus?->label ?? $newValue;
                    $metadata = [
                        'old_color' => $oldStatus?->color,
                        'new_color' => $newStatus?->color,
                    ];
                } elseif ($field === 'priority_id') {
                    $oldPriority = \App\Models\SettingOption::find($oldValue);
                    $newPriority = \App\Models\SettingOption::find($newValue);
                    $oldValue = $oldPriority?->label ?? $oldValue;
                    $newValue = $newPriority?->label ?? $newValue;
                    $metadata = [
                        'old_color' => $oldPriority?->color,
                        'new_color' => $newPriority?->color,
                    ];
                } elseif ($field === 'assigned_to') {
                    $oldUser = \App\Models\User::find($oldValue);
                    $newUser = \App\Models\User::find($newValue);
                    $oldValue = $oldUser?->name ?? ($oldValue ? 'User #' . $oldValue : null);
                    $newValue = $newUser?->name ?? ($newValue ? 'User #' . $newValue : null);
                } elseif ($field === 'list_id') {
                    $oldList = \App\Models\TaskList::find($oldValue);
                    $newList = \App\Models\TaskList::find($newValue);
                    $oldValue = $oldList?->name ?? $oldValue;
                    $newValue = $newList?->name ?? $newValue;
                } elseif ($field === 'service_id') {
                    $oldService = \App\Models\TaskService::find($oldValue);
                    $newService = \App\Models\TaskService::find($newValue);
                    $oldValue = $oldService?->name ?? ($oldValue ? 'Service #' . $oldValue : 'None');
                    $newValue = $newService?->name ?? ($newValue ? 'Service #' . $newValue : 'None');
                }

                TaskActivity::logFieldChange($task, $userId, $field, $oldValue, $newValue, $metadata);
            }
        }
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        if (Auth::check()) {
            TaskActivity::create([
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'action' => 'deleted',
                'old_value' => $task->name,
            ]);
        }
    }
}
