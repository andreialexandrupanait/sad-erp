<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\OfferItem;
use App\Models\Client;
use App\Models\Contract;
use App\Models\DocumentTemplate;
use App\Models\ExchangeRate;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OfferController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['publicView', 'publicAccept', 'publicReject']);
    }

    /**
     * Display a listing of offers.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->wantsJson() || $request->ajax()) {
            return $this->indexJson($request);
        }

        $stats = Offer::getStatistics();

        return view('offers.index', compact('stats'));
    }

    /**
     * Return offers data as JSON.
     */
    private function indexJson(Request $request): JsonResponse
    {
        $query = Offer::with(['client', 'creator']);

        // Status filter
        if ($request->filled('status')) {
            $statuses = array_filter(explode(',', $request->status));
            if (!empty($statuses)) {
                $query->whereIn('status', $statuses);
            }
        }

        // Client filter
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Search
        if ($request->filled('q')) {
            $query->search($request->q);
        }

        // Sort
        $sort = $request->get('sort', 'created_at:desc');
        [$column, $direction] = $this->parseSort($sort);
        $query->orderBy($column, $direction);

        // Pagination
        $perPage = min((int) $request->get('limit', 25), 100);
        $offers = $query->paginate($perPage);

        return response()->json([
            'offers' => $offers->map(function ($offer) {
                return [
                    'id' => $offer->id,
                    'offer_number' => $offer->offer_number,
                    'title' => $offer->title,
                    'status' => $offer->status,
                    'status_label' => $offer->status_label,
                    'status_color' => $offer->status_color,
                    'total' => $offer->total,
                    'currency' => $offer->currency,
                    'valid_until' => $offer->valid_until?->format('Y-m-d'),
                    'client' => $offer->client ? [
                        'id' => $offer->client->id,
                        'name' => $offer->client->display_name,
                        'slug' => $offer->client->slug,
                    ] : null,
                    'creator' => $offer->creator ? [
                        'id' => $offer->creator->id,
                        'name' => $offer->creator->name,
                    ] : null,
                    'created_at' => $offer->created_at?->toISOString(),
                    'sent_at' => $offer->sent_at?->toISOString(),
                    'accepted_at' => $offer->accepted_at?->toISOString(),
                ];
            }),
            'pagination' => [
                'total' => $offers->total(),
                'per_page' => $offers->perPage(),
                'current_page' => $offers->currentPage(),
                'last_page' => $offers->lastPage(),
            ],
            'stats' => Offer::getStatistics(),
        ]);
    }

    private function parseSort(string $sort): array
    {
        $parts = explode(':', $sort);
        $column = $parts[0];
        $direction = $parts[1] ?? 'desc';

        $columnMap = [
            'number' => 'offer_number',
            'title' => 'title',
            'total' => 'total',
            'status' => 'status',
            'valid_until' => 'valid_until',
            'created_at' => 'created_at',
        ];

        return [
            $columnMap[$column] ?? 'created_at',
            in_array($direction, ['asc', 'desc']) ? $direction : 'desc',
        ];
    }

    /**
     * Show the form for creating a new offer.
     */
    public function create(Request $request): View
    {
        $clients = Client::orderBy('name')->get(['id', 'name', 'company_name', 'slug']);
        $templates = DocumentTemplate::active()->ofType('offer')->get();
        $services = Service::where('is_active', true)->orderBy('sort_order')->get();
        $contracts = [];

        // Pre-select client if provided
        $selectedClient = null;
        if ($request->filled('client_id')) {
            $selectedClient = Client::find($request->client_id);
        }

        // Get contracts for the client (for adding annexes)
        if ($selectedClient) {
            $contracts = Contract::where('client_id', $selectedClient->id)
                ->where('status', 'active')
                ->get();
        }

        // Get organization for logo/branding
        $organization = auth()->user()->organization;
        $offer = null;

        // Get exchange rates for currency conversion
        $exchangeRates = $this->getExchangeRatesForView();

        // Get bank accounts from organization settings
        $bankAccounts = collect($organization->settings['bank_accounts'] ?? [])
            ->filter(fn($a) => !empty($a['iban']));

        // Use builder view
        return view('offers.builder', compact('clients', 'templates', 'services', 'selectedClient', 'contracts', 'organization', 'offer', 'exchangeRates', 'bankAccounts'));
    }

    /**
     * Store a newly created offer.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        // Determine validation rules based on whether we're creating a new client
        $clientRules = $request->has('new_client') && $request->new_client
            ? ['client_id' => 'nullable']
            : ['client_id' => 'required|exists:clients,id'];

        $newClientRules = $request->has('new_client') && $request->new_client
            ? [
                'new_client' => 'required|array',
                'new_client.company_name' => 'required|string|max:255',
                'new_client.contact_person' => 'nullable|string|max:255',
                'new_client.email' => 'nullable|email|max:255',
                'new_client.phone' => 'nullable|string|max:50',
                'new_client.tax_id' => 'nullable|string|max:50',
                'new_client.address' => 'nullable|string|max:500',
            ]
            : [];

        $validated = $request->validate(array_merge($clientRules, $newClientRules, [
            'template_id' => 'nullable|exists:document_templates,id',
            'contract_id' => 'nullable|exists:contracts,id',
            'title' => 'required|string|max:255',
            'introduction' => 'nullable|string',
            'terms' => 'nullable|string',
            'blocks' => 'nullable|array',
            'valid_until' => 'required|date|after:today',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'currency' => 'required|string|size:3',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.title' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.currency' => 'nullable|string|size:3',
            'items.*.is_recurring' => 'boolean',
            'items.*.billing_cycle' => 'nullable|string',
            'items.*.custom_cycle_days' => 'nullable|integer|min:1',
            'items.*.service_id' => 'nullable|exists:services,id',
        ]));

        DB::beginTransaction();
        try {
            // Handle inline client creation
            $clientId = $validated['client_id'] ?? null;

            if (!$clientId && !empty($validated['new_client'])) {
                // Create a new client from the inline form data
                $client = Client::create([
                    'name' => $validated['new_client']['company_name'],
                    'company_name' => $validated['new_client']['company_name'],
                    'contact_person' => $validated['new_client']['contact_person'] ?? null,
                    'email' => $validated['new_client']['email'] ?? null,
                    'phone' => $validated['new_client']['phone'] ?? null,
                    'tax_id' => $validated['new_client']['tax_id'] ?? null,
                    'address' => $validated['new_client']['address'] ?? null,
                ]);
                $clientId = $client->id;
            }

            $offer = Offer::create([
                'client_id' => $clientId,
                'template_id' => $validated['template_id'] ?? null,
                'contract_id' => $validated['contract_id'] ?? null,
                'title' => $validated['title'],
                'introduction' => $validated['introduction'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'blocks' => $validated['blocks'] ?? null,
                'valid_until' => $validated['valid_until'],
                'discount_percent' => $validated['discount_percent'] ?? null,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'currency' => $validated['currency'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create items
            foreach ($validated['items'] as $index => $itemData) {
                $offer->items()->create([
                    'service_id' => $itemData['service_id'] ?? null,
                    'title' => $itemData['title'],
                    'description' => $itemData['description'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'unit' => $itemData['unit'],
                    'unit_price' => $itemData['unit_price'],
                    'currency' => $itemData['currency'] ?? $validated['currency'],
                    'is_recurring' => $itemData['is_recurring'] ?? false,
                    'billing_cycle' => $itemData['billing_cycle'] ?? null,
                    'custom_cycle_days' => $itemData['custom_cycle_days'] ?? null,
                    'sort_order' => $index,
                ]);
            }

            // Recalculate totals
            $offer->calculateTotals();

            // Log activity
            $offer->logActivity('created');

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Offer created successfully.'),
                    'offer' => $offer->load(['client', 'items']),
                ], 201);
            }

            return redirect()
                ->route('offers.show', $offer)
                ->with('success', __('Offer created successfully.'));

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Display the specified offer.
     */
    public function show(Offer $offer): View
    {
        $offer->load(['client', 'creator', 'template', 'items.service', 'activities.user', 'contract']);

        return view('offers.show', compact('offer'));
    }

    /**
     * Show the form for editing the specified offer.
     */
    public function edit(Offer $offer): View|RedirectResponse
    {
        if (!$offer->canBeEdited()) {
            return redirect()
                ->route('offers.show', $offer)
                ->with('error', __('This offer cannot be edited.'));
        }

        $offer->load(['items', 'client']);
        $clients = Client::orderBy('name')->get(['id', 'name', 'company_name', 'slug']);
        $templates = DocumentTemplate::active()->ofType('offer')->get();
        $services = Service::where('is_active', true)->orderBy('sort_order')->get();
        $contracts = Contract::where('client_id', $offer->client_id)
            ->where('status', 'active')
            ->get();

        // Get organization for logo/branding
        $organization = auth()->user()->organization;
        $selectedClient = $offer->client;

        // Get exchange rates for currency conversion
        $exchangeRates = $this->getExchangeRatesForView();

        // Get bank accounts from organization settings
        $bankAccounts = collect($organization->settings['bank_accounts'] ?? [])
            ->filter(fn($a) => !empty($a['iban']));

        // Use builder view for editing
        return view('offers.builder', compact('offer', 'clients', 'templates', 'services', 'contracts', 'organization', 'selectedClient', 'exchangeRates', 'bankAccounts'));
    }

    /**
     * Update the specified offer.
     */
    public function update(Request $request, Offer $offer): JsonResponse|RedirectResponse
    {
        if (!$offer->canBeEdited()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => __('This offer cannot be edited.')], 403);
            }
            return redirect()
                ->route('offers.show', $offer)
                ->with('error', __('This offer cannot be edited.'));
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'template_id' => 'nullable|exists:document_templates,id',
            'contract_id' => 'nullable|exists:contracts,id',
            'title' => 'required|string|max:255',
            'introduction' => 'nullable|string',
            'terms' => 'nullable|string',
            'blocks' => 'nullable|array',
            'valid_until' => 'required|date|after:today',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'currency' => 'required|string|size:3',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:offer_items,id',
            'items.*.title' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.currency' => 'nullable|string|size:3',
            'items.*.is_recurring' => 'boolean',
            'items.*.billing_cycle' => 'nullable|string',
            'items.*.custom_cycle_days' => 'nullable|integer|min:1',
            'items.*.service_id' => 'nullable|exists:services,id',
        ]);

        DB::beginTransaction();
        try {
            $offer->update([
                'client_id' => $validated['client_id'],
                'template_id' => $validated['template_id'] ?? null,
                'contract_id' => $validated['contract_id'] ?? null,
                'title' => $validated['title'],
                'introduction' => $validated['introduction'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'blocks' => $validated['blocks'] ?? null,
                'valid_until' => $validated['valid_until'],
                'discount_percent' => $validated['discount_percent'] ?? null,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'currency' => $validated['currency'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Get existing item IDs
            $existingIds = $offer->items()->pluck('id')->toArray();
            $updatedIds = [];

            // Update or create items
            foreach ($validated['items'] as $index => $itemData) {
                if (!empty($itemData['id'])) {
                    // Update existing
                    $item = $offer->items()->find($itemData['id']);
                    if ($item) {
                        $item->update([
                            'service_id' => $itemData['service_id'] ?? null,
                            'title' => $itemData['title'],
                            'description' => $itemData['description'] ?? null,
                            'quantity' => $itemData['quantity'],
                            'unit' => $itemData['unit'],
                            'unit_price' => $itemData['unit_price'],
                            'currency' => $itemData['currency'] ?? $validated['currency'],
                            'is_recurring' => $itemData['is_recurring'] ?? false,
                            'billing_cycle' => $itemData['billing_cycle'] ?? null,
                            'custom_cycle_days' => $itemData['custom_cycle_days'] ?? null,
                            'sort_order' => $index,
                        ]);
                        $updatedIds[] = $item->id;
                    }
                } else {
                    // Create new
                    $item = $offer->items()->create([
                        'service_id' => $itemData['service_id'] ?? null,
                        'title' => $itemData['title'],
                        'description' => $itemData['description'] ?? null,
                        'quantity' => $itemData['quantity'],
                        'unit' => $itemData['unit'],
                        'unit_price' => $itemData['unit_price'],
                        'currency' => $itemData['currency'] ?? $validated['currency'],
                        'is_recurring' => $itemData['is_recurring'] ?? false,
                        'billing_cycle' => $itemData['billing_cycle'] ?? null,
                        'custom_cycle_days' => $itemData['custom_cycle_days'] ?? null,
                        'sort_order' => $index,
                    ]);
                    $updatedIds[] = $item->id;
                }
            }

            // Delete removed items
            $toDelete = array_diff($existingIds, $updatedIds);
            if (!empty($toDelete)) {
                OfferItem::whereIn('id', $toDelete)->delete();
            }

            // Recalculate totals
            $offer->calculateTotals();

            // Log activity
            $offer->logActivity('updated');

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Offer updated successfully.'),
                    'offer' => $offer->fresh()->load(['client', 'items']),
                ]);
            }

            return redirect()
                ->route('offers.show', $offer)
                ->with('success', __('Offer updated successfully.'));

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Remove the specified offer.
     */
    public function destroy(Offer $offer): RedirectResponse
    {
        $offer->delete();

        return redirect()
            ->route('offers.index')
            ->with('success', __('Offer deleted successfully.'));
    }

    /**
     * Send offer to client.
     */
    public function send(Offer $offer): JsonResponse|RedirectResponse
    {
        if (!$offer->canBeSent()) {
            if (request()->expectsJson()) {
                return response()->json(['error' => __('This offer cannot be sent.')], 403);
            }
            return back()->with('error', __('This offer cannot be sent.'));
        }

        $offer->send();

        // TODO: Send email notification to client

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Offer sent successfully.'),
                'public_url' => $offer->public_url,
            ]);
        }

        return back()->with('success', __('Offer sent successfully.'));
    }

    /**
     * Duplicate an offer.
     */
    public function duplicate(Offer $offer): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $newOffer = $offer->replicate([
                'offer_number',
                'public_token',
                'status',
                'sent_at',
                'viewed_at',
                'accepted_at',
                'rejected_at',
                'accepted_from_ip',
                'verification_code',
                'verification_code_expires_at',
                'rejection_reason',
            ]);

            $newOffer->status = 'draft';
            $newOffer->valid_until = now()->addDays(30);
            $newOffer->save();

            // Duplicate items
            foreach ($offer->items as $item) {
                $newItem = $item->replicate();
                $newItem->offer_id = $newOffer->id;
                $newItem->save();
            }

            $newOffer->logActivity('created');

            DB::commit();

            return redirect()
                ->route('offers.edit', $newOffer)
                ->with('success', __('Offer duplicated successfully.'));

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Convert accepted offer to contract.
     */
    public function convertToContract(Offer $offer): RedirectResponse
    {
        if (!$offer->isAccepted()) {
            return back()->with('error', __('Only accepted offers can be converted to contracts.'));
        }

        if ($offer->contract_id) {
            return back()->with('error', __('This offer is already linked to a contract.'));
        }

        try {
            $contract = $offer->convertToContract();
            $offer->logActivity('converted');

            return redirect()
                ->route('contracts.show', $contract)
                ->with('success', __('Contract created successfully.'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Public view for client.
     */
    public function publicView(string $token): View
    {
        $offer = Offer::withoutGlobalScopes()
            ->where('public_token', $token)
            ->firstOrFail();

        // Mark as viewed
        $offer->markAsViewed(request()->ip(), request()->userAgent());

        $offer->load(['client', 'items']);

        return view('offers.public', compact('offer'));
    }

    /**
     * Public accept action.
     */
    public function publicAccept(Request $request, string $token): JsonResponse|RedirectResponse
    {
        $offer = Offer::withoutGlobalScopes()
            ->where('public_token', $token)
            ->firstOrFail();

        if (!$offer->canBeAccepted()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => __('This offer cannot be accepted.')], 403);
            }
            return back()->with('error', __('This offer cannot be accepted.'));
        }

        // Verify code if required
        if ($offer->verification_code) {
            $validated = $request->validate([
                'verification_code' => 'required|string|size:6',
            ]);

            if ($validated['verification_code'] !== $offer->verification_code) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => __('Invalid verification code.')], 422);
                }
                return back()->with('error', __('Invalid verification code.'));
            }

            if ($offer->verification_code_expires_at < now()) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => __('Verification code has expired.')], 422);
                }
                return back()->with('error', __('Verification code has expired.'));
            }
        }

        $offer->accept($request->ip());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Offer accepted successfully. Thank you!'),
            ]);
        }

        return back()->with('success', __('Offer accepted successfully. Thank you!'));
    }

    /**
     * Public reject action.
     */
    public function publicReject(Request $request, string $token): JsonResponse|RedirectResponse
    {
        $offer = Offer::withoutGlobalScopes()
            ->where('public_token', $token)
            ->firstOrFail();

        if ($offer->status !== 'sent' && $offer->status !== 'viewed') {
            if ($request->expectsJson()) {
                return response()->json(['error' => __('This offer cannot be rejected.')], 403);
            }
            return back()->with('error', __('This offer cannot be rejected.'));
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $offer->reject($validated['reason'] ?? null);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Offer rejected.'),
            ]);
        }

        return back()->with('success', __('Offer rejected.'));
    }

    /**
     * Request verification code for acceptance.
     */
    public function requestVerificationCode(string $token): JsonResponse
    {
        $offer = Offer::withoutGlobalScopes()
            ->where('public_token', $token)
            ->firstOrFail();

        if (!$offer->canBeAccepted()) {
            return response()->json(['error' => __('This offer cannot be accepted.')], 403);
        }

        $code = $offer->generateVerificationCode();

        // TODO: Send code via email/SMS to client

        return response()->json([
            'success' => true,
            'message' => __('Verification code sent.'),
        ]);
    }

    /**
     * Get exchange rates formatted for view
     */
    private function getExchangeRatesForView(): array
    {
        $rates = ExchangeRate::latest('effective_date')
            ->get()
            ->groupBy(function ($rate) {
                return $rate->from_currency . '_' . $rate->to_currency;
            })
            ->map(function ($group) {
                $latest = $group->first();
                return [
                    'rate' => (float) $latest->rate,
                    'effective_date' => $latest->effective_date->format('Y-m-d'),
                    'source' => $latest->source,
                ];
            })
            ->toArray();

        return $rates;
    }
}
