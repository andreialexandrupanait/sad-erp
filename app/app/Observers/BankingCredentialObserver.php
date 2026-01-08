<?php

namespace App\Observers;

use App\Models\BankingCredential;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BankingCredentialObserver
{
    /**
     * Handle the BankingCredential "created" event.
     */
    public function created(BankingCredential $credential): void
    {
        $this->clearCaches($credential, 'created');
    }

    /**
     * Handle the BankingCredential "updated" event.
     */
    public function updated(BankingCredential $credential): void
    {
        $this->clearCaches($credential, 'updated');

        // Log if status or consent changed (important operations)
        if ($credential->isDirty('status')) {
            Log::info('BankingCredential status changed', [
                'credential_id' => $credential->id,
                'old_status' => $credential->getOriginal('status'),
                'new_status' => $credential->status,
            ]);
        }

        if ($credential->isDirty('consent_expires_at')) {
            Log::info('BankingCredential consent updated', [
                'credential_id' => $credential->id,
                'old_expiry' => $credential->getOriginal('consent_expires_at'),
                'new_expiry' => $credential->consent_expires_at,
            ]);
        }
    }

    /**
     * Handle the BankingCredential "deleted" event.
     */
    public function deleted(BankingCredential $credential): void
    {
        $this->clearCaches($credential, 'deleted');
    }

    /**
     * Handle the BankingCredential "restored" event.
     */
    public function restored(BankingCredential $credential): void
    {
        $this->clearCaches($credential, 'restored');
    }

    /**
     * Clear all relevant caches for the banking credential
     */
    protected function clearCaches(BankingCredential $credential, string $event): void
    {
        $orgId = $credential->organization_id;

        // Clear banking credential caches
        Cache::forget("banking.credentials.org.{$orgId}");
        Cache::forget("banking.credentials.active.org.{$orgId}");
        Cache::forget("banking.credential.{$credential->id}");

        // Clear transaction caches related to this credential
        Cache::forget("banking.transactions.credential.{$credential->id}");
        Cache::forget("banking.transactions.org.{$orgId}");

        // Clear API response caches if they exist
        Cache::forget("banking.api.accounts.{$credential->id}");
        Cache::forget("banking.api.balance.{$credential->id}");

        // Clear dashboard cache that might show banking info
        Cache::forget("dashboard.org.{$orgId}");
        Cache::forget("dashboard.banking.org.{$orgId}");

        Log::debug('BankingCredential cache cleared', [
            'event' => $event,
            'credential_id' => $credential->id,
            'organization_id' => $orgId,
        ]);
    }
}
