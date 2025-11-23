<?php

namespace App\Repositories\Task;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class EloquentTaskRepository implements TaskRepositoryInterface
{
    /**
     * Default relationships to eager load
     */
    protected array $defaultWith = ['list', 'status', 'assignedUser', 'assignees', 'watchers', 'service', 'priority'];

    /**
     * Find a task by ID with optional relationships
     */
    public function find(int $id, array $with = []): ?Task
    {
        $with = empty($with) ? $this->defaultWith : $with;

        return Task::with($with)->find($id);
    }

    /**
     * Get all tasks with filters and relationships
     */
    public function getAll(array $filters = [], array $with = []): Collection
    {
        $with = empty($with) ? $this->defaultWith : $with;

        return $this->applyFilters(Task::with($with), $filters)->get();
    }

    /**
     * Get paginated tasks with filters
     */
    public function getPaginated(array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        $query = Task::with($this->defaultWith);

        return $this->applyFilters($query, $filters)->paginate($perPage);
    }

    /**
     * Create a new task
     */
    public function create(array $data): Task
    {
        $task = Task::create($data);

        return $task->load($this->defaultWith);
    }

    /**
     * Update a task
     */
    public function update(Task $task, array $data): Task
    {
        $task->update($data);

        return $task->fresh($this->defaultWith);
    }

    /**
     * Delete a task
     */
    public function delete(Task $task): bool
    {
        return $task->delete();
    }

    /**
     * Get tasks grouped by a field (e.g., status_id)
     */
    public function getGroupedBy(string $field, array $filters = []): Collection
    {
        $tasks = $this->getAll($filters);

        return $tasks->groupBy($field);
    }

    /**
     * Get tasks by list ID
     */
    public function getByListId(int $listId, array $filters = []): Collection
    {
        $filters['list_id'] = $listId;

        return $this->getAll($filters);
    }

    /**
     * Get tasks by status ID
     */
    public function getByStatusId(int $statusId, array $filters = []): Collection
    {
        $filters['status_id'] = $statusId;

        return $this->getAll($filters);
    }

    /**
     * Get tasks assigned to a user
     */
    public function getAssignedToUser(int $userId, array $filters = []): Collection
    {
        $query = Task::with($this->defaultWith)->assignedTo($userId);

        return $this->applyFilters($query, $filters)->get();
    }

    /**
     * Get tasks created by a user
     */
    public function getCreatedByUser(int $userId, array $filters = []): Collection
    {
        $query = Task::with($this->defaultWith)->where('user_id', $userId);

        return $this->applyFilters($query, $filters)->get();
    }

    /**
     * Get tasks accessible by a user (created by or assigned to)
     */
    public function getAccessibleByUser(int $userId, array $filters = []): Collection
    {
        $query = Task::with($this->defaultWith)
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('assigned_to', $userId);
            });

        return $this->applyFilters($query, $filters)->get();
    }

    /**
     * Get the maximum position for a given list and status
     */
    public function getMaxPosition(?int $listId, ?int $statusId): int
    {
        $query = Task::query();

        if ($listId) {
            $query->where('list_id', $listId);
        }

        if ($statusId) {
            $query->where('status_id', $statusId);
        }

        return $query->max('position') ?? 0;
    }

    /**
     * Get tasks in a position range for reordering
     */
    public function getTasksInRange(?int $listId, int $statusId, int $start, int $end): Collection
    {
        $query = Task::where('status_id', $statusId)
            ->whereBetween('position', [$start, $end]);

        if ($listId) {
            $query->where('list_id', $listId);
        }

        return $query->orderBy('position')->get();
    }

    /**
     * Bulk update positions
     */
    public function bulkUpdatePositions(array $updates): bool
    {
        foreach ($updates as $taskId => $position) {
            Task::where('id', $taskId)->update(['position' => $position]);
        }

        return true;
    }

    /**
     * Get subtasks for a task
     */
    public function getSubtasks(int $taskId): Collection
    {
        return Task::with($this->defaultWith)
            ->where('parent_task_id', $taskId)
            ->ordered()
            ->get();
    }

    /**
     * Count tasks by filters
     */
    public function count(array $filters = []): int
    {
        return $this->applyFilters(Task::query(), $filters)->count();
    }

    /**
     * Apply filters to query
     */
    protected function applyFilters($query, array $filters)
    {
        // Search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // List filter
        if (!empty($filters['list_id'])) {
            $query->where('list_id', $filters['list_id']);
        }

        // Status filter
        if (!empty($filters['status_id'])) {
            $query->where('status_id', $filters['status_id']);
        }

        // Assigned user filter
        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        // Service filter
        if (!empty($filters['service_id'])) {
            $query->where('service_id', $filters['service_id']);
        }

        // Priority filter
        if (!empty($filters['priority_id'])) {
            $query->where('priority_id', $filters['priority_id']);
        }

        // User scope filter
        if (!empty($filters['scope'])) {
            switch ($filters['scope']) {
                case 'created_by_me':
                    $query->createdByMe();
                    break;
                case 'assigned_to_me':
                    $query->assignedToMe();
                    break;
                case 'accessible':
                    $query->accessibleByMe();
                    break;
            }
        }

        // Due date filters
        if (!empty($filters['due_soon'])) {
            $query->dueSoon($filters['due_soon']);
        }

        if (!empty($filters['overdue'])) {
            $query->overdue();
        }

        // Sorting
        $sortBy = $filters['sort'] ?? 'position';
        $sortDir = $filters['dir'] ?? 'asc';

        if ($sortBy === 'position') {
            $query->ordered();
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        return $query;
    }
}
