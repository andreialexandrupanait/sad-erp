<?php

namespace App\Console\Commands;

use App\Models\Organization;
use App\Models\User;
use App\Services\ClickUp\ClickUpImporter;
use Illuminate\Console\Command;

class ImportClickUpWorkspace extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clickup:import-workspace
                            {workspace_id : The ClickUp workspace/team ID}
                            {--organization= : Organization ID (required)}
                            {--user= : User ID (defaults to first admin)}
                            {--token= : ClickUp API token (defaults to env)}
                            {--tasks : Import tasks (default: true)}
                            {--time-entries : Import time tracking entries}
                            {--comments : Import comments}
                            {--attachments : Import attachments}
                            {--download-files : Download attachment files}
                            {--update-existing : Update existing tasks}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import entire ClickUp workspace (spaces, folders, lists, tasks)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $workspaceId = $this->argument('workspace_id');
        $organizationId = $this->option('organization');

        if (!$organizationId) {
            $this->error('Organization ID is required. Use --organization=ID');
            return 1;
        }

        $organization = Organization::find($organizationId);

        if (!$organization) {
            $this->error("Organization {$organizationId} not found.");
            return 1;
        }

        // Get user ID
        $userId = $this->option('user');

        if (!$userId) {
            $user = User::where('organization_id', $organizationId)
                ->where('role', 'admin')
                ->first();

            if (!$user) {
                $this->error("No admin user found for organization {$organizationId}");
                return 1;
            }

            $userId = $user->id;
        }

        $this->info("Starting ClickUp workspace import...");
        $this->info("Workspace ID: {$workspaceId}");
        $this->info("Organization: {$organization->name} (ID: {$organization->id})");
        $this->info("User ID: {$userId}");

        // Build options
        $options = [
            'import_tasks' => $this->option('tasks') !== false,
            'import_time_entries' => $this->option('time-entries'),
            'import_comments' => $this->option('comments'),
            'import_attachments' => $this->option('attachments'),
            'download_attachments' => $this->option('download-files'),
            'update_existing' => $this->option('update-existing'),
            'include_closed' => true,
            'import_assignees' => true,
            'import_watchers' => true,
            'import_tags' => true,
            'import_checklists' => true,
        ];

        $this->info("\nImport options:");
        $this->table(
            ['Option', 'Value'],
            collect($options)->map(fn($value, $key) => [$key, $value ? 'Yes' : 'No'])->values()->toArray()
        );

        if (!$this->confirm('Continue with import?', true)) {
            $this->info('Import cancelled.');
            return 0;
        }

        try {
            $importer = new ClickUpImporter($organization, $userId, $this->option('token'));

            $this->info("\nImporting workspace...\n");

            $stats = $importer->importWorkspace($workspaceId, $options);

            $this->info("\n✓ Import completed successfully!");
            $this->info("\nStatistics:");
            $this->table(
                ['Entity', 'Count'],
                [
                    ['Spaces', $stats['spaces']],
                    ['Folders', $stats['folders']],
                    ['Lists', $stats['lists']],
                    ['Tasks', $stats['tasks']],
                    ['Time Entries', $stats['time_entries']],
                    ['Comments', $stats['comments']],
                    ['Attachments', $stats['attachments']],
                    ['Errors', count($stats['errors'])],
                ]
            );

            if (!empty($stats['errors'])) {
                $this->warn("\nErrors encountered:");
                foreach (array_slice($stats['errors'], 0, 10) as $error) {
                    $this->error("- Task: {$error['task_name']} (ID: {$error['clickup_task_id']})");
                    $this->error("  Error: {$error['error']}");
                }

                if (count($stats['errors']) > 10) {
                    $this->warn("... and " . (count($stats['errors']) - 10) . " more errors");
                }
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("\n✗ Import failed!");
            $this->error("Error: {$e->getMessage()}");
            $this->error("\nTrace:");
            $this->error($e->getTraceAsString());

            return 1;
        }
    }
}
