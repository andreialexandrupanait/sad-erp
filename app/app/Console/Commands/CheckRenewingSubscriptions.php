<?php

namespace App\Console\Commands;

use App\Events\Subscription\SubscriptionOverdue;
use App\Events\Subscription\SubscriptionRenewalDue;
use App\Models\NotificationLog;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckRenewingSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check-renewals
                            {--dry-run : Show what would be sent without actually sending}
                            {--organization= : Check only a specific organization}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for subscriptions due for renewal and send notifications';

    /**
     * Notification intervals in days before renewal.
     *
     * @var array
     */
    protected array $intervals;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->intervals = config('notifications.defaults.subscription_intervals', [30, 14, 7, 3, 1]);

        $isDryRun = $this->option('dry-run');
        $organizationId = $this->option('organization');

        if ($isDryRun) {
            $this->info('DRY RUN MODE - No notifications will be sent');
        }

        $this->info('Checking for subscriptions due for renewal...');

        $sentCount = 0;
        $skippedCount = 0;

        // Check subscriptions at each interval
        foreach ($this->intervals as $days) {
            $result = $this->checkSubscriptionsAtInterval($days, $isDryRun, $organizationId);
            $sentCount += $result['sent'];
            $skippedCount += $result['skipped'];
        }

        // Check for overdue subscriptions
        $overdueResult = $this->checkOverdueSubscriptions($isDryRun, $organizationId);
        $sentCount += $overdueResult['sent'];
        $skippedCount += $overdueResult['skipped'];

        $this->newLine();
        $this->info("Summary: {$sentCount} notifications sent, {$skippedCount} skipped (already sent)");

        return Command::SUCCESS;
    }

    /**
     * Check subscriptions due at a specific interval.
     */
    protected function checkSubscriptionsAtInterval(int $days, bool $isDryRun, ?string $organizationId): array
    {
        $targetDate = Carbon::today()->addDays($days);
        $notificationType = "subscription_renewal_{$days}d";

        $query = Subscription::withoutGlobalScopes()
            ->whereDate('next_renewal_date', $targetDate)
            ->where('status', 'active');

        if ($organizationId) {
            $query->whereHas('user', function ($q) use ($organizationId) {
                $q->where('organization_id', $organizationId);
            });
        }

        $subscriptions = $query->get();

        $sent = 0;
        $skipped = 0;

        foreach ($subscriptions as $subscription) {
            // Get organization_id through user relationship
            $organizationId = $subscription->user->organization_id ?? null;

            // Check if notification was already sent for this interval
            $alreadySent = NotificationLog::wasAlreadySent(
                $notificationType,
                'subscription',
                $subscription->id,
                $organizationId,
                Carbon::today()
            );

            if ($alreadySent) {
                $this->line("  [SKIP] {$subscription->vendor_name} - {$days}d notification already sent");
                $skipped++;
                continue;
            }

            if ($isDryRun) {
                $this->line("  [DRY] Would send {$days}d renewal notification for: {$subscription->vendor_name}");
                $sent++;
            } else {
                $this->line("  [SEND] Sending {$days}d renewal notification for: {$subscription->vendor_name}");
                event(new SubscriptionRenewalDue($subscription, $days));
                $sent++;
            }
        }

        if ($subscriptions->count() > 0) {
            $this->info("Checked {$days}-day interval: {$subscriptions->count()} subscriptions found");
        }

        return ['sent' => $sent, 'skipped' => $skipped];
    }

    /**
     * Check for overdue subscriptions.
     */
    protected function checkOverdueSubscriptions(bool $isDryRun, ?string $organizationId): array
    {
        $query = Subscription::withoutGlobalScopes()
            ->whereDate('next_renewal_date', '<', Carbon::today())
            ->where('status', 'active');

        if ($organizationId) {
            $query->whereHas('user', function ($q) use ($organizationId) {
                $q->where('organization_id', $organizationId);
            });
        }

        $subscriptions = $query->get();

        $sent = 0;
        $skipped = 0;

        foreach ($subscriptions as $subscription) {
            $daysSinceRenewal = Carbon::parse($subscription->next_renewal_date)->diffInDays(Carbon::today());

            // Get organization_id through user relationship
            $organizationId = $subscription->user->organization_id ?? null;

            // Check if notification was already sent today
            $alreadySent = NotificationLog::wasAlreadySent(
                'subscription_overdue',
                'subscription',
                $subscription->id,
                $organizationId,
                Carbon::today()
            );

            if ($alreadySent) {
                $this->line("  [SKIP] {$subscription->vendor_name} - overdue notification already sent today");
                $skipped++;
                continue;
            }

            if ($isDryRun) {
                $this->line("  [DRY] Would send overdue notification for: {$subscription->vendor_name} (overdue {$daysSinceRenewal} days)");
                $sent++;
            } else {
                $this->line("  [SEND] Sending overdue notification for: {$subscription->vendor_name}");
                event(new SubscriptionOverdue($subscription));
                $sent++;
            }
        }

        if ($subscriptions->count() > 0) {
            $this->info("Checked overdue subscriptions: {$subscriptions->count()} found");
        }

        return ['sent' => $sent, 'skipped' => $skipped];
    }
}
