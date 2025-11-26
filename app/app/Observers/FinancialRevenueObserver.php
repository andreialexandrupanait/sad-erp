<?php

namespace App\Observers;

use App\Models\FinancialRevenue;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class FinancialRevenueObserver
{
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
    }

    /**
     * Update the client's total_incomes from financial_revenues.
     *
     * FIXED: Uses model queries with organization_id to maintain multi-tenant isolation.
     * Previously used raw DB queries that bypassed global scopes, potentially summing
     * revenues across all organizations.
     */
    private function updateClientTotal(?int $clientId, ?int $organizationId): void
    {
        if (!$clientId) {
            return;
        }

        // Use model query with explicit organization_id to maintain tenant isolation
        // We bypass the user scope since the organization scope is what matters for isolation
        $total = FinancialRevenue::withoutGlobalScope('user_scope')
            ->where('organization_id', $organizationId)
            ->where('client_id', $clientId)
            ->sum('amount');

        // Update the client's total_incomes within the same organization
        Client::withoutGlobalScope('user_scope')
            ->where('id', $clientId)
            ->where('organization_id', $organizationId)
            ->update(['total_incomes' => $total ?? 0]);
    }
}
