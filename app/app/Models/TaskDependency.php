<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskDependency extends Model
{
    protected $fillable = [
        'task_id',
        'depends_on_task_id',
        'dependency_type',
    ];

    protected $casts = [
        'task_id' => 'integer',
        'depends_on_task_id' => 'integer',
    ];

    /**
     * The task that has the dependency
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    /**
     * The task that this task depends on
     */
    public function dependsOnTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'depends_on_task_id');
    }

    /**
     * Check if adding this dependency would create a circular reference
     */
    public static function wouldCreateCircularDependency(int $taskId, int $dependsOnTaskId): bool
    {
        // A task can't depend on itself
        if ($taskId === $dependsOnTaskId) {
            return true;
        }

        // Check if the depends_on_task already depends on the current task (direct or indirect)
        return self::hasPath($dependsOnTaskId, $taskId);
    }

    /**
     * Check if there's a dependency path from task A to task B
     * Uses recursive traversal to detect circular dependencies
     */
    private static function hasPath(int $fromTaskId, int $toTaskId, array $visited = []): bool
    {
        // Prevent infinite loops
        if (in_array($fromTaskId, $visited)) {
            return false;
        }

        $visited[] = $fromTaskId;

        // Direct dependency check
        if ($fromTaskId === $toTaskId) {
            return true;
        }

        // Get all tasks that $fromTaskId depends on
        $dependencies = self::where('task_id', $fromTaskId)->pluck('depends_on_task_id');

        // Check each dependency recursively
        foreach ($dependencies as $dependencyId) {
            if (self::hasPath($dependencyId, $toTaskId, $visited)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all dependencies for a task (tasks that this task depends on)
     */
    public static function getDependenciesForTask(int $taskId)
    {
        return self::where('task_id', $taskId)
                   ->with('dependsOnTask')
                   ->get();
    }

    /**
     * Get all dependents for a task (tasks that depend on this task)
     */
    public static function getDependentsForTask(int $taskId)
    {
        return self::where('depends_on_task_id', $taskId)
                   ->with('task')
                   ->get();
    }
}
