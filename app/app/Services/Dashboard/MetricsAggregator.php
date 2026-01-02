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
     * Get key metrics including counts for all main entities
     */
    public function getKeyMetrics(): array
    {
        $cacheKey = $this->cacheKey('dashboard.metrics');

        return Cache::remember($cacheKey, $this->getCacheTtl(), function () {
            $clientCount = Client::count();
            $domainStats = Domain::selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN expiry_date < NOW() THEN 1 ELSE 0 END) as expired
            ")->first();
            $subscriptionCounts = Subscription::selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired,
                SUM(CASE WHEN status = 'active' AND billing_cycle = 'monthly' THEN price ELSE 0 END) as monthly_cost
            ")->first();
            $credentialCount = Credential::count();

            // Include expiring domains in cache with eager loading
            $expiringDomains = Domain::with(['client', 'subscriptions'])
                ->whereBetween('expiry_date', [now(), now()->addDays(30)])
                ->orderBy('expiry_date')
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

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($currentYear, $currentMonth) {
            $currentMonthRevenue = FinancialRevenue::where('year', $currentYear)
                ->where('month', $currentMonth)
                ->where('currency', 'RON')
                ->sum('amount');

            $currentMonthExpenses = FinancialExpense::where('year', $currentYear)
                ->where('month', $currentMonth)
                ->where('currency', 'RON')
                ->sum('amount');

            $yearlyRevenue = FinancialRevenue::where('year', $currentYear)
                ->where('currency', 'RON')
                ->sum('amount');

            $yearlyExpenses = FinancialExpense::where('year', $currentYear)
                ->where('currency', 'RON')
                ->sum('amount');

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
                'recentDomains' => Domain::with(['client', 'subscriptions.service'])->latest()->take(5)->get(),
                'recentSubscriptions' => Subscription::with(['domain', 'service'])->latest()->take(5)->get(),
                'overdueSubscriptions' => Subscription::with(['domain', 'service'])
                    ->where('status', 'active')
                    ->where('next_renewal_date', '<', now())
                    ->get(),
                'clients' => Client::with('status')->orderBy('updated_at', 'desc')->limit(200)->get(),
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
