<?php

namespace App\Events\Domain;

use App\Models\Domain;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DomainExpiringSoon
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Domain $domain The domain that is expiring soon
     * @param int $daysUntilExpiry Number of days until expiry
     */
    public function __construct(
        public Domain $domain,
        public int $daysUntilExpiry
    ) {}

    /**
     * Get the notification type based on days until expiry.
     */
    public function getNotificationType(): string
    {
        return match ($this->daysUntilExpiry) {
            30 => 'domain_expiring_30d',
            14 => 'domain_expiring_14d',
            7 => 'domain_expiring_7d',
            3 => 'domain_expiring_3d',
            1 => 'domain_expiring_1d',
            0 => 'domain_expiring_today',
            default => "domain_expiring_{$this->daysUntilExpiry}d",
        };
    }
}
