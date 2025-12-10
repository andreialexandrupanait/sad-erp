<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesBulkActions;
use App\Services\NomenclatureService;
use App\Services\Subscription\SubscriptionCalculationService;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use App\Models\Subscription;
use App\Models\SubscriptionLog;
use App\Http\Requests\Subscription\StoreSubscriptionRequest;
use App\Http\Requests\Subscription\UpdateSubscriptionRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    use HandlesBulkActions;

    protected NomenclatureService $nomenclatureService;
    protected SubscriptionCalculationService $calculationService;

    public function __construct(
        NomenclatureService $nomenclatureService,
        SubscriptionCalculationService $calculationService
    ) {
        $this->nomenclatureService = $nomenclatureService;
        $this->calculationService = $calculationService;
        $this->authorizeResource(Subscription::class, 'subscription');
    }

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

        // Always put cancelled (auto_renew=false) and paused subscriptions at the end
        $query->orderByRaw("CASE
                WHEN status = 'active' AND auto_renew = 1 THEN 0
                WHEN status = 'active' AND auto_renew = 0 THEN 1
                WHEN status = 'paused' THEN 2
                ELSE 3
            END ASC")
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
        $billingCycles = $this->nomenclatureService->getBillingCycles();
        $statuses = $this->nomenclatureService->getSubscriptionStatuses();
        $currencies = $this->nomenclatureService->getCurrencies();

        return view('subscriptions.index', compact('subscriptions', 'stats', 'activeFilters', 'billingCycles', 'statuses', 'currencies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $billingCycles = $this->nomenclatureService->getBillingCycles();
        $statuses = $this->nomenclatureService->getSubscriptionStatuses();
        $currencies = $this->nomenclatureService->getCurrencies();

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
        $billingCycles = $this->nomenclatureService->getBillingCycles();
        $statuses = $this->nomenclatureService->getSubscriptionStatuses();
        $currencies = $this->nomenclatureService->getCurrencies();

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
     * Cancel a subscription (disable auto-renewal)
     */
    public function cancel(Request $request, Subscription $subscription)
    {
        // Disable auto-renewal
        $subscription->update(['auto_renew' => false]);

        // Log the cancellation
        $this->logStatusChange(
            $subscription,
            'auto_renew',
            'manual_cancel',
            __('Auto-renewal cancelled by user')
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Subscription :name will cancel on :date.', [
                    'name' => $subscription->vendor_name,
                    'date' => $subscription->next_renewal_date->translatedFormat('d M Y')
                ]),
            ]);
        }

        return redirect()->route('subscriptions.index')
            ->with('success', __('Subscription :name will cancel on :date.', [
                'name' => $subscription->vendor_name,
                'date' => $subscription->next_renewal_date->translatedFormat('d M Y')
            ]));
    }

    /**
     * Reactivate a subscription (re-enable auto-renewal)
     */
    public function reactivate(Request $request, Subscription $subscription)
    {
        // Re-enable auto-renewal
        $subscription->update(['auto_renew' => true]);

        // If subscription is cancelled/paused, set back to active
        if (in_array($subscription->status, ['cancelled', 'paused'])) {
            $subscription->update(['status' => 'active']);
        }

        // Log the reactivation
        $this->logStatusChange(
            $subscription,
            'manual_cancel',
            'auto_renew',
            __('Auto-renewal reactivated by user')
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Subscription :name has been reactivated.', [
                    'name' => $subscription->vendor_name
                ]),
            ]);
        }

        return redirect()->route('subscriptions.index')
            ->with('success', __('Subscription :name has been reactivated.', [
                'name' => $subscription->vendor_name
            ]));
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

    protected function getBulkModelClass(): string
    {
        return Subscription::class;
    }

    protected function exportToCsv($subscriptions)
    {
        $filename = "subscriptions_export_" . date("Y-m-d_His") . ".csv";

        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($subscriptions) {
            $file = fopen("php://output", "w");
            fputcsv($file, ["Vendor", "Price", "Currency", "Billing Cycle", "Start Date", "Next Renewal", "Status"]);

            foreach ($subscriptions as $subscription) {
                fputcsv($file, [
                    $subscription->vendor_name,
                    $subscription->price,
                    $subscription->currency,
                    $subscription->billing_cycle,
                    $subscription->start_date?->format("Y-m-d") ?? "N/A",
                    $subscription->next_renewal_date?->format("Y-m-d") ?? "N/A",
                    $subscription->status,
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function bulkRenew(Request $request)
    {
        $validated = $request->validate([
            "ids" => "required|array|min:1|max:100",
            "ids.*" => "required|integer",
        ]);

        DB::beginTransaction();
        try {
            $subscriptions = Subscription::whereIn("id", $validated["ids"])->get();
            $count = 0;

            foreach ($subscriptions as $subscription) {
                Gate::authorize("update", $subscription);
                $subscription->calculateNextRenewal();
                $subscription->save();
                $count++;
            }

            DB::commit();
            return response()->json(["success" => true, "message" => "{$count} subscriptions renewed"]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(["success" => false, "message" => $e->getMessage()], 500);
        }
    }

    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            "ids" => "required|array|min:1|max:100",
            "ids.*" => "required|integer",
            "status" => "required|in:active,paused,cancelled",
        ]);

        $subscriptions = Subscription::whereIn("id", $validated["ids"])->get();

        foreach ($subscriptions as $subscription) {
            Gate::authorize("update", $subscription);
            $subscription->status = $validated["status"];
            $subscription->save();
        }

        return response()->json(["success" => true, "message" => count($subscriptions) . " subscriptions updated"]);
    }
}
