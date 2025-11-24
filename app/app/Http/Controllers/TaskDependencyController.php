<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskDependency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskDependencyController extends Controller
{
    /**
     * Add a dependency to a task
     */
    public function store(Request $request, Task $task)
    {
        $validated = $request->validate([
            'depends_on_task_id' => 'required|exists:tasks,id',
            'dependency_type' => 'nullable|in:blocks,blocked_by,related',
        ]);

        $dependsOnTaskId = $validated['depends_on_task_id'];
        $dependencyType = $validated['dependency_type'] ?? 'blocks';

        // Verify the depends_on task belongs to the same organization
        $dependsOnTask = Task::find($dependsOnTaskId);
        if (!$dependsOnTask || $dependsOnTask->organization_id !== Auth::user()->organization_id) {
            return response()->json([
                'success' => false,
                'message' => __('Invalid task.'),
            ], 403);
        }

        // Check for circular dependencies
        if (TaskDependency::wouldCreateCircularDependency($task->id, $dependsOnTaskId)) {
            return response()->json([
                'success' => false,
                'message' => __('Cannot add dependency: This would create a circular dependency.'),
            ], 422);
        }

        // Add the dependency
        $success = $task->addDependency($dependsOnTaskId, $dependencyType);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => __('Dependency already exists or could not be added.'),
            ], 422);
        }

        // Return the updated dependency list
        $dependencies = $task->dependencies()
                            ->with('dependsOnTask.status', 'dependsOnTask.list')
                            ->get();

        return response()->json([
            'success' => true,
            'message' => __('Dependency added successfully.'),
            'dependencies' => $dependencies,
        ]);
    }

    /**
     * Remove a dependency from a task
     */
    public function destroy(Task $task, $dependencyId)
    {
        $dependency = TaskDependency::find($dependencyId);

        if (!$dependency || $dependency->task_id !== $task->id) {
            return response()->json([
                'success' => false,
                'message' => __('Dependency not found.'),
            ], 404);
        }

        $dependency->delete();

        return response()->json([
            'success' => true,
            'message' => __('Dependency removed successfully.'),
        ]);
    }

    /**
     * Get all dependencies for a task
     */
    public function index(Task $task)
    {
        $dependencies = $task->dependencies()
                            ->with('dependsOnTask.status', 'dependsOnTask.list', 'dependsOnTask.priority')
                            ->get();

        $dependents = $task->dependents()
                          ->with('task.status', 'task.list', 'task.priority')
                          ->get();

        return response()->json([
            'success' => true,
            'dependencies' => $dependencies,
            'dependents' => $dependents,
            'is_blocked' => $task->isBlocked(),
            'incomplete_dependencies_count' => $task->getIncompleteDependenciesCount(),
        ]);
    }

    /**
     * Search tasks for adding as dependencies
     */
    public function search(Request $request, Task $task)
    {
        $query = $request->input('query', '');

        $tasks = Task::where('organization_id', Auth::user()->organization_id)
                    ->where('id', '!=', $task->id) // Exclude the current task
                    ->where('name', 'like', "%{$query}%")
                    ->with('status', 'list')
                    ->limit(20)
                    ->get();

        return response()->json([
            'success' => true,
            'tasks' => $tasks,
        ]);
    }
}
