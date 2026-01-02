<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractAnnex;
use App\Models\ContractTemplate;
use App\Models\Client;
use App\Models\Offer;
use App\Services\Contract\ContractService;
use App\Services\Contract\ContractVariableRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContractController extends Controller
{
    protected ContractService $contractService;

    public function __construct(ContractService $contractService)
    {
        $this->middleware('auth');
        $this->contractService = $contractService;
    }

    /**
     * Display a listing of contracts.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Contract::class);

        if ($request->wantsJson() || $request->ajax()) {
            return $this->indexJson($request);
        }

        $stats = Contract::getStatistics();

        return view('contracts.index', compact('stats'));
    }

    /**
     * Return contracts data as JSON.
     */
    private function indexJson(Request $request): JsonResponse
    {
        $query = Contract::with(['client', 'offer']);

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
        $contracts = $query->paginate($perPage);

        return response()->json([
            'contracts' => $contracts->map(function ($contract) {
                return [
                    'id' => $contract->id,
                    'contract_number' => $contract->contract_number,
                    'title' => $contract->title,
                    'status' => $contract->status,
                    'status_label' => $contract->status_label,
                    'status_color' => $contract->status_color,
                    'total_value' => $contract->total_value,
                    'currency' => $contract->currency,
                    'start_date' => $contract->start_date?->format('Y-m-d'),
                    'end_date' => $contract->end_date?->format('Y-m-d'),
                    'days_until_expiry' => $contract->days_until_expiry,
                    'expiry_urgency' => $contract->expiry_urgency,
                    'auto_renew' => $contract->auto_renew,
                    'client' => $contract->client ? [
                        'id' => $contract->client->id,
                        'name' => $contract->client->display_name,
                        'slug' => $contract->client->slug,
                    ] : null,
                    'has_pdf' => !empty($contract->pdf_path),
                    'created_at' => $contract->created_at?->toISOString(),
                ];
            }),
            'pagination' => [
                'total' => $contracts->total(),
                'per_page' => $contracts->perPage(),
                'current_page' => $contracts->currentPage(),
                'last_page' => $contracts->lastPage(),
            ],
            'stats' => Contract::getStatistics(),
        ]);
    }

    private function parseSort(string $sort): array
    {
        $parts = explode(':', $sort);
        $column = $parts[0];
        $direction = $parts[1] ?? 'desc';

        $columnMap = [
            'number' => 'contract_number',
            'title' => 'title',
            'total' => 'total_value',
            'status' => 'status',
            'start_date' => 'start_date',
            'end_date' => 'end_date',
            'created_at' => 'created_at',
        ];

        return [
            $columnMap[$column] ?? 'created_at',
            in_array($direction, ['asc', 'desc']) ? $direction : 'desc',
        ];
    }

    /**
     * Display the specified contract.
     */
    public function show(Contract $contract): View
    {
        $this->authorize('view', $contract);

        $contract->load(['client', 'offer.items', 'annexes.offer', 'template', 'parentContract', 'renewals']);

        return view('contracts.show', compact('contract'));
    }

    /**
     * Show the form for creating a new contract.
     */
    public function create(): View
    {
        $this->authorize('create', Contract::class);

        $clients = Client::select('id', 'name')->orderBy('name')->get();
        $templates = ContractTemplate::where('is_active', true)->orderBy('name')->get();

        return view('contracts.create', compact('clients', 'templates'));
    }

    /**
     * Store a newly created contract.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Contract::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'client_id' => 'nullable|exists:clients,id',
            'template_id' => 'nullable|exists:contract_templates,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'total_value' => 'nullable|numeric|min:0',
            'currency' => 'required|string|max:3',
            'auto_renew' => 'boolean',
            // Temp client fields (used when no client_id is selected)
            'temp_client_name' => 'nullable|required_without:client_id|string|max:255',
            'temp_client_email' => 'nullable|email|max:255',
            'temp_client_company' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $contract = new Contract();
            $contract->title = $validated['title'];
            $contract->client_id = $validated['client_id'] ?? null;
            $contract->contract_template_id = $validated['template_id'] ?? null;
            $contract->start_date = $validated['start_date'];
            $contract->end_date = $validated['end_date'] ?? null;
            $contract->total_value = $validated['total_value'] ?? 0;
            $contract->currency = $validated['currency'];
            $contract->auto_renew = $validated['auto_renew'] ?? false;
            $contract->status = 'draft';

            // Temp client fields
            if (empty($validated['client_id'])) {
                $contract->temp_client_name = $validated['temp_client_name'] ?? null;
                $contract->temp_client_email = $validated['temp_client_email'] ?? null;
                $contract->temp_client_company = $validated['temp_client_company'] ?? null;
            }

            // Apply template content if template selected
            if (!empty($validated['template_id'])) {
                $template = ContractTemplate::find($validated['template_id']);
                if ($template) {
                    $contract->content = $template->content;
                }
            }

            $contract->save();

            DB::commit();

            return redirect()
                ->route('contracts.builder', $contract)
                ->with('success', __('Contract created successfully. You can now edit its content.'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', __('Failed to create contract: ') . $e->getMessage());
        }
    }

    /**
     * Show form to add annex from an existing offer.
     */
    public function addAnnexForm(Contract $contract): View
    {
        $this->authorize('addAnnex', $contract);

        // Get accepted offers for this client that are not yet linked to this contract
        $availableOffers = Offer::where('client_id', $contract->client_id)
            ->where('status', 'accepted')
            ->where(function ($q) use ($contract) {
                $q->whereNull('contract_id')
                  ->orWhere('contract_id', '!=', $contract->id);
            })
            ->get();

        return view('contracts.add-annex', compact('contract', 'availableOffers'));
    }

    /**
     * Add annex from an accepted offer.
     */
    public function addAnnex(Request $request, Contract $contract): RedirectResponse
    {
        $this->authorize('addAnnex', $contract);

        $validated = $request->validate([
            'offer_id' => 'required|exists:offers,id',
        ]);

        $offer = Offer::findOrFail($validated['offer_id']);

        if (!$offer->isAccepted()) {
            return back()->with('error', __('Only accepted offers can be added as annexes.'));
        }

        try {
            $annex = $contract->addAnnexFromOffer($offer);

            return redirect()
                ->route('contracts.show', $contract)
                ->with('success', __('Annex added successfully.'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Terminate a contract.
     */
    public function terminate(Contract $contract): RedirectResponse
    {
        $this->authorize('terminate', $contract);

        if (!$contract->isActive()) {
            return back()->with('error', __('Only active contracts can be terminated.'));
        }

        $contract->terminate();

        return back()->with('success', __('Contract terminated successfully.'));
    }

    /**
     * Delete a contract.
     */
    public function destroy(Contract $contract): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $contract);

        // Only allow deleting draft or terminated contracts
        if ($contract->status === 'active') {
            $message = __('Active contracts cannot be deleted. Please terminate the contract first.');
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }
            return back()->with('error', $message);
        }

        // Delete the contract (observer handles PDF cleanup, offer unlinking, and annexes CASCADE)
        $contract->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Contract deleted successfully.'),
            ]);
        }

        return redirect()->route('contracts.index')->with('success', __('Contract deleted successfully.'));
    }

    /**
     * Download contract PDF.
     */
    public function downloadPdf(Contract $contract)
    {
        $this->authorize('download', $contract);

        if (!$contract->pdf_path || !file_exists(storage_path('app/' . $contract->pdf_path))) {
            return back()->with('error', __('PDF not available.'));
        }

        return response()->download(
            storage_path('app/' . $contract->pdf_path),
            $contract->contract_number . '.pdf'
        );
    }

    /**
     * Preview contract PDF inline (without saving).
     * Generates PDF in-memory for preview before finalizing.
     */
    public function previewPdf(Contract $contract)
    {
        $this->authorize('view', $contract);

        // Generate PDF preview (in-memory, no save)
        $pdf = $this->contractService->generatePdfPreview($contract);

        // Return as inline PDF for browser viewing
        return $pdf->stream($contract->contract_number . '-preview.pdf');
    }

    /**
     * Download annex PDF.
     */
    public function downloadAnnexPdf(Contract $contract, ContractAnnex $annex)
    {
        $this->authorize('download', $contract);

        if ($annex->contract_id !== $contract->id) {
            abort(404);
        }

        if (!$annex->pdf_path || !file_exists(storage_path('app/' . $annex->pdf_path))) {
            return back()->with('error', __('PDF not available.'));
        }

        return response()->download(
            storage_path('app/' . $annex->pdf_path),
            $annex->annex_code . '.pdf'
        );
    }

    /**
     * Get contracts for a specific client (API).
     */
    public function forClient(Client $client): JsonResponse
    {
        $this->authorize('viewAny', Contract::class);

        $contracts = Contract::where('client_id', $client->id)
            ->where('status', 'active')
            ->orderBy('contract_number')
            ->get(['id', 'contract_number', 'title', 'start_date', 'end_date']);

        return response()->json([
            'contracts' => $contracts,
        ]);
    }

    /**
     * Show the contract builder/editor.
     */
    public function builder(Contract $contract): View
    {
        $this->authorize('update', $contract);

        $contract->load(['client', 'offer.items', 'items', 'contractTemplate', 'organization']);

        // Get available templates
        $templates = ContractTemplate::where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get(['id', 'name', 'category', 'is_default', 'content']);

        // Get variables from centralized registry (grouped by category)
        $variables = ContractVariableRegistry::getForUI();

        // Generate services list HTML (bullet list, not table)
        $servicesHtml = ContractVariableRegistry::renderServicesList($contract);

        return view('contracts.builder', compact('contract', 'templates', 'variables', 'servicesHtml'));
    }

    /**
     * Update contract content.
     * Uses DB transaction for data integrity.
     */
    public function updateContent(Request $request, Contract $contract): JsonResponse
    {
        $this->authorize('updateContent', $contract);

        $validated = $request->validate([
            'content' => 'nullable|string|max:500000',
        ]);

        DB::transaction(function () use ($contract, $validated) {
            $contract->update([
                'content' => $validated['content'],
            ]);
        });

        // Refresh to get the saved content
        $contract->refresh();

        return response()->json([
            'success' => true,
            'message' => __('Contract content saved successfully.'),
            'content_hash' => md5($contract->content ?? ''),
        ]);
    }

    /**
     * Validate contract before PDF generation.
     */
    public function validateForPdf(Contract $contract): JsonResponse
    {
        $this->authorize('view', $contract);

        $contract->load(['client', 'offer.items', 'items', 'organization']);

        // Get validation errors from registry
        $errors = ContractVariableRegistry::validateContent(
            $contract->content ?? '',
            $contract
        );

        // Add critical data checks
        if (!$contract->client_id && !$contract->temp_client_name) {
            $errors[] = [
                'field' => 'client',
                'type' => 'missing_data',
                'message' => __('Nu este asignat niciun client contractului'),
            ];
        }

        if (empty($contract->contract_number)) {
            $errors[] = [
                'field' => 'contract_number',
                'type' => 'missing_data',
                'message' => __('Numărul contractului este obligatoriu'),
            ];
        }

        // Get warnings (non-blocking)
        $warnings = ContractVariableRegistry::getWarnings($contract);

        return response()->json([
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ]);
    }

    /**
     * Get content hash for synchronization verification.
     */
    public function getContentHash(Contract $contract): JsonResponse
    {
        $this->authorize('view', $contract);

        return response()->json([
            'hash' => md5($contract->content ?? ''),
        ]);
    }

    /**
     * Apply a template to the contract.
     */
    public function applyTemplate(Request $request, Contract $contract): JsonResponse
    {
        $this->authorize('applyTemplate', $contract);

        $validated = $request->validate([
            'template_id' => 'required|exists:contract_templates,id',
        ]);

        $template = ContractTemplate::findOrFail($validated['template_id']);

        // Get the rendered content with variables replaced
        $contract->load(['client', 'offer.items', 'items', 'organization']);
        $content = $this->contractService->renderTemplateForContract($contract, $template);

        // Update contract with template
        $contract->update([
            'contract_template_id' => $template->id,
            'content' => $content,
        ]);

        return response()->json([
            'success' => true,
            'content' => $content,
            'message' => __('Template applied successfully.'),
        ]);
    }

    /**
     * Generate PDF for contract.
     */
    public function generatePdf(Contract $contract): JsonResponse
    {
        $this->authorize('generatePdf', $contract);

        try {
            $path = $this->contractService->generatePdf($contract);

            return response()->json([
                'success' => true,
                'message' => __('PDF generated successfully.'),
                'redirect' => route('contracts.show', $contract),
                'pdf_path' => $path,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate services table HTML for a contract.
     */
    protected function generateServicesTableHtml(Contract $contract): string
    {
        if (!$contract->offer || $contract->offer->items->isEmpty()) {
            return '<p style="color: #64748b; font-style: italic;">' . __('No services available') . '</p>';
        }

        $offer = $contract->offer;
        $items = $offer->items;

        // Check if any item has discount
        $showDiscount = $items->contains(fn($item) => $item->discount_percent > 0);

        return view('contracts.partials.services-table', [
            'items' => $items,
            'subtotal' => $offer->subtotal,
            'discount' => $offer->discount_amount ?? 0,
            'total' => $offer->total,
            'currency' => $offer->currency,
            'showDiscount' => $showDiscount,
        ])->render();
    }

    /**
     * Update contract number.
     * Only allowed before finalization or PDF generation.
     */
    public function updateNumber(Request $request, Contract $contract): JsonResponse
    {
        $this->authorize('updateContent', $contract);

        // Check if editing is allowed
        if (!$contract->canEditContractNumber()) {
            return response()->json([
                'success' => false,
                'message' => __('Contract number cannot be changed after finalization or PDF generation.'),
            ], 422);
        }

        $validated = $request->validate([
            'contract_number' => 'required|string|max:100',
        ]);

        $newNumber = trim($validated['contract_number']);

        // Check uniqueness within organization
        $orgId = $contract->organization_id ?? auth()->user()->organization_id;
        if (!Contract::isContractNumberUnique($newNumber, $orgId, $contract->id)) {
            return response()->json([
                'success' => false,
                'message' => __('This contract number is already in use.'),
            ], 422);
        }

        $contract->update(['contract_number' => $newNumber]);

        return response()->json([
            'success' => true,
            'message' => __('Contract number updated successfully.'),
            'contract_number' => $contract->contract_number,
        ]);
    }

    /**
     * Finalize contract (lock contract number).
     */
    public function finalize(Contract $contract): JsonResponse
    {
        $this->authorize('finalize', $contract);

        if ($contract->is_finalized) {
            return response()->json([
                'success' => false,
                'message' => __('Contract is already finalized.'),
            ], 422);
        }

        $contract->update([
            'is_finalized' => true,
            'finalized_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Contract finalized successfully.'),
        ]);
    }

    /**
     * Finalize contract, generate PDF, and download it.
     */
    public function finalizeAndDownload(Contract $contract)
    {
        $this->authorize('finalize', $contract);

        if ($contract->is_finalized) {
            return redirect()->back()->with('error', __('Contract is already finalized.'));
        }

        if (!$contract->content) {
            return redirect()->back()->with('error', __('Contract has no content. Please edit the contract first.'));
        }

        // Finalize the contract
        $contract->update([
            'is_finalized' => true,
            'finalized_at' => now(),
            'status' => 'active', // Activate the contract when finalized
        ]);

        // Generate PDF
        try {
            $this->contractService->generatePdf($contract);
        } catch (\Exception $e) {
            \Log::error('Failed to generate contract PDF', [
                'contract_id' => $contract->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', __('Failed to generate PDF. Please try again.'));
        }

        // Refresh to get updated pdf_path
        $contract->refresh();

        // Download the PDF
        if ($contract->pdf_path && file_exists(storage_path('app/' . $contract->pdf_path))) {
            return response()->download(
                storage_path('app/' . $contract->pdf_path),
                $contract->contract_number . '.pdf',
                ['Content-Type' => 'application/pdf']
            );
        }

        return redirect()->route('contracts.show', $contract)
            ->with('success', __('Contract finalized and activated successfully.'));
    }

    /**
     * Get contract activity log.
     */
    public function activities(Contract $contract): JsonResponse
    {
        $this->authorize('view', $contract);

        $activities = $contract->activities()
            ->with('user:id,name')
            ->take(50)
            ->get()
            ->map(fn($activity) => [
                'id' => $activity->id,
                'action' => $activity->action,
                'action_label' => $activity->action_label,
                'action_icon' => $activity->action_icon,
                'action_color' => $activity->action_color,
                'description' => $activity->description,
                'performer' => $activity->performer_name,
                'metadata' => $activity->metadata,
                'changes' => $activity->changes,
                'created_at' => $activity->created_at->toISOString(),
                'created_at_human' => $activity->created_at->diffForHumans(),
            ]);

        return response()->json([
            'activities' => $activities,
        ]);
    }

    /**
     * Get contract version history.
     */
    public function versions(Contract $contract): JsonResponse
    {
        $this->authorize('view', $contract);

        $versions = $contract->versions()
            ->with('user:id,name')
            ->get()
            ->map(fn($version) => [
                'id' => $version->id,
                'version_number' => $version->version_number,
                'author' => $version->author_name,
                'reason' => $version->reason,
                'content_preview' => $version->content_preview,
                'is_current' => $version->version_number === $contract->current_version,
                'created_at' => $version->created_at->toISOString(),
                'created_at_human' => $version->created_at->diffForHumans(),
            ]);

        return response()->json([
            'versions' => $versions,
            'current_version' => $contract->current_version,
        ]);
    }

    /**
     * Get a specific version's content.
     */
    public function getVersion(Contract $contract, int $versionNumber): JsonResponse
    {
        $this->authorize('view', $contract);

        $version = $contract->versions()
            ->where('version_number', $versionNumber)
            ->first();

        if (!$version) {
            return response()->json([
                'success' => false,
                'message' => __('Version not found.'),
            ], 404);
        }

        return response()->json([
            'version' => [
                'version_number' => $version->version_number,
                'content' => $version->content,
                'blocks' => $version->blocks,
                'author' => $version->author_name,
                'reason' => $version->reason,
                'created_at' => $version->created_at->toISOString(),
            ],
        ]);
    }

    /**
     * Restore a previous version.
     */
    public function restoreVersion(Contract $contract, int $versionNumber): JsonResponse
    {
        $this->authorize('updateContent', $contract);

        $version = $contract->versions()
            ->where('version_number', $versionNumber)
            ->first();

        if (!$version) {
            return response()->json([
                'success' => false,
                'message' => __('Version not found.'),
            ], 404);
        }

        $version->restore();

        return response()->json([
            'success' => true,
            'message' => __('Version restored successfully.'),
            'content' => $contract->fresh()->content,
        ]);
    }

    /**
     * Get editing lock status.
     */
    public function lockStatus(Contract $contract): JsonResponse
    {
        $this->authorize('view', $contract);

        return response()->json($contract->getLockStatus());
    }

    /**
     * Acquire editing lock.
     */
    public function acquireLock(Contract $contract): JsonResponse
    {
        $this->authorize('updateContent', $contract);

        if ($contract->acquireLock()) {
            return response()->json([
                'success' => true,
                'message' => __('Lock acquired.'),
                'lock_status' => $contract->fresh()->getLockStatus(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('Contract is locked by another user.'),
            'lock_status' => $contract->getLockStatus(),
        ], 423); // 423 Locked
    }

    /**
     * Release editing lock.
     */
    public function releaseLock(Contract $contract): JsonResponse
    {
        $this->authorize('view', $contract);

        $contract->releaseLock(auth()->id());

        return response()->json([
            'success' => true,
            'message' => __('Lock released.'),
        ]);
    }

    /**
     * Refresh editing lock (heartbeat).
     */
    public function refreshLock(Contract $contract): JsonResponse
    {
        $this->authorize('view', $contract);

        if ($contract->refreshLock()) {
            return response()->json([
                'success' => true,
                'expires_at' => now()->addMinutes(15)->toISOString(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('You do not hold the lock.'),
        ], 403);
    }

    /**
     * Toggle auto-renew setting for a contract.
     */
    public function toggleAutoRenew(Request $request, Contract $contract): JsonResponse
    {
        $this->authorize('update', $contract);

        if (!$contract->isActive()) {
            return response()->json([
                'success' => false,
                'message' => __('Auto-renewal can only be changed for active contracts.'),
            ], 422);
        }

        if (!$contract->end_date) {
            return response()->json([
                'success' => false,
                'message' => __('Auto-renewal is not applicable for indefinite contracts.'),
            ], 422);
        }

        $validated = $request->validate([
            'auto_renew' => 'required|boolean',
        ]);

        $contract->update(['auto_renew' => $validated['auto_renew']]);

        return response()->json([
            'success' => true,
            'auto_renew' => $contract->auto_renew,
            'message' => $contract->auto_renew
                ? __('Auto-renewal enabled. Contract will automatically renew when it expires.')
                : __('Auto-renewal disabled.'),
        ]);
    }

    /**
     * Bulk delete contracts.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1|max:100',
            'ids.*' => 'required|integer',
        ]);

        $contracts = Contract::whereIn('id', $validated['ids'])->get();

        if ($contracts->count() !== count($validated['ids'])) {
            return response()->json([
                'success' => false,
                'message' => __('Some contracts were not found or you do not have access.'),
            ], 403);
        }

        // Check authorization for each contract
        foreach ($contracts as $contract) {
            $this->authorize('delete', $contract);

            // Cannot delete active contracts
            if ($contract->status === 'active') {
                return response()->json([
                    'success' => false,
                    'message' => __('Active contracts cannot be deleted. Please terminate them first.'),
                ], 422);
            }
        }

        DB::beginTransaction();

        try {
            $deletedCount = 0;

            foreach ($contracts as $contract) {
                // Delete associated PDF file if exists
                if ($contract->pdf_path && file_exists(storage_path('app/' . $contract->pdf_path))) {
                    unlink(storage_path('app/' . $contract->pdf_path));
                }

                // Unlink any associated offers
                if ($contract->offer) {
                    $contract->offer->update(['contract_id' => null]);
                }

                // Delete annexes
                $contract->annexes()->delete();

                // Delete the contract
                $contract->delete();
                $deletedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => trans_choice(':count contract deleted|:count contracts deleted', $deletedCount, ['count' => $deletedCount]),
                'count' => $deletedCount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Bulk delete contracts failed', [
                'ids' => $validated['ids'],
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('An error occurred while deleting contracts. Please try again.'),
            ], 500);
        }
    }

    /**
     * Bulk export contracts to CSV.
     */
    public function bulkExport(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1|max:100',
            'ids.*' => 'required|integer',
        ]);

        $contracts = Contract::with(['client', 'offer'])
            ->whereIn('id', $validated['ids'])
            ->get();

        if ($contracts->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => __('No contracts found.'),
            ], 404);
        }

        // Check authorization for each contract
        foreach ($contracts as $contract) {
            $this->authorize('view', $contract);
        }

        // Build CSV content
        $headers = [
            __('Contract Number'),
            __('Title'),
            __('Client'),
            __('Status'),
            __('Start Date'),
            __('End Date'),
            __('Total Value'),
            __('Currency'),
            __('Auto Renew'),
            __('Created At'),
        ];

        $csvContent = implode(',', array_map(fn($h) => '"' . str_replace('"', '""', $h) . '"', $headers)) . "\n";

        foreach ($contracts as $contract) {
            $row = [
                $contract->contract_number,
                $contract->title,
                $contract->client?->display_name ?? $contract->temp_client_name ?? '-',
                $contract->status_label,
                $contract->start_date?->format('Y-m-d') ?? '-',
                $contract->end_date?->format('Y-m-d') ?? __('Indefinite'),
                $contract->total_value,
                $contract->currency,
                $contract->auto_renew ? __('Yes') : __('No'),
                $contract->created_at?->format('Y-m-d H:i:s'),
            ];

            $csvContent .= implode(',', array_map(fn($v) => '"' . str_replace('"', '""', $v ?? '') . '"', $row)) . "\n";
        }

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="contracts-export-' . date('Y-m-d') . '.csv"',
        ]);
    }
}
