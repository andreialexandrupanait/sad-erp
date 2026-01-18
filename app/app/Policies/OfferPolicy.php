<?php

namespace App\Policies;

use App\Models\Offer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OfferPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can view any offers.
     */
    public function viewAny(User $user): bool
    {
        return $user->organization_id !== null;
    }

    /**
     * Determine if user can view a specific offer.
     */
    public function view(User $user, Offer $offer): bool
    {
        return $offer->organization_id === $user->organization_id;
    }

    /**
     * Determine if user can create offers.
     */
    public function create(User $user): bool
    {
        return $user->organization_id !== null;
    }

    /**
     * Determine if user can update an offer.
     */
    public function update(User $user, Offer $offer): bool
    {
        return $offer->organization_id === $user->organization_id;
    }

    /**
     * Determine if user can delete an offer.
     */
    public function delete(User $user, Offer $offer): bool
    {
        return $offer->organization_id === $user->organization_id;
    }

    /**
     * Determine if user can approve an offer.
     */
    public function approve(User $user, Offer $offer): bool
    {
        if ($offer->organization_id !== $user->organization_id) {
            return false;
        }

        return $offer->status === 'draft';
    }

    /**
     * Determine if user can send an offer.
     */
    public function send(User $user, Offer $offer): bool
    {
        return $offer->organization_id === $user->organization_id;
    }

    /**
     * Determine if user can convert offer to contract.
     */
    public function convertToContract(User $user, Offer $offer): bool
    {
        if ($offer->organization_id !== $user->organization_id) {
            return false;
        }

        return in_array($offer->status, ['approved', 'accepted']);
    }

    /**
     * Determine if user can download offer PDF.
     */
    public function download(User $user, Offer $offer): bool
    {
        return $offer->organization_id === $user->organization_id;
    }

    /**
     * Determine if user can save offer as template.
     */
    public function saveAsTemplate(User $user, Offer $offer): bool
    {
        return $offer->organization_id === $user->organization_id;
    }

    /**
     * Determine if user can restore a soft-deleted offer.
     */
    public function restore(User $user, Offer $offer): bool
    {
        return $offer->organization_id === $user->organization_id;
    }

    /**
     * Determine if user can force delete an offer.
     */
    public function forceDelete(User $user, Offer $offer): bool
    {
        return $offer->organization_id === $user->organization_id;
    }
}
