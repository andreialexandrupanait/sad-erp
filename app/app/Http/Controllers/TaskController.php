<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskList;
use App\Models\TaskService;
use App\Models\User;
use App\Models\SettingOption;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of all tasks (Everything view)
     */
    public function index(Request $request)
    {
        $query = Task::with(['list.client', 'service', 'status', 'assignedUser']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by list
        if ($request->filled('list_id')) {
            $query->where('list_id', $request->list_id);
        }

        // Filter by status
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        // Filter by assigned user
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Filter by service
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        // Get view mode (default: table)
        $viewMode = $request->get('view', 'table');

        // Sort
        $sortBy = $request->get('sort', 'position');
        $sortDir = $request->get('dir', 'asc');

        if ($sortBy === 'position') {
            $query->ordered();
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        // For kanban view, group by status
        if ($viewMode === 'kanban') {
            $tasks = $query->get()->groupBy('status_id');
        } else {
            $tasks = $query->paginate(50)->withQueryString();
        }

        // Get filter options
        $lists = TaskList::with('client')->ordered()->get();
        $services = TaskService::active()->ordered()->get();
        $users = User::where('organization_id', auth()->user()->organization_id)
                     ->orderBy('name')
                     ->get();
        $taskStatuses = SettingOption::taskStatuses()->ordered()->get();

        return view('tasks.index', compact('tasks', 'viewMode', 'lists', 'services', 'users', 'taskStatuses'));
    }

    /**
     * Show the form for creating a new task
     */
    public function create(Request $request)
    {
        $lists = TaskList::with('client')->ordered()->get();
        $services = TaskService::active()->ordered()->get();
        $users = User::where('organization_id', auth()->user()->organization_id)
                     ->orderBy('name')
                     ->get();
        $taskStatuses = SettingOption::taskStatuses()->ordered()->get();

        // Pre-select list if provided
        $selectedListId = $request->get('list_id');

        return view('tasks.create', compact('lists', 'services', 'users', 'selectedListId', 'taskStatuses'));
    }

    /**
     * Store a newly created task in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'list_id' => 'required|exists:task_lists,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'service_id' => 'nullable|exists:task_services,id',
            'status_id' => 'required|exists:settings_options,id',
            'due_date' => 'nullable|date',
            'time_tracked' => 'nullable|integer|min:0',
            'amount' => 'nullable|numeric|min:0',
            'position' => 'nullable|integer',
        ]);

        // Get the next position if not provided
        if (!isset($validated['position'])) {
            $maxPosition = Task::where('list_id', $validated['list_id'])->max('position');
            $validated['position'] = ($maxPosition ?? -1) + 1;
        }

        // If service is selected and no amount is provided, get the rate for the client
        if (!empty($validated['service_id']) && empty($validated['amount'])) {
            $list = TaskList::find($validated['list_id']);
            if ($list && $list->client_id) {
                $service = TaskService::find($validated['service_id']);
                if ($service) {
                    $validated['amount'] = $service->getRateForClient($list->client_id);
                }
            }
        }

        $task = Task::create($validated);

        return redirect()
            ->route('tasks.index')
            ->with('success', __('Task created successfully.'));
    }

    /**
     * Display the specified task
     */
    public function show(Task $task)
    {
        $task->load(['list.client', 'service', 'status', 'assignedUser', 'creator', 'customFields']);

        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified task
     */
    public function edit(Task $task)
    {
        $lists = TaskList::with('client')->ordered()->get();
        $services = TaskService::active()->ordered()->get();
        $users = User::where('organization_id', auth()->user()->organization_id)
                     ->orderBy('name')
                     ->get();
        $taskStatuses = SettingOption::taskStatuses()->ordered()->get();

        return view('tasks.edit', compact('task', 'lists', 'services', 'users', 'taskStatuses'));
    }

    /**
     * Update the specified task in storage
     */
    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'list_id' => 'required|exists:task_lists,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'service_id' => 'nullable|exists:task_services,id',
            'status_id' => 'required|exists:settings_options,id',
            'due_date' => 'nullable|date',
            'time_tracked' => 'nullable|integer|min:0',
            'amount' => 'nullable|numeric|min:0',
            'position' => 'nullable|integer',
        ]);

        $task->update($validated);

        return redirect()
            ->route('tasks.index')
            ->with('success', __('Task updated successfully.'));
    }

    /**
     * Remove the specified task from storage
     */
    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()
            ->route('tasks.index')
            ->with('success', __('Task deleted successfully.'));
    }

    /**
     * Update task status (AJAX)
     */
    public function updateStatus(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status_id' => 'required|exists:settings_options,id',
        ]);

        $task->update($validated);

        return response()->json([
            'success' => true,
            'message' => __('Task status updated successfully.'),
        ]);
    }

    /**
     * Update task time tracked (AJAX)
     */
    public function updateTime(Request $request, Task $task)
    {
        $validated = $request->validate([
            'time_tracked' => 'required|integer|min:0',
        ]);

        $task->update($validated);

        return response()->json([
            'success' => true,
            'message' => __('Time tracked updated successfully.'),
            'total_amount' => $task->fresh()->total_amount,
        ]);
    }

    /**
     * Update task position (AJAX for drag-drop)
     */
    public function updatePosition(Request $request, Task $task)
    {
        $validated = $request->validate([
            'position' => 'required|integer|min:0',
            'list_id' => 'nullable|exists:task_lists,id',
        ]);

        $task->update($validated);

        return response()->json([
            'success' => true,
            'message' => __('Task position updated successfully.'),
        ]);
    }
}
