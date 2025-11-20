<?php

namespace App\Console\Commands;

use App\Models\Organization;
use App\Models\User;
use App\Services\SmartbillImporter;
use App\Services\SmartbillService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SmartbillImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'smartbill:import
                            {organization_id : The ID of the organization}
                            {user_id : The ID of the user performing the import}
                            {--from-date= : Start date for import (YYYY-MM-DD)}
                            {--to-date= : End date for import (YYYY-MM-DD)}
                            {--year= : Import all invoices for a specific year}
                            {--no-pdf : Skip downloading PDF files}
                            {--preview : Preview what would be imported without saving}
                            {--test-connection : Test Smartbill API connection}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import invoices from Smartbill API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $organizationId = $this->argument('organization_id');
        $userId = $this->argument('user_id');

        // Find organization
        $organization = Organization::find($organizationId);
        if (!$organization) {
            $this->error("Organization with ID {$organizationId} not found");
            return 1;
        }

        // Find user
        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found");
            return 1;
        }

        // Check if user belongs to organization
        if ($user->organization_id !== $organization->id) {
            $this->error("User does not belong to the specified organization");
            return 1;
        }

        $this->info("Organization: {$organization->name}");
        $this->info("User: {$user->name}");
        $this->newLine();

        // Test connection if requested
        if ($this->option('test-connection')) {
            return $this->testConnection($organization);
        }

        // Determine date range
        $fromDate = $this->option('from-date');
        $toDate = $this->option('to-date');
        $year = $this->option('year');

        if ($year) {
            $fromDate = "{$year}-01-01";
            $toDate = "{$year}-12-31";
        }

        if (!$fromDate || !$toDate) {
            $this->error('Please specify either --year or both --from-date and --to-date');
            return 1;
        }

        // Validate dates
        try {
            $fromDate = Carbon::parse($fromDate)->format('Y-m-d');
            $toDate = Carbon::parse($toDate)->format('Y-m-d');
        } catch (\Exception $e) {
            $this->error('Invalid date format. Please use YYYY-MM-DD');
            return 1;
        }

        $downloadPdfs = !$this->option('no-pdf');
        $preview = $this->option('preview');

        if ($preview) {
            $this->warn('PREVIEW MODE - No data will be saved');
        }

        $this->info("Importing invoices from {$fromDate} to {$toDate}");
        $this->info("Download PDFs: " . ($downloadPdfs ? 'Yes' : 'No'));
        $this->newLine();

        if (!$preview && !$this->confirm('Do you want to continue?', true)) {
            $this->info('Import cancelled');
            return 0;
        }

        // Create importer
        try {
            $importer = new SmartbillImporter($organization, $userId);
        } catch (\Exception $e) {
            $this->error('Failed to initialize importer: ' . $e->getMessage());
            return 1;
        }

        // Start import
        $this->info('Starting import...');
        $progressBar = $this->output->createProgressBar();
        $progressBar->start();

        $result = $importer->importInvoices($fromDate, $toDate, $downloadPdfs, $preview);

        $progressBar->finish();
        $this->newLine(2);

        // Display results
        if ($result['success']) {
            $this->info('Import completed successfully!');
            $this->newLine();

            $stats = $result['stats'];
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total invoices found', $stats['total']],
                    ['Created', $stats['created']],
                    ['Updated', $stats['updated']],
                    ['Skipped', $stats['skipped']],
                    ['Errors', $stats['errors']],
                    ['Clients created', $stats['clients_created']],
                    ['PDFs downloaded', $stats['pdfs_downloaded']],
                ]
            );

            if ($stats['updated'] > 0) {
                $this->warn("\n{$stats['updated']} invoice(s) were updated with new data from Smartbill");
            }

            return 0;
        } else {
            $this->error('Import failed: ' . ($result['error'] ?? 'Unknown error'));
            $this->newLine();

            if (isset($result['stats'])) {
                $this->info('Partial statistics:');
                $stats = $result['stats'];
                $this->table(
                    ['Metric', 'Count'],
                    [
                        ['Total processed', $stats['total'] ?? 0],
                        ['Created', $stats['created'] ?? 0],
                        ['Updated', $stats['updated'] ?? 0],
                        ['Errors', $stats['errors'] ?? 0],
                    ]
                );
            }

            return 1;
        }
    }

    /**
     * Test Smartbill API connection
     */
    protected function testConnection($organization)
    {
        $this->info('Testing Smartbill API connection...');

        try {
            $smartbillSettings = $organization->settings['smartbill'] ?? [];
            $username = $smartbillSettings['username'] ?? null;
            $token = $smartbillSettings['token'] ?? null;
            $cif = $smartbillSettings['cif'] ?? null;

            if (!$username || !$token || !$cif) {
                $this->error('Smartbill credentials not configured for this organization');
                $this->info('Please configure the following in organization settings:');
                $this->info('  - smartbill.username');
                $this->info('  - smartbill.token');
                $this->info('  - smartbill.cif');
                return 1;
            }

            $service = new SmartbillService($username, $token, $cif);
            $result = $service->testConnection();

            if ($result['success']) {
                $this->info('✓ Successfully connected to Smartbill API');
                $this->info('✓ Credentials are valid');
                return 0;
            } else {
                $this->error('✗ Failed to connect to Smartbill API');
                $this->error($result['message']);
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('✗ Connection test failed');
            $this->error($e->getMessage());
            return 1;
        }
    }
}
