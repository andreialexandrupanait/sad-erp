<?php

namespace App\Services\ClickUp\Importers;

use App\Models\TaskList;
use App\Models\ClickUpMapping;
use App\Services\ClickUp\ClickUpClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ListImporter
{
    protected $client;
    protected $organizationId;
    protected $userId;

    public function __construct(ClickUpClient $client, $organizationId, $userId)
    {
        $this->client = $client;
        $this->organizationId = $organizationId;
        $this->userId = $userId;
    }

    /**
     * Import all lists from a folder
     *
     * @param string $clickUpFolderId ClickUp folder ID
     * @param int $laravelFolderId Laravel folder ID
     * @return array Imported lists data
     */
    public function importAllLists($clickUpFolderId, $laravelFolderId)
    {
        Log::info('Importing lists from ClickUp folder', ['folder_id' => $clickUpFolderId]);

        $response = $this->client->get("/folder/{$clickUpFolderId}/list");
        $clickUpLists = $response['lists'] ?? [];

        $importedLists = [];

        foreach ($clickUpLists as $clickUpList) {
            $list = $this->import($clickUpList, $laravelFolderId);
            if ($list) {
                $importedLists[] = $list;
            }
        }

        Log::info('Finished importing lists', ['count' => count($importedLists)]);

        return $importedLists;
    }

    /**
     * Import folderless lists from a space (lists not in folders)
     *
     * @param string $clickUpSpaceId ClickUp space ID
     * @param int $laravelFolderId Laravel folder ID to assign them to
     * @return array Imported lists data
     */
    public function importFolderlessLists($clickUpSpaceId, $laravelFolderId)
    {
        Log::info('Importing folderless lists from ClickUp space', ['space_id' => $clickUpSpaceId]);

        $response = $this->client->get("/space/{$clickUpSpaceId}/list");
        $clickUpLists = $response['lists'] ?? [];

        $importedLists = [];

        foreach ($clickUpLists as $clickUpList) {
            $list = $this->import($clickUpList, $laravelFolderId);
            if ($list) {
                $importedLists[] = $list;
            }
        }

        Log::info('Finished importing folderless lists', ['count' => count($importedLists)]);

        return $importedLists;
    }

    /**
     * Import a single list
     *
     * @param array $clickUpList ClickUp list data
     * @param int $laravelFolderId Laravel folder ID to associate with
     * @return \App\Models\TaskList|null
     */
    public function import($clickUpList, $laravelFolderId)
    {
        try {
            return DB::transaction(function () use ($clickUpList, $laravelFolderId) {
                // Check if list already exists
                $existingListId = ClickUpMapping::getLaravelId(
                    $this->organizationId,
                    'list',
                    $clickUpList['id']
                );

                $listData = [
                    'folder_id' => $laravelFolderId,
                    'organization_id' => $this->organizationId,
                    'user_id' => $this->userId,
                    'name' => $clickUpList['name'],
                    'icon' => $this->extractIcon($clickUpList),
                    'color' => $clickUpList['color'] ?? '#10b981',
                    'position' => $clickUpList['orderindex'] ?? 0,
                    'client_id' => null, // ClickUp doesn't have clients, set later if needed
                    'clickup_metadata' => [
                        'id' => $clickUpList['id'],
                        'name' => $clickUpList['name'],
                        'archived' => $clickUpList['archived'] ?? false,
                    ],
                ];

                if ($existingListId) {
                    // Update existing list
                    $list = TaskList::withoutGlobalScope('user_scope')->find($existingListId);
                    if ($list) {
                        $list->update($listData);
                        Log::debug('Updated existing list', ['list_id' => $list->id]);
                    }
                } else {
                    // Create new list
                    $list = TaskList::withoutGlobalScope('user_scope')->create($listData);
                    Log::debug('Created new list', ['list_id' => $list->id]);

                    // Create mapping
                    ClickUpMapping::createMapping(
                        $this->organizationId,
                        'list',
                        $clickUpList['id'],
                        $list->id,
                        [
                            'name' => $clickUpList['name'],
                            'archived' => $clickUpList['archived'] ?? false,
                            'override_statuses' => $clickUpList['override_statuses'] ?? false,
                        ]
                    );
                }

                return $list;
            });
        } catch (\Exception $e) {
            Log::error('Failed to import list', [
                'clickup_list_id' => $clickUpList['id'],
                'name' => $clickUpList['name'],
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Extract icon from ClickUp list data
     *
     * @param array $clickUpList
     * @return string|null
     */
    protected function extractIcon($clickUpList)
    {
        // ClickUp doesn't have icon field for lists, use default emoji
        return '📋';
    }
}
