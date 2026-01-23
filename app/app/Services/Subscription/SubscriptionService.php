<?php

namespace App\Services\Subscription;

use App\Models\Subscription;
use App\Models\SubscriptionLog;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SubscriptionService
{
    /**
     * Get paginated subscriptions with filters applied.
     */
    public function getPaginatedSubscriptions(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Subscription::query();

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['status'])) {
            $query->status($filters['status']);
        }

        if (!empty($filters['billing_cycle'])) {
            $query->billingCycle($filters['billing_cycle']);
        }

        if (!empty($filters['renewal_range'])) {
            $query->renewalRange($filters['renewal_range']);
        }

        $sortBy = $filters['sort'] ?? 'next_renewal_date';
        $sortDir = $filters['dir'] ?? 'asc';

        $allowedSorts = ['vendor_name', 'price', 'billing_cycle', 'next_renewal_date', 'status', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'next_renewal_date';
        }

        // Always put paused and cancelled subscriptions at the end
        return $query->orderByRaw("CASE WHEN status = 'active' THEN 0 WHEN status = 'paused' THEN 1 ELSE 2 END ASC")
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get subscription statistics.
     */
    public function getStatistics(): array
    {
        return Subscription::getStatistics();
    }

    /**
     * Create a new subscription.
     */
    public function create(array $data): Subscription
    {
        return Subscription::create($data);
    }

    /**
     * Update an existing subscription.
     * Uses transaction with locking to prevent race conditions.
     */
    public function update(Subscription $subscription, array $data): Subscription
    {
        return DB::transaction(function () use ($subscription, $data) {
            // Lock the subscription row to prevent concurrent updates
            $subscription = Subscription::lockForUpdate()->find($subscription->id);

            $oldRenewalDate = $subscription->next_renewal_date;
            $oldStatus = $subscription->status;

            // Check if renewal date changed
            $renewalDateChanged = isset($data['next_renewal_date']) &&
                $subscription->next_renewal_date->format('Y-m-d') !== $data['next_renewal_date'];

            // Check if status changed
            $statusChanged = isset($data['status']) && $oldStatus !== $data['status'];

            if ($renewalDateChanged) {
                $subscription->fill($data);
                $subscription->updateRenewalDate($data['next_renewal_date'], __('Manual update from form'));
            } else {
                $subscription->update($data);
            }

            // Log status change if applicable
            if ($statusChanged) {
                $this->logStatusChange($subscription, $oldStatus, $data['status'], __('Status change from form'));
            }

            return $subscription->fresh();
        });
    }

    /**
     * Update subscription status.
     */
    public function updateStatus(Subscription $subscription, string $newStatus, string $reason = null): Subscription
    {
        $oldStatus = $subscription->status;
        $subscription->update(['status' => $newStatus]);

        if ($oldStatus !== $newStatus) {
            $this->logStatusChange($subscription, $oldStatus, $newStatus, $reason ?? __('Status update'));
        }

        return $subscription->fresh();
    }

    /**
     * Renew a subscription (advance to next billing cycle).
     */
    public function renew(Subscription $subscription): Subscription
    {
        if ($subscription->status !== 'active') {
            throw new \InvalidArgumentException(__('Only active subscriptions can be renewed.'));
        }

        $newDate = $subscription->calculateNextRenewal();
        $subscription->updateRenewalDate($newDate, __('Manual renewal'));

        return $subscription->fresh();
    }

    /**
     * Advance all overdue subscriptions.
     * Uses transaction to ensure atomicity of batch operation.
     */
    public function advanceOverdueSubscriptions(): int
    {
        return DB::transaction(function () {
            // Eager load user to avoid N+1 when logging (accesses $subscription->user->organization_id)
            $subscriptions = Subscription::with('user')
                ->where('status', 'active')
                ->where('next_renewal_date', '<', Carbon::now()->startOfDay())
                ->get();

            $count = 0;
            foreach ($subscriptions as $subscription) {
                $subscription->advanceOverdueRenewals();
                $count++;
            }

            return $count;
        });
    }

    /**
     * Delete a subscription (soft delete).
     */
    public function delete(Subscription $subscription): bool
    {
        return $subscription->delete();
    }

    /**
     * Get subscriptions due for renewal within days.
     */
    public function getUpcomingRenewals(int $days = 7): Collection
    {
        return Subscription::where('status', 'active')
            ->whereBetween('next_renewal_date', [
                Carbon::now()->startOfDay(),
                Carbon::now()->addDays($days)->endOfDay()
            ])
            ->orderBy('next_renewal_date')
            ->get();
    }

    /**
     * Log a status change.
     */
    protected function logStatusChange(Subscription $subscription, string $oldStatus, string $newStatus, string $reason): void
    {
        $statusLabels = [
            'active' => __('Active'),
            'paused' => __('Paused'),
            'cancelled' => __('Cancelled'),
        ];

        $oldLabel = $statusLabels[$oldStatus] ?? $oldStatus;
        $newLabel = $statusLabels[$newStatus] ?? $newStatus;

        SubscriptionLog::create([
            'subscription_id' => $subscription->id,
            'organization_id' => $subscription->user->organization_id ?? 1,
            'old_renewal_date' => $subscription->next_renewal_date,
            'new_renewal_date' => $subscription->next_renewal_date,
            'change_reason' => "{$reason}: {$oldLabel} → {$newLabel}",
            'changed_by_user_id' => auth()->id(),
            'changed_at' => now(),
        ]);
    }

    /**
     * Get subscription history/logs.
     */
    public function getSubscriptionHistory(Subscription $subscription): Collection
    {
        return $subscription->logs()->with('changedBy')->get();
    }
}
