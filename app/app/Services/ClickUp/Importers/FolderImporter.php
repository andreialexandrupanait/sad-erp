<?php

namespace App\Services\ClickUp\Importers;

use App\Models\TaskFolder;
use App\Models\ClickUpMapping;
use App\Services\ClickUp\ClickUpClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FolderImporter
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
     * Import all folders from a space
     *
     * @param string $clickUpSpaceId ClickUp space ID
     * @param int $laravelSpaceId Laravel space ID
     * @return array Imported folders data
     */
    public function importAllFolders($clickUpSpaceId, $laravelSpaceId)
    {
        Log::info('Importing folders from ClickUp space', ['space_id' => $clickUpSpaceId]);

        $response = $this->client->get("/space/{$clickUpSpaceId}/folder");
        $clickUpFolders = $response['folders'] ?? [];

        $importedFolders = [];

        foreach ($clickUpFolders as $clickUpFolder) {
            $folder = $this->import($clickUpFolder, $laravelSpaceId);
            if ($folder) {
                $importedFolders[] = $folder;
            }
        }

        Log::info('Finished importing folders', ['count' => count($importedFolders)]);

        return $importedFolders;
    }

    /**
     * Import a single folder
     *
     * @param array $clickUpFolder ClickUp folder data
     * @param int $laravelSpaceId Laravel space ID to associate with
     * @return \App\Models\TaskFolder|null
     */
    public function import($clickUpFolder, $laravelSpaceId)
    {
        try {
            return DB::transaction(function () use ($clickUpFolder, $laravelSpaceId) {
                // Check if folder already exists
                $existingFolderId = ClickUpMapping::getLaravelId(
                    $this->organizationId,
                    'folder',
                    $clickUpFolder['id']
                );

                $folderData = [
                    'space_id' => $laravelSpaceId,
                    'organization_id' => $this->organizationId,
                    'user_id' => $this->userId,
                    'name' => $clickUpFolder['name'],
                    'icon' => $this->extractIcon($clickUpFolder),
                    'color' => $clickUpFolder['color'] ?? '#8b5cf6',
                    'position' => $clickUpFolder['orderindex'] ?? 0,
                    'clickup_metadata' => [
                        'id' => $clickUpFolder['id'],
                        'name' => $clickUpFolder['name'],
                        'hidden' => $clickUpFolder['hidden'] ?? false,
                    ],
                ];

                if ($existingFolderId) {
                    // Update existing folder
                    $folder = TaskFolder::withoutGlobalScope('user_scope')->find($existingFolderId);
                    if ($folder) {
                        $folder->update($folderData);
                        Log::debug('Updated existing folder', ['folder_id' => $folder->id]);
                    }
                } else {
                    // Create new folder
                    $folder = TaskFolder::withoutGlobalScope('user_scope')->create($folderData);
                    Log::debug('Created new folder', ['folder_id' => $folder->id]);

                    // Create mapping
                    ClickUpMapping::createMapping(
                        $this->organizationId,
                        'folder',
                        $clickUpFolder['id'],
                        $folder->id,
                        [
                            'name' => $clickUpFolder['name'],
                            'hidden' => $clickUpFolder['hidden'] ?? false,
                        ]
                    );
                }

                return $folder;
            });
        } catch (\Exception $e) {
            Log::error('Failed to import folder', [
                'clickup_folder_id' => $clickUpFolder['id'],
                'name' => $clickUpFolder['name'],
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Extract icon from ClickUp folder data
     *
     * @param array $clickUpFolder
     * @return string|null
     */
    protected function extractIcon($clickUpFolder)
    {
        // ClickUp doesn't have icon field for folders, use default emoji
        return '📂';
    }
}
