<?php

namespace App\Services\ClickUp\Importers;

use App\Models\Task;
use App\Models\TaskTimeEntry;
use App\Services\ClickUp\ClickUpClient;
use App\Services\ClickUp\Mappers\UserMapper;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TimeEntryImporter
{
    protected $client;
    protected $organizationId;
    protected $userId;
    protected $userMapper;

    public function __construct(ClickUpClient $client, $organizationId, $userId)
    {
        $this->client = $client;
        $this->organizationId = $organizationId;
        $this->userId = $userId;
        $this->userMapper = new UserMapper($organizationId);
    }

    /**
     * Import time entries for a task
     *
     * @param Task $task
     * @param string $clickUpTaskId ClickUp task ID
     * @param string $workspaceId ClickUp workspace/team ID
     * @return array Statistics
     */
    public function importTaskTimeEntries($task, $clickUpTaskId, $workspaceId)
    {
        Log::info('Importing time entries for task', [
            'task_id' => $task->id,
            'clickup_task_id' => $clickUpTaskId,
        ]);

        try {
            // Get time entries using the modern API
            $response = $this->client->get("/team/{$workspaceId}/time_entries", [
                'task_id' => $clickUpTaskId,
            ]);

            $timeEntries = $response['data'] ?? [];

            $importedCount = 0;
            $errors = [];

            foreach ($timeEntries as $clickUpEntry) {
                $result = $this->importTimeEntry($task, $clickUpEntry);

                if ($result) {
                    $importedCount++;
                } else {
                    $errors[] = $clickUpEntry['id'] ?? 'unknown';
                }
            }

            Log::info('Finished importing time entries', [
                'task_id' => $task->id,
                'imported' => $importedCount,
                'errors' => count($errors),
            ]);

            return [
                'imported' => $importedCount,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to import time entries for task', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'imported' => 0,
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Import a single time entry
     *
     * @param Task $task
     * @param array $clickUpEntry
     * @return TaskTimeEntry|null
     */
    protected function importTimeEntry($task, $clickUpEntry)
    {
        try {
            // Map user
            $userId = null;
            if (isset($clickUpEntry['user']['id'])) {
                $userId = $this->userMapper->mapToLaravel($clickUpEntry['user']['id']);
            }

            // Use task's user_id as fallback
            if (!$userId) {
                $userId = $task->user_id;
            }

            // Convert duration from milliseconds to minutes
            $durationMs = $clickUpEntry['duration'] ?? 0;
            $durationMinutes = (int) round($durationMs / 60000);

            // Parse dates
            $startedAt = $this->convertTimestamp($clickUpEntry['start'] ?? null);
            $endedAt = $this->convertTimestamp($clickUpEntry['end'] ?? null);

            // Check if entry already exists (by ClickUp entry ID stored in description)
            $clickUpEntryId = $clickUpEntry['id'] ?? null;
            $existingEntry = null;

            if ($clickUpEntryId) {
                $existingEntry = TaskTimeEntry::where('task_id', $task->id)
                    ->where('description', 'LIKE', "%ClickUp Entry: {$clickUpEntryId}%")
                    ->first();
            }

            if ($existingEntry) {
                // Update existing entry
                $existingEntry->update([
                    'user_id' => $userId,
                    'minutes' => $durationMinutes,
                    'started_at' => $startedAt,
                    'ended_at' => $endedAt,
                    'description' => $this->formatDescription($clickUpEntry),
                    'is_billable' => $clickUpEntry['billable'] ?? false,
                ]);

                return $existingEntry;
            }

            // Create new time entry
            $timeEntry = TaskTimeEntry::create([
                'task_id' => $task->id,
                'user_id' => $userId,
                'minutes' => $durationMinutes,
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
                'description' => $this->formatDescription($clickUpEntry),
                'is_billable' => $clickUpEntry['billable'] ?? false,
            ]);

            Log::debug('Imported time entry', [
                'time_entry_id' => $timeEntry->id,
                'task_id' => $task->id,
                'minutes' => $durationMinutes,
            ]);

            return $timeEntry;
        } catch (\Exception $e) {
            Log::error('Failed to import time entry', [
                'task_id' => $task->id,
                'clickup_entry_id' => $clickUpEntry['id'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Format time entry description
     *
     * @param array $clickUpEntry
     * @return string
     */
    protected function formatDescription($clickUpEntry)
    {
        $parts = [];

        // Add original description if exists
        if (isset($clickUpEntry['description']) && !empty($clickUpEntry['description'])) {
            $parts[] = $clickUpEntry['description'];
        }

        // Add ClickUp metadata
        $parts[] = "[Imported from ClickUp]";
        $parts[] = "ClickUp Entry: " . ($clickUpEntry['id'] ?? 'unknown');

        return implode("\n", $parts);
    }

    /**
     * Convert ClickUp timestamp (milliseconds) to Carbon datetime
     *
     * @param string|int|null $timestamp
     * @return \Carbon\Carbon|null
     */
    protected function convertTimestamp($timestamp)
    {
        if (!$timestamp) {
            return null;
        }

        try {
            // ClickUp can provide timestamps in milliseconds (13 digits) or seconds (10 digits)
            if (strlen((string)$timestamp) === 13) {
                return Carbon::createFromTimestampMs($timestamp);
            } else {
                return Carbon::createFromTimestamp($timestamp);
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Recalculate task's total time from imported entries
     *
     * @param Task $task
     * @return void
     */
    public function recalculateTaskTime($task)
    {
        $totalMinutes = $task->timeEntries()->sum('minutes');

        $task->update([
            'time_tracked' => $totalMinutes,
        ]);

        Log::debug('Recalculated task time', [
            'task_id' => $task->id,
            'total_minutes' => $totalMinutes,
        ]);
    }
}
