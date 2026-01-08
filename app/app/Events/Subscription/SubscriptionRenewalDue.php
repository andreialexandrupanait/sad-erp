<?php

namespace App\Events\Subscription;

use App\Models\Subscription;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionRenewalDue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Subscription $subscription The subscription due for renewal
     * @param int $daysUntilRenewal Number of days until renewal
     * @param string $urgency Urgency level ('normal', 'warning', 'urgent', 'overdue')
     */
    public function __construct(
        public Subscription $subscription,
        public int $daysUntilRenewal,
        public string $urgency = 'normal'
    ) {}

    /**
     * Get the notification type based on days until renewal.
     */
    public function getNotificationType(): string
    {
        return match ($this->daysUntilRenewal) {
            30 => 'subscription_renewal_30d',
            14 => 'subscription_renewal_14d',
            7 => 'subscription_renewal_7d',
            3 => 'subscription_renewal_3d',
            1 => 'subscription_renewal_1d',
            0 => 'subscription_overdue',
            default => $this->daysUntilRenewal < 0
                ? 'subscription_overdue'
                : "subscription_renewal_{$this->daysUntilRenewal}d",
        };
    }

    /**
     * Determine urgency from days until renewal.
     */
    public static function determineUrgency(int $daysUntilRenewal): string
    {
        if ($daysUntilRenewal < 0) {
            return 'overdue';
        }
        if ($daysUntilRenewal <= 7) {
            return 'urgent';
        }
        if ($daysUntilRenewal <= 14) {
            return 'warning';
        }

        return 'normal';
    }
}
