<?php

namespace App\Services\Dashboard;

use App\Models\Client;
use App\Models\Domain;
use App\Models\Subscription;
use App\Models\Credential;
use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;
use Illuminate\Support\Facades\Cache;

/**
 * Metrics Aggregator Service
 *
 * Handles aggregation of key metrics including counts for clients, domains,
 * subscriptions, credentials, and basic financial overview data.
 *
 * Note: All amount fields are in RON. Records with currency='EUR' that have
 * amount_eur set have been properly converted. Records without amount_eur
 * are legacy records pending migration.
 */
class MetricsAggregator
{
    /**
     * Get cache TTL from config.
     */
    private function getCacheTtl(): int
    {
        return config('erp.cache.dashboard_metrics_ttl', 300);
    }

    /**
     * Get cache key with organization prefix
     */
    private function cacheKey(string $key): string
    {
        $orgId = auth()->user()->organization_id ?? 'default';
        return "org.{$orgId}.{$key}";
    }

    /**
     * Apply filter to include only records with RON amounts
     * Includes: RON records + converted EUR records (amount_eur is set)
     * Excludes: Legacy EUR records pending migration
     */
    private function applyRonFilter($query)
    {
        return $query->where(function($q) {
            $q->where('currency', 'RON')
              ->orWhereNotNull('amount_eur');
        });
    }

    /**
     * Get key metrics including counts for all main entities
     */
    public function getKeyMetrics(): array
    {
        $cacheKey = $this->cacheKey('dashboard.metrics');

        return Cache::remember($cacheKey, $this->getCacheTtl(), function () {
            $clientCount = Client::count();
            $now = now()->toDateTimeString();
            $domainStats = Domain::selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN expiry_date < ? THEN 1 ELSE 0 END) as expired
            ", [$now])->first();
            $subscriptionCounts = Subscription::selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired,
                SUM(CASE WHEN status = 'active' AND billing_cycle = 'monthly' THEN price ELSE 0 END) as monthly_cost
            ")->first();
            $credentialCount = Credential::count();

            // Include expiring domains in cache with eager loading
            // PERFORMANCE: Limit to 50 to prevent unbounded queries
            $expiringDomains = Domain::with(['client'])
                ->whereBetween('expiry_date', [now(), now()->addDays(30)])
                ->orderBy('expiry_date')
                ->limit(50)
                ->get();

            return [
                'totalClients' => $clientCount,
                'activeClients' => $clientCount,
                'totalDomains' => $domainStats->total,
                'activeDomains' => $domainStats->active,
                'expiredDomains' => $domainStats->expired,
                'totalSubscriptions' => $subscriptionCounts->total,
                'activeSubscriptions' => $subscriptionCounts->active,
                'expiredSubscriptions' => $subscriptionCounts->expired,
                'monthlySubscriptionCost' => $subscriptionCounts->monthly_cost,
                'totalCredentials' => $credentialCount,
                'expiringDomains' => $expiringDomains,
            ];
        });
    }

    /**
     * Get financial overview for current month and year
     */
    public function getFinancialOverview(): array
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $cacheKey = $this->cacheKey("dashboard.financial.{$currentYear}.{$currentMonth}");

        return Cache::remember($cacheKey, $this->getCacheTtl(), function () use ($currentYear, $currentMonth) {
            // Include RON records and converted EUR records
            $currentMonthRevenue = $this->applyRonFilter(
                FinancialRevenue::where('year', $currentYear)
                    ->where('month', $currentMonth)
            )->sum('amount');

            $currentMonthExpenses = $this->applyRonFilter(
                FinancialExpense::where('year', $currentYear)
                    ->where('month', $currentMonth)
            )->sum('amount');

            $yearlyRevenue = $this->applyRonFilter(
                FinancialRevenue::where('year', $currentYear)
            )->sum('amount');

            $yearlyExpenses = $this->applyRonFilter(
                FinancialExpense::where('year', $currentYear)
            )->sum('amount');

            $currentMonthProfit = $currentMonthRevenue - $currentMonthExpenses;
            $yearlyProfit = $yearlyRevenue - $yearlyExpenses;

            return [
                'currentMonthRevenue' => $currentMonthRevenue,
                'currentMonthExpenses' => $currentMonthExpenses,
                'currentMonthProfit' => $currentMonthProfit,
                'yearlyRevenue' => $yearlyRevenue,
                'yearlyExpenses' => $yearlyExpenses,
                'yearlyProfit' => $yearlyProfit,
                'currentMonthProfitMargin' => $currentMonthRevenue > 0
                    ? ($currentMonthProfit / $currentMonthRevenue) * 100
                    : 0,
                'yearlyProfitMargin' => $yearlyRevenue > 0
                    ? ($yearlyProfit / $yearlyRevenue) * 100
                    : 0,
            ];
        });
    }

    /**
     * Get recent activity data with caching and eager loading
     */
    public function getRecentActivity(): array
    {
        $cacheKey = $this->cacheKey('dashboard.activity');

        return Cache::remember($cacheKey, $this->getCacheTtl(), function () {
            return [
                'recentClients' => Client::with('status')->latest()->take(5)->get(),
                'recentDomains' => Domain::with(['client'])->latest()->take(5)->get(),
                'recentSubscriptions' => Subscription::latest()->take(5)->get(),
                // PERFORMANCE: Limit overdue subscriptions to prevent unbounded queries
                'overdueSubscriptions' => Subscription::where('status', 'active')
                    ->where('next_renewal_date', '<', now())
                    ->limit(50)
                    ->get(),
                // PERFORMANCE: Reduced from 200 to 20 for dashboard widget
                'clients' => Client::with('status')->orderBy('updated_at', 'desc')->limit(20)->get(),
            ];
        });
    }

    /**
     * Clear all metrics caches
     */
    public function clearCache(): void
    {
        Cache::forget($this->cacheKey('dashboard.metrics'));
        Cache::forget($this->cacheKey('dashboard.activity'));

        // Clear financial overview caches for current and previous months
        $currentYear = now()->year;
        $currentMonth = now()->month;
        $previousMonth = now()->subMonth();

        Cache::forget($this->cacheKey("dashboard.financial.{$currentYear}.{$currentMonth}"));
        Cache::forget($this->cacheKey("dashboard.financial.{$previousMonth->year}.{$previousMonth->month}"));
    }
}
