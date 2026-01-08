<?php

namespace App\Events\Domain;

use App\Models\Domain;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DomainExpired
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Domain $domain The domain that has expired
     * @param int $daysOverdue Number of days since expiry (positive number)
     */
    public function __construct(
        public Domain $domain,
        public int $daysOverdue = 0
    ) {}

    /**
     * Get the notification type.
     */
    public function getNotificationType(): string
    {
        if ($this->daysOverdue === 0) {
            return 'domain_expired';
        }

        return "domain_expired_{$this->daysOverdue}d";
    }
}
