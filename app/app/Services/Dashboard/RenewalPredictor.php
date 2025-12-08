<?php

namespace App\Services\Dashboard;

use App\Models\Domain;
use App\Models\Subscription;

/**
 * Renewal Predictor Service
 *
 * Handles prediction and tracking of upcoming renewals for domains and subscriptions,
 * including cost calculations and expiry date monitoring.
 */
class RenewalPredictor
{
    /**
     * Get upcoming renewals for domains and subscriptions
     */
    public function getUpcomingRenewals(): array
    {
        return [
            'upcomingRenewals' => [
                'domains' => Domain::whereBetween('expiry_date', [now(), now()->addDays(30)])
                    ->orderBy('expiry_date')
                    ->take(10)
                    ->get(),
                'subscriptions' => Subscription::where('status', 'active')
                    ->whereBetween('next_renewal_date', [now(), now()->addDays(30)])
                    ->orderBy('next_renewal_date')
                    ->take(10)
                    ->get(),
            ],
        ];
    }

    /**
     * Get domain renewal costs and counts for 30, 60, 90 day windows
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
     */
    public function getExpiringDomains(int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        return Domain::whereBetween('expiry_date', [now(), now()->addDays($days)])
            ->orderBy('expiry_date')
            ->get();
    }

    /**
     * Get overdue subscriptions (active but past renewal date)
     */
    public function getOverdueSubscriptions(): \Illuminate\Database\Eloquent\Collection
    {
        return Subscription::where('status', 'active')
            ->where('next_renewal_date', '<', now())
            ->get();
    }

    /**
     * Calculate total renewal costs for upcoming period
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
     */
    public function getRenewalSummaryByMonth(int $months = 3): array
    {
        $summary = [];

        for ($i = 0; $i < $months; $i++) {
            $startDate = now()->copy()->addMonths($i)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            $domainCount = Domain::whereBetween('expiry_date', [$startDate, $endDate])->count();
            $domainCost = Domain::whereBetween('expiry_date', [$startDate, $endDate])->sum('annual_cost');

            $subscriptionCount = Subscription::where('status', 'active')
                ->whereBetween('next_renewal_date', [$startDate, $endDate])
                ->count();
            $subscriptionCost = Subscription::where('status', 'active')
                ->whereBetween('next_renewal_date', [$startDate, $endDate])
                ->sum('price');

            $summary[] = [
                'month' => $startDate->format('F Y'),
                'domains' => [
                    'count' => $domainCount,
                    'cost' => $domainCost,
                ],
                'subscriptions' => [
                    'count' => $subscriptionCount,
                    'cost' => $subscriptionCost,
                ],
                'total_cost' => $domainCost + $subscriptionCost,
            ];
        }

        return $summary;
    }
}
