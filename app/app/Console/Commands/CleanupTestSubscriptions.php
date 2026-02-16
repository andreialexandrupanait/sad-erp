<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class CleanupTestSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:cleanup-test
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find and remove TEST subscriptions from the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('Searching for TEST subscriptions...');

        // Find subscriptions with TEST in vendor name (case insensitive)
        $testSubscriptions = Subscription::withoutGlobalScopes()
            ->where(function ($query) {
                $query->where('vendor_name', 'like', '%TEST%')
                      ->orWhere('vendor_name', 'like', '%test%')
                      ->orWhere('vendor_name', 'like', '%Test%');
            })
            ->get();

        if ($testSubscriptions->isEmpty()) {
            $this->info('No TEST subscriptions found.');
            return Command::SUCCESS;
        }

        $this->newLine();
        $this->info("Found {$testSubscriptions->count()} TEST subscription(s):");
        $this->newLine();

        $this->table(
            ['ID', 'Vendor Name', 'Status', 'Next Renewal Date', 'User ID'],
            $testSubscriptions->map(fn($s) => [
                $s->id,
                $s->vendor_name,
                $s->status,
                $s->next_renewal_date?->format('Y-m-d') ?? 'N/A',
                $s->user_id,
            ])
        );

        if ($isDryRun) {
            $this->newLine();
            $this->warn('DRY RUN - No subscriptions were deleted.');
            $this->info('Run without --dry-run to actually delete these subscriptions.');
            return Command::SUCCESS;
        }

        if (!$force && !$this->confirm('Do you want to delete these subscriptions?')) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        // Delete the subscriptions
        $deletedCount = 0;
        foreach ($testSubscriptions as $subscription) {
            $subscription->forceDelete();
            $this->line("  Deleted: {$subscription->vendor_name} (ID: {$subscription->id})");
            $deletedCount++;
        }

        $this->newLine();
        $this->info("Successfully deleted {$deletedCount} TEST subscription(s).");

        return Command::SUCCESS;
    }
}
