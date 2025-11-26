<?php

namespace App\Services\ClickUp\Importers;

use App\Models\Task;
use App\Models\TaskTag;
use App\Models\ClickUpMapping;
use App\Services\ClickUp\ClickUpClient;
use Illuminate\Support\Facades\Log;

class TagImporter
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
     * Import task tags and assign them to the task
     *
     * @param Task $task
     * @param array $clickUpTags
     * @return void
     */
    public function importTaskTags($task, $clickUpTags)
    {
        if (empty($clickUpTags)) {
            return;
        }

        $tagIds = [];

        foreach ($clickUpTags as $clickUpTag) {
            $tag = $this->findOrCreateTag($clickUpTag);

            if ($tag) {
                $tagIds[] = $tag->id;
            }
        }

        // Sync tags (many-to-many relationship via task_tag_assignments)
        if (!empty($tagIds)) {
            $task->tags()->sync($tagIds);
            Log::debug('Synced task tags', [
                'task_id' => $task->id,
                'tag_count' => count($tagIds),
            ]);
        }
    }

    /**
     * Find existing tag or create new one
     *
     * @param array $clickUpTag
     * @return TaskTag|null
     */
    protected function findOrCreateTag($clickUpTag)
    {
        $tagName = $clickUpTag['name'] ?? null;

        if (!$tagName) {
            return null;
        }

        // Check if tag already exists by name
        $existingTag = TaskTag::where('name', $tagName)
            ->where('organization_id', $this->organizationId)
            ->first();

        if ($existingTag) {
            return $existingTag;
        }

        // Check if we have a mapping
        if (isset($clickUpTag['tag_fg'])) {
            // This is a space tag with full details
            $existingTagId = ClickUpMapping::getLaravelId(
                $this->organizationId,
                'tag',
                $tagName // ClickUp doesn't provide tag IDs in task responses, use name
            );

            if ($existingTagId) {
                return TaskTag::find($existingTagId);
            }
        }

        // Create new tag
        $tag = TaskTag::create([
            'organization_id' => $this->organizationId,
            'user_id' => $this->userId,
            'name' => $tagName,
            'color' => $this->extractColor($clickUpTag),
        ]);

        // Create mapping
        ClickUpMapping::createMapping(
            $this->organizationId,
            'tag',
            $tagName,
            $tag->id,
            [
                'tag_bg' => $clickUpTag['tag_bg'] ?? null,
                'tag_fg' => $clickUpTag['tag_fg'] ?? null,
            ]
        );

        Log::debug('Created new tag', [
            'tag_id' => $tag->id,
            'name' => $tagName,
        ]);

        return $tag;
    }

    /**
     * Extract color from ClickUp tag
     *
     * @param array $clickUpTag
     * @return string
     */
    protected function extractColor($clickUpTag)
    {
        // ClickUp provides tag_bg and tag_fg colors
        // Use tag_bg if available, otherwise use tag_fg
        if (isset($clickUpTag['tag_bg'])) {
            return $clickUpTag['tag_bg'];
        }

        if (isset($clickUpTag['tag_fg'])) {
            return $clickUpTag['tag_fg'];
        }

        // Default color
        return '#94a3b8';
    }
}
