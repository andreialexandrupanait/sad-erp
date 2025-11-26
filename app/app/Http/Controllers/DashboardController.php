<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Credential;
use App\Models\Domain;
use App\Models\Subscription;
use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;
use App\Models\SettingOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $orgId = auth()->user()->organization_id ?? 0;
        $cacheKey = "dashboard_stats_{$orgId}";
        $cacheTtl = 300; // 5 minutes

        // Aggregate counts in fewer queries - cached for 5 minutes
        $cachedStats = Cache::remember($cacheKey, $cacheTtl, function () {
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
            ];
        });

        $clientCount = $cachedStats['clientCount'];
        $domainStats = $cachedStats['domainStats'];
        $subscriptionCounts = $cachedStats['subscriptionCounts'];

        $data = [
            // Key Metrics - reuse the count
            'totalClients' => $clientCount,
            'activeClients' => $clientCount,

            // Subscriptions Statistics - from aggregated query
            'totalSubscriptions' => $subscriptionCounts->total,
            'activeSubscriptions' => $subscriptionCounts->active,
            'expiredSubscriptions' => $subscriptionCounts->expired,
            'monthlySubscriptionCost' => $subscriptionCounts->monthly_cost,

            // Domains Statistics - from aggregated query
            'totalDomains' => $domainStats->total,
            'activeDomains' => $domainStats->active,
            'expiringDomains' => Domain::whereBetween('expiry_date', [now(), now()->addDays(30)])->get(),
            'expiredDomains' => $domainStats->expired,

            // Credentials
            'totalCredentials' => Credential::count(),

            // Financial Overview (current month) - RON only
            'currentMonthRevenue' => FinancialRevenue::where('year', now()->year)
                ->where('month', now()->month)
                ->where('currency', 'RON')
                ->sum('amount'),
            'currentMonthExpenses' => FinancialExpense::where('year', now()->year)
                ->where('month', now()->month)
                ->where('currency', 'RON')
                ->sum('amount'),

            // Financial Overview (current year) - RON only
            'yearlyRevenue' => FinancialRevenue::where('year', now()->year)->where('currency', 'RON')->sum('amount'),
            'yearlyExpenses' => FinancialExpense::where('year', now()->year)->where('currency', 'RON')->sum('amount'),

            // Recent Activity
            'recentClients' => Client::with('status')->latest()->take(5)->get(),
            'recentDomains' => Domain::with('client')->latest()->take(5)->get(),
            'recentSubscriptions' => Subscription::latest()->take(5)->get(),
            'overdueSubscriptions' => Subscription::where('status', 'active')
                ->where('next_renewal_date', '<', now())
                ->get(),

            // Trends (last 6 months) - cached for 10 minutes
            'revenueTrend' => Cache::remember("dashboard_revenue_trend_6m_{$orgId}", 600, fn() => $this->getMonthlyTrend('revenue', 6)),
            'expenseTrend' => Cache::remember("dashboard_expense_trend_6m_{$orgId}", 600, fn() => $this->getMonthlyTrend('expense', 6)),

            // Financial Trends for current year (12 months) - for main chart - cached for 10 minutes
            'yearlyRevenueTrend' => Cache::remember("dashboard_yearly_revenue_{$orgId}", 600, fn() => $this->getYearlyTrend('revenue')),
            'yearlyExpenseTrend' => Cache::remember("dashboard_yearly_expense_{$orgId}", 600, fn() => $this->getYearlyTrend('expense')),
            'yearlyProfitTrend' => Cache::remember("dashboard_yearly_profit_{$orgId}", 600, function() {
                return $this->calculateProfitTrend(
                    $this->getYearlyTrend('revenue'),
                    $this->getYearlyTrend('expense')
                );
            }),

            // Top Clients by Revenue - cached for 10 minutes
            'topClients' => Cache::remember("dashboard_top_clients_{$orgId}", 600, fn() => $this->getTopClientsByRevenue()),

            // Upcoming Renewals (next 30 days)
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

            // All clients for dropdowns - limit to 200 most recently used
            'clients' => Client::with('status')->orderBy('updated_at', 'desc')->limit(200)->get(),

            // Client nomenclature for forms - cached for 1 hour (rarely changes)
            'clientStatuses' => Cache::remember("nomenclature_client_statuses_{$orgId}", 3600, fn() => SettingOption::clientStatuses()->get()),

            // Expense categories for forms - cached for 1 hour
            'expenseCategories' => Cache::remember("nomenclature_expense_categories_{$orgId}", 3600, fn() => SettingOption::rootCategories()->with('children')->get()),

            // Subscription nomenclature for forms - cached for 1 hour
            'billingCycles' => Cache::remember("nomenclature_billing_cycles_{$orgId}", 3600, fn() => SettingOption::billingCycles()->get()),
            'statuses' => Cache::remember("nomenclature_subscription_statuses_{$orgId}", 3600, fn() => SettingOption::subscriptionStatuses()->get()),

            // Credential nomenclature for forms - cached for 1 hour
            'platforms' => Cache::remember("nomenclature_platforms_{$orgId}", 3600, fn() => SettingOption::accessPlatforms()->get()),

            // Domain nomenclature for forms - cached for 1 hour
            'registrars' => Cache::remember("nomenclature_registrars_{$orgId}", 3600, fn() => SettingOption::domainRegistrars()->get()),
            'domainStatuses' => Cache::remember("nomenclature_domain_statuses_{$orgId}", 3600, fn() => SettingOption::domainStatuses()->get()),

            // Currency nomenclature for forms - cached for 1 hour
            'currencies' => Cache::remember("nomenclature_currencies_{$orgId}", 3600, fn() => SettingOption::currencies()->get()),

            // Dashboard quick actions - cached for 1 hour
            'quickActions' => Cache::remember("nomenclature_quick_actions_{$orgId}", 3600, fn() => SettingOption::dashboardQuickActions()->get()),
        ];

        // Calculate profit
        $data['currentMonthProfit'] = $data['currentMonthRevenue'] - $data['currentMonthExpenses'];
        $data['yearlyProfit'] = $data['yearlyRevenue'] - $data['yearlyExpenses'];

        // Additional analytics for Tier 1 widgets

        // 1. Profit Margin Indicator
        $data['currentMonthProfitMargin'] = $data['currentMonthRevenue'] > 0
            ? ($data['currentMonthProfit'] / $data['currentMonthRevenue']) * 100
            : 0;
        $data['yearlyProfitMargin'] = $data['yearlyRevenue'] > 0
            ? ($data['yearlyProfit'] / $data['yearlyRevenue']) * 100
            : 0;

        // 2. Month-over-Month Growth - RON only
        $previousMonth = now()->subMonth();
        $previousMonthRevenue = FinancialRevenue::where('year', $previousMonth->year)
            ->where('month', $previousMonth->month)
            ->where('currency', 'RON')
            ->sum('amount');
        $previousMonthExpenses = FinancialExpense::where('year', $previousMonth->year)
            ->where('month', $previousMonth->month)
            ->where('currency', 'RON')
            ->sum('amount');

        $data['revenueGrowth'] = $previousMonthRevenue > 0
            ? (($data['currentMonthRevenue'] - $previousMonthRevenue) / $previousMonthRevenue) * 100
            : 0;
        $data['expenseGrowth'] = $previousMonthExpenses > 0
            ? (($data['currentMonthExpenses'] - $previousMonthExpenses) / $previousMonthExpenses) * 100
            : 0;

        // New clients this month vs last month
        $newClientsThisMonth = Client::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        $newClientsLastMonth = Client::whereYear('created_at', $previousMonth->year)
            ->whereMonth('created_at', $previousMonth->month)
            ->count();
        $data['newClientsThisMonth'] = $newClientsThisMonth;
        $data['newClientsLastMonth'] = $newClientsLastMonth;
        $data['clientGrowth'] = $newClientsLastMonth > 0
            ? (($newClientsThisMonth - $newClientsLastMonth) / $newClientsLastMonth) * 100
            : 0;

        // 3. Subscription Cost Summary - optimized with database-level aggregation
        $subscriptionStats = Subscription::select('status', 'billing_cycle', DB::raw('COUNT(*) as count'), DB::raw('SUM(price) as total'))
            ->groupBy('status', 'billing_cycle')
            ->get();

        $data['monthlySubscriptionCost'] = $subscriptionStats
            ->where('status', 'active')
            ->where('billing_cycle', 'monthly')
            ->sum('total');
        $data['yearlySubscriptionCost'] = $subscriptionStats
            ->where('status', 'active')
            ->where('billing_cycle', 'yearly')
            ->sum('total');
        $data['annualProjectedCost'] = ($data['monthlySubscriptionCost'] * 12) + $data['yearlySubscriptionCost'];
        $data['activeSubscriptionsCount'] = $subscriptionStats->where('status', 'active')->sum('count');
        $data['pausedSubscriptionsCount'] = $subscriptionStats->where('status', 'paused')->sum('count');
        $data['cancelledSubscriptionsCount'] = $subscriptionStats->where('status', 'cancelled')->sum('count');

        // 4. Domain Renewal Costs - optimized with single query for all ranges
        $domainsExpiring = Domain::whereBetween('expiry_date', [now(), now()->addDays(90)])
            ->select('id', 'expiry_date', 'annual_cost')
            ->get();

        $now = now();
        $day30 = $now->copy()->addDays(30);
        $day60 = $now->copy()->addDays(60);

        $domains30Days = $domainsExpiring->filter(fn($d) => $d->expiry_date <= $day30);
        $domains60Days = $domainsExpiring->filter(fn($d) => $d->expiry_date <= $day60);

        $data['domainRenewals30Days'] = [
            'count' => $domains30Days->count(),
            'cost' => $domains30Days->sum('annual_cost'),
        ];
        $data['domainRenewals60Days'] = [
            'count' => $domains60Days->count(),
            'cost' => $domains60Days->sum('annual_cost'),
        ];
        $data['domainRenewals90Days'] = [
            'count' => $domainsExpiring->count(),
            'cost' => $domainsExpiring->sum('annual_cost'),
        ];

        // 5. Revenue Concentration Risk
        $topClientsRevenue = $data['topClients']->take(3)->sum('total_revenue');
        $totalRevenue = $data['yearlyRevenue'];
        $data['revenueConcentration'] = $totalRevenue > 0
            ? ($topClientsRevenue / $totalRevenue) * 100
            : 0;
        $data['topThreeClientsRevenue'] = $topClientsRevenue;

        // 6. Expense Category Breakdown (current year, top 8)
        $data['categoryBreakdown'] = FinancialExpense::where('year', now()->year)
            ->whereNotNull('category_option_id')
            ->select('category_option_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category_option_id')
            ->with('category')
            ->get()
            ->sortByDesc('total')
            ->take(8);

        return view('dashboard', $data);
    }

    /**
     * Get monthly trend data - optimized to use single query
     */
    private function getMonthlyTrend($type = 'revenue', $months = 6)
    {
        $data = [];
        $startDate = now()->subMonths($months - 1)->startOfMonth();
        $model = $type === 'revenue' ? FinancialRevenue::class : FinancialExpense::class;

        // Get all data in a single query instead of N queries
        $monthlyData = $model::where(function($query) use ($startDate, $months) {
                for ($i = 0; $i < $months; $i++) {
                    $date = $startDate->copy()->addMonths($i);
                    $query->orWhere(function($q) use ($date) {
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
     * Get top clients by total revenue (RON only)
     */
    private function getTopClientsByRevenue($limit = 5)
    {
        $userId = auth()->id();

        return Client::select('clients.*')
            ->selectRaw('COALESCE(SUM(financial_revenues.amount), 0) as total_revenue')
            ->leftJoin('financial_revenues', function($join) use ($userId) {
                $join->on('clients.id', '=', 'financial_revenues.client_id')
                     ->where('financial_revenues.currency', '=', 'RON');
                if ($userId) {
                    $join->where('financial_revenues.user_id', '=', $userId);
                }
            })
            ->when($userId, function($query) use ($userId) {
                return $query->where('clients.user_id', $userId);
            })
            ->withoutGlobalScope('user')
            ->groupBy('clients.id')
            ->orderByDesc('total_revenue')
            ->take($limit)
            ->get();
    }

    /**
     * Get yearly trend data for all 12 months of current year (RON only)
     */
    private function getYearlyTrend($type = 'revenue')
    {
        $data = [];
        $currentYear = now()->year;
        $model = $type === 'revenue' ? FinancialRevenue::class : FinancialExpense::class;

        $romanianMonths = ['Ian', 'Feb', 'Mar', 'Apr', 'Mai', 'Iun', 'Iul', 'Aug', 'Sep', 'Oct', 'Noi', 'Dec'];

        // Get all data for the year at once
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
     * Calculate profit trend from revenue and expense trends
     */
    private function calculateProfitTrend($revenueTrend, $expenseTrend)
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
