<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
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

        return view('subscriptions.index', compact('subscriptions', 'stats', 'activeFilters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('subscriptions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0|max:999999.99',
            'billing_cycle' => 'required|in:monthly,annual,custom',
            'custom_days' => 'nullable|integer|min:1|max:3650|required_if:billing_cycle,custom',
            'start_date' => 'required|date',
            'next_renewal_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:active,paused,cancelled',
            'notes' => 'nullable|string',
        ]);

        Subscription::create($validated);

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
        return view('subscriptions.edit', compact('subscription'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'vendor_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0|max:999999.99',
            'billing_cycle' => 'required|in:monthly,annual,custom',
            'custom_days' => 'nullable|integer|min:1|max:3650|required_if:billing_cycle,custom',
            'start_date' => 'required|date',
            'next_renewal_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:active,paused,cancelled',
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
