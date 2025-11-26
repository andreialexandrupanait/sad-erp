<?php

namespace App\Services\ClickUp\Transformers;

use App\Services\ClickUp\Mappers\UserMapper;
use App\Services\ClickUp\Mappers\StatusMapper;
use App\Services\ClickUp\Mappers\PriorityMapper;
use App\Models\ClickUpMapping;
use Carbon\Carbon;

class TaskTransformer
{
    protected $organizationId;
    protected $userMapper;
    protected $statusMapper;
    protected $priorityMapper;

    public function __construct($organizationId)
    {
        $this->organizationId = $organizationId;
        $this->userMapper = new UserMapper($organizationId);
        $this->statusMapper = new StatusMapper($organizationId);
        $this->priorityMapper = new PriorityMapper($organizationId);
    }

    /**
     * Transform ClickUp task to Laravel task format
     *
     * @param array $clickUpTask ClickUp task data
     * @return array Laravel task data
     */
    public function transform($clickUpTask)
    {
        return [
            // Basic fields
            'name' => $clickUpTask['name'],
            'description' => $clickUpTask['description'] ?? null,

            // Dates - Convert from milliseconds to Carbon
            'due_date' => $this->convertTimestamp($clickUpTask['due_date'] ?? null),
            'start_date' => $this->convertTimestamp($clickUpTask['start_date'] ?? null),
            'date_closed' => $this->convertTimestamp($clickUpTask['date_closed'] ?? null),
            'created_at' => $this->convertTimestamp($clickUpTask['date_created'] ?? null),
            'updated_at' => $this->convertTimestamp($clickUpTask['date_updated'] ?? null),

            // Time tracking - Convert from milliseconds to minutes
            'time_tracked' => $this->msToMinutes($clickUpTask['time_spent'] ?? 0),
            'time_estimate' => $this->msToMinutes($clickUpTask['time_estimate'] ?? 0),

            // Relationships
            'status_id' => $this->mapStatus($clickUpTask),
            'priority_id' => $this->mapPriority($clickUpTask),
            'list_id' => $this->getListMapping($clickUpTask),
            'user_id' => $this->mapCreator($clickUpTask),
            'assigned_to' => $this->mapPrimaryAssignee($clickUpTask),
            'parent_task_id' => $this->getTaskMapping($clickUpTask['parent'] ?? null),

            // Additional fields
            'position' => $clickUpTask['orderindex'] ?? 0,
            'amount' => null, // ClickUp doesn't have amount field
            'total_amount' => null, // Will be calculated later

            // ClickUp metadata
            'clickup_id' => $clickUpTask['id'],
            'clickup_url' => $clickUpTask['url'] ?? null,
            'clickup_imported_at' => now(),
            'clickup_metadata' => json_encode([
                'text_content' => $clickUpTask['text_content'] ?? null,
                'custom_id' => $clickUpTask['custom_id'] ?? null,
                'team_id' => $clickUpTask['team_id'] ?? null,
                'points' => $clickUpTask['points'] ?? null,
                'archived' => $clickUpTask['archived'] ?? false,
            ]),
        ];
    }

    /**
     * Convert ClickUp timestamp (milliseconds) to Carbon datetime
     *
     * @param string|int|null $timestamp Timestamp in milliseconds
     * @return \Carbon\Carbon|null
     */
    protected function convertTimestamp($timestamp)
    {
        if (!$timestamp) {
            return null;
        }

        // ClickUp timestamps are in milliseconds (13 digits)
        try {
            return Carbon::createFromTimestampMs($timestamp);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Convert milliseconds to minutes
     *
     * @param int $milliseconds
     * @return int Minutes
     */
    protected function msToMinutes($milliseconds)
    {
        if (!$milliseconds) {
            return 0;
        }

        return (int) round($milliseconds / 60000);
    }

    /**
     * Map ClickUp status to Laravel status ID
     *
     * @param array $clickUpTask
     * @return int|null
     */
    protected function mapStatus($clickUpTask)
    {
        $statusName = $clickUpTask['status']['status'] ?? null;

        if (!$statusName) {
            return $this->statusMapper->getDefaultStatus();
        }

        return $this->statusMapper->mapToLaravel($statusName);
    }

    /**
     * Map ClickUp priority to Laravel priority ID
     *
     * @param array $clickUpTask
     * @return int|null
     */
    protected function mapPriority($clickUpTask)
    {
        $priorityValue = $clickUpTask['priority']['id'] ?? $clickUpTask['priority'] ?? null;

        // ClickUp priority can be null, 1, 2, 3, or 4
        if ($priorityValue === null || $priorityValue === '0' || $priorityValue === 0) {
            return null;
        }

        // Extract numeric value if priority is an array
        if (is_array($priorityValue)) {
            $priorityValue = $priorityValue['id'] ?? null;
        }

        return $this->priorityMapper->mapToLaravel((int)$priorityValue);
    }

    /**
     * Get Laravel list ID from ClickUp list
     *
     * @param array $clickUpTask
     * @return int|null
     */
    protected function getListMapping($clickUpTask)
    {
        $clickUpListId = $clickUpTask['list']['id'] ?? null;

        if (!$clickUpListId) {
            return null;
        }

        return ClickUpMapping::getLaravelId(
            $this->organizationId,
            'list',
            $clickUpListId
        );
    }

    /**
     * Map task creator to Laravel user
     *
     * @param array $clickUpTask
     * @return int|null
     */
    protected function mapCreator($clickUpTask)
    {
        $creatorId = $clickUpTask['creator']['id'] ?? null;

        if (!$creatorId) {
            return $this->userMapper->getDefaultUserId();
        }

        $userId = $this->userMapper->mapToLaravel($creatorId);

        // If no mapping found, use default user
        return $userId ?? $this->userMapper->getDefaultUserId();
    }

    /**
     * Map primary assignee (first assignee in ClickUp)
     *
     * @param array $clickUpTask
     * @return int|null
     */
    protected function mapPrimaryAssignee($clickUpTask)
    {
        $assignees = $clickUpTask['assignees'] ?? [];

        if (empty($assignees)) {
            return null;
        }

        // Get first assignee
        $primaryAssignee = $assignees[0];
        $assigneeId = $primaryAssignee['id'] ?? null;

        if (!$assigneeId) {
            return null;
        }

        return $this->userMapper->mapToLaravel($assigneeId);
    }

    /**
     * Get parent task mapping from ClickUp
     *
     * @param string|null $clickUpParentId ClickUp parent task ID
     * @return int|null Laravel parent task ID
     */
    protected function getTaskMapping($clickUpParentId)
    {
        if (!$clickUpParentId) {
            return null;
        }

        return ClickUpMapping::getLaravelId(
            $this->organizationId,
            'task',
            $clickUpParentId
        );
    }
}
