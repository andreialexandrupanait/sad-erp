<?php

namespace App\Services\Offer;

use App\Exports\OffersExport;
use App\Models\Offer;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Offer Bulk Action Service - Business logic for bulk operations on offers.
 *
 * Handles:
 * - Bulk export to Excel/CSV
 * - Bulk delete (with draft-only restriction)
 * - Bulk status changes (with allowed transitions)
 */
class OfferBulkActionService
{
    public function __construct(
        protected OfferService $offerService
    ) {}

    /**
     * Export offers to Excel/CSV.
     *
     * @param array|null $offerIds Specific offer IDs to export, or null for all
     * @param string|null $statusFilter Filter by status
     * @param string $format Export format (xlsx, csv)
     * @return BinaryFileResponse
     */
    public function export(?array $offerIds, ?string $statusFilter = null, string $format = 'xlsx'): BinaryFileResponse
    {
        $export = new OffersExport($offerIds, $statusFilter);
        $filename = 'offers_' . now()->format('Y-m-d_His') . '.' . $format;

        return Excel::download($export, $filename);
    }

    /**
     * Bulk delete offers (only drafts can be deleted).
     *
     * @param array $offerIds Array of offer IDs to delete
     * @return array Results with counts and message
     */
    public function bulkDelete(array $offerIds): array
    {
        $deleted = 0;
        $skipped = 0;

        // Batch load all offers in a single query, scoped to current organization
        $organizationId = auth()->user()->organization_id;
        $offers = Offer::whereIn('id', $offerIds)
            ->where('organization_id', $organizationId)
            ->get()
            ->keyBy('id');

        foreach ($offerIds as $id) {
            $offer = $offers->get($id);
            if ($offer) {
                // Only allow deletion of draft offers
                if ($offer->status === 'draft') {
                    $this->offerService->delete($offer);
                    $deleted++;
                } else {
                    $skipped++;
                }
            }
        }

        $message = __(':deleted offer(s) deleted successfully.', ['deleted' => $deleted]);
        if ($skipped > 0) {
            $message .= ' ' . __(':skipped offer(s) skipped (only drafts can be deleted).', ['skipped' => $skipped]);
        }

        return [
            'success' => true,
            'message' => $message,
            'deleted' => $deleted,
            'skipped' => $skipped,
        ];
    }

    /**
     * Legacy bulk delete (allows deleting non-draft offers except those with contracts).
     *
     * @param array $offerIds Array of offer IDs to delete
     * @return array Results with counts and message
     */
    public function bulkDeleteLegacy(array $offerIds): array
    {
        $deleted = 0;
        $skipped = 0;

        // Batch load all offers in a single query, scoped to current organization
        $organizationId = auth()->user()->organization_id;
        $offers = Offer::whereIn('id', $offerIds)
            ->where('organization_id', $organizationId)
            ->get()
            ->keyBy('id');

        foreach ($offerIds as $id) {
            $offer = $offers->get($id);
            if ($offer) {
                // Only skip accepted offers that have contracts
                if ($offer->status === 'accepted' && $offer->contract_id) {
                    $skipped++;
                    continue;
                }
                $this->offerService->delete($offer);
                $deleted++;
            }
        }

        $message = __(':count offer(s) deleted successfully.', ['count' => $deleted]);
        if ($skipped > 0) {
            $message .= ' ' . __(':count offer(s) skipped (have linked contracts).', ['count' => $skipped]);
        }

        return [
            'success' => true,
            'message' => $message,
            'deleted' => $deleted,
            'skipped' => $skipped,
        ];
    }

    /**
     * Bulk status change (limited transitions allowed).
     *
     * @param array $offerIds Array of offer IDs to update
     * @param string $newStatus The new status to set
     * @return array Results with counts and message
     */
    public function bulkStatusChange(array $offerIds, string $newStatus): array
    {
        $changed = 0;
        $skipped = 0;

        // Define allowed status transitions
        $allowedTransitions = [
            // Expired offers can be reset to draft for re-sending
            'expired' => ['draft'],
            // Rejected offers can be reset to draft
            'rejected' => ['draft'],
        ];

        // Batch load all offers in a single query, scoped to current organization
        $organizationId = auth()->user()->organization_id;
        $offers = Offer::whereIn('id', $offerIds)
            ->where('organization_id', $organizationId)
            ->get()
            ->keyBy('id');

        foreach ($offerIds as $id) {
            $offer = $offers->get($id);
            if (!$offer) {
                continue;
            }

            if (
                isset($allowedTransitions[$offer->status]) &&
                in_array($newStatus, $allowedTransitions[$offer->status])
            ) {
                $originalStatus = $offer->status;
                $offer->update(['status' => $newStatus]);
                $offer->logActivity('status_changed', [
                    'from' => $originalStatus,
                    'to' => $newStatus,
                    'bulk_action' => true,
                ]);
                $changed++;
            } else {
                $skipped++;
            }
        }

        $message = __(':changed offer(s) status changed to :status.', ['changed' => $changed, 'status' => $newStatus]);
        if ($skipped > 0) {
            $message .= ' ' . __(':skipped offer(s) skipped (status change not allowed).', ['skipped' => $skipped]);
        }

        return [
            'success' => true,
            'message' => $message,
            'changed' => $changed,
            'skipped' => $skipped,
        ];
    }
}
