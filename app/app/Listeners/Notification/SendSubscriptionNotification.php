<?php

namespace App\Listeners\Notification;

use App\Events\Subscription\SubscriptionOverdue;
use App\Events\Subscription\SubscriptionRenewalDue;
use App\Services\Notification\Channels\EmailChannel;
use App\Services\Notification\Channels\SlackChannel;
use App\Services\Notification\Messages\SubscriptionRenewalMessage;
use App\Services\Notification\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSubscriptionNotification implements ShouldQueue
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
     * Handle SubscriptionRenewalDue event.
     */
    public function handleSubscriptionRenewalDue(SubscriptionRenewalDue $event): void
    {
        $message = new SubscriptionRenewalMessage(
            $event->subscription,
            $event->daysUntilRenewal,
            $event->urgency
        );

        // Get organization_id from the subscription's user
        $organizationId = $event->subscription->user?->organization_id;

        $this->notificationService->send(
            $message,
            ['slack', 'email'],
            $organizationId
        );
    }

    /**
     * Handle SubscriptionOverdue event.
     */
    public function handleSubscriptionOverdue(SubscriptionOverdue $event): void
    {
        $message = new SubscriptionRenewalMessage(
            $event->subscription,
            -$event->daysOverdue, // Negative to indicate overdue
            'overdue'
        );

        $organizationId = $event->subscription->user?->organization_id;

        $this->notificationService->send(
            $message,
            ['slack', 'email'],
            $organizationId
        );
    }

    /**
     * Handle a job failure.
     */
    public function failed(SubscriptionRenewalDue|SubscriptionOverdue $event, \Throwable $exception): void
    {
        \Log::error('Failed to send subscription notification', [
            'subscription_id' => $event->subscription->id,
            'vendor_name' => $event->subscription->vendor_name,
            'error' => $exception->getMessage(),
        ]);
    }
}
