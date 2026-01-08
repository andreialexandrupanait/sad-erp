<?php

namespace App\Events\FinancialRevenue;

use App\Models\FinancialRevenue;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RevenueCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param FinancialRevenue $revenue The newly created revenue record
     */
    public function __construct(
        public FinancialRevenue $revenue
    ) {}

    /**
     * Get the notification type.
     */
    public function getNotificationType(): string
    {
        return 'revenue_created';
    }
}
