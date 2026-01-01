<?php

namespace App\Listeners\Notification;

use App\Events\System\SystemErrorOccurred;
use App\Services\Notification\Channels\EmailChannel;
use App\Services\Notification\Channels\SlackChannel;
use App\Services\Notification\Messages\SystemErrorMessage;
use App\Services\Notification\NotificationService;

class SendSystemErrorNotification
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
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

}
