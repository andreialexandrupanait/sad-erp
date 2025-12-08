<?php

namespace App\Listeners\Notification;

use App\Events\FinancialRevenue\RevenueCreated;
use App\Services\Notification\Channels\EmailChannel;
use App\Services\Notification\Channels\SlackChannel;
use App\Services\Notification\Messages\RevenueCreatedMessage;
use App\Services\Notification\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendRevenueNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The name of the queue the job should be sent to.
     */
    public string $queue = 'notifications';

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    public function __construct(
        protected NotificationService $notificationService
    ) {
        $this->notificationService->registerChannel(new SlackChannel());
        $this->notificationService->registerChannel(new EmailChannel());
    }

    /**
     * Handle RevenueCreated event.
     */
    public function handle(RevenueCreated $event): void
    {
        // Check if revenue notifications are enabled (default: disabled)
        $typeConfig = config('notifications.types.revenue_created');
        if (!($typeConfig['default_enabled'] ?? false)) {
            // Skip if not enabled by default and no user preference check
            // In a full implementation, you'd check user preferences here
            return;
        }

        $message = new RevenueCreatedMessage($event->revenue);

        $this->notificationService->send(
            $message,
            ['slack', 'email'],
            $event->revenue->organization_id
        );
    }

    /**
     * Handle a job failure.
     */
    public function failed(RevenueCreated $event, \Throwable $exception): void
    {
        \Log::error('Failed to send revenue notification', [
            'revenue_id' => $event->revenue->id,
            'amount' => $event->revenue->amount,
            'error' => $exception->getMessage(),
        ]);
    }
}
