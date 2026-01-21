<?php

namespace App\Services\Dashboard;

use App\Models\Domain;
use App\Models\Subscription;

/**
 * Renewal Predictor Service
 *
 * Handles prediction and tracking of upcoming renewals for domains and subscriptions,
 * including cost calculations and expiry date monitoring.
 *
 * OPTIMIZED VERSION: All queries now use eager loading to prevent N+1 issues
 */
class RenewalPredictor
{
    /**
     * Get upcoming renewals for domains and subscriptions
     *
     * OPTIMIZATION: Added eager loading for client, organization, and user relationships
     */
    public function getUpcomingRenewals(): array
    {
        // Cache now() to avoid redundant Carbon instantiations
        $now = now();
        $thirtyDaysFromNow = $now->copy()->addDays(30);

        return [
            'upcomingRenewals' => [
                'domains' => Domain::with('client')
                    ->whereBetween('expiry_date', [$now, $thirtyDaysFromNow])
                    ->orderBy('expiry_date')
                    ->take(10)
                    ->get(),
                'subscriptions' => Subscription::with(['organization', 'user'])
                    ->where('status', 'active')
                    ->whereBetween('next_renewal_date', [$now, $thirtyDaysFromNow])
                    ->orderBy('next_renewal_date')
                    ->take(10)
                    ->get(),
            ],
        ];
    }

    /**
     * Get domain renewal analytics for 30, 60, 90 day windows
     *
     * OPTIMIZATION: Use database aggregation with conditional sums instead of PHP filtering.
     * Single query returns only aggregated values, not all domain records.
     */
    public function getDomainRenewalAnalytics(): array
    {
        // Cache now() to avoid redundant Carbon instantiations
        $now = now();
        $day30 = $now->copy()->addDays(30)->toDateString();
        $day60 = $now->copy()->addDays(60)->toDateString();
        $day90 = $now->copy()->addDays(90)->toDateString();
        $today = $now->toDateString();

        // Single query with conditional aggregation - returns 1 row instead of N domain records
        $stats = Domain::whereBetween('expiry_date', [$today, $day90])
            ->selectRaw("
                SUM(CASE WHEN expiry_date <= ? THEN 1 ELSE 0 END) as count_30,
                SUM(CASE WHEN expiry_date <= ? THEN annual_cost ELSE 0 END) as cost_30,
                SUM(CASE WHEN expiry_date <= ? THEN 1 ELSE 0 END) as count_60,
                SUM(CASE WHEN expiry_date <= ? THEN annual_cost ELSE 0 END) as cost_60,
                COUNT(*) as count_90,
                COALESCE(SUM(annual_cost), 0) as cost_90
            ", [$day30, $day30, $day60, $day60])
            ->first();

        return [
            'domainRenewals30Days' => [
                'count' => (int) ($stats->count_30 ?? 0),
                'cost' => (float) ($stats->cost_30 ?? 0),
            ],
            'domainRenewals60Days' => [
                'count' => (int) ($stats->count_60 ?? 0),
                'cost' => (float) ($stats->cost_60 ?? 0),
            ],
            'domainRenewals90Days' => [
                'count' => (int) ($stats->count_90 ?? 0),
                'cost' => (float) ($stats->cost_90 ?? 0),
            ],
        ];
    }

    /**
     * Get domains expiring within specified days
     *
     * OPTIMIZATION: Added eager loading for client relationship
     */
    public function getExpiringDomains(int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        $now = now();
        return Domain::with('client')
            ->whereBetween('expiry_date', [$now, $now->copy()->addDays($days)])
            ->orderBy('expiry_date')
            ->get();
    }

    /**
     * Get overdue subscriptions (active but past renewal date)
     *
     * OPTIMIZATION: Added eager loading for organization and user relationships
     */
    public function getOverdueSubscriptions(): \Illuminate\Database\Eloquent\Collection
    {
        return Subscription::with(['organization', 'user'])
            ->where('status', 'active')
            ->where('next_renewal_date', '<', now())
            ->get();
    }

    /**
     * Calculate total renewal costs for upcoming period
     *
     * This method is already optimized - uses SUM aggregation
     */
    public function calculateUpcomingRenewalCosts(int $days = 30): array
    {
        $now = now();
        $endDate = $now->copy()->addDays($days);

        $domains = Domain::whereBetween('expiry_date', [$now, $endDate])
            ->sum('annual_cost');

        $subscriptions = Subscription::where('status', 'active')
            ->whereBetween('next_renewal_date', [$now, $endDate])
            ->sum('price');

        return [
            'domains' => $domains,
            'subscriptions' => $subscriptions,
            'total' => $domains + $subscriptions,
        ];
    }

    /**
     * Get renewal summary by month for next N months
     *
     * OPTIMIZATION: Reduced database queries from 4N to 2N by combining count/sum operations
     */
    public function getRenewalSummaryByMonth(int $months = 3): array
    {
        $summary = [];

        for ($i = 0; $i < $months; $i++) {
            $startDate = now()->copy()->addMonths($i)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            // OPTIMIZATION: Use selectRaw to get both count and sum in single query
            $domainStats = Domain::whereBetween('expiry_date', [$startDate, $endDate])
                ->selectRaw('COUNT(*) as count, COALESCE(SUM(annual_cost), 0) as total_cost')
                ->first();

            $subscriptionStats = Subscription::where('status', 'active')
                ->whereBetween('next_renewal_date', [$startDate, $endDate])
                ->selectRaw('COUNT(*) as count, COALESCE(SUM(price), 0) as total_cost')
                ->first();

            $summary[] = [
                'month' => $startDate->format('F Y'),
                'domains' => [
                    'count' => $domainStats->count ?? 0,
                    'cost' => $domainStats->total_cost ?? 0,
                ],
                'subscriptions' => [
                    'count' => $subscriptionStats->count ?? 0,
                    'cost' => $subscriptionStats->total_cost ?? 0,
                ],
                'total_cost' => ($domainStats->total_cost ?? 0) + ($subscriptionStats->total_cost ?? 0),
            ];
        }

        return $summary;
    }
}
