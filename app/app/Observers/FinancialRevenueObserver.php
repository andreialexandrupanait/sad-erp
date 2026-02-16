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
        $this->updateClientStats($revenue->client_id, $revenue->organization_id);
        $this->clearRevenueCache($revenue->year, $revenue->organization_id, $revenue->month);
    }

    /**
     * Handle the FinancialRevenue "deleted" event.
     */
    public function deleted(FinancialRevenue $revenue): void
    {
        $this->updateClientStats($revenue->client_id, $revenue->organization_id);
        $this->clearRevenueCache($revenue->year, $revenue->organization_id, $revenue->month);
    }

    /**
     * Handle the FinancialRevenue "restored" event.
     */
    public function restored(FinancialRevenue $revenue): void
    {
        $this->updateClientStats($revenue->client_id, $revenue->organization_id);
        $this->clearRevenueCache($revenue->year, $revenue->organization_id, $revenue->month);
    }

    /**
     * Handle the FinancialRevenue "force deleted" event.
     */
    public function forceDeleted(FinancialRevenue $revenue): void
    {
        $this->updateClientStats($revenue->client_id, $revenue->organization_id);
        $this->clearRevenueCache($revenue->year, $revenue->organization_id, $revenue->month);
    }

    /**
     * Clear revenue-related caches when data changes.
     */
    private function clearRevenueCache(?int $year, ?int $organizationId = null, ?int $month = null): void
    {
        // Get organization ID from parameter or authenticated user
        $orgId = $organizationId ?? (Auth::check() ? Auth::user()->organization_id : null);

        if (!$orgId) {
            return;
        }

        $currentMonth = $month ?? now()->month;
        $currentYear = $year ?? now()->year;

        // Clear revenue aggregator caches (org-prefixed)
        if ($year) {
            Cache::forget("org.{$orgId}.financial.revenues.totals.{$year}");
            Cache::forget("org.{$orgId}.financial.revenues.monthly.{$year}.all");
            Cache::forget("org.{$orgId}.financial.revenues.monthly.{$year}.RON");
            Cache::forget("org.{$orgId}.financial.revenues.monthly.{$year}.EUR");
            Cache::forget("org.{$orgId}.financial.revenues.count.{$year}");
            Cache::forget("org.{$orgId}.financial.revenues.clients.{$year}");
        }
        Cache::forget("org.{$orgId}.financial.revenues.all_years");

        // Clear financial dashboard caches
        Cache::forget("org.{$orgId}.financial.available_years");
        Cache::forget("org.{$orgId}.financial.cashflow.revenues.{$currentYear}");
        Cache::forget("org.{$orgId}.financial.analytics.top_clients");

        // Clear dashboard trend caches
        Cache::forget("org.{$orgId}.dashboard.revenue_trend_6m");
        Cache::forget("org.{$orgId}.dashboard.yearly_revenue");
        Cache::forget("org.{$orgId}.dashboard.yearly_profit");
        Cache::forget("org.{$orgId}.dashboard.top_clients");
        Cache::forget("org.{$orgId}.dashboard.top_clients_current_year");

        // Clear dashboard metrics
        Cache::forget("org.{$orgId}.dashboard.metrics");
        Cache::forget("org.{$orgId}.dashboard.activity");
        Cache::forget("org.{$orgId}.dashboard.financial.{$currentYear}.{$currentMonth}");
    }

    /**
     * Update the client's total_incomes, last_invoice_at, and currency from financial_revenues.
     *
     * Uses user_id for client isolation since the clients table uses user_id,
     * while revenues use organization_id.
     */
    private function updateClientStats(?int $clientId, ?int $organizationId): void
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

        // Get the most recent invoice date
        $lastInvoiceAt = FinancialRevenue::withoutGlobalScopes()
            ->where('client_id', $clientId)
            ->when($organizationId, fn($q) => $q->where('organization_id', $organizationId))
            ->max('occurred_at');

        // Get the most common currency for this client
        $currency = FinancialRevenue::withoutGlobalScopes()
            ->where('client_id', $clientId)
            ->when($organizationId, fn($q) => $q->where('organization_id', $organizationId))
            ->selectRaw('currency, COUNT(*) as cnt')
            ->groupBy('currency')
            ->orderByDesc('cnt')
            ->value('currency');

        // Update the client's stats
        $client->total_incomes = $total ?? 0;
        $client->last_invoice_at = $lastInvoiceAt;
        if ($currency) {
            $client->currency = $currency;
        }
        $client->saveQuietly();
    }
}
