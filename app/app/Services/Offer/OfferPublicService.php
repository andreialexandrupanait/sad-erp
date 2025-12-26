<?php

namespace App\Services\Offer;

use App\Models\Offer;
use Illuminate\Support\Facades\Log;

/**
 * Offer Public Service - Business logic for public (client-facing) offer operations.
 *
 * Handles:
 * - Public offer state retrieval
 * - Customer service selection updates
 * - Real-time sync data
 */
class OfferPublicService
{
    public function __construct(
        protected OfferService $offerService
    ) {}

    /**
     * Get offer by public token (bypasses global scope).
     */
    public function getOfferByToken(string $token): Offer
    {
        return $this->offerService->getOfferByToken($token);
    }

    /**
     * Record offer view.
     */
    public function recordView(Offer $offer, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        $this->offerService->recordView($offer, $ipAddress, $userAgent);
    }

    /**
     * Get public offer state for real-time sync.
     *
     * @param string $token The public token
     * @return array State data for the offer
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getPublicState(string $token): array
    {
        $offer = $this->getOfferByToken($token);
        $offer->load(['items']);

        // Separate custom services and card services
        $customItems = $offer->items->filter(fn($item) => $item->type === 'custom' || $item->type === null);
        $cardItems = $offer->items->filter(fn($item) => $item->type === 'card');

        // Get blocks
        $blocks = $offer->blocks ?? [];

        // Get optional services from blocks
        $optionalServicesBlock = collect($blocks)->firstWhere('type', 'optional_services');
        $optionalServices = $optionalServicesBlock['data']['services'] ?? [];

        return [
            'success' => true,
            'updated_at' => $offer->updated_at->timestamp,
            'status' => $offer->status,
            'currency' => $offer->currency,
            'discount_percent' => $offer->discount_percent ?? 0,
            'custom_items' => $customItems->map(fn($item) => [
                'id' => $item->id,
                'title' => $item->title,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'unit_price' => $item->unit_price,
                'discount_percent' => $item->discount_percent,
                'total_price' => $item->total_price,
                'is_selected' => $item->is_selected,
            ])->values()->toArray(),
            'card_items' => $cardItems->map(fn($item) => [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
                'is_selected' => $item->is_selected,
            ])->values()->toArray(),
            'optional_services' => collect($optionalServices)->map(fn($s) => [
                '_key' => $s['_key'] ?? uniqid(),
                'title' => $s['title'] ?? '',
                'description' => $s['description'] ?? '',
                'quantity' => $s['quantity'] ?? 1,
                'unit' => $s['unit'] ?? 'ora',
                'unit_price' => $s['unit_price'] ?? 0,
                'currency' => $s['currency'] ?? $offer->currency,
            ])->values()->toArray(),
        ];
    }

    /**
     * Update customer's service selections.
     *
     * @param string $token The public token
     * @param array $selections The selection data
     * @return array Result with updated timestamp
     * @throws \RuntimeException If offer cannot be modified
     */
    public function updateSelections(string $token, array $selections): array
    {
        $offer = $this->getOfferByToken($token);

        // Only allow updates for sent or viewed offers
        if (!in_array($offer->status, ['sent', 'viewed'])) {
            throw new \RuntimeException(__('This offer cannot be modified.'));
        }

        // Track if any changes were made for admin notification
        $changesWereMade = false;

        // Update custom service selections
        if (isset($selections['deselected_services'])) {
            $deselectedIds = $selections['deselected_services'];

            // Update each custom item's is_selected status
            foreach ($offer->items as $item) {
                if ($item->type === 'custom' || $item->type === null) {
                    $newSelected = !in_array($item->id, $deselectedIds);
                    if ($item->is_selected !== $newSelected) {
                        $changesWereMade = true;
                        $item->is_selected = $newSelected;
                        $item->saveQuietly(); // Don't trigger events
                    }
                }
            }
        }

        // Update card service selections
        if (isset($selections['selected_cards'])) {
            $selectedCardIds = $selections['selected_cards'];

            foreach ($offer->items as $item) {
                if ($item->type === 'card') {
                    $newSelected = in_array($item->id, $selectedCardIds);
                    if ($item->is_selected !== $newSelected) {
                        $changesWereMade = true;
                        $item->is_selected = $newSelected;
                        $item->saveQuietly();
                    }
                }
            }
        }

        // Touch the offer to update its timestamp
        $offer->touch();

        // Recalculate totals
        $offer->recalculateTotals();

        // Log activity if changes were made
        if ($changesWereMade) {
            try {
                $offer->logActivity('selections_modified', [
                    'deselected_services' => $selections['deselected_services'] ?? [],
                    'selected_cards' => $selections['selected_cards'] ?? [],
                ]);
            } catch (\Exception $e) {
                // Log activity failure is not critical
                Log::warning('Failed to log selection modification', [
                    'offer_id' => $offer->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'success' => true,
            'message' => __('Selection updated.'),
            'updated_at' => $offer->fresh()->updated_at->timestamp,
            'changes_made' => $changesWereMade,
        ];
    }

    /**
     * Accept offer via public link with optional verification.
     */
    public function acceptPublic(string $token, ?string $verificationCode, ?string $ipAddress): ?\App\Models\Contract
    {
        return $this->offerService->acceptPublic($token, $verificationCode, $ipAddress);
    }

    /**
     * Reject offer via public link.
     */
    public function rejectPublic(string $token, ?string $reason = null): void
    {
        $this->offerService->rejectPublic($token, $reason);
    }

    /**
     * Generate and send verification code.
     */
    public function sendVerificationCode(string $token): void
    {
        $this->offerService->sendVerificationCode($token);
    }
}
