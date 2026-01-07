<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\SafeJson;
use App\Http\Requests\Offer\BulkActionRequest;
use App\Http\Requests\Offer\StoreOfferRequest;
use App\Http\Requests\Offer\UpdateOfferRequest;
use App\Models\Client;
use App\Models\DocumentTemplate;
use App\Models\Offer;
use App\Models\Service;
use App\Services\Offer\OfferBulkActionService;
use App\Services\Offer\OfferPublicService;
use App\Services\Offer\OfferService;
use App\Services\Offer\SimpleBlockRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OfferController extends Controller
{
    use SafeJson;
    public function __construct(
        protected OfferService $offerService,
        protected OfferPublicService $offerPublicService,
        protected OfferBulkActionService $bulkActionService
    ) {
        $this->middleware('auth')->except(['publicView', 'publicAccept', 'publicReject']);
    }

    /**
     * Display a listing of offers.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->wantsJson() || $request->ajax()) {
            return $this->offerService->getOffersJson($request);
        }

        // Cache statistics for 5 minutes (cleared when offers are created/updated/deleted)
        $stats = cache()->remember(
            'offer_stats_' . auth()->user()->organization_id,
            now()->addMinutes(5),
            fn() => Offer::getStatistics()
        );

        // Cache client dropdown for 10 minutes
        $clients = cache()->remember(
            'client_dropdown_' . auth()->user()->organization_id,
            now()->addMinutes(10),
            fn() => Client::orderBy('name')->get(['id', 'name', 'company_name'])
        );

        return view('offers.index', compact('stats', 'clients'));
    }

    /**
     * Show the form for creating a new offer.
     */
    public function create(Request $request): View
    {
        $data = $this->offerService->getBuilderData($request->client_id);

        return view('offers.builder', $data);
    }

    /**
     * Store a newly created offer.
     */
    public function store(StoreOfferRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();
        $items = $validated['items'] ?? [];
        unset($validated['items']);

        // Handle inline client creation
        if (!empty($validated['new_client'])) {
            $client = \App\Models\Client::create([
                'name' => $validated['new_client']['company_name'],
                'company_name' => $validated['new_client']['company_name'],
                'contact_person' => $validated['new_client']['contact_person'] ?? null,
                'email' => $validated['new_client']['email'] ?? null,
                'phone' => $validated['new_client']['phone'] ?? null,
                'tax_id' => $validated['new_client']['tax_id'] ?? null,
                'address' => $validated['new_client']['address'] ?? null,
            ]);
            $validated['client_id'] = $client->id;
            unset($validated['new_client']);
        }

        $offer = $this->offerService->create($validated, $items);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Offer created successfully.'),
                'offer' => $offer,
            ], 201);
        }

        return redirect()
            ->route('offers.show', $offer)
            ->with('success', __('Offer created successfully.'));
    }

    /**
     * Display the specified offer.
     */
    public function show(Offer $offer): View
    {
        $offer = $this->offerService->getOfferForShow($offer);

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
        $data = $this->offerService->getBuilderData($offer->client_id, $offer);

        return view('offers.builder', $data);
    }

    /**
     * Update the specified offer.
     */
    public function update(UpdateOfferRequest $request, Offer $offer): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();
        $items = $validated['items'] ?? [];
        unset($validated['items']);

        $offer = $this->offerService->update($offer, $validated, $items);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Offer updated successfully.'),
                'offer' => $offer,
            ]);
        }

        return redirect()
            ->route('offers.show', $offer)
            ->with('success', __('Offer updated successfully.'));
    }

    /**
     * Remove the specified offer.
     */
    public function destroy(Offer $offer): JsonResponse|RedirectResponse
    {
        $this->offerService->delete($offer);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Offer deleted successfully.')
            ]);
        }

        return redirect()
            ->route('offers.index')
            ->with('success', __('Offer deleted successfully.'));
    }

    /**
     * Handle bulk actions on offers.
     */
    public function bulkAction(BulkActionRequest $request)
    {
        $action = $request->input('action');

        switch ($action) {
            case 'export':
                $format = $request->input('format', 'xlsx');
                $offerIds = $request->boolean('export_all') ? null : $request->input('offer_ids', []);
                $statusFilter = $request->input('status_filter');
                return $this->bulkActionService->export($offerIds, $statusFilter, $format);

            case 'delete':
                $result = $this->bulkActionService->bulkDelete($request->input('offer_ids', []));
                return response()->json($result);

            case 'status_change':
                $result = $this->bulkActionService->bulkStatusChange(
                    $request->input('offer_ids', []),
                    $request->input('new_status')
                );
                return response()->json($result);

            default:
                return response()->json(['error' => __('Invalid action.')], 400);
        }
    }

    /**
     * Legacy bulk delete endpoint.
     * @deprecated Use bulkAction with action=delete instead.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:offers,id',
        ]);

        $result = $this->bulkActionService->bulkDeleteLegacy($validated['ids']);
        return response()->json($result);
    }

    /**
     * Bulk export offers to CSV.
     */
    public function bulkExport(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:offers,id',
        ]);

        return $this->bulkActionService->export($validated['ids'], null, 'csv');
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

        try {
            $this->offerService->send($offer);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Offer sent successfully.'),
                    'public_url' => $offer->public_url,
                ]);
            }

            return back()->with('success', __('Offer sent successfully.'));
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Resend offer to client (for already sent offers).
     */
    public function resend(Offer $offer): JsonResponse|RedirectResponse
    {
        // Allow resending for sent, viewed, or even rejected/expired offers
        if ($offer->status === 'draft') {
            if (request()->expectsJson()) {
                return response()->json(['error' => __('Cannot resend a draft offer. Use Send instead.')], 403);
            }
            return back()->with('error', __('Cannot resend a draft offer. Use Send instead.'));
        }

        try {
            $this->offerService->resend($offer);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Offer resent successfully.'),
                ]);
            }

            return back()->with('success', __('Offer resent successfully.'));
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Download offer as PDF.
     */
    public function downloadPdf(Offer $offer)
    {
        try {
            $pdfPath = $this->offerService->generatePdfForDownload($offer);

            return response()->download(
                storage_path('app/' . $pdfPath),
                $offer->offer_number . '.pdf',
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            return back()->with('error', __('Failed to generate PDF: ') . $e->getMessage());
        }
    }

    /**
     * Duplicate an offer.
     */
    public function duplicate(Offer $offer): RedirectResponse
    {
        $newOffer = $this->offerService->duplicate($offer);

        return redirect()
            ->route('offers.edit', $newOffer)
            ->with('success', __('Offer duplicated successfully.'));
    }

    /**
     * Approve offer and generate contract.
     */
    public function approve(Request $request, Offer $offer): JsonResponse
    {
        try {
            $contract = $this->offerService->approveAndConvert(
                $offer,
                $request->ip(),
                $request->signature_text
            );

            return response()->json([
                'success' => true,
                'message' => __('Offer approved successfully! Contract has been generated.'),
                'contract_id' => $contract->id,
                'offer_id' => $offer->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
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
            $contract = $this->offerService->convertToContract($offer);

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
        $offer = $this->offerPublicService->getOfferByToken($token);

        // Record the view
        $this->offerPublicService->recordView($offer, request()->ip(), request()->userAgent());

        // Load relationships - scope client query to offer's organization for security
        $offer->load(['items']);
        $offer->setRelation('client', $offer->client_id
            ? \App\Models\Client::where('id', $offer->client_id)
                ->where('organization_id', $offer->organization_id)
                ->first()
            : null);
        $offer->setRelation('organization', \App\Models\Organization::find($offer->organization_id));

        return view('offers.public', compact('offer'));
    }

    /**
     * Public API endpoint to get current offer state for real-time sync.
     */
    public function publicState(string $token): JsonResponse
    {
        try {
            return response()->json($this->offerPublicService->getPublicState($token));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Public API endpoint to update customer's service selections.
     */
    public function publicUpdateSelections(Request $request, string $token): JsonResponse
    {
        $validated = $request->validate([
            'deselected_services' => 'array',
            'deselected_services.*' => 'integer',
            'selected_cards' => 'array',
            'selected_cards.*' => 'integer',
            'selected_optional_services' => 'array',
            'selected_optional_services.*' => 'string',
        ]);

        try {
            $result = $this->offerPublicService->updateSelections($token, $validated);
            return response()->json($result);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Public accept action.
     */
    public function publicAccept(Request $request, string $token): JsonResponse|RedirectResponse
    {
        try {
            $this->offerPublicService->acceptPublic(
                $token,
                $request->verification_code,
                $request->ip()
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Offer accepted successfully. Thank you!'),
                ]);
            }

            return back()->with('success', __('Offer accepted successfully. Thank you!'));
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Public reject action.
     */
    public function publicReject(Request $request, string $token): JsonResponse|RedirectResponse
    {
        try {
            $this->offerPublicService->rejectPublic($token, $request->reason);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Offer rejected.'),
                ]);
            }

            return back()->with('success', __('Offer rejected.'));
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 403);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Request verification code for acceptance.
     */
    public function requestVerificationCode(string $token): JsonResponse
    {
        try {
            $this->offerPublicService->sendVerificationCode($token);

            return response()->json([
                'success' => true,
                'message' => __('Verification code sent.'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    /**
     * Save current offer layout as a template.
     */
    public function saveAsTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'blocks' => 'required|array',
            'services' => 'nullable|array',
            'is_default' => 'boolean',
        ]);

        // Create template content (blocks + services)
        $content = $this->safeJsonEncode([
            'blocks' => $validated['blocks'],
            'services' => $validated['services'] ?? [],
        ]);

        $template = DocumentTemplate::create([
            'name' => $validated['name'],
            'type' => 'offer',
            'content' => $content,
            'is_default' => $validated['is_default'] ?? false,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Template saved successfully.'),
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
            ],
        ], 201);
    }

    // ========================================================================
    // SIMPLE BUILDER METHODS
    // ========================================================================
    //
    // The "simple*" methods provide a streamlined offer creation workflow:
    // - simpleCreate/simpleStore: Create new offer via simplified builder UI
    // - simpleEdit/simpleUpdate: Edit existing offer via simplified builder UI
    //
    // These are separate from the standard CRUD methods (create/store/edit/update)
    // which use the full-featured offer builder with all customization options.
    // ========================================================================

    /**
     * Show the simple builder for creating a new offer.
     *
     * @see create() for the full-featured offer builder
     */
    public function simpleCreate(Request $request): View
    {
        $blockRenderer = new SimpleBlockRenderer();

        $organization = auth()->user()->organization;
        $offerDefaults = $organization->settings['offer_defaults'] ?? [];

        // Cache client dropdown for 10 minutes
        $clients = cache()->remember(
            'client_dropdown_full_' . auth()->user()->organization_id,
            now()->addMinutes(10),
            fn() => Client::orderBy('name')->get(['id', 'name', 'company_name', 'email'])
        );

        // Cache predefined services for 10 minutes
        $predefinedServices = cache()->remember(
            'predefined_services_' . auth()->user()->organization_id,
            now()->addMinutes(10),
            fn() => Service::where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name', 'description', 'default_rate', 'unit', 'currency'])
        );

        $data = [
            'offer' => null,
            'clients' => $clients,
            'predefinedServices' => $predefinedServices,
            'defaultBlocks' => $blockRenderer->getDefaultBlocks(),
            'existingItems' => [],
            'selectedClientId' => $request->client_id,
            'organization' => $organization,
            'bankAccounts' => collect($organization->settings['bank_accounts'] ?? []),
            'offerDefaults' => $offerDefaults,
        ];

        return view('offers.simple-builder', $data);
    }

    /**
     * Show the simple builder for editing an existing offer.
     *
     * @see edit() for the full-featured offer builder
     */
    public function simpleEdit(Offer $offer): View|RedirectResponse
    {
        if (!$offer->canBeEdited()) {
            return redirect()
                ->route('offers.show', $offer)
                ->with('error', __('This offer cannot be edited.'));
        }

        $offer->load(['items', 'client']);
        $blockRenderer = new SimpleBlockRenderer();

        // Get blocks from offer, or use defaults
        $blocks = $this->ensureArray($offer->blocks, $blockRenderer->getDefaultBlocks());

        $organization = auth()->user()->organization;
        $offerDefaults = $organization->settings['offer_defaults'] ?? [];

        // Convert items to the format expected by the builder
        // Read type and is_selected from database
        $existingItems = $offer->items->map(function ($item, $index) {
            return [
                '_key' => $item->id ?? (now()->timestamp * 1000 + $index),
                '_type' => $item->type ?? 'custom',
                '_selected' => $item->is_selected ?? true,
                'id' => $item->id,
                'service_id' => $item->service_id,
                'title' => $item->title,
                'description' => $item->description,
                'quantity' => floatval($item->quantity),
                'unit' => $item->unit ?? 'buc',
                'unit_price' => floatval($item->unit_price),
                'discount_percent' => floatval($item->discount_percent ?? 0),
                'currency' => $item->currency ?? $offer->currency ?? 'EUR',
                'total' => floatval($item->total_price ?? ($item->quantity * $item->unit_price)),
            ];
        })->toArray();

        // Check if offer has any card-type items
        $hasCardItems = collect($existingItems)->where('_type', 'card')->isNotEmpty();

        // If no card items exist in the offer, add default card services from organization settings
        if (!$hasCardItems && !empty($offerDefaults['default_services'])) {
            $defaultCardServices = collect($offerDefaults['default_services'])
                ->filter(fn($svc) => ($svc['type'] ?? 'custom') === 'card')
                ->values();

            foreach ($defaultCardServices as $index => $svc) {
                $existingItems[] = [
                    '_key' => now()->timestamp * 1000 + 1000 + $index,
                    '_type' => 'card',
                    '_selected' => false, // Card services start unselected
                    'id' => null, // Not saved to DB yet
                    'service_id' => $svc['service_id'] ?? null,
                    'title' => $svc['title'] ?? '',
                    'description' => $svc['description'] ?? '',
                    'quantity' => 1,
                    'unit' => $svc['unit'] ?? 'buc',
                    'unit_price' => floatval($svc['unit_price'] ?? 0),
                    'discount_percent' => 0,
                    'currency' => $offer->currency ?? 'EUR',
                    'total' => floatval($svc['unit_price'] ?? 0),
                ];
            }
        }

        // Cache client dropdown for 10 minutes
        $clients = cache()->remember(
            'client_dropdown_full_' . auth()->user()->organization_id,
            now()->addMinutes(10),
            fn() => Client::orderBy('name')->get(['id', 'name', 'company_name', 'email'])
        );

        // Cache predefined services for 10 minutes
        $predefinedServices = cache()->remember(
            'predefined_services_' . auth()->user()->organization_id,
            now()->addMinutes(10),
            fn() => Service::where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name', 'description', 'default_rate', 'unit', 'currency'])
        );

        $data = [
            'offer' => $offer,
            'clients' => $clients,
            'predefinedServices' => $predefinedServices,
            'defaultBlocks' => $blocks,
            'existingItems' => $existingItems,
            'selectedClientId' => $offer->client_id,
            'organization' => $organization,
            'bankAccounts' => collect($organization->settings['bank_accounts'] ?? []),
            'offerDefaults' => $offerDefaults,
        ];

        return view('offers.simple-builder', $data);
    }

    /**
     * Store a new offer from the simple builder.
     *
     * @see store() for creating offers via the full-featured builder
     */
    public function simpleStore(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'temp_client_name' => 'nullable|string|max:255',
            'temp_client_email' => 'nullable|email|max:255',
            'temp_client_phone' => 'nullable|string|max:50',
            'temp_client_company' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'valid_until' => 'nullable|date',
            'currency' => 'required|string|max:10',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'blocks' => 'required|array',
            'header_data' => 'nullable|array',
            'items' => 'nullable|array',
            'items.*.title' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.type' => 'nullable|string|in:custom,card',
            'items.*.is_selected' => 'nullable|boolean',
        ]);

        // Validate that we have either client_id or temp_client_name
        if (empty($validated['client_id']) && empty($validated['temp_client_name'])) {
            return response()->json([
                'error' => __('Please select an existing client or enter a new client name.'),
                'errors' => ['client_id' => [__('Client is required.')]]
            ], 422);
        }

        // Create the offer
        $offer = Offer::create([
            'client_id' => $validated['client_id'] ?? null,
            'temp_client_name' => $validated['temp_client_name'] ?? null,
            'temp_client_email' => $validated['temp_client_email'] ?? null,
            'temp_client_phone' => $validated['temp_client_phone'] ?? null,
            'temp_client_company' => $validated['temp_client_company'] ?? null,
            'title' => $validated['title'],
            'valid_until' => $validated['valid_until'],
            'currency' => $validated['currency'],
            'discount_percent' => $validated['discount_percent'] ?? 0,
            'blocks' => $validated['blocks'],
            'header_data' => $validated['header_data'] ?? null,
            'status' => 'draft',
        ]);

        // Create ALL items (including card services that are not selected yet)
        $items = $validated['items'] ?? [];
        $sortOrder = 0;
        foreach ($items as $index => $itemData) {
            $quantity = $itemData['quantity'];
            $unitPrice = $itemData['unit_price'];
            $discountPercent = $itemData['discount_percent'] ?? 0;
            $subtotal = $quantity * $unitPrice;
            $discount = $subtotal * ($discountPercent / 100);

            $offer->items()->create([
                'service_id' => $itemData['service_id'] ?? null,
                'type' => $itemData['type'] ?? 'custom',
                'is_selected' => $itemData['is_selected'] ?? true,
                'title' => $itemData['title'],
                'description' => $itemData['description'] ?? null,
                'quantity' => $quantity,
                'unit' => $itemData['unit'] ?? 'buc',
                'unit_price' => $unitPrice,
                'discount_percent' => $discountPercent,
                'total_price' => $subtotal - $discount,
                'currency' => $validated['currency'],
                'sort_order' => $sortOrder++,
            ]);
        }

        // Calculate and update totals
        $this->updateOfferTotals($offer);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Offer created successfully.'),
                'offer' => $offer->fresh(['items']),
                'redirect' => route('offers.show', $offer),
            ], 201);
        }

        return redirect()
            ->route('offers.show', $offer)
            ->with('success', __('Offer created successfully.'));
    }

    /**
     * Update an offer from the simple builder.
     *
     * @see update() for updating offers via the full-featured builder
     */
    public function simpleUpdate(Request $request, Offer $offer): JsonResponse|RedirectResponse
    {
        if (!$offer->canBeEdited()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => __('This offer cannot be edited.')], 403);
            }
            return back()->with('error', __('This offer cannot be edited.'));
        }

        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'temp_client_name' => 'nullable|string|max:255',
            'temp_client_email' => 'nullable|email|max:255',
            'temp_client_phone' => 'nullable|string|max:50',
            'temp_client_company' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'valid_until' => 'nullable|date',
            'currency' => 'required|string|max:10',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'blocks' => 'required|array',
            'header_data' => 'nullable|array',
            'items' => 'nullable|array',
            'items.*.title' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.type' => 'nullable|string|in:custom,card',
            'items.*.is_selected' => 'nullable|boolean',
        ]);

        // Validate that we have either client_id or temp_client_name
        if (empty($validated['client_id']) && empty($validated['temp_client_name'])) {
            return response()->json([
                'error' => __('Please select an existing client or enter a new client name.'),
                'errors' => ['client_id' => [__('Client is required.')]]
            ], 422);
        }

        // Update the offer
        $offer->update([
            'client_id' => $validated['client_id'] ?? null,
            'temp_client_name' => $validated['temp_client_name'] ?? null,
            'temp_client_email' => $validated['temp_client_email'] ?? null,
            'temp_client_phone' => $validated['temp_client_phone'] ?? null,
            'temp_client_company' => $validated['temp_client_company'] ?? null,
            'title' => $validated['title'],
            'valid_until' => $validated['valid_until'],
            'currency' => $validated['currency'],
            'discount_percent' => $validated['discount_percent'] ?? 0,
            'blocks' => $validated['blocks'],
            'header_data' => $validated['header_data'] ?? null,
        ]);

        // Sync items - delete all and recreate ALL items (including unselected cards)
        $offer->items()->delete();

        $items = $validated['items'] ?? [];
        $sortOrder = 0;
        foreach ($items as $index => $itemData) {
            $quantity = $itemData['quantity'];
            $unitPrice = $itemData['unit_price'];
            $discountPercent = $itemData['discount_percent'] ?? 0;
            $subtotal = $quantity * $unitPrice;
            $discount = $subtotal * ($discountPercent / 100);

            $offer->items()->create([
                'service_id' => $itemData['service_id'] ?? null,
                'type' => $itemData['type'] ?? 'custom',
                'is_selected' => $itemData['is_selected'] ?? true,
                'title' => $itemData['title'],
                'description' => $itemData['description'] ?? null,
                'quantity' => $quantity,
                'unit' => $itemData['unit'] ?? 'buc',
                'unit_price' => $unitPrice,
                'discount_percent' => $discountPercent,
                'total_price' => $subtotal - $discount,
                'currency' => $validated['currency'],
                'sort_order' => $sortOrder++,
            ]);
        }

        // Calculate and update totals
        $this->updateOfferTotals($offer);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Offer updated successfully.'),
                'offer' => $offer->fresh(['items']),
            ]);
        }

        return redirect()
            ->route('offers.show', $offer)
            ->with('success', __('Offer updated successfully.'));
    }

    /**
     * Update offer totals based on items.
     */
    protected function updateOfferTotals(Offer $offer): void
    {
        $offer->refresh();

        // Only sum items where is_selected is true or null (for backwards compatibility)
        // Card-type items that are unselected should NOT be included in the total
        $subtotal = $offer->items
            ->filter(fn($item) => $item->is_selected !== false)
            ->sum('total_price');
        $discountAmount = $subtotal * (($offer->discount_percent ?? 0) / 100);

        // VAT is disabled - will be enabled from organization settings when needed
        // When enabled, it should show as "TVA - XX%" in the table
        $grandTotal = $subtotal - $discountAmount;

        $offer->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'total' => $grandTotal,
        ]);
    }

    /**
     * Upload image for offer blocks (brands, etc.)
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:5120', // 5MB max
            'type' => 'nullable|string|in:brands,general'
        ]);

        $type = $request->input('type', 'general');
        $path = $request->file('image')->store("offers/{$type}", 'public');

        return response()->json([
            'success' => true,
            'url' => Storage::url($path),
            'path' => $path,
        ]);
    }

    // =========================================================================
    // SIGNED URL METHODS
    // These methods use Laravel's signed URL verification for enhanced security.
    // The 'signed' middleware verifies the URL signature before these are called.
    // =========================================================================

    /**
     * Public view for client using signed URL.
     *
     * Security: The 'signed' middleware ensures the URL hasn't been tampered with
     * and hasn't expired. No token lookup needed - we use the offer ID directly.
     */
    public function publicViewSigned(Offer $offer): View
    {
        // Record the view using the token (for compatibility with existing service)
        $this->offerPublicService->recordView($offer, request()->ip(), request()->userAgent());

        // Load relationships - scope client query to offer's organization for security
        $offer->load(['items']);
        $offer->setRelation('client', $offer->client_id
            ? \App\Models\Client::where('id', $offer->client_id)
                ->where('organization_id', $offer->organization_id)
                ->first()
            : null);
        $offer->setRelation('organization', \App\Models\Organization::find($offer->organization_id));

        return view('offers.public', compact('offer'));
    }

    /**
     * Public API endpoint to get current offer state (signed URL version).
     */
    public function publicStateSigned(Offer $offer): JsonResponse
    {
        try {
            // Use token to maintain compatibility with existing service
            return response()->json($this->offerPublicService->getPublicState($offer->public_token));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Public API endpoint to update customer's service selections (signed URL version).
     */
    public function publicUpdateSelectionsSigned(Request $request, Offer $offer): JsonResponse
    {
        $validated = $request->validate([
            'deselected_services' => 'array',
            'deselected_services.*' => 'integer',
            'selected_cards' => 'array',
            'selected_cards.*' => 'integer',
            'selected_optional_services' => 'array',
            'selected_optional_services.*' => 'string',
        ]);

        try {
            $result = $this->offerPublicService->updateSelections($offer->public_token, $validated);
            return response()->json($result);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Public accept action (signed URL version).
     */
    public function publicAcceptSigned(Request $request, Offer $offer): JsonResponse|RedirectResponse
    {
        try {
            $this->offerPublicService->acceptPublic(
                $offer->public_token,
                $request->verification_code,
                $request->ip()
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Offer accepted successfully. Thank you!'),
                ]);
            }

            return back()->with('success', __('Offer accepted successfully. Thank you!'));
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Public reject action (signed URL version).
     */
    public function publicRejectSigned(Request $request, Offer $offer): JsonResponse|RedirectResponse
    {
        try {
            $this->offerPublicService->rejectPublic($offer->public_token, $request->reason);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Offer rejected.'),
                ]);
            }

            return back()->with('success', __('Offer rejected.'));
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 403);
            }
            return back()->with('error', $e->getMessage());
        }
    }
}
