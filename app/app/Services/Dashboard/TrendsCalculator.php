<?php

namespace App\Services\Dashboard;

use App\Models\Client;
use App\Models\Subscription;
use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Trends Calculator Service
 *
 * Handles calculation of financial trends, growth rates, and analytics
 * including monthly/yearly trends and top client rankings.
 */
class TrendsCalculator
{
    private const CACHE_TTL = 600; // 10 minutes

    /**
     * Get cache key with organization prefix
     */
    private function cacheKey(string $key): string
    {
        $orgId = auth()->user()->organization_id ?? 'default';
        return "org.{$orgId}.{$key}";
    }

    /**
     * Get all trend data for dashboard
     */
    public function getTrends(): array
    {
        return [
            'revenueTrend' => Cache::remember(
                $this->cacheKey('dashboard.revenue_trend_6m'),
                self::CACHE_TTL,
                fn() => $this->getMonthlyTrend('revenue', 6)
            ),
            'expenseTrend' => Cache::remember(
                $this->cacheKey('dashboard.expense_trend_6m'),
                self::CACHE_TTL,
                fn() => $this->getMonthlyTrend('expense', 6)
            ),
            'yearlyRevenueTrend' => Cache::remember(
                $this->cacheKey('dashboard.yearly_revenue'),
                self::CACHE_TTL,
                fn() => $this->getYearlyTrend('revenue')
            ),
            'yearlyExpenseTrend' => Cache::remember(
                $this->cacheKey('dashboard.yearly_expense'),
                self::CACHE_TTL,
                fn() => $this->getYearlyTrend('expense')
            ),
            'yearlyProfitTrend' => Cache::remember(
                $this->cacheKey('dashboard.yearly_profit'),
                self::CACHE_TTL,
                function () {
                    return $this->calculateProfitTrend(
                        $this->getYearlyTrend('revenue'),
                        $this->getYearlyTrend('expense')
                    );
                }
            ),
            'topClients' => Cache::remember(
                $this->cacheKey('dashboard.top_clients'),
                self::CACHE_TTL,
                fn() => $this->getTopClientsByRevenue()
            ),
        ];
    }

    /**
     * Get analytics including growth rates and revenue concentration
     */
    public function getAnalytics(): array
    {
        $previousMonth = now()->subMonth();

        // Month-over-month growth
        $previousMonthRevenue = FinancialRevenue::where('year', $previousMonth->year)
            ->where('month', $previousMonth->month)
            ->where('currency', 'RON')
            ->sum('amount');
        $previousMonthExpenses = FinancialExpense::where('year', $previousMonth->year)
            ->where('month', $previousMonth->month)
            ->where('currency', 'RON')
            ->sum('amount');

        $currentMonthRevenue = FinancialRevenue::where('year', now()->year)
            ->where('month', now()->month)
            ->where('currency', 'RON')
            ->sum('amount');
        $currentMonthExpenses = FinancialExpense::where('year', now()->year)
            ->where('month', now()->month)
            ->where('currency', 'RON')
            ->sum('amount');

        // Client growth
        $newClientsThisMonth = Client::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        $newClientsLastMonth = Client::whereYear('created_at', $previousMonth->year)
            ->whereMonth('created_at', $previousMonth->month)
            ->count();

        // Subscription stats
        $subscriptionStats = Subscription::select('status', 'billing_cycle', DB::raw('COUNT(*) as count'), DB::raw('SUM(price) as total'))
            ->groupBy('status', 'billing_cycle')
            ->get();

        $monthlySubscriptionCost = $subscriptionStats->where('status', 'active')->where('billing_cycle', 'monthly')->sum('total');
        $yearlySubscriptionCost = $subscriptionStats->where('status', 'active')->where('billing_cycle', 'yearly')->sum('total');

        // Revenue concentration
        $topClients = $this->getTopClientsByRevenue(3);
        $topClientsRevenue = $topClients->sum('total_revenue');
        $yearlyRevenue = FinancialRevenue::where('year', now()->year)->where('currency', 'RON')->sum('amount');

        // Expense categories
        $categoryBreakdown = FinancialExpense::where('year', now()->year)
            ->whereNotNull('category_option_id')
            ->select('category_option_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category_option_id')
            ->with('category')
            ->get()
            ->sortByDesc('total')
            ->take(8);

        return [
            'revenueGrowth' => $previousMonthRevenue > 0
                ? (($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100
                : 0,
            'expenseGrowth' => $previousMonthExpenses > 0
                ? (($currentMonthExpenses - $previousMonthExpenses) / $previousMonthExpenses) * 100
                : 0,
            'newClientsThisMonth' => $newClientsThisMonth,
            'newClientsLastMonth' => $newClientsLastMonth,
            'clientGrowth' => $newClientsLastMonth > 0
                ? (($newClientsThisMonth - $newClientsLastMonth) / $newClientsLastMonth) * 100
                : 0,
            'monthlySubscriptionCost' => $monthlySubscriptionCost,
            'yearlySubscriptionCost' => $yearlySubscriptionCost,
            'annualProjectedCost' => ($monthlySubscriptionCost * 12) + $yearlySubscriptionCost,
            'activeSubscriptionsCount' => $subscriptionStats->where('status', 'active')->sum('count'),
            'pausedSubscriptionsCount' => $subscriptionStats->where('status', 'paused')->sum('count'),
            'cancelledSubscriptionsCount' => $subscriptionStats->where('status', 'cancelled')->sum('count'),
            'revenueConcentration' => $yearlyRevenue > 0
                ? ($topClientsRevenue / $yearlyRevenue) * 100
                : 0,
            'topThreeClientsRevenue' => $topClientsRevenue,
            'categoryBreakdown' => $categoryBreakdown,
        ];
    }

    /**
     * Get monthly trend data for specified number of months
     */
    public function getMonthlyTrend(string $type = 'revenue', int $months = 6): array
    {
        $data = [];
        $startDate = now()->subMonths($months - 1)->startOfMonth();
        $model = $type === 'revenue' ? FinancialRevenue::class : FinancialExpense::class;

        $monthlyData = $model::where(function ($query) use ($startDate, $months) {
            for ($i = 0; $i < $months; $i++) {
                $date = $startDate->copy()->addMonths($i);
                $query->orWhere(function ($q) use ($date) {
                    $q->where('year', $date->year)->where('month', $date->month);
                });
            }
        })
            ->select('year', 'month', DB::raw('SUM(amount) as total'))
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(fn($item) => $item->year . '-' . $item->month);

        for ($i = 0; $i < $months; $i++) {
            $date = $startDate->copy()->addMonths($i);
            $key = $date->year . '-' . $date->month;
            $amount = $monthlyData->get($key)?->total ?? 0;

            $data[] = [
                'month' => $date->format('M'),
                'year' => $date->year,
                'amount' => $amount,
                'formatted' => number_format($amount, 2) . ' RON',
            ];
        }

        return $data;
    }

    /**
     * Get yearly trend data for all 12 months of current year
     */
    public function getYearlyTrend(string $type = 'revenue'): array
    {
        $data = [];
        $currentYear = now()->year;
        $model = $type === 'revenue' ? FinancialRevenue::class : FinancialExpense::class;

        $romanianMonths = ['Ian', 'Feb', 'Mar', 'Apr', 'Mai', 'Iun', 'Iul', 'Aug', 'Sep', 'Oct', 'Noi', 'Dec'];

        $monthlyData = $model::where('year', $currentYear)
            ->where('currency', 'RON')
            ->select('month', DB::raw('SUM(amount) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->mapWithKeys(fn($item) => [$item->month => $item->total]);

        for ($month = 1; $month <= 12; $month++) {
            $amount = $monthlyData->get($month, 0);

            $data[] = [
                'month' => $romanianMonths[$month - 1],
                'month_number' => $month,
                'year' => $currentYear,
                'amount' => $amount,
                'formatted' => number_format($amount, 2) . ' RON',
            ];
        }

        return $data;
    }

    /**
     * Get top clients ranked by total revenue
     */
    public function getTopClientsByRevenue(int $limit = 5): Collection
    {
        $userId = auth()->id();

        return Client::select('clients.*')
            ->selectRaw('COALESCE(SUM(financial_revenues.amount), 0) as total_revenue')
            ->leftJoin('financial_revenues', function ($join) use ($userId) {
                $join->on('clients.id', '=', 'financial_revenues.client_id')
                    ->where('financial_revenues.currency', '=', 'RON');
                if ($userId) {
                    $join->where('financial_revenues.user_id', '=', $userId);
                }
            })
            ->when($userId, function ($query) use ($userId) {
                return $query->where('clients.user_id', $userId);
            })
            ->withoutGlobalScope('organization')
            ->groupBy('clients.id')
            ->orderByDesc('total_revenue')
            ->take($limit)
            ->get();
    }

    /**
     * Calculate profit trend from revenue and expense trends
     */
    public function calculateProfitTrend(array $revenueTrend, array $expenseTrend): array
    {
        $profitTrend = [];

        foreach ($revenueTrend as $index => $revenue) {
            $profit = $revenue['amount'] - $expenseTrend[$index]['amount'];

            $profitTrend[] = [
                'month' => $revenue['month'],
                'month_number' => $revenue['month_number'],
                'year' => $revenue['year'],
                'amount' => $profit,
                'formatted' => number_format($profit, 2) . ' RON',
            ];
        }

        return $profitTrend;
    }

    /**
     * Clear all trend caches
     */
    public function clearCache(): void
    {
        Cache::forget($this->cacheKey('dashboard.revenue_trend_6m'));
        Cache::forget($this->cacheKey('dashboard.expense_trend_6m'));
        Cache::forget($this->cacheKey('dashboard.yearly_revenue'));
        Cache::forget($this->cacheKey('dashboard.yearly_expense'));
        Cache::forget($this->cacheKey('dashboard.yearly_profit'));
        Cache::forget($this->cacheKey('dashboard.top_clients'));
    }
}
