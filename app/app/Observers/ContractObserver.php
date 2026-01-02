<?php

namespace App\Observers;

use App\Models\Contract;
use App\Models\ContractActivity;
use App\Models\ContractVersion;

/**
 * Contract Observer - Automatically logs activities and manages versioning.
 *
 * Hooks into Eloquent events to:
 * - Log all CRUD operations
 * - Track status transitions
 * - Create content versions on significant changes
 * - Record field changes for audit purposes
 */
class ContractObserver
{
    /**
     * Fields to track for changes.
     */
    protected array $trackedFields = [
        'status',
        'contract_number',
        'title',
        'content',
        'total_value',
        'currency',
        'start_date',
        'end_date',
        'is_finalized',
        'client_id',
        'contract_template_id',
    ];

    /**
     * Handle the Contract "created" event.
     */
    public function created(Contract $contract): void
    {
        ContractActivity::log($contract, 'created', [
            'contract_number' => $contract->contract_number,
            'status' => $contract->status,
            'client_id' => $contract->client_id,
            'offer_id' => $contract->offer_id,
        ]);

        // Create initial version if content exists
        if (!empty($contract->content)) {
            ContractVersion::createVersion($contract, __('Initial version'));
        }
    }

    /**
     * Handle the Contract "updated" event.
     */
    public function updated(Contract $contract): void
    {
        $changes = $this->getTrackedChanges($contract);

        // Handle status transitions specially
        if ($contract->wasChanged('status')) {
            $this->logStatusTransition($contract);
        }

        // Handle finalization
        if ($contract->wasChanged('is_finalized') && $contract->is_finalized) {
            ContractActivity::log($contract, 'finalized');
        }

        // Handle contract number changes
        if ($contract->wasChanged('contract_number')) {
            ContractActivity::log($contract, 'number_changed', [
                'from' => $contract->getOriginal('contract_number'),
                'to' => $contract->contract_number,
            ]);
        }

        // Handle content changes - create new version
        if ($contract->wasChanged('content')) {
            ContractVersion::createVersion($contract);
            ContractActivity::log($contract, 'content_updated', null, [
                'content_length' => strlen($contract->content ?? ''),
            ]);
        }

        // Log general update if there were tracked changes (excluding special cases)
        $generalChanges = array_diff_key($changes, array_flip(['status', 'is_finalized', 'contract_number', 'content']));
        if (!empty($generalChanges)) {
            ContractActivity::log($contract, 'updated', null, $generalChanges);
        }
    }

    /**
     * Handle the Contract "deleting" event.
     * Performs cleanup before the contract is deleted.
     */
    public function deleting(Contract $contract): void
    {
        // Delete associated PDF file if exists
        if ($contract->pdf_path) {
            $fullPath = storage_path('app/' . $contract->pdf_path);
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }

        // Unlink any associated offers (set contract_id to null)
        if ($contract->offer_id) {
            \App\Models\Offer::where('id', $contract->offer_id)
                ->where('contract_id', $contract->id)
                ->update(['contract_id' => null]);
        }

        // Note: Annexes are deleted via database CASCADE constraint
    }

    /**
     * Handle the Contract "deleted" event.
     */
    public function deleted(Contract $contract): void
    {
        // Only log if soft deleted (not force deleted)
        if (!$contract->isForceDeleting()) {
            ContractActivity::log($contract, 'deleted', [
                'contract_number' => $contract->contract_number,
            ]);
        }
    }

    /**
     * Handle the Contract "restored" event.
     */
    public function restored(Contract $contract): void
    {
        ContractActivity::log($contract, 'restored', [
            'contract_number' => $contract->contract_number,
        ]);
    }

    /**
     * Log status transition with appropriate action name.
     */
    protected function logStatusTransition(Contract $contract): void
    {
        $oldStatus = $contract->getOriginal('status');
        $newStatus = $contract->status;

        $actionMap = [
            'active' => 'activated',
            'terminated' => 'terminated',
            'completed' => 'completed',
            'expired' => 'expired',
        ];

        $action = $actionMap[$newStatus] ?? 'status_changed';

        ContractActivity::log($contract, $action, [
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
        ]);
    }

    /**
     * Get tracked field changes.
     */
    protected function getTrackedChanges(Contract $contract): array
    {
        $changes = [];

        foreach ($this->trackedFields as $field) {
            if ($contract->wasChanged($field)) {
                $changes[$field] = [
                    'from' => $contract->getOriginal($field),
                    'to' => $contract->{$field},
                ];
            }
        }

        return $changes;
    }
}
