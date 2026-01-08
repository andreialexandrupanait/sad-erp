<?php

namespace App\Listeners\Notification;

use App\Events\Client\ClientStatusChanged;
use App\Services\Notification\Channels\EmailChannel;
use App\Services\Notification\Channels\SlackChannel;
use App\Services\Notification\Messages\ClientStatusChangedMessage;
use App\Services\Notification\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendClientNotification implements ShouldQueue
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
     * Handle ClientStatusChanged event.
     */
    public function handle(ClientStatusChanged $event): void
    {
        $message = new ClientStatusChangedMessage(
            $event->client,
            $event->oldStatusId,
            $event->newStatusId
        );

        // Get organization_id from the client's user
        $organizationId = $event->client->user?->organization_id;

        $this->notificationService->send(
            $message,
            ['slack', 'email'],
            $organizationId
        );
    }

    /**
     * Handle a job failure.
     */
    public function failed(ClientStatusChanged $event, \Throwable $exception): void
    {
        \Log::error('Failed to send client status notification', [
            'client_id' => $event->client->id,
            'client_name' => $event->client->display_name,
            'old_status' => $event->oldStatusId,
            'new_status' => $event->newStatusId,
            'error' => $exception->getMessage(),
        ]);
    }
}
