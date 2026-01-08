<?php

namespace App\Console\Commands;

use App\Events\Domain\DomainExpired;
use App\Events\Domain\DomainExpiringSoon;
use App\Models\Domain;
use App\Models\NotificationLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckExpiringDomains extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'domains:check-expiring
                            {--dry-run : Show what would be sent without actually sending}
                            {--organization= : Check only a specific organization}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expiring and expired domains and send notifications';

    /**
     * Notification intervals in days before expiry.
     *
     * @var array
     */
    protected array $intervals;

    /**
     * Days between overdue notifications.
     *
     * @var int
     */
    protected int $overdueInterval;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->intervals = config('notifications.defaults.domain_intervals', [30, 14, 7, 3, 1, 0]);
        $this->overdueInterval = config('notifications.defaults.domain_overdue_interval', 7);

        $isDryRun = $this->option('dry-run');
        $organizationId = $this->option('organization');

        if ($isDryRun) {
            $this->info('DRY RUN MODE - No notifications will be sent');
        }

        $this->info('Checking for expiring and expired domains...');

        $sentCount = 0;
        $skippedCount = 0;

        // Check domains expiring at each interval
        foreach ($this->intervals as $days) {
            $result = $this->checkDomainsAtInterval($days, $isDryRun, $organizationId);
            $sentCount += $result['sent'];
            $skippedCount += $result['skipped'];
        }

        // Check for expired (overdue) domains
        $overdueResult = $this->checkOverdueDomains($isDryRun, $organizationId);
        $sentCount += $overdueResult['sent'];
        $skippedCount += $overdueResult['skipped'];

        $this->newLine();
        $this->info("Summary: {$sentCount} notifications sent, {$skippedCount} skipped (already sent)");

        return Command::SUCCESS;
    }

    /**
     * Check domains expiring at a specific interval.
     */
    protected function checkDomainsAtInterval(int $days, bool $isDryRun, ?string $organizationId): array
    {
        $targetDate = Carbon::today()->addDays($days);
        $notificationType = $days === 0 ? 'domain_expiring_today' : "domain_expiring_{$days}d";

        $query = Domain::query()
            ->whereDate('expiry_date', $targetDate)
            ->where('status', 'Active');

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        $domains = $query->get();

        $sent = 0;
        $skipped = 0;

        foreach ($domains as $domain) {
            // Check if notification was already sent for this interval
            $alreadySent = NotificationLog::wasAlreadySent(
                $notificationType,
                'domain',
                $domain->id,
                $domain->organization_id,
                Carbon::today()
            );

            if ($alreadySent) {
                $this->line("  [SKIP] {$domain->domain_name} - {$days}d notification already sent");
                $skipped++;
                continue;
            }

            if ($isDryRun) {
                $this->line("  [DRY] Would send {$days}d expiry notification for: {$domain->domain_name}");
                $sent++;
            } else {
                $this->line("  [SEND] Sending {$days}d expiry notification for: {$domain->domain_name}");
                event(new DomainExpiringSoon($domain, $days));
                $sent++;
            }
        }

        if ($domains->count() > 0) {
            $this->info("Checked {$days}-day interval: {$domains->count()} domains found");
        }

        return ['sent' => $sent, 'skipped' => $skipped];
    }

    /**
     * Check for overdue (expired) domains.
     */
    protected function checkOverdueDomains(bool $isDryRun, ?string $organizationId): array
    {
        $query = Domain::query()
            ->whereDate('expiry_date', '<', Carbon::today())
            ->where('status', 'Active');

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        $domains = $query->get();

        $sent = 0;
        $skipped = 0;

        foreach ($domains as $domain) {
            $daysSinceExpiry = Carbon::parse($domain->expiry_date)->diffInDays(Carbon::today());

            // Only send overdue notification every X days
            if ($daysSinceExpiry % $this->overdueInterval !== 0) {
                continue;
            }

            // Check if notification was already sent today
            $alreadySent = NotificationLog::wasAlreadySent(
                'domain_expired',
                'domain',
                $domain->id,
                $domain->organization_id,
                Carbon::today()
            );

            if ($alreadySent) {
                $this->line("  [SKIP] {$domain->domain_name} - expired notification already sent today");
                $skipped++;
                continue;
            }

            if ($isDryRun) {
                $this->line("  [DRY] Would send expired notification for: {$domain->domain_name} (expired {$daysSinceExpiry} days ago)");
                $sent++;
            } else {
                $this->line("  [SEND] Sending expired notification for: {$domain->domain_name}");
                event(new DomainExpired($domain));
                $sent++;
            }
        }

        if ($domains->count() > 0) {
            $this->info("Checked expired domains: {$domains->count()} found");
        }

        return ['sent' => $sent, 'skipped' => $skipped];
    }
}
