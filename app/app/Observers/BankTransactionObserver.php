<?php

namespace App\Observers;

use App\Models\BankTransaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BankTransactionObserver
{
    /**
     * Handle the BankTransaction "created" event.
     */
    public function created(BankTransaction $transaction): void
    {
        $this->clearCaches($transaction, 'created');
    }

    /**
     * Handle the BankTransaction "updated" event.
     */
    public function updated(BankTransaction $transaction): void
    {
        $this->clearCaches($transaction, 'updated');

        // Log if match status changed (important operation)
        if ($transaction->isDirty('match_status')) {
            Log::info('BankTransaction match status changed', [
                'transaction_id' => $transaction->id,
                'old_status' => $transaction->getOriginal('match_status'),
                'new_status' => $transaction->match_status,
                'matched_revenue_id' => $transaction->matched_revenue_id,
                'matched_expense_id' => $transaction->matched_expense_id,
            ]);
        }
    }

    /**
     * Handle the BankTransaction "deleted" event.
     */
    public function deleted(BankTransaction $transaction): void
    {
        $this->clearCaches($transaction, 'deleted');
    }

    /**
     * Handle the BankTransaction "restored" event.
     */
    public function restored(BankTransaction $transaction): void
    {
        $this->clearCaches($transaction, 'restored');
    }

    /**
     * Clear all relevant caches for the bank transaction
     */
    protected function clearCaches(BankTransaction $transaction, string $event): void
    {
        $orgId = $transaction->organization_id;

        // Clear banking transaction caches
        Cache::forget("banking.transactions.org.{$orgId}");
        Cache::forget("banking.transactions.org.{$orgId}.unmatched");
        Cache::forget("banking.transactions.org.{$orgId}.matched");
        Cache::forget("banking.transactions.org.{$orgId}.stats");

        // Clear credential-specific caches
        if ($transaction->banking_credential_id) {
            Cache::forget("banking.transactions.credential.{$transaction->banking_credential_id}");
        }

        // Clear dashboard cache that might show banking stats
        Cache::forget("dashboard.org.{$orgId}");
        Cache::forget("dashboard.banking.org.{$orgId}");

        Log::debug('BankTransaction cache cleared', [
            'event' => $event,
            'transaction_id' => $transaction->id,
            'organization_id' => $orgId,
        ]);
    }
}
