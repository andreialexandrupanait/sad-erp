<?php

namespace App\Listeners\Notification;

use App\Events\System\SystemErrorOccurred;
use App\Services\Notification\Channels\EmailChannel;
use App\Services\Notification\Channels\SlackChannel;
use App\Services\Notification\Messages\SystemErrorMessage;
use App\Services\Notification\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSystemErrorNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The name of the queue the job should be sent to.
     */
    public string $queue = 'notifications';

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 2; // Fewer retries for error notifications

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 30;

    public function __construct(
        protected NotificationService $notificationService
    ) {
        $this->notificationService->registerChannel(new SlackChannel());
        $this->notificationService->registerChannel(new EmailChannel());
    }

    /**
     * Handle SystemErrorOccurred event.
     */
    public function handle(SystemErrorOccurred $event): void
    {
        // Check if exception notifications are enabled
        if (!config('notifications.notify_on_exceptions', true)) {
            return;
        }

        $message = new SystemErrorMessage(
            $event->exception,
            $event->severity,
            $event->context
        );

        // Use organization from context if available, otherwise use default
        $organizationId = $event->context['organization_id'] ?? null;

        $this->notificationService->send(
            $message,
            ['slack', 'email'],
            $organizationId
        );
    }

    /**
     * Handle a job failure.
     *
     * Note: Be careful not to create infinite loops here.
     * Don't dispatch new error events from failed error notifications.
     */
    public function failed(SystemErrorOccurred $event, \Throwable $exception): void
    {
        // Just log it - don't try to send another notification
        \Log::error('Failed to send system error notification', [
            'original_error' => $event->exception->getMessage(),
            'notification_error' => $exception->getMessage(),
        ]);
    }
}
