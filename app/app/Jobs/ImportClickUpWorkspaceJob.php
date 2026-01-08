<?php

namespace App\Jobs;

use App\Models\Organization;
use App\Models\ClickUpSync;
use App\Services\ClickUp\ClickUpImporter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ImportClickUpWorkspaceJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 3600; // 1 hour timeout

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * Exponential backoff: 5 min, 15 min, 30 min
     */
    public function backoff(): array
    {
        return [300, 900, 1800]; // 5 min, 15 min, 30 min
    }

    protected $organizationId;
    protected $userId;
    protected $workspaceId;
    protected $options;
    protected $syncId;
    protected $token;

    /**
     * Create a new job instance.
     */
    public function __construct($organizationId, $userId, $workspaceId, $options, $syncId, $token = null)
    {
        $this->organizationId = $organizationId;
        $this->userId = $userId;
        $this->workspaceId = $workspaceId;
        $this->options = $options;
        $this->syncId = $syncId;
        $this->token = $token;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting ClickUp workspace import job', [
            'sync_id' => $this->syncId,
            'workspace_id' => $this->workspaceId,
            'organization_id' => $this->organizationId,
        ]);

        try {
            // Get sync record
            $sync = ClickUpSync::find($this->syncId);

            if (!$sync) {
                throw new \Exception('Sync record not found');
            }

            // Update status to running
            $sync->update([
                'status' => 'running',
                'started_at' => now(),
            ]);

            // Get organization
            $organization = Organization::find($this->organizationId);

            if (!$organization) {
                throw new \Exception('Organization not found');
            }

            // Create importer with existing sync record
            $importer = new ClickUpImporter($organization, $this->userId, $this->token, $sync);

            // Run import
            $stats = $importer->importWorkspace($this->workspaceId, $this->options);

            // Update sync with results
            $sync->update([
                'status' => 'completed',
                'completed_at' => now(),
                'stats' => $stats,
            ]);

            Log::info('ClickUp workspace import completed', [
                'sync_id' => $this->syncId,
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            Log::error('ClickUp workspace import failed', [
                'sync_id' => $this->syncId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Update sync with error
            if (isset($sync)) {
                $sync->update([
                    'status' => 'failed',
                    'completed_at' => now(),
                    'errors' => [$e->getMessage()],
                ]);
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ClickUp import job failed permanently', [
            'sync_id' => $this->syncId,
            'error' => $exception->getMessage(),
        ]);
    }
}
