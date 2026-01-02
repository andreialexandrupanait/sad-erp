<?php

namespace App\Contracts;

use App\Models\Contract;
use App\Models\Offer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Interface for Offer business logic operations.
 *
 * Implementing interfaces for services provides:
 * - Clear API documentation
 * - Easier mocking in unit tests
 * - Dependency injection flexibility
 * - Separation of contracts from implementation
 */
interface OfferServiceInterface
{
    /**
     * Create a new offer with items.
     */
    public function create(array $data, array $items = []): Offer;

    /**
     * Update an offer.
     */
    public function update(Offer $offer, array $data, array $items = [], ?string $versionReason = null): Offer;

    /**
     * Duplicate an offer.
     */
    public function duplicate(Offer $offer): Offer;

    /**
     * Generate PDF for an offer.
     */
    public function generatePdf(Offer $offer): string;

    /**
     * Send offer to client.
     */
    public function send(Offer $offer): bool;

    /**
     * Resend offer to client.
     */
    public function resend(Offer $offer): bool;

    /**
     * Generate PDF for download.
     */
    public function generatePdfForDownload(Offer $offer): string;

    /**
     * Record offer view.
     */
    public function recordView(Offer $offer, ?string $ipAddress = null, ?string $userAgent = null): void;

    /**
     * Accept an offer.
     */
    public function accept(Offer $offer, ?string $ipAddress = null, bool $createContract = true): ?Contract;

    /**
     * Reject an offer.
     */
    public function reject(Offer $offer, ?string $reason = null): void;

    /**
     * Convert accepted offer to contract.
     */
    public function convertToContract(Offer $offer, array $options = []): Contract;

    /**
     * Get offer statistics.
     */
    public function getStatistics(?int $organizationId = null): array;

    /**
     * Get offers list as JSON.
     */
    public function getOffersJson(Request $request): JsonResponse;

    /**
     * Get data for builder view.
     */
    public function getBuilderData(?int $clientId = null, ?Offer $offer = null): array;

    /**
     * Get offer for show page.
     */
    public function getOfferForShow(Offer $offer): Offer;

    /**
     * Get offer by public token.
     */
    public function getOfferByToken(string $token): Offer;

    /**
     * Accept offer via public link.
     */
    public function acceptPublic(string $token, ?string $verificationCode, ?string $ipAddress): ?Contract;

    /**
     * Reject offer via public link.
     */
    public function rejectPublic(string $token, ?string $reason = null): void;

    /**
     * Generate and send verification code.
     */
    public function sendVerificationCode(string $token): void;

    /**
     * Delete an offer.
     */
    public function delete(Offer $offer): bool;

    /**
     * Get version history for an offer.
     */
    public function getVersionHistory(Offer $offer): array;
}
