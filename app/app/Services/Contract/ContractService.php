<?php

namespace App\Services\Contract;

use App\Models\Client;
use App\Models\Contract;
use App\Models\ContractAnnex;
use App\Models\ContractItem;
use App\Models\ContractTemplate;
use App\Models\Offer;
use App\Models\Organization;
use App\Models\Template;
use App\Services\Contract\ContractVariableRegistry;
use App\Services\Document\DocumentFileService;
use App\Services\VariableRegistry;
use App\Services\Notification\NotificationService;
use App\Services\Notification\Messages\ContractExpiringMessage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Contract Service - Business logic for contract management.
 *
 * This is a unified service that handles all contract-related operations.
 * For better separation of concerns, consider extracting into focused services:
 *
 * Potential Future Architecture:
 * - ContractFactory: createFromOffer(), createDraftFromOffer(), createClientFromTempData()
 * - ContractPdfService: generatePdf(), generateAnnexPdf()
 * - ContractNotificationService: sendExpiryNotifications(), getExpiringContracts()
 * - ContractTemplateService: renderTemplateForContract(), getTemplateVariables()
 *
 * Current Responsibilities:
 * - Contract creation from offers (draft and active)
 * - PDF generation for contracts and annexes
 * - Contract renewal and termination
 * - Annex management
 * - Expiry notifications
 * - Template rendering with variable replacement
 *
 * @see ContractVariableRegistry for variable definitions and replacement
 * @see ContractTemplate for the preferred template system (vs deprecated DocumentTemplate)
 */
class ContractService
{
    public function __construct(
        protected ?NotificationService $notificationService = null,
        protected ?DocumentFileService $documentFileService = null
    ) {
        // Auto-resolve DocumentFileService if not provided
        $this->documentFileService ??= app(DocumentFileService::class);
    }

    /**
     * Create a contract from an accepted offer.
     */
    public function createFromOffer(Offer $offer): Contract
    {
        if (!$offer->isAccepted()) {
            throw new \RuntimeException(__('Only accepted offers can be converted to contracts.'));
        }

        return DB::transaction(function () use ($offer) {
            // Get next available number
            $contractNumber = Contract::generateContractNumber();

            // Hard delete any soft-deleted contract with the same number to avoid unique constraint violation
            $oldContracts = Contract::onlyTrashed()
                ->where('organization_id', $offer->organization_id)
                ->where('contract_number', $contractNumber)
                ->get();

            foreach ($oldContracts as $old) {
                // Delete PDF file if exists
                if ($old->pdf_path && Storage::exists($old->pdf_path)) {
                    Storage::delete($old->pdf_path);
                }
                // Delete related items
                $old->items()->forceDelete();
                // Force delete the contract
                $old->forceDelete();
            }

            $contract = Contract::create([
                'organization_id' => $offer->organization_id,
                'client_id' => $offer->client_id,
                'offer_id' => $offer->id,
                'template_id' => $offer->template_id,
                'contract_number' => $contractNumber,
                'title' => $offer->title,
                'content' => $offer->introduction ?? '<p>Contract generated from offer ' . $offer->offer_number . '</p>',
                'total_value' => $offer->total,
                'currency' => $offer->currency,
                'status' => 'active',
                'start_date' => now(),
            ]);

            // Link offer to contract
            $offer->update(['contract_id' => $contract->id]);

            // Generate PDF
            $this->generatePdf($contract);

            Log::info("Contract created from offer", [
                'contract_id' => $contract->id,
                'offer_id' => $offer->id,
            ]);

            return $contract;
        });
    }

    /**
     * Generate PDF for a contract.
     * Uses DocumentFileService for versioned storage.
     */
    public function generatePdf(Contract $contract): string
    {
        $contract->load(['client', 'offer.items', 'template', 'contractTemplate', 'annexes', 'organization']);

        // If contract has no content but has a contract template, render it
        $content = null;
        if (!$contract->content && $contract->contractTemplate) {
            $content = $this->renderTemplateForContract($contract, $contract->contractTemplate);
        } elseif (!$contract->content && $contract->template) {
            // Fallback to old DocumentTemplate system
            $content = $contract->template->render($this->getTemplateVariables($contract));
        }

        // Generate PDF using Dompdf
        $pdf = Pdf::loadView('contracts.pdf', [
            'contract' => $contract,
            'content' => $content,
        ]);
        $pdf->setPaper('A4', 'portrait');
        $pdfOutput = $pdf->output();

        // Store using DocumentFileService (versioned, with proper path structure)
        $documentFile = $this->documentFileService->storeDraft(
            $contract,
            $pdfOutput,
            $contract->contract_number . '.pdf'
        );

        // Also update legacy pdf_path for backward compatibility
        $contract->update(['pdf_path' => $documentFile->file_path]);

        return $documentFile->file_path;
    }

    /**
     * Generate PDF preview (in-memory, no save) for inline viewing.
     * Returns base64-encoded PDF content.
     */
    public function generatePdfPreview(Contract $contract): string
    {
        $contract->load(['client', 'offer.items', 'template', 'contractTemplate', 'annexes', 'organization', 'items']);

        // If contract has no content but has a contract template, render it
        $content = null;
        if (!$contract->content && $contract->contractTemplate) {
            $content = $this->renderTemplateForContract($contract, $contract->contractTemplate);
        } elseif (!$contract->content && $contract->template) {
            // Fallback to old DocumentTemplate system
            $content = $contract->template->render($this->getTemplateVariables($contract));
        }

        // Generate PDF in memory using Dompdf
        $pdf = Pdf::loadView('contracts.pdf', [
            'contract' => $contract,
            'content' => $content,
        ]);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('defaultFont', 'DejaVu Sans');
        $pdf->setOption('margin_top', 15);
        $pdf->setOption('margin_bottom', 15);
        $pdf->setOption('margin_left', 15);
        $pdf->setOption('margin_right', 15);
        return base64_encode($pdf->output());
    }

    /**
     * Generate PDF for a contract annex.
     * Uses DocumentFileService for versioned storage.
     */
    public function generateAnnexPdf(ContractAnnex $annex): string
    {
        $annex->load(['contract.client', 'contract.organization', 'offer.items']);

        // Render annex content with variables replaced
        $renderedContent = $this->renderAnnexContent($annex);

        // Generate PDF using Dompdf
        $pdf = Pdf::loadView('contracts.annex-pdf', [
            'annex' => $annex,
            'contract' => $annex->contract,
            'content' => $renderedContent,
        ]);
        $pdf->setPaper('A4', 'portrait');
        $pdfOutput = $pdf->output();

        // Store using DocumentFileService (versioned, with proper path structure)
        $documentFile = $this->documentFileService->storeDraft(
            $annex,
            $pdfOutput,
            $annex->annex_code . '.pdf'
        );

        // Also update legacy pdf_path for backward compatibility
        $annex->update(['pdf_path' => $documentFile->file_path]);

        return $documentFile->file_path;
    }

    /**
     * Terminate a contract.
     */
    public function terminate(Contract $contract, ?string $reason = null): void
    {
        if (!$contract->isActive()) {
            throw new \RuntimeException(__('Only active contracts can be terminated.'));
        }

        $contract->update([
            'status' => 'terminated',
        ]);

        Log::info("Contract terminated", [
            'contract_id' => $contract->id,
            'reason' => $reason,
        ]);
    }

    /**
     * Renew a contract.
     */
    public function renew(Contract $contract, ?array $data = []): Contract
    {
        return DB::transaction(function () use ($contract, $data) {
            // Expire current contract
            $contract->update([
                'status' => 'expired',
            ]);

            // Create new contract
            $newContract = $contract->replicate([
                'contract_number',
                'status',
                'start_date',
                'end_date',
                'pdf_path',
            ]);

            $newContract->contract_number = Contract::generateContractNumber();
            $newContract->status = 'active';
            $newContract->start_date = $data['start_date'] ?? ($contract->end_date ?? now());
            $newContract->end_date = $data['end_date'] ?? null;
            $newContract->parent_contract_id = $contract->id;

            if (isset($data['total_value'])) {
                $newContract->total_value = $data['total_value'];
            }

            $newContract->save();

            // Generate PDF
            $this->generatePdf($newContract);

            Log::info("Contract renewed", [
                'old_contract_id' => $contract->id,
                'new_contract_id' => $newContract->id,
            ]);

            return $newContract;
        });
    }

    /**
     * Get contracts expiring within days.
     */
    public function getExpiringContracts(int $days = 30, ?int $organizationId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Contract::where('status', 'active')
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now(), now()->addDays($days)]);

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        return $query->with('client')->orderBy('end_date')->get();
    }

    /**
     * Send expiry notifications.
     */
    public function sendExpiryNotifications(int $days = 30): int
    {
        $contracts = $this->getExpiringContracts($days);
        $count = 0;

        foreach ($contracts as $contract) {
            if ($this->notificationService) {
                $message = new ContractExpiringMessage($contract, $contract->days_until_expiry);
                $this->notificationService->send($message, null, $contract->organization_id);
                $count++;
            }
        }

        Log::info("Contract expiry notifications sent", ['count' => $count]);

        return $count;
    }

    /**
     * Get template variables for a contract.
     *
     * @deprecated Use ContractVariableRegistry::resolve() instead.
     *             This method is kept for backwards compatibility with DocumentTemplate.
     */
    protected function getTemplateVariables(Contract $contract): array
    {
        $client = $contract->client;
        $offer = $contract->offer;
        $organization = $contract->organization ?? Organization::find($contract->organization_id);

        // Handle temp client fields if no real client exists
        $clientName = $client?->display_name ?? $contract->temp_client_name ?? $offer?->temp_client_name ?? '';
        $clientCompany = $client?->company_name ?? $contract->temp_client_company ?? $offer?->temp_client_company ?? '';
        $clientEmail = $client?->email ?? $contract->temp_client_email ?? $offer?->temp_client_email ?? '';

        return [
            // Contract variables
            'contract_number' => $contract->contract_number,
            'contract_title' => $contract->title,
            'contract_date' => $contract->created_at?->format('d.m.Y'),
            'start_date' => $contract->start_date?->format('d.m.Y'),
            'end_date' => $contract->end_date?->format('d.m.Y') ?? __('Indefinite'),
            'total_value' => number_format($contract->total_value, 2, ',', '.'),
            'contract_total' => number_format($contract->total_value, 2, ',', '.'),
            'currency' => $contract->currency,
            'description' => $contract->description ?? '',

            // Client variables (supports both real clients and temp client data)
            'client_name' => $clientName,
            'client_company' => $clientCompany,
            'client_email' => $clientEmail,
            'client_phone' => $client?->phone ?? '',
            'client_address' => $client?->full_address ?? '',
            'client_fiscal_code' => $client?->fiscal_code ?? '',
            'client_tax_id' => $client?->fiscal_code ?? '',
            'client_registration_number' => $client?->registration_number ?? '',

            // Offer variables
            'offer_number' => $offer?->offer_number ?? '',
            'offer_title' => $offer?->title ?? '',
            'offer_date' => $offer?->created_at?->format('d.m.Y') ?? '',
            'offer_subtotal' => $offer ? number_format($offer->subtotal, 2, ',', '.') : '',
            'offer_discount' => $offer ? number_format($offer->discount_amount ?? 0, 2, ',', '.') : '',
            'offer_total' => $offer ? number_format($offer->total, 2, ',', '.') : '',

            // Organization variables
            'org_name' => $organization?->name ?? config('app.name'),
            'org_address' => $organization?->address ?? '',
            'org_email' => $organization?->email ?? config('mail.from.address'),
            'org_phone' => $organization?->phone ?? '',
            'org_tax_id' => $organization?->tax_id ?? '',
            'org_bank_account' => $organization?->bank_account ?? '',

            // Company (alias for organization)
            'company_name' => $organization?->name ?? config('app.name'),
            'company_email' => $organization?->email ?? config('mail.from.address'),

            // Date variables
            'current_date' => now()->format('d.m.Y'),
            'CURRENT_DATE' => now()->format('d.m.Y'),
            'current_year' => now()->year,
        ];
    }

    /**
     * Render a template for a contract with variables replaced.
     * Uses the unified VariableRegistry for consistent {{variable}} format.
     *
     * @param Contract $contract The contract to render for
     * @param ContractTemplate|Template $template The template to render
     * @return string Rendered HTML content
     */
    public function renderTemplateForContract(Contract $contract, ContractTemplate|Template $template): string
    {
        $content = $template->content ?? '';

        if (empty($content)) {
            return '';
        }

        // Use the unified VariableRegistry for variable replacement
        $content = VariableRegistry::render($content, $contract);

        // Handle legacy SERVICES_TABLE placeholder (uppercase - backwards compatibility)
        if (str_contains($content, '{{SERVICES_TABLE}}')) {
            $servicesTable = VariableRegistry::renderServicesList($contract);
            $content = str_replace('{{SERVICES_TABLE}}', $servicesTable, $content);
        }

        // Handle SIGNATURES placeholder
        if (str_contains($content, '{{SIGNATURES}}')) {
            $signatures = $this->renderSignaturesBlock($contract);
            $content = str_replace('{{SIGNATURES}}', $signatures, $content);
        }

        return $content;
    }

    /**
     * Render a unified Template for a contract.
     * Preferred method - uses new Template model.
     *
     * @param Contract $contract The contract to render for
     * @param Template $template The unified template to render
     * @return string Rendered HTML content
     */
    public function renderTemplate(Contract $contract, Template $template): string
    {
        // Use Template's built-in render method which uses VariableRegistry
        $content = $template->render($contract);

        // Handle SIGNATURES placeholder
        if (str_contains($content, '{{SIGNATURES}}')) {
            $signatures = $this->renderSignaturesBlock($contract);
            $content = str_replace('{{SIGNATURES}}', $signatures, $content);
        }

        return $content;
    }

    /**
     * Render services table HTML for a contract.
     * Uses ContractItems (self-contained) with fallback to OfferItems.
     * Only includes SELECTED items from offers.
     */
    public function renderServicesTable(Contract $contract): string
    {
        // Prefer ContractItems (self-contained data)
        $items = $contract->items;

        // Fallback to OfferItems if ContractItems don't exist
        // IMPORTANT: Only include items where is_selected = true
        if ($items->isEmpty() && $contract->offer) {
            $offerItems = $contract->offer->items ?? collect();
            // Sort: custom first, then card, then by sort_order
            $items = $offerItems->filter(fn($item) => $item->is_selected === true)
                ->sortBy([
                    ['type', 'desc'], // 'custom' comes before 'card' alphabetically reversed
                    ['sort_order', 'asc'],
                ]);
        }

        if ($items->isEmpty()) {
            return '<p><em>' . __('No services specified') . '</em></p>';
        }

        $showDiscount = $items->contains(fn($item) => ($item->discount_percent ?? 0) > 0);

        // Calculate totals from items
        $subtotal = $items->sum(fn($item) => ($item->quantity ?? 1) * ($item->unit_price ?? 0));
        $total = $items->sum(fn($item) => $item->total_price ?? $item->total ?? 0);
        $discount = $subtotal - $total;

        return view('contracts.partials.services-table', [
            'items' => $items,
            'subtotal' => $subtotal,
            'discount' => $discount > 0 ? $discount : 0,
            'total' => $total,
            'currency' => $contract->currency ?? 'EUR',
            'showDiscount' => $showDiscount,
        ])->render();
    }

    /**
     * Render signatures block HTML.
     */
    protected function renderSignaturesBlock(Contract $contract): string
    {
        $organization = $contract->organization ?? Organization::find($contract->organization_id);
        $client = $contract->client;
        $offer = $contract->offer;

        // Get client name from various sources
        $clientName = $client?->display_name
            ?? $contract->temp_client_name
            ?? $offer?->temp_client_name
            ?? '';

        return '
        <div style="margin-top: 40px; display: flex; justify-content: space-between;">
            <div style="width: 45%; border-top: 1px solid #000; padding-top: 10px;">
                <p style="margin: 0; font-weight: bold;">' . __('Provider') . '</p>
                <p style="margin: 5px 0;">' . ($organization?->name ?? config('app.name')) . '</p>
                <p style="margin: 5px 0;">' . __('Signature') . ': _______________</p>
                <p style="margin: 5px 0;">' . __('Date') . ': _______________</p>
            </div>
            <div style="width: 45%; border-top: 1px solid #000; padding-top: 10px;">
                <p style="margin: 0; font-weight: bold;">' . __('Client') . '</p>
                <p style="margin: 5px 0;">' . $clientName . '</p>
                <p style="margin: 5px 0;">' . __('Signature') . ': _______________</p>
                <p style="margin: 5px 0;">' . __('Date') . ': _______________</p>
            </div>
        </div>';
    }

    /**
     * Create a draft contract from an accepted offer with template.
     * If the offer has a temporary client (no client_id), converts it to a real client first.
     */
    public function createDraftFromOffer(Offer $offer, ?ContractTemplate $template = null): Contract
    {
        if (!$offer->isAccepted()) {
            throw new \RuntimeException(__('Only accepted offers can be converted to contracts.'));
        }

        return DB::transaction(function () use ($offer, $template) {
            // If offer has temp client but no real client, create a real client from temp data
            $clientId = $offer->client_id;
            if (!$clientId && $offer->temp_client_name) {
                $client = $this->createClientFromTempData($offer);
                $clientId = $client->id;

                // Update the offer with the new client_id
                $offer->update(['client_id' => $clientId]);
            }

            // Get default template if not provided (exclude annex templates)
            if (!$template) {
                $template = ContractTemplate::where('organization_id', $offer->organization_id)
                    ->where('is_default', true)
                    ->where('is_active', true)
                    ->where(function($q) {
                        $q->where('category', '!=', 'annex')
                           ->orWhereNull('category');
                    })
                    ->first();
            }

            // Get next available number
            $contractNumber = Contract::generateContractNumber();

            // Hard delete any soft-deleted contract with the same number to avoid unique constraint violation
            $oldContracts = Contract::onlyTrashed()
                ->where('organization_id', $offer->organization_id)
                ->where('contract_number', $contractNumber)
                ->get();

            foreach ($oldContracts as $old) {
                // Delete PDF file if exists
                if ($old->pdf_path && Storage::exists($old->pdf_path)) {
                    Storage::delete($old->pdf_path);
                }
                // Delete related items
                $old->items()->forceDelete();
                // Force delete the contract
                $old->forceDelete();
            }

            $contractData = [
                'organization_id' => $offer->organization_id,
                'client_id' => $clientId,
                'offer_id' => $offer->id,
                'template_id' => $offer->template_id,
                'contract_template_id' => $template?->id,
                'contract_number' => $contractNumber,
                'title' => $offer->title ?: __('Contract from Offer :number', ['number' => $offer->offer_number]),
                'content' => $offer->introduction ?? '<p>Contract generated from offer ' . $offer->offer_number . '</p>',
                'total_value' => $offer->total,
                'currency' => $offer->currency,
                'language' => $offer->language ?? 'ro',
                'status' => 'draft', // Start as draft so user can edit
                'start_date' => now(),
            ];

            // If still no client (shouldn't happen, but safety), store temp data on contract
            if (!$clientId) {
                $contractData['temp_client_name'] = $offer->temp_client_name;
                $contractData['temp_client_email'] = $offer->temp_client_email;
                $contractData['temp_client_company'] = $offer->temp_client_company;
                $contractData['temp_client_address'] = $offer->temp_client_address;
                $contractData['temp_client_tax_id'] = $offer->temp_client_tax_id;
                $contractData['temp_client_registration_number'] = $offer->temp_client_registration_number;
            }

            $contract = Contract::create($contractData);

            // Link offer to contract
            $offer->update(['contract_id' => $contract->id]);

            // Create ContractItems from OfferItems (only selected items)
            // Sort: custom (standard) services first, then card (extra) services
            if ($offer->items->isNotEmpty()) {
                $sortedItems = $offer->items
                    ->filter(fn($item) => $item->is_selected !== false)
                    ->sortBy([
                        ['type', 'desc'], // 'custom' comes before 'card' alphabetically reversed
                        ['sort_order', 'asc'],
                    ]);

                $sortOrder = 0;
                foreach ($sortedItems as $offerItem) {
                    ContractItem::create([
                        'contract_id' => $contract->id,
                        'offer_item_id' => $offerItem->id,
                        'service_id' => $offerItem->service_id,
                        'description' => $offerItem->title ?? $offerItem->name ?? __('Service'),
                        'quantity' => $offerItem->quantity ?? 1,
                        'unit' => $offerItem->unit ?? 'unit',
                        'unit_price' => $offerItem->unit_price ?? 0,
                        'discount_percent' => $offerItem->discount_percent ?? 0,
                        'total_price' => $offerItem->total_price ?? $offerItem->total ?? 0,
                        'sort_order' => $sortOrder++,
                    ]);
                }
            }

            // Apply template content if available
            if ($template) {
                $contract->load(['client', 'offer.items', 'items', 'organization']);
                $content = $this->renderTemplateForContract($contract, $template);
                $contract->update(['content' => $content]);
            }

            Log::info("Draft contract created from offer", [
                'contract_id' => $contract->id,
                'offer_id' => $offer->id,
                'client_id' => $clientId,
                'has_template' => $template !== null,
                'items_count' => $contract->items()->count(),
            ]);

            return $contract;
        });
    }

    /**
     * Get all active finalized contracts for the offer's client.
     * Used to present choices when converting offer to contract.
     *
     * @param Offer $offer The offer to check
     * @return \Illuminate\Database\Eloquent\Collection Active contracts collection
     */
    public function getActiveContractsForOfferClient(Offer $offer): \Illuminate\Database\Eloquent\Collection
    {
        $clientId = $offer->client_id;

        if (!$clientId) {
            return collect();
        }

        return Contract::withoutGlobalScopes()
            ->where('organization_id', $offer->organization_id)
            ->where('client_id', $clientId)
            ->where('status', 'active')
            ->where('is_finalized', true)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Process an accepted offer - creates contract or annex based on parameters.
     *
     * If offer has explicit parent_contract_id → create annex for that contract
     * If forceNewContract is true → always create new contract
     * If addToContractId is specified → add as annex to that contract
     * Else → create new contract (no longer auto-detects)
     *
     * @param Offer $offer The accepted offer to process
     * @param bool $forceNewContract If true, always create a new contract
     * @param int|null $addToContractId If set, add as annex to this contract
     * @return Contract|ContractAnnex The created contract or annex
     */
    public function processAcceptedOffer(Offer $offer, bool $forceNewContract = false, ?int $addToContractId = null): Contract|ContractAnnex
    {
        if (!$offer->isAccepted()) {
            throw new \RuntimeException(__('Only accepted offers can be processed.'));
        }

        return DB::transaction(function () use ($offer, $forceNewContract, $addToContractId) {
            // 1. EXPLICIT CHECK FIRST: If offer is linked to a parent contract (from UI)
            if ($offer->parent_contract_id && !$forceNewContract) {
                $parentContract = Contract::find($offer->parent_contract_id);
                if ($parentContract && $parentContract->canAcceptAnnex()) {
                    Log::info("Creating annex for explicitly linked contract", [
                        'offer_id' => $offer->id,
                        'parent_contract_id' => $offer->parent_contract_id,
                    ]);
                    return $this->createAnnexFromOffer($parentContract, $offer);
                }
                // If parent contract can't accept annex, log warning and proceed to create new contract
                Log::warning("Parent contract cannot accept annexes, creating new contract instead", [
                    'offer_id' => $offer->id,
                    'parent_contract_id' => $offer->parent_contract_id,
                    'parent_exists' => $parentContract !== null,
                    'can_accept' => $parentContract?->canAcceptAnnex() ?? false,
                ]);
                // Fall through to create new contract
            }

            // 2. If specific contract ID provided for annex
            if ($addToContractId && !$forceNewContract) {
                $targetContract = Contract::find($addToContractId);
                if (!$targetContract) {
                    throw new \RuntimeException(__('The specified contract was not found.'));
                }
                if (!$targetContract->canAcceptAnnex()) {
                    throw new \RuntimeException(__('The specified contract cannot accept annexes. It must be active and finalized.'));
                }
                Log::info("Creating annex for user-selected contract", [
                    'offer_id' => $offer->id,
                    'contract_id' => $addToContractId,
                ]);
                return $this->createAnnexFromOffer($targetContract, $offer);
            }

            // 3. Resolve client ID (create from temp data if needed)
            $clientId = $offer->client_id;
            if (!$clientId && $offer->temp_client_name) {
                $client = $this->createClientFromTempData($offer);
                $clientId = $client->id;
                $offer->update(['client_id' => $clientId]);
            }

            // 4. Create new draft contract (no longer auto-detecting active contracts)
            Log::info("Creating new contract", [
                'offer_id' => $offer->id,
                'client_id' => $clientId,
                'force_new' => $forceNewContract,
            ]);
            return $this->createDraftFromOffer($offer);
        });
    }

    /**
     * Find the active finalized contract for a client.
     *
     * @param int|null $clientId The client ID to search for
     * @param int $organizationId The organization ID
     * @return Contract|null The active contract or null
     */
    public function findActiveContractForClient(?int $clientId, int $organizationId): ?Contract
    {
        if (!$clientId) {
            return null;
        }

        return Contract::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->where('client_id', $clientId)
            ->where('status', 'active')
            ->where('is_finalized', true)
            ->orderByDesc('created_at')
            ->first();
    }

    /**
     * Create a contract annex from an accepted offer with template rendering.
     *
     * @param Contract $contract The parent contract
     * @param Offer $offer The accepted offer
     * @param ContractTemplate|null $template Optional annex template
     * @return ContractAnnex The created annex
     */
    public function createAnnexFromOffer(Contract $contract, Offer $offer, ?ContractTemplate $template = null): ContractAnnex
    {
        if (!$offer->isAccepted()) {
            throw new \RuntimeException(__('Only accepted offers can be added as annexes.'));
        }

        if (!$contract->canAcceptAnnex()) {
            throw new \RuntimeException(__('This contract cannot accept annexes. Only active, finalized contracts can have annexes.'));
        }

        return DB::transaction(function () use ($contract, $offer, $template) {
            $annexNumber = $contract->annexes()->count() + 1;
            // Format for UI: ANX XX la PREFIX XX din DD.MM.YYYY (e.g., ANX 05 la CTR SAD 11 din 09.01.2026)
            $contractDate = $contract->created_at ? $contract->created_at->format('d.m.Y') : now()->format('d.m.Y');

            // Get organization prefix for annex code
            $org = $contract->organization ?? Organization::find($contract->organization_id);
            $prefix = trim($org?->settings['contract_prefix'] ?? 'CTR');

            // If empty, default to CTR
            if (empty($prefix)) {
                $prefix = 'CTR';
            }

            $annexCode = sprintf('ANX %02d la %s %s din %s', $annexNumber, $prefix, $contract->sequential_number, $contractDate);

            // Try to get an annex template if not provided
            if (!$template) {
                $template = ContractTemplate::where('organization_id', $offer->organization_id)
                    ->where('category', 'annex')
                    ->where('is_active', true)
                    ->first();
            }

            // Build annex content
            $content = $offer->introduction ?? '';
            if ($template) {
                $content = $template->content ?? '';
            }

            $annex = ContractAnnex::create([
                'contract_id' => $contract->id,
                'offer_id' => $offer->id,
                'template_id' => $template?->id,
                'annex_number' => $annexNumber,
                'annex_code' => $annexCode,
                'title' => $offer->title ?: __('Annex :number to contract :contract dated :date', [
                    'number' => str_pad($annexNumber, 2, '0', STR_PAD_LEFT),
                    'contract' => $contract->contract_number,
                    'date' => $contract->created_at->format('d.m.Y'),
                ]),
                'content' => $content,
                'additional_value' => $offer->total,
                'currency' => $offer->currency,
                'effective_date' => now(),
            ]);

            // Render template content with annex variables
            if ($template || !empty($content)) {
                $annex->load(['contract.client', 'contract.organization', 'offer.items']);
                $renderedContent = $this->renderAnnexContent($annex);
                $annex->update(['content' => $renderedContent]);
            }

            // Update contract total value
            $contract->update([
                'total_value' => $contract->total_value + $offer->total,
            ]);

            // Link offer to contract
            $offer->update(['contract_id' => $contract->id]);

            // Generate PDF for annex
            $this->generateAnnexPdf($annex);

            Log::info("Contract annex created from offer", [
                'contract_id' => $contract->id,
                'annex_id' => $annex->id,
                'annex_number' => $annexNumber,
                'offer_id' => $offer->id,
            ]);

            return $annex;
        });
    }

    /**
     * Render annex content with all variables replaced.
     *
     * @param ContractAnnex $annex The annex to render
     * @return string Rendered HTML content
     */
    public function renderAnnexContent(ContractAnnex $annex): string
    {
        $content = $annex->content ?? '';
        if (empty($content)) {
            return '';
        }

        // Ensure contract has all necessary relationships loaded including organization
        $annex->load(['contract.client', 'contract.organization', 'offer.items']);

        // Get contract-level variables first (includes client and org representatives)
        $variables = ContractVariableRegistry::resolve($annex->contract);

        // Add/override annex-specific variables
        $variables['annex_number'] = $annex->annex_number;
        $variables['annex_code'] = $annex->annex_code;
        $variables['annex_date'] = $annex->effective_date?->format('d.m.Y') ?? now()->format('d.m.Y');
        $variables['annex_title'] = $annex->title ?? '';
        $variables['annex_value'] = number_format($annex->additional_value ?? 0, 2, ',', '.');
        // Use document-friendly format for parent contract
        $variables['parent_contract_number'] = $annex->contract->document_number ?? $annex->contract->contract_number;
        $variables['parent_contract_date'] = $annex->contract->created_at?->format('d.m.Y');
        $variables['new_contract_total'] = number_format(
            ($annex->contract->total_value ?? 0) + ($annex->additional_value ?? 0), 
            2, ',', '.'
        );

        // Render services list from offer items
        if ($annex->offer) {
            $variables['annex_services_list'] = $this->renderAnnexServicesList($annex);
        }

        // Replace all variables in content
        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', (string) $value, $content);
        }

        return $content;
    }

    /**
     * Render services list for an annex from its offer items.
     */
    protected function renderAnnexServicesList(ContractAnnex $annex): string
    {
        $offer = $annex->offer;
        if (!$offer || $offer->items->isEmpty()) {
            return '<p><em>' . __('Nu sunt specificate servicii') . '</em></p>';
        }

        $items = $offer->items->filter(fn($item) => $item->is_selected === true);
        if ($items->isEmpty()) {
            return '<p><em>' . __('Nu sunt specificate servicii') . '</em></p>';
        }

        $currency = e($annex->currency ?? 'EUR');
        $total = 0;

        $html = '';
        foreach ($items as $item) {
            $name = $item->title ?? $item->name ?? $item->description ?? __('Serviciu');
            $itemTotal = (float) ($item->total_price ?? $item->total ?? 0);
            $total += $itemTotal;
            $price = number_format($itemTotal, 2, ',', '.');
            $html .= '<p style="margin: 0 0 4px 20px;">&bull; <strong>' . e($name) . '</strong> - ' . $price . ' ' . $currency . '</p>';
        }

        if ($items->count() > 1) {
            $html .= '<p style="margin-top: 10px;"><strong>' . __('Total') . ': ' . number_format($total, 2, ',', '.') . ' ' . $currency . '</strong></p>';
        }

        return $html;
    }
    /**
     * Create a real client from offer's temporary client data.
     */
    protected function createClientFromTempData(Offer $offer): Client
    {
        $client = Client::create([
            'organization_id' => $offer->organization_id,
            'created_from_temp' => true,
            'name' => $offer->temp_client_name,
            'company_name' => $offer->temp_client_company,
            'email' => $offer->temp_client_email,
            'address' => $offer->temp_client_address,
            'tax_id' => $offer->temp_client_tax_id,
            'registration_number' => $offer->temp_client_registration_number,
            'status_id' => $this->getDefaultClientStatusId($offer->organization_id),
        ]);

        Log::info("Client created from temporary offer data", [
            'client_id' => $client->id,
            'offer_id' => $offer->id,
            'email' => $offer->temp_client_email,
        ]);

        return $client;
    }

    /**
     * Get the default status ID for new clients.
     */
    protected function getDefaultClientStatusId(int $organizationId): ?int
    {
        // Try to find an "Active" status for clients
        $status = \App\Models\SettingOption::where('organization_id', $organizationId)
            ->where('category', 'client_status')
            ->where('is_default', true)
            ->first();

        if (!$status) {
            // Fallback: get any active client status
            $status = \App\Models\SettingOption::where('organization_id', $organizationId)
                ->where('category', 'client_status')
                ->first();
        }

        return $status?->id;
    }

    /**
     * Get contract statistics.
     */
    public function getStatistics(?int $organizationId = null): array
    {
        $organizationId = $organizationId ?? auth()->user()->organization_id;

        return Contract::where('organization_id', $organizationId)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired,
                SUM(CASE WHEN status = 'terminated' THEN 1 ELSE 0 END) as terminated,
                SUM(CASE WHEN status = 'active' THEN total_value ELSE 0 END) as active_value,
                SUM(total_value) as total_value
            ")
            ->first()
            ->toArray();
    }
}
