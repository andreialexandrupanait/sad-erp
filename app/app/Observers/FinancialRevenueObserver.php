<?php

namespace App\Observers;

use App\Events\FinancialRevenue\RevenueCreated;
use App\Models\FinancialRevenue;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class FinancialRevenueObserver
{
    /**
     * Handle the FinancialRevenue "created" event.
     * Fires notification event for new revenue.
     */
    public function created(FinancialRevenue $revenue): void
    {
        // Dispatch notification event for new revenue
        event(new RevenueCreated($revenue));
    }

    /**
     * Handle the FinancialRevenue "saved" event.
     */
    public function saved(FinancialRevenue $revenue): void
    {
        $this->updateClientTotal($revenue->client_id, $revenue->organization_id);
        $this->clearRevenueCache($revenue->year);
    }

    /**
     * Handle the FinancialRevenue "deleted" event.
     */
    public function deleted(FinancialRevenue $revenue): void
    {
        $this->updateClientTotal($revenue->client_id, $revenue->organization_id);
        $this->clearRevenueCache($revenue->year);
    }

    /**
     * Handle the FinancialRevenue "restored" event.
     */
    public function restored(FinancialRevenue $revenue): void
    {
        $this->updateClientTotal($revenue->client_id, $revenue->organization_id);
        $this->clearRevenueCache($revenue->year);
    }

    /**
     * Handle the FinancialRevenue "force deleted" event.
     */
    public function forceDeleted(FinancialRevenue $revenue): void
    {
        $this->updateClientTotal($revenue->client_id, $revenue->organization_id);
        $this->clearRevenueCache($revenue->year);
    }

    /**
     * Clear revenue-related caches when data changes.
     */
    private function clearRevenueCache(?int $year): void
    {
        if ($year) {
            Cache::forget("financial.revenues.totals.{$year}");
            Cache::forget("financial.revenues.monthly.{$year}");
        }
        Cache::forget('financial.revenues.yearly_aggregates');
        Cache::forget('financial.available_years');
        
        // Clear dashboard cache for organization
        if (Auth::check()) {
            Cache::forget('dashboard_stats_' . Auth::user()->organization_id);
        }
    }

    /**
     * Update the client's total_incomes from financial_revenues.
     *
     * Uses user_id for client isolation since the clients table uses user_id,
     * while revenues use organization_id.
     */
    private function updateClientTotal(?int $clientId, ?int $organizationId): void
    {
        if (!$clientId) {
            return;
        }

        // Get the client first to find its user_id
        $client = Client::withoutGlobalScopes()->find($clientId);
        if (!$client) {
            return;
        }

        // Sum revenues for this client within the organization
        $total = FinancialRevenue::withoutGlobalScopes()
            ->where('client_id', $clientId)
            ->when($organizationId, fn($q) => $q->where('organization_id', $organizationId))
            ->sum('amount');

        // Update the client's total_incomes
        $client->total_incomes = $total ?? 0;
        $client->saveQuietly();
    }
}
