<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContractPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can view any contracts.
     */
    public function viewAny(User $user): bool
    {
        return $user->organization_id !== null;
    }

    /**
     * Determine if user can view a specific contract.
     * Organization-scoped: any user in the org can view.
     */
    public function view(User $user, Contract $contract): bool
    {
        return $contract->organization_id === $user->organization_id;
    }

    /**
     * Determine if user can create contracts.
     */
    public function create(User $user): bool
    {
        return $user->organization_id !== null;
    }

    /**
     * Determine if user can update a contract.
     * Can only update if not finalized.
     */
    public function update(User $user, Contract $contract): bool
    {
        if ($contract->organization_id !== $user->organization_id) {
            return false;
        }

        // Allow updates to non-finalized contracts
        // Finalized contracts can only be updated for specific fields (handled in controller)
        return true;
    }

    /**
     * Determine if user can update contract content.
     * Content cannot be edited after finalization.
     */
    public function updateContent(User $user, Contract $contract): bool
    {
        if ($contract->organization_id !== $user->organization_id) {
            return false;
        }

        // Cannot update content of finalized contracts
        return !$contract->is_finalized;
    }

    /**
     * Determine if user can delete a contract.
     * Only draft or terminated contracts can be deleted.
     */
    public function delete(User $user, Contract $contract): bool
    {
        if ($contract->organization_id !== $user->organization_id) {
            return false;
        }

        // Only allow deleting draft or terminated contracts
        return in_array($contract->status, ['draft', 'terminated']);
    }

    /**
     * Determine if user can terminate an active contract.
     */
    public function terminate(User $user, Contract $contract): bool
    {
        if ($contract->organization_id !== $user->organization_id) {
            return false;
        }

        return $contract->status === 'active';
    }

    /**
     * Determine if user can finalize a contract.
     * Locks the contract number and prevents further content edits.
     */
    public function finalize(User $user, Contract $contract): bool
    {
        if ($contract->organization_id !== $user->organization_id) {
            return false;
        }

        // Cannot finalize if already finalized
        return !$contract->is_finalized;
    }

    /**
     * Determine if user can generate PDF for contract.
     */
    public function generatePdf(User $user, Contract $contract): bool
    {
        return $contract->organization_id === $user->organization_id;
    }

    /**
     * Determine if user can download contract PDF.
     */
    public function download(User $user, Contract $contract): bool
    {
        return $contract->organization_id === $user->organization_id;
    }

    /**
     * Determine if user can add annex to contract.
     */
    public function addAnnex(User $user, Contract $contract): bool
    {
        if ($contract->organization_id !== $user->organization_id) {
            return false;
        }

        // Allow access to the add annex page for active contracts
        // The controller will check canAcceptAnnex() before actually creating
        return $contract->isActive();
    }

    /**
     * Determine if user can apply template to contract.
     */
    public function applyTemplate(User $user, Contract $contract): bool
    {
        if ($contract->organization_id !== $user->organization_id) {
            return false;
        }

        // Cannot apply template to finalized contracts
        return !$contract->is_finalized;
    }

    /**
     * Determine if user can restore a soft-deleted contract.
     */
    public function restore(User $user, Contract $contract): bool
    {
        return $contract->organization_id === $user->organization_id;
    }

    /**
     * Determine if user can force delete a contract.
     */
    public function forceDelete(User $user, Contract $contract): bool
    {
        return $contract->organization_id === $user->organization_id;
    }
}
