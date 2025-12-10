<?php

namespace App\Services\Offer;

use App\Models\Client;
use App\Models\Contract;
use App\Models\DocumentTemplate;
use App\Models\Offer;
use App\Models\OfferItem;
use App\Services\Notification\NotificationService;
use App\Services\Notification\Messages\OfferSentMessage;
use App\Services\Notification\Messages\OfferAcceptedMessage;
use App\Services\Notification\Messages\OfferRejectedMessage;
use Barryvdh\DomPDF\Facade\Pdf;
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
        protected ?NotificationService $notificationService = null
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
    public function update(Offer $offer, array $data, array $items = []): Offer
    {
        return DB::transaction(function () use ($offer, $data, $items) {
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

            // Log activity
            $offer->logActivity('updated', "Offer updated");

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
        $offer->load(['client', 'items', 'template']);

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
        if (!$offer->isDraft()) {
            throw new \RuntimeException(__('Only draft offers can be sent.'));
        }

        if (!$offer->client || !$offer->client->email) {
            throw new \RuntimeException(__('Client does not have an email address.'));
        }

        return DB::transaction(function () use ($offer) {
            // Generate PDF if not exists
            if (!$offer->pdf_path) {
                $this->generatePdf($offer);
            }

            // Mark as sent
            $offer->markAsSent();

            // Send email notification
            $this->sendOfferEmail($offer);

            // Send internal notification
            if ($this->notificationService) {
                $message = new OfferSentMessage($offer);
                $this->notificationService->send($message);
            }

            return true;
        });
    }

    /**
     * Send offer email to client.
     */
    protected function sendOfferEmail(Offer $offer): void
    {
        $offer->load('client');

        try {
            Mail::send('emails.offer-sent', [
                'offer' => $offer,
                'publicUrl' => $offer->public_url,
            ], function ($mail) use ($offer) {
                $mail->to($offer->client->email, $offer->client->display_name)
                    ->subject(__('Offer :number from :company', [
                        'number' => $offer->offer_number,
                        'company' => config('app.name'),
                    ]));

                // Attach PDF if exists
                if ($offer->pdf_path && file_exists(storage_path('app/' . $offer->pdf_path))) {
                    $mail->attach(storage_path('app/' . $offer->pdf_path), [
                        'as' => $offer->offer_number . '.pdf',
                        'mime' => 'application/pdf',
                    ]);
                }
            });

            Log::info("Offer email sent", [
                'offer_id' => $offer->id,
                'client_email' => $offer->client->email,
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
     * Record offer view.
     */
    public function recordView(Offer $offer, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        if (!$offer->viewed_at) {
            $offer->update(['viewed_at' => now()]);
        }

        $offer->logActivity('viewed', 'Offer viewed by client', [
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Accept an offer.
     */
    public function accept(Offer $offer): void
    {
        if (!$offer->canBeAccepted()) {
            throw new \RuntimeException(__('This offer cannot be accepted.'));
        }

        $offer->markAsAccepted();

        // Send notification
        if ($this->notificationService) {
            $message = new OfferAcceptedMessage($offer);
            $this->notificationService->send($message);
        }
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

        // Send notification
        if ($this->notificationService) {
            $message = new OfferRejectedMessage($offer);
            $this->notificationService->send($message);
        }
    }

    /**
     * Convert accepted offer to contract.
     */
    public function convertToContract(Offer $offer): Contract
    {
        if (!$offer->isAccepted()) {
            throw new \RuntimeException(__('Only accepted offers can be converted to contracts.'));
        }

        if ($offer->contract_id) {
            throw new \RuntimeException(__('This offer has already been converted to a contract.'));
        }

        return DB::transaction(function () use ($offer) {
            $contract = $offer->convertToContract();

            // Log activity on offer
            $offer->logActivity(
                'converted_to_contract',
                "Converted to contract {$contract->contract_number}"
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
}
