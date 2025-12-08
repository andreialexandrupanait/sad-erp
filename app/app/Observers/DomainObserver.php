<?php

namespace App\Observers;

use App\Events\Domain\DomainExpired;
use App\Events\Domain\DomainExpiringSoon;
use App\Models\Domain;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DomainObserver
{
    /**
     * Handle the Domain "created" event.
     */
    public function created(Domain $domain): void
    {
        // Check if domain is already expiring soon when created
        $this->checkExpiryStatus($domain);
    }

    /**
     * Handle the Domain "saved" event - clear dashboard cache.
     */
    public function saved(Domain $domain): void
    {
        if (Auth::check()) {
            Cache::forget('dashboard_stats_' . Auth::user()->organization_id);
        }
    }

    /**
     * Handle the Domain "updated" event.
     */
    public function updated(Domain $domain): void
    {
        // Only fire events if expiry_date was changed
        if ($domain->isDirty('expiry_date')) {
            $this->checkExpiryStatus($domain);
        }
    }

    /**
     * Check the domain's expiry status and fire appropriate events.
     */
    protected function checkExpiryStatus(Domain $domain): void
    {
        // Skip during console commands (let scheduled commands handle batch notifications)
        // This prevents duplicate notifications during imports or bulk operations
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return;
        }

        // Skip if domain has no expiry date
        if (!$domain->expiry_date) {
            return;
        }

        $daysUntilExpiry = $domain->days_until_expiry;

        if ($daysUntilExpiry === null) {
            return;
        }

        // Fire expired event
        if ($domain->is_expired) {
            event(new DomainExpired($domain, abs($daysUntilExpiry)));
            return;
        }

        // Fire expiring soon event
        if ($domain->is_expiring_soon) {
            event(new DomainExpiringSoon($domain, $daysUntilExpiry));
        }
    }
}
