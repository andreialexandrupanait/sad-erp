<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionLog;
use App\Models\SettingOption;
use App\Http\Requests\Subscription\StoreSubscriptionRequest;
use App\Http\Requests\Subscription\UpdateSubscriptionRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Subscription::query();

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->status($request->status);
        }

        // Filter by billing cycle
        if ($request->filled('billing_cycle')) {
            $query->billingCycle($request->billing_cycle);
        }

        // Filter by renewal range
        if ($request->filled('renewal_range')) {
            $query->renewalRange($request->renewal_range);
        }

        // Sorting
        $sortBy = $request->get('sort', 'next_renewal_date');
        $sortDir = $request->get('dir', 'asc');

        $allowedSorts = ['vendor_name', 'price', 'billing_cycle', 'next_renewal_date', 'status', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'next_renewal_date';
        }

        // Always put paused and cancelled subscriptions at the end
        $query->orderByRaw("CASE WHEN status = 'active' THEN 0 WHEN status = 'paused' THEN 1 ELSE 2 END ASC")
              ->orderBy($sortBy, $sortDir);

        // Paginate
        $subscriptions = $query->paginate(15)->withQueryString();

        // Statistics
        $stats = Subscription::getStatistics();

        // Count active filters
        $activeFilters = collect([
            $request->search,
            $request->status,
            $request->billing_cycle,
            $request->renewal_range,
        ])->filter()->count();

        // Get nomenclature data for forms
        $billingCycles = SettingOption::billingCycles()->get();
        $statuses = SettingOption::subscriptionStatuses()->get();
        $currencies = SettingOption::currencies()->get();

        return view('subscriptions.index', compact('subscriptions', 'stats', 'activeFilters', 'billingCycles', 'statuses', 'currencies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $billingCycles = SettingOption::billingCycles()->get();
        $statuses = SettingOption::subscriptionStatuses()->get();
        $currencies = SettingOption::currencies()->get();

        return view('subscriptions.create', compact('billingCycles', 'statuses', 'currencies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSubscriptionRequest $request)
    {
        $subscription = Subscription::create($request->validated());

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Subscription created successfully.'),
                'subscription' => $subscription,
            ], 201);
        }

        return redirect()->route('subscriptions.index')
            ->with('success', __('Subscription created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Subscription $subscription)
    {
        $subscription->load('logs.changedBy');

        return view('subscriptions.show', compact('subscription'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subscription $subscription)
    {
        $billingCycles = SettingOption::billingCycles()->get();
        $statuses = SettingOption::subscriptionStatuses()->get();
        $currencies = SettingOption::currencies()->get();

        return view('subscriptions.edit', compact('subscription', 'billingCycles', 'statuses', 'currencies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSubscriptionRequest $request, Subscription $subscription)
    {
        $validated = $request->validated();

        // Check if renewal date changed
        $renewalDateChanged = $subscription->next_renewal_date->format('Y-m-d') !== $validated['next_renewal_date'];

        // Check if status changed
        $oldStatus = $subscription->status;
        $statusChanged = $oldStatus !== $validated['status'];

        if ($renewalDateChanged) {
            // Use the helper method to update and log
            $subscription->fill($validated);
            $subscription->updateRenewalDate($validated['next_renewal_date'], __('Manual update from form'));
        } else {
            $subscription->update($validated);
        }

        // Log status change if applicable
        if ($statusChanged) {
            $this->logStatusChange($subscription, $oldStatus, $validated['status'], __('Status change from form'));
        }

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Subscription updated successfully.'),
                'subscription' => $subscription->fresh(),
            ]);
        }

        return redirect()->route('subscriptions.index')
            ->with('success', __('Subscription updated successfully.'));
    }

    /**
     * Update subscription status (for AJAX requests)
     */
    public function updateStatus(Request $request, Subscription $subscription)
    {
        // Validate against the actual ENUM values in the database
        $validated = $request->validate([
            'status' => ['required', Rule::in(['active', 'paused', 'cancelled'])],
        ]);

        $oldStatus = $subscription->status;
        $subscription->update($validated);
        $newStatus = $subscription->fresh()->status;

        // Log status change
        if ($oldStatus !== $newStatus) {
            $this->logStatusChange($subscription, $oldStatus, $newStatus, __('Status change from list'));
        }

        return response()->json([
            'success' => true,
            'message' => __('Subscription status updated successfully.'),
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subscription $subscription)
    {
        $subscription->delete();

        return redirect()->route('subscriptions.index')
            ->with('success', __('Subscription deleted successfully.'));
    }

    /**
     * Manually renew a subscription (advance to next billing cycle)
     */
    public function renew(Request $request, Subscription $subscription)
    {
        // Only active subscriptions can be renewed
        if ($subscription->status !== 'active') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Only active subscriptions can be renewed.'),
                ], 422);
            }
            return redirect()->route('subscriptions.index')
                ->with('error', __('Only active subscriptions can be renewed.'));
        }

        $oldDate = $subscription->next_renewal_date;
        $newDate = $subscription->calculateNextRenewal();

        $subscription->updateRenewalDate($newDate, __('Manual renewal'));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Subscription :name has been renewed.', ['name' => $subscription->vendor_name]),
                'old_date' => $oldDate->format('Y-m-d'),
                'new_date' => $newDate->format('Y-m-d'),
            ]);
        }

        return redirect()->route('subscriptions.index')
            ->with('success', __('Subscription :name has been renewed until :date.', ['name' => $subscription->vendor_name, 'date' => $newDate->translatedFormat('d M Y')]));
    }

    /**
     * Check and advance overdue renewals
     */
    public function checkRenewals()
    {
        $subscriptions = Subscription::where('status', 'active')
            ->where('next_renewal_date', '<', Carbon::now()->startOfDay())
            ->get();

        $updatedCount = 0;

        foreach ($subscriptions as $subscription) {
            $subscription->advanceOverdueRenewals();
            $updatedCount++;
        }

        if ($updatedCount > 0) {
            return redirect()->route('subscriptions.index')
                ->with('success', __('Updated :count overdue subscription(s).', ['count' => $updatedCount]));
        }

        return redirect()->route('subscriptions.index')
            ->with('info', __('No overdue subscriptions to update.'));
    }

    /**
     * Log status changes to the subscription_logs table
     */
    private function logStatusChange(Subscription $subscription, string $oldStatus, string $newStatus, string $reason): void
    {
        if (!class_exists(SubscriptionLog::class)) {
            return;
        }

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
}
