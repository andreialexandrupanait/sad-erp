<?php

namespace App\Services\Task;

use App\Events\Task\TaskAssigned;
use App\Events\Task\TaskCreated;
use App\Events\Task\TaskDeleted;
use App\Events\Task\TaskStatusChanged;
use App\Events\Task\TaskUpdated;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\TaskCustomFieldValue;
use App\Repositories\Task\TaskRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class TaskService
{
    protected TaskRepositoryInterface $repository;

    public function __construct(TaskRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
    /**
     * Create a new task
     */
    public function create(array $data): Task
    {
        return DB::transaction(function () use ($data) {
            // Extract custom fields data
            $customFieldsData = $data['custom_fields'] ?? [];
            unset($data['custom_fields']);

            // Set defaults
            $data['organization_id'] = $data['organization_id'] ?? Auth::user()->organization_id;
            $data['user_id'] = $data['user_id'] ?? Auth::id();

            // Set position if not provided
            if (!isset($data['position'])) {
                $data['position'] = $this->getNextPosition($data['list_id'] ?? null, $data['status_id'] ?? null);
            }

            $task = $this->repository->create($data);

            // Save custom field values
            $this->saveCustomFieldValues($task, $customFieldsData);

            event(new TaskCreated($task));

            return $task;
        });
    }

    /**
     * Update an existing task
     */
    public function update(Task $task, array $data): Task
    {
        return DB::transaction(function () use ($task, $data) {
            // Extract custom fields data
            $customFieldsData = $data['custom_fields'] ?? [];
            unset($data['custom_fields']);

            $oldStatusId = $task->status_id;
            $oldAssigneeId = $task->assigned_to;
            $changes = array_keys($data);

            $task = $this->repository->update($task, $data);

            // Save custom field values
            if (!empty($customFieldsData)) {
                $this->saveCustomFieldValues($task, $customFieldsData);
            }

            // If status changed, update position to end of new status group
            if (isset($data['status_id']) && $data['status_id'] != $oldStatusId) {
                $task->position = $this->getNextPosition($task->list_id, $data['status_id']);
                $task = $this->repository->update($task, ['position' => $task->position]);

                event(new TaskStatusChanged($task, $oldStatusId, $data['status_id']));
            }

            // If assignee changed, dispatch assignment event
            if (isset($data['assigned_to']) && $data['assigned_to'] != $oldAssigneeId) {
                event(new TaskAssigned($task, $oldAssigneeId, $data['assigned_to']));
            }

            event(new TaskUpdated($task, $changes));

            return $task;
        });
    }

    /**
     * Delete a task
     */
    public function delete(Task $task): bool
    {
        return DB::transaction(function () use ($task) {
            $taskId = $task->id;
            $listId = $task->list_id;
            $organizationId = $task->organization_id;

            // Soft delete
            $deleted = $this->repository->delete($task);

            if ($deleted) {
                event(new TaskDeleted($taskId, $listId, $organizationId));
            }

            return $deleted;
        });
    }

    /**
     * Update a single field on a task (for inline editing)
     */
    public function updateField(Task $task, string $field, $value): Task
    {
        return $this->update($task, [$field => $value]);
    }

    /**
     * Assign task to a user
     */
    public function assign(Task $task, int $userId): Task
    {
        $oldAssigneeId = $task->assigned_to;
        $task->update(['assigned_to' => $userId]);

        event(new TaskAssigned($task, $oldAssigneeId, $userId));

        return $task->fresh(['assignedUser']);
    }

    /**
     * Update task position (for drag & drop)
     */
    public function updatePosition(Task $task, int $newPosition, ?int $newStatusId = null): Task
    {
        return DB::transaction(function () use ($task, $newPosition, $newStatusId) {
            $oldStatusId = $task->status_id;

            // If moving to a different status
            if ($newStatusId && $newStatusId != $oldStatusId) {
                $task->status_id = $newStatusId;

                event(new TaskStatusChanged($task, $oldStatusId, $newStatusId));
            }

            $task->position = $newPosition;
            $task->save();

            // Reorder other tasks in the same group
            $this->reorderTasksInGroup($task->list_id, $task->status_id);

            event(new TaskUpdated($task, ['position']));

            return $task->fresh();
        });
    }

    /**
     * Get next position for a task in a status group
     */
    private function getNextPosition(?int $listId, ?int $statusId): int
    {
        $maxPosition = $this->repository->getMaxPosition($listId, $statusId);

        return $maxPosition + 1;
    }

    /**
     * Reorder tasks in a group to ensure sequential positions
     */
    private function reorderTasksInGroup(?int $listId, int $statusId): void
    {
        $query = Task::where('status_id', $statusId);

        if ($listId) {
            $query->where('list_id', $listId);
        }

        $tasks = $query->orderBy('position')->get();

        $position = 1;
        foreach ($tasks as $task) {
            if ($task->position != $position) {
                $task->position = $position;
                $task->saveQuietly(); // Save without triggering events
            }
            $position++;
        }
    }

    /**
     * Duplicate a task
     */
    public function duplicate(Task $task, array $overrides = []): Task
    {
        return DB::transaction(function () use ($task, $overrides) {
            $newTaskData = array_merge(
                $task->only([
                    'list_id', 'service_id', 'priority_id', 'description',
                    'amount', 'time_tracked'
                ]),
                [
                    'name' => $overrides['name'] ?? $task->name . ' (Copy)',
                    'status_id' => $overrides['status_id'] ?? $task->status_id,
                    'assigned_to' => $overrides['assigned_to'] ?? null,
                    'due_date' => $overrides['due_date'] ?? null,
                ],
                $overrides
            );

            $newTask = $this->create($newTaskData);

            // Copy subtasks if any
            if ($task->subtasks()->exists()) {
                foreach ($task->subtasks as $subtask) {
                    $this->duplicate($subtask, ['parent_task_id' => $newTask->id]);
                }
            }

            return $newTask;
        });
    }

    /**
     * Get tasks accessible by current user with filters
     */
    public function getTasksForUser(array $filters = []): Collection
    {
        return $this->repository->getAll($filters, ['list.client', 'service', 'status', 'assignedUser', 'priority']);
    }

    /**
     * Get tasks grouped by status
     */
    public function getTasksGroupedByStatus(array $filters = []): Collection
    {
        return $this->repository->getGroupedBy('status_id', $filters);
    }

    /**
     * Get paginated tasks for table view
     */
    public function getPaginatedTasks(array $filters = [], int $perPage = 50)
    {
        return $this->repository->getPaginated($filters, $perPage);
    }

    /**
     * Save custom field values for a task
     */
    protected function saveCustomFieldValues(Task $task, array $customFieldsData): void
    {
        foreach ($customFieldsData as $fieldId => $value) {
            // Skip empty values
            if ($value === null || $value === '') {
                // Delete existing value if any
                TaskCustomFieldValue::where('task_id', $task->id)
                    ->where('custom_field_id', $fieldId)
                    ->delete();
                continue;
            }

            // Update or create the custom field value
            TaskCustomFieldValue::updateOrCreate(
                [
                    'task_id' => $task->id,
                    'custom_field_id' => $fieldId,
                ],
                [
                    'value' => is_array($value) ? json_encode($value) : $value,
                ]
            );
        }
    }
}
