<?php

namespace App\Events\Client;

use App\Models\Client;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClientStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Client $client The client whose status changed
     * @param int|null $oldStatusId The previous status ID
     * @param int $newStatusId The new status ID
     */
    public function __construct(
        public Client $client,
        public ?int $oldStatusId,
        public int $newStatusId
    ) {}

    /**
     * Get the notification type.
     */
    public function getNotificationType(): string
    {
        return 'client_status_changed';
    }
}
