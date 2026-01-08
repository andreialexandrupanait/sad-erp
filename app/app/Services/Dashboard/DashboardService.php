<?php

namespace App\Services\Dashboard;

/**
 * Dashboard Service
 *
 * Orchestrates dashboard data aggregation by delegating to specialized services.
 * Provides a unified interface for retrieving all dashboard-related data.
 *
 * @package App\Services\Dashboard
 */
class DashboardService
{
    protected MetricsAggregator $metricsAggregator;
    protected TrendsCalculator $trendsCalculator;
    protected RenewalPredictor $renewalPredictor;
    protected NomenclatureProvider $nomenclatureProvider;

    public function __construct(
        MetricsAggregator $metricsAggregator,
        TrendsCalculator $trendsCalculator,
        RenewalPredictor $renewalPredictor,
        NomenclatureProvider $nomenclatureProvider
    ) {
        $this->metricsAggregator = $metricsAggregator;
        $this->trendsCalculator = $trendsCalculator;
        $this->renewalPredictor = $renewalPredictor;
        $this->nomenclatureProvider = $nomenclatureProvider;
    }

    /**
     * Get all dashboard data aggregated from all sources.
     * Orchestrates calls to all specialized aggregators.
     *
     * @return array<string, mixed> Complete dashboard data array
     */
    public function getDashboardData(): array
    {
        $data = array_merge(
            $this->getKeyMetrics(),
            $this->getFinancialOverview(),
            $this->getRecentActivity(),
            $this->getTrends(),
            $this->getUpcomingRenewals(),
            $this->getNomenclature(),
            $this->getAnalytics()
        );

        return $data;
    }

    /**
     * Get key metrics including counts for clients, domains, subscriptions, and credentials.
     * Delegates to MetricsAggregator.
     */
    public function getKeyMetrics(): array
    {
        return $this->metricsAggregator->getKeyMetrics();
    }

    /**
     * Get financial overview including revenue, expenses, and profit for current month and year.
     * Delegates to MetricsAggregator.
     */
    public function getFinancialOverview(): array
    {
        return $this->metricsAggregator->getFinancialOverview();
    }

    /**
     * Get recent activity including latest clients, domains, and subscriptions.
     * Delegates to MetricsAggregator.
     */
    public function getRecentActivity(): array
    {
        return $this->metricsAggregator->getRecentActivity();
    }

    /**
     * Get trend data for revenue, expenses, and profit over time.
     * Delegates to TrendsCalculator.
     */
    public function getTrends(): array
    {
        return $this->trendsCalculator->getTrends();
    }

    /**
     * Get upcoming domain and subscription renewals within the next 30 days.
     * Delegates to RenewalPredictor.
     */
    public function getUpcomingRenewals(): array
    {
        return $this->renewalPredictor->getUpcomingRenewals();
    }

    /**
     * Get nomenclature data for form dropdowns.
     * Delegates to NomenclatureProvider.
     */
    public function getNomenclature(): array
    {
        return $this->nomenclatureProvider->getNomenclature();
    }

    /**
     * Get additional analytics including growth rates, subscription costs, and revenue concentration.
     * Orchestrates TrendsCalculator and RenewalPredictor.
     */
    public function getAnalytics(): array
    {
        $analytics = $this->trendsCalculator->getAnalytics();
        $renewalAnalytics = $this->renewalPredictor->getDomainRenewalAnalytics();

        return array_merge($analytics, $renewalAnalytics);
    }

    /**
     * Get monthly trend data for a specified number of months.
     * Delegates to TrendsCalculator.
     */
    public function getMonthlyTrend(string $type = 'revenue', int $months = 6): array
    {
        return $this->trendsCalculator->getMonthlyTrend($type, $months);
    }

    /**
     * Get yearly trend data for all 12 months of the current year.
     * Delegates to TrendsCalculator.
     */
    public function getYearlyTrend(string $type = 'revenue'): array
    {
        return $this->trendsCalculator->getYearlyTrend($type);
    }

    /**
     * Get top clients ranked by total revenue.
     * Delegates to TrendsCalculator.
     */
    public function getTopClientsByRevenue(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return $this->trendsCalculator->getTopClientsByRevenue($limit);
    }

    /**
     * Calculate profit trend by subtracting expenses from revenue for each period.
     * Delegates to TrendsCalculator.
     */
    public function calculateProfitTrend(array $revenueTrend, array $expenseTrend): array
    {
        return $this->trendsCalculator->calculateProfitTrend($revenueTrend, $expenseTrend);
    }

    /**
     * Clear all dashboard caches.
     * Delegates to all aggregators.
     */
    public function clearCache(): void
    {
        $this->metricsAggregator->clearCache();
        $this->trendsCalculator->clearCache();
        $this->nomenclatureProvider->clearCache();
    }
}
