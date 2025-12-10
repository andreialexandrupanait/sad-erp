<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\NotificationLog;
use App\Services\Contract\ContractService;
use App\Services\Notification\NotificationService;
use App\Services\Notification\Messages\ContractExpiringMessage;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckExpiringContracts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contracts:check-expiring
                            {--dry-run : Show what would be sent without actually sending}
                            {--organization= : Check only a specific organization}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expiring contracts and send notifications';

    /**
     * Notification intervals in days before expiry.
     *
     * @var array
     */
    protected array $intervals = [30, 14, 7, 3, 1, 0];

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $isDryRun = $this->option('dry-run');
        $organizationId = $this->option('organization');

        if ($isDryRun) {
            $this->info('DRY RUN MODE - No notifications will be sent');
        }

        $this->info('Checking for expiring contracts...');

        $sentCount = 0;
        $skippedCount = 0;

        // Check contracts expiring at each interval
        foreach ($this->intervals as $days) {
            $result = $this->checkContractsAtInterval($days, $isDryRun, $organizationId, $notificationService);
            $sentCount += $result['sent'];
            $skippedCount += $result['skipped'];
        }

        // Check for already expired contracts
        $expiredResult = $this->checkExpiredContracts($isDryRun, $organizationId, $notificationService);
        $sentCount += $expiredResult['sent'];
        $skippedCount += $expiredResult['skipped'];

        $this->newLine();
        $this->info("Summary: {$sentCount} notifications sent, {$skippedCount} skipped (already sent)");

        return Command::SUCCESS;
    }

    /**
     * Check contracts expiring at a specific interval.
     */
    protected function checkContractsAtInterval(
        int $days,
        bool $isDryRun,
        ?string $organizationId,
        NotificationService $notificationService
    ): array {
        $targetDate = Carbon::today()->addDays($days);
        $notificationType = $days === 0 ? 'contract_expiring_today' : "contract_expiring_{$days}d";

        $query = Contract::query()
            ->whereDate('end_date', $targetDate)
            ->where('status', 'active');

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        $contracts = $query->with('client')->get();

        $sent = 0;
        $skipped = 0;

        foreach ($contracts as $contract) {
            // Check if notification was already sent for this interval
            $alreadySent = NotificationLog::wasAlreadySent(
                'Contract',
                $contract->id,
                $notificationType,
                'email'
            );

            if ($alreadySent) {
                $this->line("  [SKIP] {$contract->contract_number} - {$days}d notification already sent");
                $skipped++;
                continue;
            }

            if ($isDryRun) {
                $this->line("  [DRY] Would send {$days}d expiry notification for: {$contract->contract_number} ({$contract->client?->display_name})");
                $sent++;
            } else {
                $this->line("  [SEND] Sending {$days}d expiry notification for: {$contract->contract_number}");

                $message = new ContractExpiringMessage($contract, $days);
                $notificationService->send($message, null, $contract->organization_id);

                $sent++;
            }
        }

        if ($contracts->count() > 0) {
            $this->info("Checked {$days}-day interval: {$contracts->count()} contracts found");
        }

        return ['sent' => $sent, 'skipped' => $skipped];
    }

    /**
     * Check for already expired contracts and mark them as expired.
     */
    protected function checkExpiredContracts(
        bool $isDryRun,
        ?string $organizationId,
        NotificationService $notificationService
    ): array {
        $query = Contract::query()
            ->whereDate('end_date', '<', Carbon::today())
            ->where('status', 'active')
            ->where('auto_renew', false);

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        $contracts = $query->with('client')->get();

        $sent = 0;
        $skipped = 0;

        foreach ($contracts as $contract) {
            $daysSinceExpiry = Carbon::parse($contract->end_date)->diffInDays(Carbon::today());

            if ($isDryRun) {
                $this->line("  [DRY] Would mark as expired: {$contract->contract_number} (expired {$daysSinceExpiry} days ago)");
                $sent++;
            } else {
                $this->line("  [EXPIRE] Marking contract as expired: {$contract->contract_number}");

                $contract->update(['status' => 'expired']);

                // Send notification
                $message = new ContractExpiringMessage($contract, -$daysSinceExpiry);
                $notificationService->send($message, null, $contract->organization_id);

                $sent++;
            }
        }

        if ($contracts->count() > 0) {
            $this->info("Found {$contracts->count()} expired contracts");
        }

        return ['sent' => $sent, 'skipped' => $skipped];
    }
}
