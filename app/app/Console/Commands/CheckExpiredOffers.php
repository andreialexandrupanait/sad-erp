<?php

namespace App\Console\Commands;

use App\Mail\OfferExpiredNotification;
use App\Mail\OfferExpiringReminder;
use App\Models\Offer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckExpiredOffers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'offers:check-expired
                            {--dry-run : Show what would happen without actually updating}
                            {--organization= : Check only a specific organization}
                            {--skip-reminders : Skip sending expiry reminders}
                            {--skip-expiry : Skip marking offers as expired}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark offers as expired and send expiry reminder emails';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $organizationId = $this->option('organization');
        $skipReminders = $this->option('skip-reminders');
        $skipExpiry = $this->option('skip-expiry');

        if ($isDryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }

        // Send expiry reminders first (3 days and 1 day before)
        if (!$skipReminders) {
            $this->sendExpiryReminders($isDryRun, $organizationId);
        }

        // Then mark expired offers
        if (!$skipExpiry) {
            $this->markExpiredOffers($isDryRun, $organizationId);
        }

        return Command::SUCCESS;
    }

    /**
     * Send expiry reminders for offers expiring in 3 days or 1 day.
     */
    protected function sendExpiryReminders(bool $isDryRun, ?int $organizationId): void
    {
        $this->info('Checking for offers expiring soon...');

        // Find offers expiring in 3 days or 1 day that haven't received a reminder
        $reminderDays = [3, 1];

        foreach ($reminderDays as $days) {
            $targetDate = Carbon::today()->addDays($days);

            $query = Offer::query()
                ->withoutGlobalScopes()
                ->whereIn('status', ['sent', 'viewed'])
                ->whereNotNull('valid_until')
                ->whereDate('valid_until', $targetDate)
                ->where('expiry_reminder_enabled', true)
                ->where(function ($q) use ($days) {
                    // For 3-day reminder: no reminder sent yet
                    // For 1-day reminder: allow if no reminder or last was > 1 day ago
                    if ($days === 3) {
                        $q->whereNull('expiry_reminder_sent_at');
                    } else {
                        $q->where(function ($sub) {
                            $sub->whereNull('expiry_reminder_sent_at')
                                ->orWhere('expiry_reminder_sent_at', '<', Carbon::now()->subDay());
                        });
                    }
                });

            if ($organizationId) {
                $query->where('organization_id', $organizationId);
            }

            $offers = $query->with(['client', 'organization'])->get();

            if ($offers->isEmpty()) {
                $this->line("  No offers expiring in {$days} days needing reminders.");
                continue;
            }

            $this->info("Found {$offers->count()} offers expiring in {$days} days:");

            foreach ($offers as $offer) {
                $clientEmail = $offer->client?->email ?? $offer->temp_client_email;

                if (!$clientEmail) {
                    $this->warn("  [SKIP] {$offer->offer_number} - No client email");
                    continue;
                }

                if ($isDryRun) {
                    $this->line("  [DRY] Would send {$days}-day reminder: {$offer->offer_number} to {$clientEmail}");
                } else {
                    try {
                        Mail::to($clientEmail)->send(new OfferExpiringReminder($offer, $days));

                        $offer->update(['expiry_reminder_sent_at' => now()]);
                        $offer->logActivity('expiry_reminder_sent', [
                            'days_remaining' => $days,
                            'email' => $clientEmail,
                        ]);

                        $this->line("  [SENT] {$days}-day reminder: {$offer->offer_number} to {$clientEmail}");

                        Log::info("Expiry reminder sent", [
                            'offer_id' => $offer->id,
                            'days_remaining' => $days,
                            'email' => $clientEmail,
                        ]);
                    } catch (\Exception $e) {
                        $this->error("  [ERROR] Failed to send reminder for {$offer->offer_number}: {$e->getMessage()}");
                        Log::error("Failed to send expiry reminder", [
                            'offer_id' => $offer->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        $this->newLine();
    }

    /**
     * Mark expired offers and send notifications.
     */
    protected function markExpiredOffers(bool $isDryRun, ?int $organizationId): void
    {
        $this->info('Checking for expired offers...');

        // Find offers that are still open (sent or viewed) but past validity date
        $query = Offer::query()
            ->withoutGlobalScopes()
            ->whereIn('status', ['sent', 'viewed'])
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', '<', Carbon::today());

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        $offers = $query->with(['client', 'organization', 'creator'])->get();

        if ($offers->isEmpty()) {
            $this->info('No expired offers found.');
            return;
        }

        $this->info("Found {$offers->count()} expired offers:");
        $this->newLine();

        $count = 0;
        foreach ($offers as $offer) {
            $daysSinceExpiry = Carbon::parse($offer->valid_until)->diffInDays(Carbon::today());

            $clientName = $offer->client?->display_name ?? $offer->temp_client_name ?? '-';

            if ($isDryRun) {
                $this->line("  [DRY] Would mark as expired: {$offer->offer_number} ({$clientName}) - expired {$daysSinceExpiry} days ago");
            } else {
                $this->line("  [EXPIRE] Marking as expired: {$offer->offer_number} ({$clientName})");

                $offer->update(['status' => 'expired']);
                $offer->logActivity('expired', ['days_overdue' => $daysSinceExpiry]);

                // Send admin notification
                $this->sendExpiredNotification($offer);

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
    }

    /**
     * Send expired notification to admin.
     */
    protected function sendExpiredNotification(Offer $offer): void
    {
        // Get admin email
        $adminEmail = $offer->organization?->billing_email
            ?? $offer->organization?->email
            ?? $offer->creator?->email;

        if (!$adminEmail) {
            return;
        }

        try {
            Mail::to($adminEmail)->send(new OfferExpiredNotification($offer));

            Log::info("Expired notification sent", [
                'offer_id' => $offer->id,
                'admin_email' => $adminEmail,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send expired notification", [
                'offer_id' => $offer->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
