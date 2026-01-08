<?php

namespace App\Observers;

use App\Events\Subscription\SubscriptionOverdue;
use App\Events\Subscription\SubscriptionRenewalDue;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SubscriptionObserver
{
    /**
     * Handle the Subscription "created" event.
     */
    public function created(Subscription $subscription): void
    {
        // Check if subscription is already due for renewal when created
        $this->checkRenewalStatus($subscription);
    }

    /**
     * Handle the Subscription "saved" event - clear dashboard cache.
     */
    public function saved(Subscription $subscription): void
    {
        if (Auth::check()) {
            Cache::forget('dashboard_stats_' . Auth::user()->organization_id);
        }
    }

    /**
     * Handle the Subscription "updated" event.
     */
    public function updated(Subscription $subscription): void
    {
        // Only fire events if next_renewal_date was changed
        if ($subscription->isDirty('next_renewal_date')) {
            $this->checkRenewalStatus($subscription);
        }

        // Also check if status changed to active
        if ($subscription->isDirty('status') && $subscription->status === 'active') {
            $this->checkRenewalStatus($subscription);
        }
    }

    /**
     * Check the subscription's renewal status and fire appropriate events.
     */
    protected function checkRenewalStatus(Subscription $subscription): void
    {
        // Skip during console commands (let scheduled commands handle batch notifications)
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return;
        }

        // Skip if not active
        if ($subscription->status !== 'active') {
            return;
        }

        // Skip if no renewal date
        if (!$subscription->next_renewal_date) {
            return;
        }

        $daysUntilRenewal = $subscription->days_until_renewal;

        if ($daysUntilRenewal === null) {
            return;
        }

        $urgency = $subscription->renewal_urgency;

        // Fire overdue event
        if ($urgency === 'overdue') {
            event(new SubscriptionOverdue($subscription, abs($daysUntilRenewal)));
            return;
        }

        // Fire renewal due event for urgent or warning levels
        if (in_array($urgency, ['urgent', 'warning'])) {
            event(new SubscriptionRenewalDue(
                $subscription,
                $daysUntilRenewal,
                $urgency
            ));
        }
    }
}
