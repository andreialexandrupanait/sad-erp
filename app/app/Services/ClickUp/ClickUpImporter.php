<?php

namespace App\Services\ClickUp;

use App\Models\Organization;
use App\Models\ClickUpSync;
use App\Services\ClickUp\Importers\SpaceImporter;
use App\Services\ClickUp\Importers\FolderImporter;
use App\Services\ClickUp\Importers\ListImporter;
use App\Services\ClickUp\Importers\TaskImporter;
use App\Services\ClickUp\Importers\TimeEntryImporter;
use App\Services\ClickUp\Importers\CommentImporter;
use App\Services\ClickUp\Importers\AttachmentImporter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ClickUpImporter
{
    protected $client;
    protected $organization;
    protected $userId;
    protected $sync;
    protected $stats = [
        'spaces' => 0,
        'folders' => 0,
        'lists' => 0,
        'tasks' => 0,
        'time_entries' => 0,
        'comments' => 0,
        'attachments' => 0,
        'errors' => [],
    ];

    public function __construct(Organization $organization, $userId, $token = null, ClickUpSync $sync = null)
    {
        $this->organization = $organization;
        $this->userId = $userId;
        $this->sync = $sync;

        // Use provided token or get from organization settings
        $token = $token ?? config('services.clickup.personal_token');

        if (!$token) {
            throw new Exception('ClickUp token not configured');
        }

        $this->client = new ClickUpClient($token);
    }

    /**
     * Import entire workspace (spaces → folders → lists → tasks)
     *
     * @param string $workspaceId ClickUp workspace/team ID
     * @param array $options Import options
     * @return array Statistics
     */
    public function importWorkspace($workspaceId, $options = [])
    {
        Log::info('Starting ClickUp workspace import', [
            'workspace_id' => $workspaceId,
            'organization_id' => $this->organization->id,
        ]);

        // Create sync record only if not provided
        if (!$this->sync) {
            $this->sync = ClickUpSync::create([
                'organization_id' => $this->organization->id,
                'user_id' => $this->userId,
                'sync_type' => 'workspace',
                'clickup_workspace_id' => $workspaceId,
                'status' => 'running',
                'options' => $options,
                'started_at' => now(),
            ]);
        }

        try {
            // Import hierarchy
            $spaces = $this->importSpaces($workspaceId, $options);

            foreach ($spaces as $space) {
                $this->importFolders($space['clickup_id'], $space['laravel_id'], $options);
            }

            // Update sync status
            $this->sync->update([
                'status' => 'completed',
                'completed_at' => now(),
                'stats' => $this->stats,
            ]);

            Log::info('Completed ClickUp workspace import', $this->stats);

            return $this->stats;
        } catch (Exception $e) {
            Log::error('Failed to import ClickUp workspace', [
                'workspace_id' => $workspaceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Update sync status
            $this->sync->update([
                'status' => 'failed',
                'completed_at' => now(),
                'errors' => [$e->getMessage()],
                'stats' => $this->stats,
            ]);

            throw $e;
        }
    }

    /**
     * Import a single list and its tasks
     *
     * @param string $clickUpListId ClickUp list ID
     * @param array $options Import options
     * @return array Statistics
     */
    public function importList($clickUpListId, $options = [])
    {
        Log::info('Starting ClickUp list import', [
            'list_id' => $clickUpListId,
            'organization_id' => $this->organization->id,
        ]);

        // Create sync record only if not provided
        if (!$this->sync) {
            $this->sync = ClickUpSync::create([
                'organization_id' => $this->organization->id,
                'user_id' => $this->userId,
                'sync_type' => 'list',
                'clickup_list_id' => $clickUpListId,
                'status' => 'running',
                'options' => $options,
                'started_at' => now(),
            ]);
        }

        try {
            // Import tasks from this list
            $taskImporter = new TaskImporter($this->client, $this->organization->id, $this->userId);
            $taskStats = $taskImporter->importAllTasks($clickUpListId, $options);

            $this->stats['tasks'] = $taskStats['imported'];
            $this->stats['errors'] = $taskStats['error_details'] ?? [];

            // Update sync status
            $this->sync->update([
                'status' => 'completed',
                'completed_at' => now(),
                'stats' => $this->stats,
            ]);

            Log::info('Completed ClickUp list import', $this->stats);

            return $this->stats;
        } catch (Exception $e) {
            Log::error('Failed to import ClickUp list', [
                'list_id' => $clickUpListId,
                'error' => $e->getMessage(),
            ]);

            // Update sync status
            $this->sync->update([
                'status' => 'failed',
                'completed_at' => now(),
                'errors' => [$e->getMessage()],
                'stats' => $this->stats,
            ]);

            throw $e;
        }
    }

    /**
     * Import spaces from workspace
     *
     * @param string $workspaceId
     * @param array $options
     * @return array Imported spaces with IDs
     */
    protected function importSpaces($workspaceId, $options)
    {
        $spaceImporter = new SpaceImporter($this->client, $this->organization->id, $this->userId);
        $spaces = $spaceImporter->importAllSpaces($workspaceId);

        // Filter out null values (failed imports)
        $spaces = array_filter($spaces, function ($space) {
            return $space !== null;
        });

        $this->stats['spaces'] = count($spaces);

        // Return spaces with their ClickUp and Laravel IDs
        return array_map(function ($space) {
            return [
                'clickup_id' => $space->clickup_metadata['id'] ?? null,
                'laravel_id' => $space->id,
            ];
        }, $spaces);
    }

    /**
     * Import folders from a space
     *
     * @param string $clickUpSpaceId
     * @param int $laravelSpaceId
     * @param array $options
     * @return void
     */
    protected function importFolders($clickUpSpaceId, $laravelSpaceId, $options)
    {
        $folderImporter = new FolderImporter($this->client, $this->organization->id, $this->userId);
        $folders = $folderImporter->importAllFolders($clickUpSpaceId, $laravelSpaceId);

        $this->stats['folders'] += count($folders);

        // Import lists from each folder
        foreach ($folders as $folder) {
            $this->importLists($folder->clickup_metadata['id'] ?? null, $folder->id, $options);
        }

        // Also import folderless lists (lists directly in space)
        $listImporter = new ListImporter($this->client, $this->organization->id, $this->userId);

        // Create a default folder for folderless lists if needed
        // Or you can skip this if you want to handle it differently
        // For now, we'll skip folderless lists in workspace import
    }

    /**
     * Import lists from a folder
     *
     * @param string $clickUpFolderId
     * @param int $laravelFolderId
     * @param array $options
     * @return void
     */
    protected function importLists($clickUpFolderId, $laravelFolderId, $options)
    {
        if (!$clickUpFolderId) {
            return;
        }

        $listImporter = new ListImporter($this->client, $this->organization->id, $this->userId);
        $lists = $listImporter->importAllLists($clickUpFolderId, $laravelFolderId);

        $this->stats['lists'] += count($lists);

        // Import tasks from each list if enabled
        if ($options['import_tasks'] ?? true) {
            foreach ($lists as $list) {
                $this->importTasks($list->clickup_metadata['id'] ?? null, $options);
            }
        }
    }

    /**
     * Import tasks from a list
     *
     * @param string $clickUpListId
     * @param array $options
     * @return void
     */
    protected function importTasks($clickUpListId, $options)
    {
        if (!$clickUpListId) {
            return;
        }

        $taskImporter = new TaskImporter($this->client, $this->organization->id, $this->userId);
        $taskStats = $taskImporter->importAllTasks($clickUpListId, $options);

        $this->stats['tasks'] += $taskStats['imported'];

        if (!empty($taskStats['error_details'])) {
            $this->stats['errors'] = array_merge(
                $this->stats['errors'],
                $taskStats['error_details']
            );
        }

        // Import rich data for tasks if enabled
        if ($options['import_time_entries'] ?? false) {
            $this->importTimeEntries($clickUpListId, $options);
        }

        if ($options['import_comments'] ?? false) {
            $this->importComments($clickUpListId);
        }

        if ($options['import_attachments'] ?? false) {
            $this->importAttachments($clickUpListId, $options);
        }
    }

    /**
     * Import time entries for tasks in a list
     *
     * @param string $clickUpListId
     * @param array $options
     * @return void
     */
    protected function importTimeEntries($clickUpListId, $options)
    {
        // This would require fetching all tasks and then their time entries
        // For now, this is a placeholder - time entries should be imported per task
        Log::debug('Time entry import per list not yet implemented', [
            'list_id' => $clickUpListId,
        ]);
    }

    /**
     * Import comments for tasks in a list
     *
     * @param string $clickUpListId
     * @return void
     */
    protected function importComments($clickUpListId)
    {
        // This would require fetching all tasks and then their comments
        // For now, this is a placeholder - comments should be imported per task
        Log::debug('Comment import per list not yet implemented', [
            'list_id' => $clickUpListId,
        ]);
    }

    /**
     * Import attachments for tasks in a list
     *
     * @param string $clickUpListId
     * @param array $options
     * @return void
     */
    protected function importAttachments($clickUpListId, $options)
    {
        // This would require fetching all tasks and then their attachments
        // For now, this is a placeholder - attachments should be imported per task
        Log::debug('Attachment import per list not yet implemented', [
            'list_id' => $clickUpListId,
        ]);
    }

    /**
     * Get import statistics
     *
     * @return array
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * Get the sync record
     *
     * @return ClickUpSync|null
     */
    public function getSync()
    {
        return $this->sync;
    }
}
