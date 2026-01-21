<?php

namespace App\Services\Dashboard;

use App\Models\Client;
use App\Models\Subscription;
use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Trends Calculator Service
 *
 * Handles calculation of financial trends, growth rates, and analytics
 * including monthly/yearly trends and top client rankings.
 *
 * Note: All amount fields are in RON. Records with currency='EUR' that have
 * amount_eur set have been properly converted. Records without amount_eur
 * are legacy records pending migration.
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
                $this->cacheKey('dashboard.top_clients_current_year'),
                self::CACHE_TTL,
                fn() => $this->getTopClientsByRevenue(
                    5,
                    Carbon::now()->startOfYear(),
                    Carbon::now()->endOfDay()
                )
            ),
        ];
    }

    /**
     * Get analytics including growth rates and revenue concentration
     */
    public function getAnalytics(): array
    {
        // Cache now() at method start to avoid redundant Carbon instantiations
        $now = now();
        $currentYear = $now->year;
        $currentMonth = $now->month;
        $previousMonth = $now->copy()->subMonth();

        // Month-over-month growth - include all RON-valued records
        $previousMonthRevenue = $this->applyRonFilter(
            FinancialRevenue::where('year', $previousMonth->year)
                ->where('month', $previousMonth->month)
        )->sum('amount');

        $previousMonthExpenses = $this->applyRonFilter(
            FinancialExpense::where('year', $previousMonth->year)
                ->where('month', $previousMonth->month)
        )->sum('amount');

        $currentMonthRevenue = $this->applyRonFilter(
            FinancialRevenue::where('year', $currentYear)
                ->where('month', $currentMonth)
        )->sum('amount');

        $currentMonthExpenses = $this->applyRonFilter(
            FinancialExpense::where('year', $currentYear)
                ->where('month', $currentMonth)
        )->sum('amount');

        // Client growth
        $newClientsThisMonth = Client::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
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
        $yearlyRevenue = $this->applyRonFilter(
            FinancialRevenue::where('year', $currentYear)
        )->sum('amount');

        // Expense categories - use SQL ordering
        $categoryBreakdown = FinancialExpense::where('year', $currentYear)
            ->whereNotNull('category_option_id')
            ->select('category_option_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category_option_id')
            ->orderByDesc('total')
            ->limit(8)
            ->with('category')
            ->get();

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

        // Build array of year-month combinations for efficient whereIn query
        $yearMonthConditions = [];
        for ($i = 0; $i < $months; $i++) {
            $date = $startDate->copy()->addMonths($i);
            $yearMonthConditions[] = ['year' => $date->year, 'month' => $date->month];
        }

        $monthlyData = $model::where(function ($query) use ($yearMonthConditions) {
            foreach ($yearMonthConditions as $condition) {
                $query->orWhere(function ($q) use ($condition) {
                    $q->where('year', $condition['year'])->where('month', $condition['month']);
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

        // Include RON records and converted EUR records
        $monthlyData = $this->applyRonFilter(
            $model::where('year', $currentYear)
        )
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
     *
     * @param int $limit Number of clients to return
     * @param Carbon|null $from Start date for filtering revenues
     * @param Carbon|null $to End date for filtering revenues
     */
    public function getTopClientsByRevenue(int $limit = 5, ?Carbon $from = null, ?Carbon $to = null): Collection
    {
        $userId = auth()->id();

        // Build revenue subquery - include RON and converted EUR records
        $revenueQuery = FinancialRevenue::select('client_id', DB::raw('SUM(amount) as total_revenue'))
            ->where(function($q) {
                $q->where('currency', 'RON')
                  ->orWhereNotNull('amount_eur');
            })
            ->when($userId, function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->when($from && $to, function ($query) use ($from, $to) {
                $query->whereBetween('occurred_at', [
                    $from->copy()->startOfDay(),
                    $to->copy()->endOfDay()
                ]);
            })
            ->groupBy('client_id');

        return Client::select('clients.*')
            ->selectRaw('COALESCE(revenue_totals.total_revenue, 0) as total_revenue')
            ->leftJoinSub($revenueQuery, 'revenue_totals', function ($join) {
                $join->on('clients.id', '=', 'revenue_totals.client_id');
            })
            ->when($userId, function ($query) use ($userId) {
                return $query->where('clients.user_id', $userId);
            })
            ->having('total_revenue', '>', 0)
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
        Cache::forget($this->cacheKey('dashboard.top_clients_current_year'));
    }
}
