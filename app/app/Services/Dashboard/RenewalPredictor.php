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
        return [
            'upcomingRenewals' => [
                'domains' => Domain::with('client')
                    ->whereBetween('expiry_date', [now(), now()->addDays(30)])
                    ->orderBy('expiry_date')
                    ->take(10)
                    ->get(),
                'subscriptions' => Subscription::with(['organization', 'user'])
                    ->where('status', 'active')
                    ->whereBetween('next_renewal_date', [now(), now()->addDays(30)])
                    ->orderBy('next_renewal_date')
                    ->take(10)
                    ->get(),
            ],
        ];
    }

    /**
     * Get domain renewal analytics for 30, 60, 90 day windows
     *
     * OPTIMIZATION: Single query for all data, then filter in PHP for better performance
     */
    public function getDomainRenewalAnalytics(): array
    {
        $domainsExpiring = Domain::whereBetween('expiry_date', [now(), now()->addDays(90)])
            ->select('id', 'expiry_date', 'annual_cost')
            ->get();

        $day30 = now()->copy()->addDays(30);
        $day60 = now()->copy()->addDays(60);

        $domains30Days = $domainsExpiring->filter(fn($d) => $d->expiry_date <= $day30);
        $domains60Days = $domainsExpiring->filter(fn($d) => $d->expiry_date <= $day60);

        return [
            'domainRenewals30Days' => [
                'count' => $domains30Days->count(),
                'cost' => $domains30Days->sum('annual_cost'),
            ],
            'domainRenewals60Days' => [
                'count' => $domains60Days->count(),
                'cost' => $domains60Days->sum('annual_cost'),
            ],
            'domainRenewals90Days' => [
                'count' => $domainsExpiring->count(),
                'cost' => $domainsExpiring->sum('annual_cost'),
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
        return Domain::with('client')
            ->whereBetween('expiry_date', [now(), now()->addDays($days)])
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
        $domains = Domain::whereBetween('expiry_date', [now(), now()->addDays($days)])
            ->sum('annual_cost');

        $subscriptions = Subscription::where('status', 'active')
            ->whereBetween('next_renewal_date', [now(), now()->addDays($days)])
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
