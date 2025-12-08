<?php

namespace App\Listeners\Notification;

use App\Events\Domain\DomainExpired;
use App\Events\Domain\DomainExpiringSoon;
use App\Services\Notification\Channels\EmailChannel;
use App\Services\Notification\Channels\SlackChannel;
use App\Services\Notification\Messages\DomainExpiringMessage;
use App\Services\Notification\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendDomainExpiryNotification implements ShouldQueue
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
        // Register channels
        $this->notificationService->registerChannel(new SlackChannel());
        $this->notificationService->registerChannel(new EmailChannel());
    }

    /**
     * Handle DomainExpiringSoon event.
     */
    public function handleDomainExpiringSoon(DomainExpiringSoon $event): void
    {
        $message = new DomainExpiringMessage(
            $event->domain,
            $event->daysUntilExpiry,
            false
        );

        $this->notificationService->send(
            $message,
            ['slack', 'email'],
            $event->domain->organization_id
        );
    }

    /**
     * Handle DomainExpired event.
     */
    public function handleDomainExpired(DomainExpired $event): void
    {
        $message = new DomainExpiringMessage(
            $event->domain,
            $event->daysOverdue,
            true
        );

        $this->notificationService->send(
            $message,
            ['slack', 'email'],
            $event->domain->organization_id
        );
    }

    /**
     * Handle a job failure.
     */
    public function failed(DomainExpiringSoon|DomainExpired $event, \Throwable $exception): void
    {
        \Log::error('Failed to send domain expiry notification', [
            'domain_id' => $event->domain->id,
            'domain_name' => $event->domain->domain_name,
            'error' => $exception->getMessage(),
        ]);
    }
}
