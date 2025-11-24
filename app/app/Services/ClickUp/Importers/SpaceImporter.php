<?php

namespace App\Services\ClickUp\Importers;

use App\Models\TaskSpace;
use App\Models\ClickUpMapping;
use App\Services\ClickUp\ClickUpClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SpaceImporter
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
     * Import all spaces from a workspace
     *
     * @param string $workspaceId ClickUp workspace/team ID
     * @return array Imported spaces data
     */
    public function importAllSpaces($workspaceId)
    {
        Log::info('Importing spaces from ClickUp workspace', ['workspace_id' => $workspaceId]);

        $response = $this->client->get("/team/{$workspaceId}/space");
        $clickUpSpaces = $response['spaces'] ?? [];

        $importedSpaces = [];

        foreach ($clickUpSpaces as $clickUpSpace) {
            $space = $this->import($clickUpSpace);
            if ($space) {
                $importedSpaces[] = $space;
            }
        }

        Log::info('Finished importing spaces', ['count' => count($importedSpaces)]);

        return $importedSpaces;
    }

    /**
     * Import a single space
     *
     * @param array $clickUpSpace ClickUp space data
     * @return \App\Models\TaskSpace|null
     */
    public function import($clickUpSpace)
    {
        try {
            return DB::transaction(function () use ($clickUpSpace) {
                // Check if space already exists
                $existingSpaceId = ClickUpMapping::getLaravelId(
                    $this->organizationId,
                    'space',
                    $clickUpSpace['id']
                );

                $spaceData = [
                    'organization_id' => $this->organizationId,
                    'user_id' => $this->userId,
                    'name' => $clickUpSpace['name'],
                    'icon' => $this->extractIcon($clickUpSpace),
                    'color' => $clickUpSpace['color'] ?? '#3b82f6',
                    'position' => $clickUpSpace['orderindex'] ?? 0,
                    'clickup_metadata' => [
                        'id' => $clickUpSpace['id'],
                        'name' => $clickUpSpace['name'],
                        'private' => $clickUpSpace['private'] ?? false,
                    ],
                ];

                if ($existingSpaceId) {
                    // Update existing space
                    $space = TaskSpace::withoutGlobalScope('user_scope')->find($existingSpaceId);
                    if ($space) {
                        $space->update($spaceData);
                        Log::debug('Updated existing space', ['space_id' => $space->id]);
                    }
                } else {
                    // Create new space (without global scope)
                    $space = TaskSpace::withoutGlobalScope('user_scope')->create($spaceData);
                    Log::debug('Created new space', ['space_id' => $space->id]);

                    // Create mapping
                    ClickUpMapping::createMapping(
                        $this->organizationId,
                        'space',
                        $clickUpSpace['id'],
                        $space->id,
                        [
                            'name' => $clickUpSpace['name'],
                            'private' => $clickUpSpace['private'] ?? false,
                        ]
                    );
                }

                return $space;
            });
        } catch (\Exception $e) {
            Log::error('Failed to import space', [
                'clickup_space_id' => $clickUpSpace['id'],
                'name' => $clickUpSpace['name'],
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Extract icon from ClickUp space data
     *
     * @param array $clickUpSpace
     * @return string|null
     */
    protected function extractIcon($clickUpSpace)
    {
        // ClickUp doesn't have icon field for spaces, use default emoji
        return '📁';
    }
}
