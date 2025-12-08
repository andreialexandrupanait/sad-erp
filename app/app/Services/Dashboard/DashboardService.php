<?php

namespace App\Services\Dashboard;

use App\Models\Client;
use App\Models\Credential;
use App\Models\Domain;
use App\Models\Subscription;
use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;
use App\Models\SettingOption;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Dashboard Service
 *
 * Provides aggregated data for the main dashboard including metrics,
 * financial overview, trends, and nomenclature data.
 *
 * @package App\Services\Dashboard
 */
class DashboardService
{
    /**
     * Cache TTL constants (in seconds)
     */
    private const CACHE_TTL_SHORT = 300;    // 5 minutes - for frequently changing data
    private const CACHE_TTL_MEDIUM = 600;   // 10 minutes - for trends and analytics
    private const CACHE_TTL_LONG = 3600;    // 1 hour - for nomenclature/settings

    /**
     * Get all dashboard data aggregated from all sources.
     *
     * @return array<string, mixed> Complete dashboard data array
     */
    public function getDashboardData(): array
    {
        $orgId = auth()->user()->organization_id ?? 0;

        $data = array_merge(
            $this->getKeyMetrics($orgId),
            $this->getFinancialOverview($orgId),
            $this->getRecentActivity(),
            $this->getTrends($orgId),
            $this->getUpcomingRenewals(),
            $this->getNomenclature($orgId),
            $this->getAnalytics($orgId)
        );

        return $data;
    }

    /**
     * Get key metrics including counts for clients, domains, subscriptions, and credentials.
     *
     * @param int $orgId Organization ID for cache key scoping
     * @return array<string, mixed> Key metrics data
     */
    public function getKeyMetrics(int $orgId): array
    {
        $cacheKey = "dashboard_stats_{$orgId}";

        $cachedStats = Cache::remember($cacheKey, self::CACHE_TTL_SHORT, function () {
            return [
                'clientCount' => Client::count(),
                'domainStats' => Domain::selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN expiry_date < NOW() THEN 1 ELSE 0 END) as expired
                ")->first(),
                'subscriptionCounts' => Subscription::selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired,
                    SUM(CASE WHEN status = 'active' AND billing_cycle = 'monthly' THEN price ELSE 0 END) as monthly_cost
                ")->first(),
                'credentialCount' => Credential::count(),
            ];
        });

        return [
            'totalClients' => $cachedStats['clientCount'],
            'activeClients' => $cachedStats['clientCount'],
            'totalDomains' => $cachedStats['domainStats']->total,
            'activeDomains' => $cachedStats['domainStats']->active,
            'expiredDomains' => $cachedStats['domainStats']->expired,
            'totalSubscriptions' => $cachedStats['subscriptionCounts']->total,
            'activeSubscriptions' => $cachedStats['subscriptionCounts']->active,
            'expiredSubscriptions' => $cachedStats['subscriptionCounts']->expired,
            'monthlySubscriptionCost' => $cachedStats['subscriptionCounts']->monthly_cost,
            'totalCredentials' => $cachedStats['credentialCount'],
            'expiringDomains' => Domain::whereBetween('expiry_date', [now(), now()->addDays(30)])->get(),
        ];
    }

    /**
     * Get financial overview including revenue, expenses, and profit for current month and year.
     *
     * @param int $orgId Organization ID (currently unused but kept for consistency)
     * @return array<string, float|int> Financial metrics in RON currency
     */
    public function getFinancialOverview(int $orgId): array
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

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
    }

    /**
     * Get recent activity including latest clients, domains, and subscriptions.
     *
     * @return array<string, Collection> Recent activity collections
     */
    public function getRecentActivity(): array
    {
        return [
            'recentClients' => Client::with('status')->latest()->take(5)->get(),
            'recentDomains' => Domain::with('client')->latest()->take(5)->get(),
            'recentSubscriptions' => Subscription::latest()->take(5)->get(),
            'overdueSubscriptions' => Subscription::where('status', 'active')
                ->where('next_renewal_date', '<', now())
                ->get(),
            'clients' => Client::with('status')->orderBy('updated_at', 'desc')->limit(200)->get(),
        ];
    }

    /**
     * Get trend data for revenue, expenses, and profit over time.
     *
     * @param int $orgId Organization ID for cache key scoping
     * @return array<string, array> Trend data arrays with monthly breakdowns
     */
    public function getTrends(int $orgId): array
    {
        return [
            'revenueTrend' => Cache::remember(
                "dashboard_revenue_trend_6m_{$orgId}",
                self::CACHE_TTL_MEDIUM,
                fn() => $this->getMonthlyTrend('revenue', 6)
            ),
            'expenseTrend' => Cache::remember(
                "dashboard_expense_trend_6m_{$orgId}",
                self::CACHE_TTL_MEDIUM,
                fn() => $this->getMonthlyTrend('expense', 6)
            ),
            'yearlyRevenueTrend' => Cache::remember(
                "dashboard_yearly_revenue_{$orgId}",
                self::CACHE_TTL_MEDIUM,
                fn() => $this->getYearlyTrend('revenue')
            ),
            'yearlyExpenseTrend' => Cache::remember(
                "dashboard_yearly_expense_{$orgId}",
                self::CACHE_TTL_MEDIUM,
                fn() => $this->getYearlyTrend('expense')
            ),
            'yearlyProfitTrend' => Cache::remember(
                "dashboard_yearly_profit_{$orgId}",
                self::CACHE_TTL_MEDIUM,
                function () {
                    return $this->calculateProfitTrend(
                        $this->getYearlyTrend('revenue'),
                        $this->getYearlyTrend('expense')
                    );
                }
            ),
            'topClients' => Cache::remember(
                "dashboard_top_clients_{$orgId}",
                self::CACHE_TTL_MEDIUM,
                fn() => $this->getTopClientsByRevenue()
            ),
        ];
    }

    /**
     * Get upcoming domain and subscription renewals within the next 30 days.
     *
     * @return array<string, array<string, Collection>> Renewals grouped by type
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
     * Get nomenclature data for form dropdowns (cached for 1 hour).
     *
     * @param int $orgId Organization ID for cache key scoping
     * @return array<string, Collection> Nomenclature collections for various entity types
     */
    public function getNomenclature(int $orgId): array
    {
        return [
            'clientStatuses' => Cache::remember(
                "nomenclature_client_statuses_{$orgId}",
                self::CACHE_TTL_LONG,
                fn() => SettingOption::clientStatuses()->get()
            ),
            'expenseCategories' => Cache::remember(
                "nomenclature_expense_categories_{$orgId}",
                self::CACHE_TTL_LONG,
                fn() => SettingOption::rootCategories()->with('children')->get()
            ),
            'billingCycles' => Cache::remember(
                "nomenclature_billing_cycles_{$orgId}",
                self::CACHE_TTL_LONG,
                fn() => SettingOption::billingCycles()->get()
            ),
            'statuses' => Cache::remember(
                "nomenclature_subscription_statuses_{$orgId}",
                self::CACHE_TTL_LONG,
                fn() => SettingOption::subscriptionStatuses()->get()
            ),
            'platforms' => Cache::remember(
                "nomenclature_platforms_{$orgId}",
                self::CACHE_TTL_LONG,
                fn() => SettingOption::accessPlatforms()->get()
            ),
            'registrars' => Cache::remember(
                "nomenclature_registrars_{$orgId}",
                self::CACHE_TTL_LONG,
                fn() => SettingOption::domainRegistrars()->get()
            ),
            'domainStatuses' => Cache::remember(
                "nomenclature_domain_statuses_{$orgId}",
                self::CACHE_TTL_LONG,
                fn() => SettingOption::domainStatuses()->get()
            ),
            'currencies' => Cache::remember(
                "nomenclature_currencies_{$orgId}",
                self::CACHE_TTL_LONG,
                fn() => SettingOption::currencies()->get()
            ),
            'quickActions' => Cache::remember(
                "nomenclature_quick_actions_{$orgId}",
                self::CACHE_TTL_LONG,
                fn() => SettingOption::dashboardQuickActions()->get()
            ),
        ];
    }

    /**
     * Get additional analytics including growth rates, subscription costs, and revenue concentration.
     *
     * @param int $orgId Organization ID (currently unused but kept for consistency)
     * @return array<string, mixed> Analytics metrics and breakdowns
     */
    public function getAnalytics(int $orgId): array
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

        // Domain renewals
        $domainsExpiring = Domain::whereBetween('expiry_date', [now(), now()->addDays(90)])
            ->select('id', 'expiry_date', 'annual_cost')
            ->get();

        $day30 = now()->copy()->addDays(30);
        $day60 = now()->copy()->addDays(60);

        $domains30Days = $domainsExpiring->filter(fn($d) => $d->expiry_date <= $day30);
        $domains60Days = $domainsExpiring->filter(fn($d) => $d->expiry_date <= $day60);

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
            'revenueConcentration' => $yearlyRevenue > 0
                ? ($topClientsRevenue / $yearlyRevenue) * 100
                : 0,
            'topThreeClientsRevenue' => $topClientsRevenue,
            'categoryBreakdown' => $categoryBreakdown,
        ];
    }

    /**
     * Get monthly trend data for a specified number of months.
     *
     * @param string $type Type of trend: 'revenue' or 'expense'
     * @param int $months Number of months to include in the trend
     * @return array<int, array{month: string, year: int, amount: float, formatted: string}> Monthly trend data
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
     * Get yearly trend data for all 12 months of the current year (RON currency only).
     *
     * @param string $type Type of trend: 'revenue' or 'expense'
     * @return array<int, array{month: string, month_number: int, year: int, amount: float, formatted: string}> Yearly trend data
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
     * Get top clients ranked by total revenue (RON currency only).
     *
     * @param int $limit Maximum number of clients to return
     * @return Collection<int, Client> Collection of clients with total_revenue attribute
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
     * Calculate profit trend by subtracting expenses from revenue for each period.
     *
     * @param array<int, array{month: string, month_number: int, year: int, amount: float}> $revenueTrend Revenue trend data
     * @param array<int, array{amount: float}> $expenseTrend Expense trend data
     * @return array<int, array{month: string, month_number: int, year: int, amount: float, formatted: string}> Profit trend data
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
}
