<?php

namespace App\Services\Subscription;

use App\Models\Subscription;
use App\Models\SubscriptionLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Subscription Calculation Service
 *
 * Handles billing cycle calculations, renewal date updates, and overdue subscription management.
 */
class SubscriptionCalculationService
{
    /**
     * Calculate the next renewal date based on the subscription's billing cycle.
     *
     * @param Subscription $subscription The subscription to calculate for
     * @param Carbon|null $fromDate Optional base date (defaults to current next_renewal_date)
     * @return Carbon The calculated next renewal date
     */
    public function calculateNextRenewal(Subscription $subscription, ?Carbon $fromDate = null): Carbon
    {
        $baseDate = $fromDate ?? Carbon::parse($subscription->next_renewal_date);

        return match ($subscription->billing_cycle) {
            'weekly' => $baseDate->copy()->addWeek(),
            'monthly' => $baseDate->copy()->addMonth(),
            'annual' => $baseDate->copy()->addYear(),
            'custom' => $baseDate->copy()->addDays($subscription->custom_days ?? 30),
            default => $baseDate->copy()->addMonth(),
        };
    }

    /**
     * Update the renewal date for a subscription with audit logging.
     *
     * @param Subscription $subscription The subscription to update
     * @param Carbon $newDate The new renewal date
     * @param string|null $reason Optional reason for the change
     * @return void
     */
    public function updateRenewalDate(Subscription $subscription, Carbon $newDate, ?string $reason = null): void
    {
        $oldDate = $subscription->next_renewal_date;
        $subscription->next_renewal_date = $newDate;
        $subscription->save();

        SubscriptionLog::create([
            'subscription_id' => $subscription->id,
            'organization_id' => $subscription->user->organization_id ?? 1,
            'old_renewal_date' => $oldDate,
            'new_renewal_date' => $newDate,
            'change_reason' => $reason ?? __('Manual update'),
            'changed_at' => now(),
        ]);

        Log::info('Subscription renewal date updated', [
            'subscription_id' => $subscription->id,
            'old_date' => $oldDate,
            'new_date' => $newDate,
            'reason' => $reason,
        ]);
    }

    /**
     * Advance overdue subscriptions by their billing cycles until they're current.
     *
     * @param Subscription $subscription The subscription to advance
     * @return int Number of billing cycles advanced
     */
    public function advanceOverdueRenewals(Subscription $subscription): int
    {
        $today = Carbon::now()->startOfDay();
        $oldDate = Carbon::parse($subscription->next_renewal_date);
        $currentDate = $oldDate->copy();
        $cyclesAdvanced = 0;

        // Keep advancing until we're past today
        while ($currentDate->startOfDay()->lt($today)) {
            $currentDate = $this->calculateNextRenewal($subscription, $currentDate);
            $cyclesAdvanced++;

            // Safety check to prevent infinite loops
            if ($cyclesAdvanced > 1000) {
                Log::error('Subscription advancement exceeded safety limit', [
                    'subscription_id' => $subscription->id,
                    'cycles' => $cyclesAdvanced,
                ]);
                break;
            }
        }

        // Update the subscription if cycles were advanced
        if ($cyclesAdvanced > 0) {
            $subscription->next_renewal_date = $currentDate;
            $subscription->save();

            SubscriptionLog::create([
                'subscription_id' => $subscription->id,
                'organization_id' => $subscription->user->organization_id ?? 1,
                'old_renewal_date' => $oldDate,
                'new_renewal_date' => $currentDate,
                'change_reason' => __('Auto-advance :count billing cycles', ['count' => $cyclesAdvanced]),
                'changed_at' => now(),
            ]);

            Log::info('Advanced overdue subscription', [
                'subscription_id' => $subscription->id,
                'cycles_advanced' => $cyclesAdvanced,
                'old_date' => $oldDate,
                'new_date' => $currentDate,
            ]);
        }

        return $cyclesAdvanced;
    }

    /**
     * Calculate total revenue for a subscription over a period.
     *
     * @param Subscription $subscription The subscription
     * @param Carbon $startDate Period start date
     * @param Carbon $endDate Period end date
     * @return float Total projected revenue
     */
    public function calculateRevenueForPeriod(Subscription $subscription, Carbon $startDate, Carbon $endDate): float
    {
        if (!$subscription->price || $subscription->price <= 0) {
            return 0.0;
        }

        $cycles = $this->calculateCyclesInPeriod($subscription, $startDate, $endDate);
        return $cycles * $subscription->price;
    }

    /**
     * Calculate how many billing cycles occur within a date range.
     *
     * @param Subscription $subscription The subscription
     * @param Carbon $startDate Period start date
     * @param Carbon $endDate Period end date
     * @return int Number of billing cycles
     */
    public function calculateCyclesInPeriod(Subscription $subscription, Carbon $startDate, Carbon $endDate): int
    {
        $currentDate = Carbon::parse($subscription->next_renewal_date);
        $cycles = 0;

        while ($currentDate->lte($endDate)) {
            if ($currentDate->gte($startDate)) {
                $cycles++;
            }
            $currentDate = $this->calculateNextRenewal($subscription, $currentDate);
        }

        return $cycles;
    }
}
