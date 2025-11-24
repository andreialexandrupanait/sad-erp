<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskDisplayCache;
use App\Models\TaskList;
use App\Models\TaskService as TaskServiceModel;
use App\Models\User;
use App\Models\SettingOption;
use App\Services\Task\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskApiController extends Controller
{
    protected TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Get tasks for a specific status (lazy loading endpoint with full ClickUp design)
     */
    public function getTasksByStatus(Request $request, int $statusId)
    {
        $organizationId = $request->input('organization_id') ?? Auth::user()->organization_id;
        $page = $request->input('page', 1);
        $perPage = 50;

        // Get filters from request
        $filters = [];
        if ($request->has('search')) {
            $filters['search'] = $request->input('search');
        }
        if ($request->has('list_id')) {
            $filters['list_id'] = $request->input('list_id');
        }
        if ($request->has('assignee')) {
            $filters['assignee'] = $request->input('assignee');
        }

        // Step 1: Use cache for fast filtering/sorting
        $query = TaskDisplayCache::where('organization_id', $organizationId)
            ->where('status_id', $statusId);

        // Apply filters
        if (!empty($filters['search'])) {
            $query->where('task_name', 'like', "%{$filters['search']}%");
        }
        if (!empty($filters['list_id'])) {
            $query->where('list_id', $filters['list_id']);
        }

        $cachedTasks = $query->orderBy('position')
            ->orderBy('updated_at', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $taskIds = $cachedTasks->pluck('task_id')->toArray();
        $total = $query->count();

        // Step 2: Load full Task models with ALL relationships for ClickUp design
        $tasks = Task::with([
            'status',
            'list.client',
            'assignedUser',
            'priority',
            'assignees',
            'service',
            'checklists.items',
            'dependencies.dependsOnTask.status',
            'tags'
        ])
            ->whereIn('id', $taskIds)
            ->get()
            ->keyBy('id');

        // Maintain cache order
        $orderedTasks = $cachedTasks->map(function($cached) use ($tasks) {
            return $tasks->get($cached->task_id);
        })->filter();

        // Get status for rendering status icons
        $status = SettingOption::find($statusId);

        // Get data needed for dropdowns (same as original ClickUp view)
        $lists = TaskList::with('client')->ordered()->get();
        $services = TaskServiceModel::active()->ordered()->get();
        $users = User::where('organization_id', $organizationId)->get();
        $taskPriorities = SettingOption::taskPriorities()->get();

        // Render task rows with full ClickUp design
        $html = view('components.tasks.v2.task-rows', [
            'tasks' => $orderedTasks,
            'status' => $status,
            'lists' => $lists,
            'services' => $services,
            'users' => $users,
            'taskPriorities' => $taskPriorities,
        ])->render();

        return response()->json([
            'html' => $html,
            'has_more' => ($page * $perPage) < $total,
            'page' => $page,
            'total' => $total,
        ]);
    }

    /**
     * Get status counts (for refreshing counts without page reload)
     */
    public function getStatusCounts(Request $request)
    {
        $organizationId = $request->input('organization_id') ?? Auth::user()->organization_id;

        // Get filters from request
        $filters = [];
        if ($request->has('search')) {
            $filters['search'] = $request->input('search');
        }
        if ($request->has('list_id')) {
            $filters['list_id'] = $request->input('list_id');
        }
        if ($request->has('assignee')) {
            $filters['assignee'] = $request->input('assignee');
        }

        $statuses = $this->taskService->getStatusesWithCounts($organizationId, $filters);

        return response()->json([
            'statuses' => $statuses->map(function ($item) {
                return [
                    'status_id' => $item['status']->id,
                    'label' => $item['status']->label,
                    'color' => $item['status']->color,
                    'count' => $item['count'],
                ];
            }),
        ]);
    }
}
