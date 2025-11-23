<?php

namespace App\Repositories\Task;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TaskRepositoryInterface
{
    /**
     * Find a task by ID with optional relationships
     */
    public function find(int $id, array $with = []): ?Task;

    /**
     * Get all tasks with filters and relationships
     */
    public function getAll(array $filters = [], array $with = []): Collection;

    /**
     * Get paginated tasks with filters
     */
    public function getPaginated(array $filters = [], int $perPage = 50): LengthAwarePaginator;

    /**
     * Create a new task
     */
    public function create(array $data): Task;

    /**
     * Update a task
     */
    public function update(Task $task, array $data): Task;

    /**
     * Delete a task
     */
    public function delete(Task $task): bool;

    /**
     * Get tasks grouped by a field (e.g., status_id)
     */
    public function getGroupedBy(string $field, array $filters = []): Collection;

    /**
     * Get tasks by list ID
     */
    public function getByListId(int $listId, array $filters = []): Collection;

    /**
     * Get tasks by status ID
     */
    public function getByStatusId(int $statusId, array $filters = []): Collection;

    /**
     * Get tasks assigned to a user
     */
    public function getAssignedToUser(int $userId, array $filters = []): Collection;

    /**
     * Get tasks created by a user
     */
    public function getCreatedByUser(int $userId, array $filters = []): Collection;

    /**
     * Get tasks accessible by a user (created by or assigned to)
     */
    public function getAccessibleByUser(int $userId, array $filters = []): Collection;

    /**
     * Get the maximum position for a given list and status
     */
    public function getMaxPosition(?int $listId, ?int $statusId): int;

    /**
     * Get tasks in a position range for reordering
     */
    public function getTasksInRange(?int $listId, int $statusId, int $start, int $end): Collection;

    /**
     * Bulk update positions
     */
    public function bulkUpdatePositions(array $updates): bool;

    /**
     * Get subtasks for a task
     */
    public function getSubtasks(int $taskId): Collection;

    /**
     * Count tasks by filters
     */
    public function count(array $filters = []): int;
}
