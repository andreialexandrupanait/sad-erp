<?php

namespace App\Console\Commands;

use App\Models\Offer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckExpiredOffers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'offers:check-expired
                            {--dry-run : Show what would be marked as expired without actually updating}
                            {--organization= : Check only a specific organization}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark offers as expired when their validity date has passed';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $organizationId = $this->option('organization');

        if ($isDryRun) {
            $this->info('DRY RUN MODE - No offers will be marked as expired');
        }

        $this->info('Checking for expired offers...');

        // Find offers that are still open (sent or viewed) but past validity date
        $query = Offer::query()
            ->whereIn('status', ['sent', 'viewed'])
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', '<', Carbon::today());

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        $offers = $query->with('client')->get();

        if ($offers->isEmpty()) {
            $this->info('No expired offers found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$offers->count()} expired offers:");
        $this->newLine();

        $count = 0;
        foreach ($offers as $offer) {
            $daysSinceExpiry = Carbon::parse($offer->valid_until)->diffInDays(Carbon::today());

            if ($isDryRun) {
                $this->line("  [DRY] Would mark as expired: {$offer->offer_number} ({$offer->client?->display_name}) - expired {$daysSinceExpiry} days ago");
            } else {
                $this->line("  [EXPIRE] Marking as expired: {$offer->offer_number} ({$offer->client?->display_name})");

                $offer->update(['status' => 'expired']);
                $offer->logActivity('expired', "Offer expired (validity ended {$daysSinceExpiry} days ago)");

                $count++;
            }
        }

        $this->newLine();

        if ($isDryRun) {
            $this->info("Would mark {$offers->count()} offers as expired.");
        } else {
            $this->info("Marked {$count} offers as expired.");

            Log::info('Expired offers check completed', [
                'count' => $count,
                'organization_id' => $organizationId,
            ]);
        }

        return Command::SUCCESS;
    }
}
