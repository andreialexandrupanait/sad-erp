<?php

namespace App\Services\ClickUp\Importers;

use App\Models\Task;
use App\Models\ClickUpMapping;
use App\Services\ClickUp\ClickUpClient;
use App\Services\ClickUp\Transformers\TaskTransformer;
use App\Services\ClickUp\Mappers\UserMapper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class TaskImporter
{
    protected $client;
    protected $organizationId;
    protected $userId;
    protected $transformer;
    protected $userMapper;
    protected $errors = [];

    public function __construct(ClickUpClient $client, $organizationId, $userId)
    {
        $this->client = $client;
        $this->organizationId = $organizationId;
        $this->userId = $userId;
        $this->transformer = new TaskTransformer($organizationId);
        $this->userMapper = new UserMapper($organizationId);
    }

    /**
     * Import all tasks from a list
     *
     * @param string $clickUpListId ClickUp list ID
     * @param array $options Import options
     * @return array Statistics
     */
    public function importAllTasks($clickUpListId, $options = [])
    {
        Log::info('Importing tasks from ClickUp list', ['list_id' => $clickUpListId]);

        $page = 0;
        $totalImported = 0;
        $totalSkipped = 0;
        $totalErrors = 0;

        do {
            $response = $this->client->get("/list/{$clickUpListId}/task", [
                'page' => $page,
                'include_closed' => $options['include_closed'] ?? true,
                'include_subtasks' => false, // We'll handle subtasks separately
            ]);

            $tasks = $response['tasks'] ?? [];

            foreach ($tasks as $clickUpTask) {
                $result = $this->import($clickUpTask, $options);

                if ($result === true) {
                    $totalImported++;
                } elseif ($result === false) {
                    $totalErrors++;
                } else {
                    $totalSkipped++;
                }
            }

            $page++;
        } while (count($tasks) === 100); // Continue if full page

        Log::info('Finished importing tasks', [
            'imported' => $totalImported,
            'skipped' => $totalSkipped,
            'errors' => $totalErrors,
        ]);

        return [
            'imported' => $totalImported,
            'skipped' => $totalSkipped,
            'errors' => $totalErrors,
            'error_details' => $this->errors,
        ];
    }

    /**
     * Import a single task
     *
     * @param array $clickUpTask ClickUp task data
     * @param array $options Import options
     * @return bool|null true=imported, false=error, null=skipped
     */
    public function import($clickUpTask, $options = [])
    {
        try {
            return DB::transaction(function () use ($clickUpTask, $options) {
                // Check if task already exists
                $existingTaskId = ClickUpMapping::getLaravelId(
                    $this->organizationId,
                    'task',
                    $clickUpTask['id']
                );

                // Skip if already imported and not updating
                if ($existingTaskId && !($options['update_existing'] ?? false)) {
                    Log::debug('Skipping existing task', ['clickup_task_id' => $clickUpTask['id']]);
                    return null;
                }

                // Transform task data
                $taskData = $this->transformer->transform($clickUpTask);

                // Ensure required fields are present
                if (!$taskData['list_id']) {
                    throw new Exception('List mapping not found for task');
                }

                if (!$taskData['status_id']) {
                    throw new Exception('Status mapping not found for task');
                }

                if (!$taskData['user_id']) {
                    throw new Exception('User mapping not found for task creator');
                }

                // Add organization_id
                $taskData['organization_id'] = $this->organizationId;

                if ($existingTaskId) {
                    // Update existing task
                    $task = Task::withoutGlobalScope('organization_scope')->find($existingTaskId);
                    if ($task) {
                        $task->update($taskData);
                        Log::debug('Updated existing task', ['task_id' => $task->id]);
                    }
                } else {
                    // Create new task
                    $task = Task::withoutGlobalScope('organization_scope')->create($taskData);
                    Log::debug('Created new task', ['task_id' => $task->id]);

                    // Create mapping
                    ClickUpMapping::createMapping(
                        $this->organizationId,
                        'task',
                        $clickUpTask['id'],
                        $task->id,
                        [
                            'name' => $clickUpTask['name'],
                            'status' => $clickUpTask['status']['status'] ?? null,
                            'list_id' => $clickUpTask['list']['id'] ?? null,
                        ]
                    );
                }

                // Import relationships
                if ($task) {
                    $this->importRelationships($task, $clickUpTask, $options);
                }

                return true;
            });
        } catch (Exception $e) {
            $error = [
                'clickup_task_id' => $clickUpTask['id'],
                'task_name' => $clickUpTask['name'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ];

            $this->errors[] = $error;

            Log::error('Failed to import task', $error);

            // Stop on error if option is set
            if ($options['stop_on_error'] ?? false) {
                throw $e;
            }

            return false;
        }
    }

    /**
     * Import task relationships (assignees, watchers, tags, etc.)
     *
     * @param Task $task
     * @param array $clickUpTask
     * @param array $options
     * @return void
     */
    protected function importRelationships($task, $clickUpTask, $options)
    {
        // Import assignees
        if ($options['import_assignees'] ?? true) {
            $this->importAssignees($task, $clickUpTask['assignees'] ?? []);
        }

        // Import watchers
        if ($options['import_watchers'] ?? true) {
            $this->importWatchers($task, $clickUpTask['watchers'] ?? []);
        }

        // Import tags
        if ($options['import_tags'] ?? true) {
            $this->importTags($task, $clickUpTask['tags'] ?? []);
        }

        // Import checklists
        if ($options['import_checklists'] ?? true) {
            $this->importChecklists($task, $clickUpTask['checklists'] ?? []);
        }
    }

    /**
     * Import task assignees
     *
     * @param Task $task
     * @param array $clickUpAssignees
     * @return void
     */
    protected function importAssignees($task, $clickUpAssignees)
    {
        if (empty($clickUpAssignees)) {
            return;
        }

        $assigneeIds = [];

        foreach ($clickUpAssignees as $clickUpAssignee) {
            $userId = $this->userMapper->mapToLaravel($clickUpAssignee['id']);

            if ($userId) {
                $assigneeIds[] = $userId;
            }
        }

        // Sync assignees (many-to-many relationship)
        if (!empty($assigneeIds)) {
            $task->assignees()->sync($assigneeIds);
            Log::debug('Synced task assignees', [
                'task_id' => $task->id,
                'assignee_count' => count($assigneeIds),
            ]);
        }
    }

    /**
     * Import task watchers
     *
     * @param Task $task
     * @param array $clickUpWatchers
     * @return void
     */
    protected function importWatchers($task, $clickUpWatchers)
    {
        if (empty($clickUpWatchers)) {
            return;
        }

        $watcherIds = [];

        foreach ($clickUpWatchers as $clickUpWatcher) {
            $userId = $this->userMapper->mapToLaravel($clickUpWatcher['id']);

            if ($userId) {
                $watcherIds[] = $userId;
            }
        }

        // Sync watchers (many-to-many relationship)
        if (!empty($watcherIds)) {
            $task->watchers()->sync($watcherIds);
            Log::debug('Synced task watchers', [
                'task_id' => $task->id,
                'watcher_count' => count($watcherIds),
            ]);
        }
    }

    /**
     * Import task tags
     *
     * @param Task $task
     * @param array $clickUpTags
     * @return void
     */
    protected function importTags($task, $clickUpTags)
    {
        if (empty($clickUpTags)) {
            return;
        }

        $tagImporter = new TagImporter($this->client, $this->organizationId, $this->userId);
        $tagImporter->importTaskTags($task, $clickUpTags);
    }

    /**
     * Import task checklists
     *
     * @param Task $task
     * @param array $clickUpChecklists
     * @return void
     */
    protected function importChecklists($task, $clickUpChecklists)
    {
        if (empty($clickUpChecklists)) {
            return;
        }

        $checklistImporter = new ChecklistImporter($this->client, $this->organizationId, $this->userId);
        $checklistImporter->importTaskChecklists($task, $clickUpChecklists);
    }

    /**
     * Get import errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Clear errors
     *
     * @return void
     */
    public function clearErrors()
    {
        $this->errors = [];
    }
}
