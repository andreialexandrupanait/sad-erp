<?php

namespace App\Events\Subscription;

use App\Models\Subscription;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionOverdue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Subscription $subscription The overdue subscription
     * @param int $daysOverdue Number of days past the renewal date
     */
    public function __construct(
        public Subscription $subscription,
        public int $daysOverdue = 0
    ) {}

    /**
     * Get the notification type.
     */
    public function getNotificationType(): string
    {
        return 'subscription_overdue';
    }
}
