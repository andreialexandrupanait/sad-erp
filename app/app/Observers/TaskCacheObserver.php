<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\TaskDisplayCache;

class TaskCacheObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        $this->updateCache($task);
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        $this->updateCache($task);
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        TaskDisplayCache::where('task_id', $task->id)->delete();
    }

    /**
     * Update the display cache for this task
     */
    protected function updateCache(Task $task): void
    {
        // Load relationships if not already loaded
        if (!$task->relationLoaded('list')) {
            $task->load(['list.client', 'service', 'status', 'assignedUser', 'priority']);
        }

        TaskDisplayCache::rebuildForTask($task);
    }
}
