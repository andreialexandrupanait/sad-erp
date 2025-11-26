<?php

namespace App\Services\ClickUp\Importers;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Services\ClickUp\ClickUpClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AttachmentImporter
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
     * Import attachments for a task
     *
     * @param Task $task
     * @param array $clickUpAttachments
     * @param array $options
     * @return array Statistics
     */
    public function importTaskAttachments($task, $clickUpAttachments, $options = [])
    {
        Log::info('Importing attachments for task', [
            'task_id' => $task->id,
            'attachment_count' => count($clickUpAttachments),
        ]);

        $importedCount = 0;
        $errors = [];
        $downloadEnabled = $options['download_attachments'] ?? false;

        foreach ($clickUpAttachments as $clickUpAttachment) {
            $result = $this->importAttachment($task, $clickUpAttachment, $downloadEnabled);

            if ($result) {
                $importedCount++;
            } else {
                $errors[] = $clickUpAttachment['id'] ?? 'unknown';
            }
        }

        Log::info('Finished importing attachments', [
            'task_id' => $task->id,
            'imported' => $importedCount,
            'errors' => count($errors),
        ]);

        return [
            'imported' => $importedCount,
            'errors' => $errors,
        ];
    }

    /**
     * Import a single attachment
     *
     * @param Task $task
     * @param array $clickUpAttachment
     * @param bool $downloadFile
     * @return TaskAttachment|null
     */
    protected function importAttachment($task, $clickUpAttachment, $downloadFile = false)
    {
        try {
            $clickUpAttachmentId = $clickUpAttachment['id'] ?? null;

            // Check if attachment already exists
            $existingAttachment = TaskAttachment::where('task_id', $task->id)
                ->where('clickup_attachment_id', $clickUpAttachmentId)
                ->first();

            $fileName = $clickUpAttachment['title'] ?? 'attachment';
            $fileExtension = $clickUpAttachment['extension'] ?? '';
            $clickUpUrl = $clickUpAttachment['url'] ?? null;
            $filePath = null;

            // Download file if enabled
            if ($downloadFile && $clickUpUrl) {
                try {
                    $filePath = $this->downloadAttachment($clickUpUrl, $fileName, $fileExtension);
                } catch (\Exception $e) {
                    Log::warning('Failed to download attachment', [
                        'task_id' => $task->id,
                        'attachment_id' => $clickUpAttachmentId,
                        'error' => $e->getMessage(),
                    ]);
                    // Continue without download
                }
            }

            // Parse uploaded date
            $uploadedAt = $this->convertTimestamp($clickUpAttachment['date'] ?? null);

            $attachmentData = [
                'task_id' => $task->id,
                'user_id' => $this->userId,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_size' => null, // ClickUp doesn't provide file size in API
                'mime_type' => $this->guessMinType($fileExtension),
                'clickup_attachment_id' => $clickUpAttachmentId,
                'clickup_url' => $clickUpUrl,
                'thumbnail_small' => $clickUpAttachment['thumbnail_small'] ?? null,
                'thumbnail_large' => $clickUpAttachment['thumbnail_large'] ?? null,
                'created_at' => $uploadedAt,
            ];

            if ($existingAttachment) {
                // Update existing attachment
                $existingAttachment->update($attachmentData);
                return $existingAttachment;
            }

            // Create new attachment
            $attachment = TaskAttachment::create($attachmentData);

            Log::debug('Imported attachment', [
                'attachment_id' => $attachment->id,
                'task_id' => $task->id,
                'downloaded' => $downloadFile && $filePath !== null,
            ]);

            return $attachment;
        } catch (\Exception $e) {
            Log::error('Failed to import attachment', [
                'task_id' => $task->id,
                'clickup_attachment_id' => $clickUpAttachment['id'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Download attachment from ClickUp
     *
     * @param string $url
     * @param string $fileName
     * @param string $extension
     * @return string|null File path in storage
     */
    protected function downloadAttachment($url, $fileName, $extension)
    {
        // Create storage path
        $directory = 'task-attachments/' . date('Y/m');
        $safeFileName = Str::slug(pathinfo($fileName, PATHINFO_FILENAME)) . '.' . $extension;
        $fullPath = $directory . '/' . $safeFileName;

        // Download file
        $fileContents = file_get_contents($url);

        if ($fileContents === false) {
            throw new \Exception('Failed to download file from ClickUp');
        }

        // Store file
        Storage::put($fullPath, $fileContents);

        return $fullPath;
    }

    /**
     * Guess MIME type from file extension
     *
     * @param string $extension
     * @return string
     */
    protected function guessMinType($extension)
    {
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'zip' => 'application/zip',
            'txt' => 'text/plain',
        ];

        return $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
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
