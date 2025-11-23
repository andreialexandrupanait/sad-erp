<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskList;
use App\Models\TaskService as TaskServiceModel;
use App\Models\User;
use App\Models\SettingOption;
use App\Models\TaskCustomField;
use App\Services\Task\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    protected TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
        $this->authorizeResource(Task::class, 'task');
    }
    /**
     * Display a listing of all tasks (Everything view)
     */
    public function index(Request $request)
    {
        // Build filters from request
        $filters = [
            'search' => $request->search,
            'list_id' => $request->list_id,
            'status_id' => $request->status_id,
            'assigned_to' => $request->assigned_to,
            'service_id' => $request->service_id,
            'priority_id' => $request->priority_id,
            'sort' => $request->get('sort', 'position'),
            'dir' => $request->get('dir', 'asc'),
            'scope' => 'accessible', // Show tasks accessible by current user
        ];

        // Get view mode (default: list - ClickUp style)
        $viewMode = $request->get('view', 'list');

        // For kanban and list views, group by status
        if (in_array($viewMode, ['kanban', 'list'])) {
            $tasksByStatus = $this->taskService->getTasksGroupedByStatus($filters);
            $tasks = $tasksByStatus; // For backwards compatibility
        } else {
            // For table view, use pagination
            $tasks = $this->taskService->getPaginatedTasks($filters, 50);
            $tasksByStatus = collect();
        }

        // Get filter options
        $lists = TaskList::with('client')->ordered()->get();
        $services = TaskServiceModel::active()->ordered()->get();
        $users = User::where('organization_id', auth()->user()->organization_id)
                     ->orderBy('name')
                     ->get();
        $clients = \App\Models\Client::orderBy('name')->get();
        $taskStatuses = SettingOption::taskStatuses()->ordered()->get();
        $taskPriorities = SettingOption::taskPriorities()->ordered()->get();

        // Load hierarchy for sidebar
        $spaces = \App\Models\TaskSpace::with(['folders.lists' => function($query) {
            $query->withCount('tasks');
        }])->ordered()->get();

        // Get current list for breadcrumb
        $currentList = $request->filled('list_id')
            ? TaskList::with('folder.space', 'client')->find($request->list_id)
            : null;

        return view('tasks.index', compact('tasks', 'tasksByStatus', 'viewMode', 'lists', 'services', 'users', 'clients', 'taskStatuses', 'taskPriorities', 'spaces', 'currentList'));
    }

    /**
     * Show the form for creating a new task
     */
    public function create(Request $request)
    {
        $lists = TaskList::with('client')->ordered()->get();
        $services = TaskServiceModel::active()->ordered()->get();
        $users = User::where('organization_id', auth()->user()->organization_id)
                     ->orderBy('name')
                     ->get();
        $taskStatuses = SettingOption::taskStatuses()->ordered()->get();
        $taskPriorities = SettingOption::taskPriorities()->ordered()->get();
        $customFields = TaskCustomField::forOrganization(auth()->user()->organization_id)
                                       ->active()
                                       ->ordered()
                                       ->get();

        // Pre-select list if provided
        $selectedListId = $request->get('list_id');

        return view('tasks.create', compact('lists', 'services', 'users', 'selectedListId', 'taskStatuses', 'taskPriorities', 'customFields'));
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
            'priority_id' => 'nullable|exists:settings_options,id',
            'due_date' => 'nullable|date',
            'time_tracked' => 'nullable|integer|min:0',
            'amount' => 'nullable|numeric|min:0',
            'position' => 'nullable|integer',
            'custom_fields' => 'nullable|array',
            'custom_fields.*' => 'nullable',
        ]);

        // If service is selected and no amount is provided, get the rate for the client
        if (!empty($validated['service_id']) && empty($validated['amount'])) {
            $list = TaskList::find($validated['list_id']);
            if ($list && $list->client_id) {
                $service = TaskServiceModel::find($validated['service_id']);
                if ($service) {
                    $validated['amount'] = $service->getRateForClient($list->client_id);
                }
            }
        }

        $task = $this->taskService->create($validated);

        // Handle AJAX requests from inline creator
        if ($request->expectsJson()) {
            // Load relationships for complete task data
            $task->load(['list.client', 'service', 'status', 'assignedUser', 'priority']);

            return response()->json([
                'success' => true,
                'task' => $task->toArray(),
                'task_id' => $task->id,
                'status_id' => $task->status_id,
                'message' => __('Task created successfully.')
            ]);
        }

        // Traditional form submission
        return redirect()
            ->route('tasks.index')
            ->with('success', __('Task created successfully.'));
    }

    /**
     * Display the specified task
     */
    public function show(Task $task)
    {
        $task->load(['list.client', 'service', 'status', 'assignedUser', 'creator', 'customFieldValues.customField']);

        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified task
     */
    public function edit(Task $task)
    {
        $lists = TaskList::with('client')->ordered()->get();
        $services = TaskServiceModel::active()->ordered()->get();
        $users = User::where('organization_id', auth()->user()->organization_id)
                     ->orderBy('name')
                     ->get();
        $taskStatuses = SettingOption::taskStatuses()->ordered()->get();
        $taskPriorities = SettingOption::taskPriorities()->ordered()->get();
        $customFields = TaskCustomField::forOrganization(auth()->user()->organization_id)
                                       ->active()
                                       ->ordered()
                                       ->get();

        // Load existing custom field values
        $task->load('customFieldValues.customField');

        return view('tasks.edit', compact('task', 'lists', 'services', 'users', 'taskStatuses', 'taskPriorities', 'customFields'));
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
            'priority_id' => 'nullable|exists:settings_options,id',
            'due_date' => 'nullable|date',
            'time_tracked' => 'nullable|integer|min:0',
            'amount' => 'nullable|numeric|min:0',
            'position' => 'nullable|integer',
            'custom_fields' => 'nullable|array',
            'custom_fields.*' => 'nullable',
        ]);

        $task = $this->taskService->update($task, $validated);

        return redirect()
            ->route('tasks.index')
            ->with('success', __('Task updated successfully.'));
    }

    /**
     * Remove the specified task from storage
     */
    public function destroy(Task $task)
    {
        $this->taskService->delete($task);

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

        $task = $this->taskService->update($task, $validated);

        return response()->json([
            'success' => true,
            'task' => $task,
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

        $task = $this->taskService->updateField($task, 'time_tracked', $validated['time_tracked']);

        return response()->json([
            'success' => true,
            'message' => __('Time tracked updated successfully.'),
            'total_amount' => $task->total_amount,
        ]);
    }

    /**
     * Update task position (AJAX for drag-drop)
     */
    public function updatePosition(Request $request, Task $task)
    {
        $validated = $request->validate([
            'position' => 'required|integer|min:0',
            'status_id' => 'nullable|exists:settings_options,id',
        ]);

        $task = $this->taskService->updatePosition(
            $task,
            $validated['position'],
            $validated['status_id'] ?? null
        );

        return response()->json([
            'success' => true,
            'task' => $task,
            'message' => __('Task position updated successfully.'),
        ]);
    }

    /**
     * Get task details for side panel (AJAX)
     */
    public function getDetails(Task $task)
    {
        $task->load([
            'list.client',
            'status',
            'priority',
            'assignedUser',
            'service',
            'subtasks.status',
            'subtasks.assignedUser',
            'comments.user',
            'comments.replies.user',
            'attachments.user'
        ]);

        return response()->json($task);
    }

    /**
     * Quick update single field (AJAX)
     */
    public function quickUpdate(Request $request, Task $task)
    {
        $allowedFields = [
            'name', 'description', 'status_id', 'priority_id', 'list_id',
            'assigned_to', 'due_date', 'time_tracked', 'amount', 'service_id'
        ];

        $data = $request->only($allowedFields);

        // Validate based on field
        $rules = [];
        foreach ($data as $field => $value) {
            switch ($field) {
                case 'name':
                    $rules[$field] = 'required|string|max:255';
                    break;
                case 'status_id':
                case 'priority_id':
                    $rules[$field] = 'nullable|exists:settings_options,id';
                    break;
                case 'list_id':
                    $rules[$field] = 'required|exists:task_lists,id';
                    break;
                case 'service_id':
                    $rules[$field] = 'nullable|exists:task_services,id';
                    break;
                case 'assigned_to':
                    $rules[$field] = 'nullable|exists:users,id';
                    break;
                case 'due_date':
                    $rules[$field] = 'nullable|date';
                    break;
                case 'time_tracked':
                    $rules[$field] = 'nullable|integer|min:0';
                    break;
                case 'amount':
                    $rules[$field] = 'nullable|numeric|min:0';
                    break;
            }
        }

        $validated = $request->validate($rules);
        $task = $this->taskService->update($task, $validated);

        return response()->json([
            'success' => true,
            'task' => $task
        ]);
    }

    /**
     * Update a custom field value for a task (AJAX)
     */
    public function updateCustomField(Request $request, Task $task, TaskCustomField $customField)
    {
        // Verify custom field belongs to organization
        if ($customField->organization_id !== auth()->user()->organization_id) {
            abort(403);
        }

        $validated = $request->validate([
            'value' => 'nullable',
        ]);

        // Update or create the custom field value
        $task->customFieldValues()->updateOrCreate(
            ['custom_field_id' => $customField->id],
            ['value' => $validated['value'] ?? '']
        );

        // Reload the relationship
        $task->load('customFieldValues.customField');

        return response()->json([
            'success' => true,
            'message' => __('Custom field updated successfully.'),
            'value' => $validated['value'] ?? '',
        ]);
    }

    /**
     * Add subtask (AJAX)
     */
    public function addSubtask(Request $request, Task $task)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $subtask = $this->taskService->create([
            'parent_task_id' => $task->id,
            'list_id' => $task->list_id,
            'name' => $validated['name'],
            'status_id' => $task->status_id, // Inherit parent status
        ]);

        return response()->json($subtask);
    }

    /**
     * Toggle subtask status (AJAX)
     */
    public function toggleStatus(Task $task)
    {
        $completedStatus = SettingOption::taskStatuses()
            ->where('value', 'completed')
            ->first();

        $todoStatus = SettingOption::taskStatuses()
            ->where('value', 'todo')
            ->first();

        $newStatusId = $task->status_id == $completedStatus?->id
            ? $todoStatus?->id
            : $completedStatus?->id;

        $task = $this->taskService->updateField($task, 'status_id', $newStatusId);

        return response()->json($task);
    }

    /**
     * Add comment to task (AJAX)
     */
    public function addComment(Request $request, Task $task)
    {
        $validated = $request->validate([
            'comment' => 'required|string',
            'parent_comment_id' => 'nullable|exists:task_comments,id',
        ]);

        $comment = $task->comments()->create($validated);
        $comment->load('user', 'replies.user');

        return response()->json($comment);
    }

    /**
     * Delete comment (AJAX)
     */
    public function deleteComment(\App\Models\TaskComment $comment)
    {
        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => __('Comment deleted successfully.'),
        ]);
    }

    /**
     * Upload attachment (AJAX)
     */
    public function uploadAttachment(Request $request, Task $task)
    {
        $validated = $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $path = $file->store('task-attachments', 'public');

        $attachment = $task->attachments()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);

        $attachment->load('user');

        return response()->json($attachment);
    }

    /**
     * Download attachment
     */
    public function downloadAttachment(\App\Models\TaskAttachment $attachment)
    {
        if (!\Storage::disk('public')->exists($attachment->file_path)) {
            abort(404);
        }

        return \Storage::disk('public')->download(
            $attachment->file_path,
            $attachment->file_name
        );
    }

    /**
     * Delete attachment (AJAX)
     */
    public function deleteAttachment(\App\Models\TaskAttachment $attachment)
    {
        $attachment->delete();

        return response()->json([
            'success' => true,
            'message' => __('Attachment deleted successfully.'),
        ]);
    }

    /**
     * Bulk operations on tasks (AJAX)
     */
    public function bulkUpdate(\App\Http\Requests\Task\BulkUpdateTasksRequest $request)
    {
        $validated = $request->validated();
        $taskIds = $validated['task_ids'];
        $action = $validated['action'];

        // Verify user has permission for all tasks
        $tasks = Task::whereIn('id', $taskIds)->get();
        foreach ($tasks as $task) {
            $this->authorize('update', $task);
        }

        $updatedCount = 0;

        try {
            DB::beginTransaction();

            switch ($action) {
                case 'update_status':
                    foreach ($tasks as $task) {
                        $this->taskService->update($task, ['status_id' => $validated['status_id']]);
                        $updatedCount++;
                    }
                    $message = __(':count tasks updated to new status.', ['count' => $updatedCount]);
                    break;

                case 'update_priority':
                    foreach ($tasks as $task) {
                        $this->taskService->update($task, ['priority_id' => $validated['priority_id']]);
                        $updatedCount++;
                    }
                    $message = __(':count tasks updated to new priority.', ['count' => $updatedCount]);
                    break;

                case 'update_assigned_to':
                    foreach ($tasks as $task) {
                        $this->taskService->update($task, ['assigned_to' => $validated['assigned_to']]);
                        $updatedCount++;
                    }
                    $message = __(':count tasks assigned to user.', ['count' => $updatedCount]);
                    break;

                case 'update_list':
                    foreach ($tasks as $task) {
                        $this->taskService->update($task, ['list_id' => $validated['list_id']]);
                        $updatedCount++;
                    }
                    $message = __(':count tasks moved to new list.', ['count' => $updatedCount]);
                    break;

                case 'delete':
                    foreach ($tasks as $task) {
                        $this->authorize('delete', $task);
                        $this->taskService->delete($task);
                        $updatedCount++;
                    }
                    $message = __(':count tasks deleted.', ['count' => $updatedCount]);
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => __('Invalid action.'),
                    ], 400);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'updated_count' => $updatedCount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => __('Bulk operation failed: :error', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    /**
     * Duplicate a task with all its properties
     */
    public function duplicate(Task $task)
    {
        $this->authorize('view', $task);

        try {
            $newTask = $this->taskService->duplicate($task);

            return response()->json([
                'success' => true,
                'message' => __('Task duplicated successfully.'),
                'task' => $newTask,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to duplicate task: :error', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    /**
     * Add an assignee to a task
     */
    public function addAssignee(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            $task->assignUser($validated['user_id']);

            return response()->json([
                'success' => true,
                'message' => __('Assignee added successfully.'),
                'assignees' => $task->fresh()->load('assignees')->assignees,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to add assignee: :error', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    /**
     * Remove an assignee from a task
     */
    public function removeAssignee(Task $task, $userId)
    {
        $this->authorize('update', $task);

        try {
            $task->removeAssignee($userId);

            return response()->json([
                'success' => true,
                'message' => __('Assignee removed successfully.'),
                'assignees' => $task->fresh()->load('assignees')->assignees,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to remove assignee: :error', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    /**
     * Add a watcher to a task
     */
    public function addWatcher(Request $request, Task $task)
    {
        $this->authorize('view', $task);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            $task->addWatcher($validated['user_id']);

            return response()->json([
                'success' => true,
                'message' => __('Watcher added successfully.'),
                'watchers' => $task->fresh()->load('watchers')->watchers,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to add watcher: :error', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    /**
     * Remove a watcher from a task
     */
    public function removeWatcher(Task $task, $userId)
    {
        $this->authorize('view', $task);

        try {
            $task->removeWatcher($userId);

            return response()->json([
                'success' => true,
                'message' => __('Watcher removed successfully.'),
                'watchers' => $task->fresh()->load('watchers')->watchers,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to remove watcher: :error', ['error' => $e->getMessage()]),
            ], 500);
        }
    }
}
