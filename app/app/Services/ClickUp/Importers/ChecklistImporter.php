<?php

namespace App\Services\ClickUp\Importers;

use App\Models\Task;
use App\Models\TaskChecklist;
use App\Models\TaskChecklistItem;
use App\Services\ClickUp\ClickUpClient;
use App\Services\ClickUp\Mappers\UserMapper;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ChecklistImporter
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
     * Import task checklists and their items
     *
     * @param Task $task
     * @param array $clickUpChecklists
     * @return void
     */
    public function importTaskChecklists($task, $clickUpChecklists)
    {
        if (empty($clickUpChecklists)) {
            return;
        }

        // Delete existing checklists for clean import
        $task->checklists()->delete();

        foreach ($clickUpChecklists as $index => $clickUpChecklist) {
            $this->importChecklist($task, $clickUpChecklist, $index);
        }

        Log::debug('Imported task checklists', [
            'task_id' => $task->id,
            'checklist_count' => count($clickUpChecklists),
        ]);
    }

    /**
     * Import a single checklist
     *
     * @param Task $task
     * @param array $clickUpChecklist
     * @param int $position
     * @return TaskChecklist|null
     */
    protected function importChecklist($task, $clickUpChecklist, $position = 0)
    {
        try {
            $checklist = TaskChecklist::create([
                'task_id' => $task->id,
                'name' => $clickUpChecklist['name'] ?? 'Checklist',
                'position' => $clickUpChecklist['orderindex'] ?? $position,
            ]);

            // Import checklist items
            $items = $clickUpChecklist['items'] ?? [];
            foreach ($items as $itemIndex => $clickUpItem) {
                $this->importChecklistItem($checklist, $clickUpItem, $itemIndex);
            }

            return $checklist;
        } catch (\Exception $e) {
            Log::error('Failed to import checklist', [
                'task_id' => $task->id,
                'checklist_name' => $clickUpChecklist['name'] ?? 'Unknown',
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Import a checklist item
     *
     * @param TaskChecklist $checklist
     * @param array $clickUpItem
     * @param int $position
     * @return TaskChecklistItem|null
     */
    protected function importChecklistItem($checklist, $clickUpItem, $position = 0)
    {
        try {
            // Map assignee if present
            $assigneeId = null;
            if (isset($clickUpItem['assignee']['id'])) {
                $assigneeId = $this->userMapper->mapToLaravel($clickUpItem['assignee']['id']);
            }

            // Determine if item is completed
            $isCompleted = $clickUpItem['resolved'] ?? false;

            $item = TaskChecklistItem::create([
                'checklist_id' => $checklist->id,
                'name' => $clickUpItem['name'] ?? '',
                'completed' => $isCompleted,
                'assignee_id' => $assigneeId,
                'position' => $clickUpItem['orderindex'] ?? $position,
            ]);

            return $item;
        } catch (\Exception $e) {
            Log::error('Failed to import checklist item', [
                'checklist_id' => $checklist->id,
                'item_name' => $clickUpItem['name'] ?? 'Unknown',
                'error' => $e->getMessage(),
            ]);

            return null;
        }
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
            return Carbon::createFromTimestampMs($timestamp);
        } catch (\Exception $e) {
            return null;
        }
    }
}
