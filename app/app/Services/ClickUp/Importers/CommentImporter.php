<?php

namespace App\Services\ClickUp\Importers;

use App\Models\Task;
use App\Models\TaskComment;
use App\Services\ClickUp\ClickUpClient;
use App\Services\ClickUp\Mappers\UserMapper;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CommentImporter
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
     * Import comments for a task
     *
     * @param Task $task
     * @param string $clickUpTaskId ClickUp task ID
     * @return array Statistics
     */
    public function importTaskComments($task, $clickUpTaskId)
    {
        Log::info('Importing comments for task', [
            'task_id' => $task->id,
            'clickup_task_id' => $clickUpTaskId,
        ]);

        try {
            $allComments = [];
            $page = 0;

            // Fetch all comments (paginated by 25)
            do {
                $params = [];
                if ($page > 0) {
                    // For pagination, use the last comment's ID
                    $lastComment = end($allComments);
                    if ($lastComment) {
                        $params['start_id'] = $lastComment['id'];
                    }
                }

                $response = $this->client->get("/task/{$clickUpTaskId}/comment", $params);
                $comments = $response['comments'] ?? [];

                if (!empty($comments)) {
                    $allComments = array_merge($allComments, $comments);
                }

                $page++;
            } while (count($comments) === 25); // Continue if full page

            $importedCount = 0;
            $errors = [];

            foreach ($allComments as $clickUpComment) {
                $result = $this->importComment($task, $clickUpComment);

                if ($result) {
                    $importedCount++;
                } else {
                    $errors[] = $clickUpComment['id'] ?? 'unknown';
                }
            }

            Log::info('Finished importing comments', [
                'task_id' => $task->id,
                'imported' => $importedCount,
                'errors' => count($errors),
            ]);

            return [
                'imported' => $importedCount,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to import comments for task', [
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
     * Import a single comment
     *
     * @param Task $task
     * @param array $clickUpComment
     * @return TaskComment|null
     */
    protected function importComment($task, $clickUpComment)
    {
        try {
            // Map user
            $userId = null;
            if (isset($clickUpComment['user']['id'])) {
                $userId = $this->userMapper->mapToLaravel($clickUpComment['user']['id']);
            }

            // Use task's user_id as fallback
            if (!$userId) {
                $userId = $task->user_id;
            }

            // Get comment text (prefer comment_text which is parsed, fallback to comment)
            $commentText = $clickUpComment['comment_text'] ?? $clickUpComment['comment'] ?? '';

            // Parse created date
            $createdAt = $this->convertTimestamp($clickUpComment['date'] ?? null);

            // Check if this is a reply (threaded comment)
            $parentId = $clickUpComment['parent'] ?? null;
            $laravelParentId = null;

            if ($parentId) {
                // Try to find the parent comment in our system
                $parentComment = TaskComment::where('task_id', $task->id)
                    ->where('clickup_comment_id', $parentId)
                    ->first();

                if ($parentComment) {
                    $laravelParentId = $parentComment->id;
                }
            }

            // Check if comment already exists
            $clickUpCommentId = $clickUpComment['id'] ?? null;
            $existingComment = null;

            if ($clickUpCommentId) {
                $existingComment = TaskComment::where('task_id', $task->id)
                    ->where('clickup_comment_id', $clickUpCommentId)
                    ->first();
            }

            if ($existingComment) {
                // Update existing comment
                $existingComment->update([
                    'user_id' => $userId,
                    'comment' => $commentText,
                    'parent_id' => $laravelParentId,
                    'created_at' => $createdAt,
                ]);

                return $existingComment;
            }

            // Create new comment
            $comment = TaskComment::create([
                'task_id' => $task->id,
                'user_id' => $userId,
                'comment' => $commentText,
                'parent_id' => $laravelParentId,
                'clickup_comment_id' => $clickUpCommentId,
                'created_at' => $createdAt,
            ]);

            Log::debug('Imported comment', [
                'comment_id' => $comment->id,
                'task_id' => $task->id,
            ]);

            return $comment;
        } catch (\Exception $e) {
            Log::error('Failed to import comment', [
                'task_id' => $task->id,
                'clickup_comment_id' => $clickUpComment['id'] ?? 'unknown',
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
            // ClickUp timestamps are typically in milliseconds (13 digits)
            if (strlen((string)$timestamp) === 13) {
                return Carbon::createFromTimestampMs($timestamp);
            } else {
                return Carbon::createFromTimestamp($timestamp);
            }
        } catch (\Exception $e) {
            return null;
        }
    }
}
