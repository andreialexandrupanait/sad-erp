<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SettingOption;
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
        $sortField = $request->get('sort', 'next_renewal_date');
        $sortDirection = $request->get('direction', 'asc');

        $allowedSorts = ['vendor_name', 'price', 'billing_cycle', 'next_renewal_date', 'status', 'created_at'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'next_renewal_date';
        }

        $query->orderBy($sortField, $sortDirection);

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

        return view('subscriptions.index', compact('subscriptions', 'stats', 'activeFilters', 'billingCycles', 'statuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $billingCycles = SettingOption::billingCycles()->get();
        $statuses = SettingOption::subscriptionStatuses()->get();

        return view('subscriptions.create', compact('billingCycles', 'statuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validBillingCycles = SettingOption::billingCycles()->pluck('value')->toArray();
        $validStatuses = SettingOption::subscriptionStatuses()->pluck('value')->toArray();

        $validated = $request->validate([
            'vendor_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0|max:999999.99',
            'billing_cycle' => ['required', Rule::in($validBillingCycles)],
            'custom_days' => 'nullable|integer|min:1|max:3650|required_if:billing_cycle,custom',
            'start_date' => 'required|date',
            'next_renewal_date' => 'required|date|after_or_equal:start_date',
            'status' => ['required', Rule::in($validStatuses)],
            'notes' => 'nullable|string',
        ]);

        $subscription = Subscription::create($validated);

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Subscription created successfully!',
                'subscription' => $subscription,
            ], 201);
        }

        return redirect()->route('subscriptions.index')
            ->with('success', 'Subscription created successfully.');
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

        return view('subscriptions.edit', compact('subscription', 'billingCycles', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subscription $subscription)
    {
        $validBillingCycles = SettingOption::billingCycles()->pluck('value')->toArray();
        $validStatuses = SettingOption::subscriptionStatuses()->pluck('value')->toArray();

        $validated = $request->validate([
            'vendor_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0|max:999999.99',
            'billing_cycle' => ['required', Rule::in($validBillingCycles)],
            'custom_days' => 'nullable|integer|min:1|max:3650|required_if:billing_cycle,custom',
            'start_date' => 'required|date',
            'next_renewal_date' => 'required|date|after_or_equal:start_date',
            'status' => ['required', Rule::in($validStatuses)],
            'notes' => 'nullable|string',
        ]);

        // Check if renewal date changed
        $renewalDateChanged = $subscription->next_renewal_date->format('Y-m-d') !== $validated['next_renewal_date'];

        if ($renewalDateChanged) {
            // Use the helper method to update and log
            $oldDate = $subscription->next_renewal_date;
            $subscription->fill($validated);
            $subscription->updateRenewalDate($validated['next_renewal_date'], 'Manual update via edit form');
        } else {
            $subscription->update($validated);
        }

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Subscription updated successfully!',
                'subscription' => $subscription->fresh(),
            ]);
        }

        return redirect()->route('subscriptions.index')
            ->with('success', 'Subscription updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subscription $subscription)
    {
        $subscription->delete();

        return redirect()->route('subscriptions.index')
            ->with('success', 'Subscription deleted successfully.');
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
                ->with('success', "Updated {$updatedCount} overdue " . ($updatedCount === 1 ? 'subscription' : 'subscriptions') . ".");
        }

        return redirect()->route('subscriptions.index')
            ->with('info', 'No overdue subscriptions to update.');
    }
}
