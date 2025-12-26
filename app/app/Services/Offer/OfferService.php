<?php

namespace App\Services\Offer;

use App\Jobs\GenerateDocumentPdfJob;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\ExchangeRate;
use App\Models\Offer;
use App\Models\OfferItem;
use App\Models\Service;
use App\Services\Contract\ContractService;
use App\Services\Notification\NotificationService;
use App\Services\Notification\Messages\OfferSentMessage;
use App\Services\Notification\Messages\OfferAcceptedMessage;
use App\Services\Notification\Messages\OfferRejectedMessage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Offer Service - Business logic for offer management.
 *
 * Handles:
 * - Offer creation and duplication
 * - PDF generation
 * - Sending offers to clients
 * - Converting offers to contracts
 * - Activity logging
 */
class OfferService
{
    public function __construct(
        protected ?NotificationService $notificationService = null,
        protected ?ContractService $contractService = null
    ) {}

    /**
     * Create a new offer with items.
     */
    public function create(array $data, array $items = []): Offer
    {
        return DB::transaction(function () use ($data, $items) {
            // Generate offer number if not provided
            if (empty($data['offer_number'])) {
                $data['offer_number'] = Offer::generateOfferNumber();
            }

            // Generate public token
            $data['public_token'] = Str::random(64);

            // Set organization
            $data['organization_id'] = $data['organization_id'] ?? auth()->user()->organization_id;

            // Set created by
            $data['created_by'] = $data['created_by'] ?? auth()->id();

            $offer = Offer::create($data);

            // Create items
            foreach ($items as $index => $itemData) {
                $itemData['sort_order'] = $index;
                $offer->items()->create($itemData);
            }

            // Recalculate totals
            $offer->recalculateTotals();

            // Log activity
            $offer->logActivity('created', "Offer {$offer->offer_number} created");

            return $offer->fresh(['items', 'client']);
        });
    }

    /**
     * Update an offer.
     */
    public function update(Offer $offer, array $data, array $items = [], ?string $versionReason = null): Offer
    {
        return DB::transaction(function () use ($offer, $data, $items, $versionReason) {
            // Create version snapshot before updating if offer is sent/viewed
            $version = $offer->createVersion($versionReason);

            $offer->update($data);

            // Sync items if provided
            if (!empty($items)) {
                // Delete existing items
                $offer->items()->delete();

                // Create new items
                foreach ($items as $index => $itemData) {
                    $itemData['sort_order'] = $index;
                    $offer->items()->create($itemData);
                }
            }

            // Recalculate totals
            $offer->recalculateTotals();

            // Log activity with version info
            $activityMessage = $version
                ? "Offer updated (version {$version->version_number} archived)"
                : "Offer updated";
            $offer->logActivity('updated', ['message' => $activityMessage, 'version_id' => $version?->id]);

            return $offer->fresh(['items', 'client']);
        });
    }

    /**
     * Duplicate an offer.
     */
    public function duplicate(Offer $offer): Offer
    {
        return DB::transaction(function () use ($offer) {
            $newOffer = $offer->replicate([
                'offer_number',
                'public_token',
                'status',
                'sent_at',
                'viewed_at',
                'accepted_at',
                'rejected_at',
                'rejection_reason',
                'pdf_path',
                'contract_id',
            ]);

            $newOffer->offer_number = Offer::generateOfferNumber();
            $newOffer->public_token = Str::random(64);
            $newOffer->status = 'draft';
            $newOffer->created_by = auth()->id();
            $newOffer->save();

            // Duplicate items
            foreach ($offer->items as $item) {
                $newItem = $item->replicate(['offer_id']);
                $newItem->offer_id = $newOffer->id;
                $newItem->save();
            }

            // Log activity
            $newOffer->logActivity('created', "Offer duplicated from {$offer->offer_number}");

            return $newOffer->fresh(['items', 'client']);
        });
    }

    /**
     * Generate PDF for an offer.
     */
    public function generatePdf(Offer $offer): string
    {
        $offer->load(['client', 'items', 'template', 'organization']);

        // Get template content or use default
        $template = $offer->template;
        $content = $template ? $template->render($this->getTemplateVariables($offer)) : null;

        // Generate PDF
        $pdf = Pdf::loadView('offers.pdf', [
            'offer' => $offer,
            'content' => $content,
        ]);

        // Save PDF
        $filename = "offers/{$offer->organization_id}/{$offer->offer_number}.pdf";
        $path = storage_path("app/{$filename}");

        // Ensure directory exists
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $pdf->save($path);

        // Update offer with PDF path
        $offer->update(['pdf_path' => $filename]);

        return $filename;
    }

    /**
     * Send offer to client.
     */
    public function send(Offer $offer): bool
    {
        if (!$offer->isDraft() && !$offer->canBeSent()) {
            throw new \RuntimeException(__('This offer cannot be sent.'));
        }

        // Check for email: either from existing client or temp client
        $clientEmail = $offer->client?->email ?? $offer->temp_client_email;
        if (!$clientEmail) {
            throw new \RuntimeException(__('Client does not have an email address.'));
        }

        // Database operations in transaction
        DB::transaction(function () use ($offer) {
            // Mark as sent
            $offer->markAsSent();

            // Generate PDF synchronously so it's ready for the email
            GenerateDocumentPdfJob::dispatchSync($offer, Document::TYPE_OFFER_SENT);

            // Log activity
            $offer->logActivity('sent');
        });

        // Email and notifications OUTSIDE transaction to prevent rollback on email failure
        try {
            $this->sendOfferEmail($offer);

            if ($this->notificationService) {
                $message = new OfferSentMessage($offer);
                $this->notificationService->send($message);
            }
        } catch (\Exception $e) {
            // Log email failure but don't roll back the offer status change
            Log::error("Failed to send offer email after status update", [
                'offer_id' => $offer->id,
                'error' => $e->getMessage(),
            ]);
            // Re-throw so caller knows email failed
            throw $e;
        }

        return true;
    }

    /**
     * Send offer email to client.
     */
    protected function sendOfferEmail(Offer $offer): void
    {
        $offer->load(['client', 'organization']);

        // Get email and name from client or temp client fields
        $clientEmail = $offer->client?->email ?? $offer->temp_client_email;
        $clientName = $offer->client?->display_name ?? $offer->temp_client_name ?? '';

        // Find the generated PDF document
        $pdfDocument = Document::where('documentable_type', Offer::class)
            ->where('documentable_id', $offer->id)
            ->where('type', Document::TYPE_OFFER_SENT)
            ->latest()
            ->first();

        try {
            // Configure SMTP from database settings if enabled
            $this->configureSmtpFromDatabase();

            Mail::send('emails.offer-sent', [
                'offer' => $offer,
                'publicUrl' => $offer->public_url,
                'organization' => $offer->organization,
            ], function ($mail) use ($offer, $clientEmail, $clientName, $pdfDocument) {
                $mail->to($clientEmail, $clientName)
                    ->subject(__('Offer :number from :company', [
                        'number' => $offer->offer_number,
                        'company' => $offer->organization->name ?? config('app.name'),
                    ]));

                // Attach PDF if exists
                if ($pdfDocument && $pdfDocument->file_path && file_exists(storage_path('app/' . $pdfDocument->file_path))) {
                    $mail->attach(storage_path('app/' . $pdfDocument->file_path), [
                        'as' => $offer->offer_number . '.pdf',
                        'mime' => 'application/pdf',
                    ]);
                }
            });

            Log::info("Offer email sent", [
                'offer_id' => $offer->id,
                'client_email' => $clientEmail,
                'pdf_attached' => $pdfDocument ? true : false,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send offer email", [
                'offer_id' => $offer->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Configure SMTP from database settings if enabled.
     */
    protected function configureSmtpFromDatabase(): void
    {
        $smtpEnabled = \App\Models\ApplicationSetting::get('smtp_enabled', false);

        if ($smtpEnabled) {
            $smtpHost = \App\Models\ApplicationSetting::get('smtp_host');
            $smtpPort = \App\Models\ApplicationSetting::get('smtp_port', 587);
            $smtpUsername = \App\Models\ApplicationSetting::get('smtp_username');
            $smtpPassword = \App\Models\ApplicationSetting::get('smtp_password');
            $smtpEncryption = \App\Models\ApplicationSetting::get('smtp_encryption', 'tls');
            $fromEmail = \App\Models\ApplicationSetting::get('smtp_from_email');
            $fromName = \App\Models\ApplicationSetting::get('smtp_from_name', config('app.name'));

            // Decrypt password if encrypted
            if ($smtpPassword) {
                try {
                    $smtpPassword = decrypt($smtpPassword);
                } catch (\Exception $e) {
                    // Password might not be encrypted
                }
            }

            // Configure SMTP on the fly
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $smtpHost,
                'mail.mailers.smtp.port' => (int) $smtpPort,
                'mail.mailers.smtp.username' => $smtpUsername,
                'mail.mailers.smtp.password' => $smtpPassword,
                'mail.mailers.smtp.encryption' => $smtpEncryption === 'none' ? null : $smtpEncryption,
                'mail.from.address' => $fromEmail ?: $smtpUsername,
                'mail.from.name' => $fromName,
            ]);

            Log::info('SMTP configured from database settings', ['host' => $smtpHost, 'from' => $fromEmail]);
        }
    }

    /**
     * Resend offer to client (for already sent offers).
     */
    public function resend(Offer $offer): bool
    {
        // Check for email: either from existing client or temp client
        $clientEmail = $offer->client?->email ?? $offer->temp_client_email;
        if (!$clientEmail) {
            throw new \RuntimeException(__('Client does not have an email address.'));
        }

        // Log activity
        $offer->logActivity('resent');

        // Send email notification (reuse existing PDF if available)
        $this->sendOfferEmail($offer);

        // Update sent_at timestamp
        $offer->update(['sent_at' => now()]);

        return true;
    }

    /**
     * Generate PDF for download (creates or returns existing).
     */
    public function generatePdfForDownload(Offer $offer): string
    {
        // Check if we already have a PDF
        $existingDocument = Document::where('documentable_type', Offer::class)
            ->where('documentable_id', $offer->id)
            ->whereIn('type', [Document::TYPE_OFFER_SENT, Document::TYPE_OFFER_ACCEPTED])
            ->latest()
            ->first();

        if ($existingDocument && $existingDocument->fileExists()) {
            return $existingDocument->file_path;
        }

        // Generate new PDF synchronously
        GenerateDocumentPdfJob::dispatchSync($offer, Document::TYPE_OFFER_SENT);

        // Get the newly created document
        $newDocument = Document::where('documentable_type', Offer::class)
            ->where('documentable_id', $offer->id)
            ->where('type', Document::TYPE_OFFER_SENT)
            ->latest()
            ->first();

        if (!$newDocument || !$newDocument->fileExists()) {
            throw new \RuntimeException(__('Failed to generate PDF.'));
        }

        return $newDocument->file_path;
    }

    /**
     * Record offer view.
     */
    public function recordView(Offer $offer, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        try {
            $isFirstView = !$offer->viewed_at;

            // Update viewed_at without triggering global scopes
            if ($isFirstView) {
                Offer::withoutGlobalScopes()
                    ->where('id', $offer->id)
                    ->update(['viewed_at' => now(), 'status' => 'viewed']);
                $offer->viewed_at = now();
                $offer->status = 'viewed';
            }

            // Log activity (may fail if no auth user, that's ok for public view)
            $offer->logActivity('viewed', [
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);

            // Send admin notification on first view
            if ($isFirstView) {
                $this->sendAdminViewedEmail($offer);
            }
        } catch (\Exception $e) {
            // Log but don't fail - view recording is not critical
            Log::warning('Failed to record offer view', [
                'offer_id' => $offer->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Accept an offer.
     */
    public function accept(Offer $offer, ?string $ipAddress = null, bool $createContract = true): ?Contract
    {
        if (!$offer->canBeAccepted()) {
            throw new \RuntimeException(__('This offer cannot be accepted.'));
        }

        $contract = null;

        DB::transaction(function () use ($offer, $ipAddress, $createContract, &$contract) {
            $offer->markAsAccepted($ipAddress);

            // Dispatch PDF generation for accepted version
            GenerateDocumentPdfJob::dispatch($offer, Document::TYPE_OFFER_ACCEPTED);

            // Log activity
            $offer->logActivity('accepted', ['ip_address' => $ipAddress]);

            // Auto-create draft contract if enabled and contract service is available
            if ($createContract && $this->contractService && !$offer->contract_id) {
                try {
                    $contract = $this->contractService->createDraftFromOffer($offer);
                    Log::info("Draft contract auto-created from accepted offer", [
                        'offer_id' => $offer->id,
                        'contract_id' => $contract->id,
                    ]);
                } catch (\Exception $e) {
                    // Log but don't fail the acceptance
                    Log::warning("Failed to auto-create contract from offer", [
                        'offer_id' => $offer->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });

        // Send email notifications (outside transaction)
        $this->sendAdminAcceptedEmail($offer);
        $this->sendClientAcceptedEmail($offer);

        // Send internal notification
        if ($this->notificationService) {
            $message = new OfferAcceptedMessage($offer);
            $this->notificationService->send($message);
        }

        return $contract;
    }

    /**
     * Reject an offer.
     */
    public function reject(Offer $offer, ?string $reason = null): void
    {
        if (!$offer->canBeRejected()) {
            throw new \RuntimeException(__('This offer cannot be rejected.'));
        }

        $offer->markAsRejected($reason);

        // Log activity
        $offer->logActivity('rejected', ['reason' => $reason]);

        // Send email notifications
        $this->sendAdminRejectedEmail($offer);
        $this->sendClientRejectedEmail($offer);

        // Send internal notification
        if ($this->notificationService) {
            $message = new OfferRejectedMessage($offer);
            $this->notificationService->send($message);
        }
    }

    /**
     * Convert accepted offer to contract with options.
     */
    public function convertToContract(Offer $offer, array $options = []): Contract
    {
        if (!$offer->isAccepted()) {
            throw new \RuntimeException(__('Only accepted offers can be converted to contracts.'));
        }

        if ($offer->contract_id) {
            throw new \RuntimeException(__('This offer has already been converted to a contract.'));
        }

        return DB::transaction(function () use ($offer, $options) {
            $contract = $offer->convertToContract($options);

            // Log activity on offer
            $offer->logActivity(
                'converted_to_contract',
                [
                    'contract_id' => $contract->id,
                    'contract_number' => $contract->contract_number,
                    'items_copied' => $contract->items()->count(),
                ]
            );

            return $contract;
        });
    }

    /**
     * Get template variables for an offer.
     */
    protected function getTemplateVariables(Offer $offer): array
    {
        $client = $offer->client;

        return [
            // Offer variables
            'offer_number' => $offer->offer_number,
            'offer_date' => $offer->offer_date?->format('d.m.Y'),
            'valid_until' => $offer->valid_until?->format('d.m.Y'),
            'subtotal' => number_format($offer->subtotal, 2, ',', '.'),
            'tax_amount' => number_format($offer->tax_amount, 2, ',', '.'),
            'discount_amount' => number_format($offer->discount_amount, 2, ',', '.'),
            'total' => number_format($offer->total, 2, ',', '.'),
            'currency' => $offer->currency,
            'notes' => $offer->notes ?? '',
            'terms' => $offer->terms ?? '',

            // Client variables
            'client_name' => $client?->display_name ?? '',
            'client_company' => $client?->company_name ?? '',
            'client_email' => $client?->email ?? '',
            'client_phone' => $client?->phone ?? '',
            'client_address' => $client?->full_address ?? '',
            'client_fiscal_code' => $client?->fiscal_code ?? '',
            'client_registration_number' => $client?->registration_number ?? '',

            // Company variables
            'company_name' => config('app.name'),
            'company_email' => config('mail.from.address'),

            // Date variables
            'current_date' => now()->format('d.m.Y'),
            'current_year' => now()->year,
        ];
    }

    /**
     * Get offer statistics.
     */
    public function getStatistics(?int $organizationId = null): array
    {
        $organizationId = $organizationId ?? auth()->user()->organization_id;

        return Offer::where('organization_id', $organizationId)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = 'viewed' THEN 1 ELSE 0 END) as viewed,
                SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired,
                SUM(CASE WHEN status = 'accepted' THEN total ELSE 0 END) as accepted_value,
                SUM(CASE WHEN status IN ('sent', 'viewed') THEN total ELSE 0 END) as pending_value
            ")
            ->first()
            ->toArray();
    }

    /**
     * Get offers list as JSON for index page.
     */
    public function getOffersJson(Request $request): JsonResponse
    {
        $query = Offer::with(['client', 'creator']);

        // Filter by status
        if ($request->filled('status')) {
            $statuses = array_filter(explode(',', $request->status));
            if (!empty($statuses)) {
                $query->whereIn('status', $statuses);
            }
        }

        // Filter by client
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

        // Paginate
        $perPage = min((int) $request->get('limit', 25), 100);
        $offers = $query->paginate($perPage);

        return response()->json([
            'offers' => $offers->map(fn($offer) => $this->formatOfferForList($offer)),
            'pagination' => [
                'total' => $offers->total(),
                'per_page' => $offers->perPage(),
                'current_page' => $offers->currentPage(),
                'last_page' => $offers->lastPage(),
                'from' => $offers->firstItem(),
                'to' => $offers->lastItem(),
            ],
            'stats' => Offer::getStatistics(),
        ]);
    }

    /**
     * Get data for builder view.
     */
    public function getBuilderData(?int $clientId = null, ?Offer $offer = null): array
    {
        $organization = auth()->user()->organization;

        $clients = Client::orderBy('name')->get(['id', 'name', 'company_name', 'slug']);
        $templates = DocumentTemplate::active()->ofType('offer')->get();
        $services = Service::where('is_active', true)->orderBy('sort_order')->get();

        $selectedClient = $clientId ? Client::find($clientId) : ($offer?->client);

        $contracts = $selectedClient
            ? Contract::where('client_id', $selectedClient->id)->where('status', 'active')->get()
            : collect();

        $exchangeRates = $this->getExchangeRatesForView();

        $bankAccounts = collect($organization->settings['bank_accounts'] ?? [])
            ->filter(fn($a) => !empty($a['iban']));

        return compact(
            'clients', 'templates', 'services', 'selectedClient',
            'contracts', 'organization', 'offer', 'exchangeRates', 'bankAccounts'
        );
    }

    /**
     * Get offer for show page with eager loaded relations.
     */
    public function getOfferForShow(Offer $offer): Offer
    {
        return $offer->load(['client', 'creator', 'template', 'items.service', 'activities.user', 'contract']);
    }

    /**
     * Get offer by public token (bypasses global scope).
     */
    public function getOfferByToken(string $token): Offer
    {
        return Offer::withoutGlobalScopes()
            ->where('public_token', $token)
            ->firstOrFail();
    }

    /**
     * Accept offer via public link with optional verification.
     */
    public function acceptPublic(string $token, ?string $verificationCode, ?string $ipAddress): ?Contract
    {
        $offer = $this->getOfferByToken($token);

        if (!$offer->canBeAccepted()) {
            throw new \RuntimeException(__('This offer cannot be accepted.'));
        }

        // Verify code if required
        if ($offer->verification_code) {
            if ($verificationCode !== $offer->verification_code) {
                throw new \RuntimeException(__('Invalid verification code.'));
            }
            if ($offer->verification_code_expires_at < now()) {
                throw new \RuntimeException(__('Verification code has expired.'));
            }
        }

        // Use the service's accept method which handles contract creation
        return $this->accept($offer, $ipAddress, true);
    }

    /**
     * Reject offer via public link.
     */
    public function rejectPublic(string $token, ?string $reason = null): void
    {
        $offer = $this->getOfferByToken($token);

        if (!$offer->canBeRejected()) {
            throw new \RuntimeException(__('This offer cannot be rejected.'));
        }

        $offer->reject($reason);

        // Send notification
        if ($this->notificationService) {
            $message = new OfferRejectedMessage($offer);
            $this->notificationService->send($message);
        }
    }

    /**
     * Generate and send verification code.
     */
    public function sendVerificationCode(string $token): void
    {
        $offer = $this->getOfferByToken($token);

        if (!$offer->canBeAccepted()) {
            throw new \RuntimeException(__('This offer cannot be accepted.'));
        }

        if (!$offer->client || !$offer->client->email) {
            throw new \RuntimeException(__('Client does not have an email address.'));
        }

        $code = $offer->generateVerificationCode();

        // Send verification code via email notification
        $offer->client->notify(new \App\Notifications\OfferVerificationCode($offer, $code));

        Log::info("Verification code sent for offer", [
            'offer_id' => $offer->id,
            'client_email' => $offer->client->email,
        ]);
    }

    /**
     * Approve offer and convert to contract.
     */
    public function approveAndConvert(Offer $offer, ?string $ipAddress = null, ?string $signatureText = null): Contract
    {
        return DB::transaction(function () use ($offer, $ipAddress, $signatureText) {
            // Accept if not already accepted
            if (!$offer->isAccepted()) {
                $offer->accept($ipAddress);

                // Store signature in blocks if provided
                if ($signatureText) {
                    $this->storeSignature($offer, $signatureText);
                }

                $offer->logActivity('accepted', ['ip_address' => $ipAddress]);
            }

            // Return existing contract if already converted
            if ($offer->contract_id) {
                return Contract::find($offer->contract_id);
            }

            // Convert to contract
            return $this->convertToContract($offer);
        });
    }

    /**
     * Delete an offer.
     */
    public function delete(Offer $offer): bool
    {
        return $offer->delete();
    }

    /**
     * Store signature in offer blocks.
     */
    protected function storeSignature(Offer $offer, string $signatureText): void
    {
        $blocks = is_string($offer->blocks) ? json_decode($offer->blocks, true) : ($offer->blocks ?? []);

        if (is_array($blocks)) {
            foreach ($blocks as &$block) {
                if (($block['type'] ?? '') === 'signature') {
                    $block['data']['signatureText'] = $signatureText;
                    $block['data']['signedAt'] = now()->toIso8601String();
                    break;
                }
            }
            $offer->update(['blocks' => $blocks]);
        }
    }

    /**
     * Parse sort parameter.
     */
    protected function parseSort(string $sort): array
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
     * Format offer for list display.
     */
    protected function formatOfferForList(Offer $offer): array
    {
        // Build client info - either from real client or temp client fields
        $clientData = null;
        if ($offer->client) {
            $clientData = [
                'id' => $offer->client->id,
                'name' => $offer->client->display_name ?? $offer->client->name,
                'slug' => $offer->client->slug,
            ];
        } elseif ($offer->temp_client_name) {
            // Temporary client - show name and company
            $clientData = [
                'id' => null,
                'name' => $offer->temp_client_name,
                'company' => $offer->temp_client_company,
                'slug' => null,
                'is_temporary' => true,
            ];
        }

        return [
            'id' => $offer->id,
            'offer_number' => $offer->offer_number,
            'title' => $offer->title,
            'status' => $offer->status,
            'status_label' => $offer->status_label,
            'status_color' => $offer->status_color,
            'total' => $offer->total,
            'currency' => $offer->currency,
            'valid_until' => $offer->valid_until?->format('d.m.Y'),
            'is_expired' => $offer->isExpired(),
            'client' => $clientData,
            'creator' => $offer->creator ? [
                'id' => $offer->creator->id,
                'name' => $offer->creator->name,
            ] : null,
            'created_at' => $offer->created_at?->format('d.m.Y'),
            'sent_at' => $offer->sent_at?->format('d.m.Y'),
            'accepted_at' => $offer->accepted_at?->format('d.m.Y'),
        ];
    }

    /**
     * Get exchange rates for view.
     */
    protected function getExchangeRatesForView(): array
    {
        return ExchangeRate::latest('effective_date')
            ->get()
            ->groupBy(fn($rate) => $rate->from_currency . '_' . $rate->to_currency)
            ->map(fn($group) => [
                'rate' => (float) $group->first()->rate,
                'effective_date' => $group->first()->effective_date->format('Y-m-d'),
                'source' => $group->first()->source ?? null,
            ])
            ->toArray();
    }

    /**
     * Get admin email address for notifications.
     */
    protected function getAdminEmail(Offer $offer): ?string
    {
        // First try the organization's billing email, then organization email, then creator email
        $organization = $offer->organization;
        return $organization->billing_email
            ?? $organization->email
            ?? $offer->creator?->email;
    }

    /**
     * Send admin notification when offer is viewed.
     */
    protected function sendAdminViewedEmail(Offer $offer): void
    {
        $adminEmail = $this->getAdminEmail($offer);
        if (!$adminEmail) {
            return;
        }

        try {
            $this->configureSmtpFromDatabase();

            $offer->load('organization');

            Mail::send('emails.offer-viewed', [
                'offer' => $offer,
                'organization' => $offer->organization,
            ], function ($mail) use ($offer, $adminEmail) {
                $mail->to($adminEmail)
                    ->subject(__('Offer Viewed - :number', ['number' => $offer->offer_number]));
            });

            Log::info("Admin viewed notification sent", ['offer_id' => $offer->id]);
        } catch (\Exception $e) {
            Log::error("Failed to send admin viewed email", [
                'offer_id' => $offer->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send admin notification when offer is accepted.
     */
    protected function sendAdminAcceptedEmail(Offer $offer): void
    {
        $adminEmail = $this->getAdminEmail($offer);
        if (!$adminEmail) {
            return;
        }

        try {
            $this->configureSmtpFromDatabase();

            $offer->load('organization');

            Mail::send('emails.offer-accepted-admin', [
                'offer' => $offer,
                'organization' => $offer->organization,
            ], function ($mail) use ($offer, $adminEmail) {
                $mail->to($adminEmail)
                    ->subject(__('Offer Accepted - :number', ['number' => $offer->offer_number]));
            });

            Log::info("Admin accepted notification sent", ['offer_id' => $offer->id]);
        } catch (\Exception $e) {
            Log::error("Failed to send admin accepted email", [
                'offer_id' => $offer->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send admin notification when offer is rejected.
     */
    protected function sendAdminRejectedEmail(Offer $offer): void
    {
        $adminEmail = $this->getAdminEmail($offer);
        if (!$adminEmail) {
            return;
        }

        try {
            $this->configureSmtpFromDatabase();

            $offer->load('organization');

            Mail::send('emails.offer-rejected-admin', [
                'offer' => $offer,
                'organization' => $offer->organization,
            ], function ($mail) use ($offer, $adminEmail) {
                $mail->to($adminEmail)
                    ->subject(__('Offer Declined - :number', ['number' => $offer->offer_number]));
            });

            Log::info("Admin rejected notification sent", ['offer_id' => $offer->id]);
        } catch (\Exception $e) {
            Log::error("Failed to send admin rejected email", [
                'offer_id' => $offer->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send admin notification when offer selections are modified by client.
     */
    public function sendAdminModifiedEmail(Offer $offer, array $changes = []): void
    {
        $adminEmail = $this->getAdminEmail($offer);
        if (!$adminEmail) {
            return;
        }

        try {
            $this->configureSmtpFromDatabase();

            $offer->load('organization');

            Mail::send('emails.offer-modified-admin', [
                'offer' => $offer,
                'organization' => $offer->organization,
                'changes' => $changes,
            ], function ($mail) use ($offer, $adminEmail) {
                $mail->to($adminEmail)
                    ->subject(__('Offer Modified - :number', ['number' => $offer->offer_number]));
            });

            Log::info("Admin modified notification sent", ['offer_id' => $offer->id]);
        } catch (\Exception $e) {
            Log::error("Failed to send admin modified email", [
                'offer_id' => $offer->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send client confirmation when offer is accepted.
     */
    protected function sendClientAcceptedEmail(Offer $offer): void
    {
        $clientEmail = $offer->client?->email ?? $offer->temp_client_email;
        if (!$clientEmail) {
            return;
        }

        try {
            $this->configureSmtpFromDatabase();

            $offer->load(['organization', 'items']);

            Mail::send('emails.offer-accepted-client', [
                'offer' => $offer,
                'organization' => $offer->organization,
            ], function ($mail) use ($offer, $clientEmail) {
                $clientName = $offer->client?->display_name ?? $offer->temp_client_name ?? '';
                $mail->to($clientEmail, $clientName)
                    ->subject(__('Offer Confirmation - :number', ['number' => $offer->offer_number]));
            });

            Log::info("Client accepted confirmation sent", [
                'offer_id' => $offer->id,
                'client_email' => $clientEmail,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send client accepted email", [
                'offer_id' => $offer->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get version history for an offer.
     */
    public function getVersionHistory(Offer $offer): array
    {
        $versions = $offer->versions()
            ->with('creator:id,name')
            ->get();

        return $versions->map(function ($version) {
            return [
                'id' => $version->id,
                'version_number' => $version->version_number,
                'reason' => $version->reason,
                'changes_summary' => $version->changes_summary,
                'created_by' => $version->creator?->name ?? __('System'),
                'created_at' => $version->created_at->format('d/m/Y H:i'),
                'snapshot' => [
                    'total' => $version->snapshot['total'] ?? null,
                    'subtotal' => $version->snapshot['subtotal'] ?? null,
                    'items_count' => count($version->snapshot['items'] ?? []),
                    'valid_until' => $version->snapshot['valid_until'] ?? null,
                ],
            ];
        })->toArray();
    }

    /**
     * Send client confirmation when offer is rejected.
     */
    protected function sendClientRejectedEmail(Offer $offer): void
    {
        $clientEmail = $offer->client?->email ?? $offer->temp_client_email;
        if (!$clientEmail) {
            return;
        }

        try {
            $this->configureSmtpFromDatabase();

            $offer->load('organization');

            Mail::send('emails.offer-rejected-client', [
                'offer' => $offer,
                'organization' => $offer->organization,
            ], function ($mail) use ($offer, $clientEmail) {
                $clientName = $offer->client?->display_name ?? $offer->temp_client_name ?? '';
                $mail->to($clientEmail, $clientName)
                    ->subject(__('Offer Response Confirmed - :number', ['number' => $offer->offer_number]));
            });

            Log::info("Client rejected confirmation sent", [
                'offer_id' => $offer->id,
                'client_email' => $clientEmail,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send client rejected email", [
                'offer_id' => $offer->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
