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
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            // Key Metrics
            'totalClients' => Client::count(),
            'activeClients' => Client::count(),

            // Subscriptions Statistics
            'totalSubscriptions' => Subscription::count(),
            'activeSubscriptions' => Subscription::where('status', 'active')->count(),
            'expiredSubscriptions' => Subscription::where('status', 'expired')->count(),
            'monthlySubscriptionCost' => Subscription::where('status', 'active')
                ->where('billing_cycle', 'monthly')
                ->sum('price'),

            // Domains Statistics
            'totalDomains' => Domain::count(),
            'activeDomains' => Domain::where('status', 'Active')->count(),
            'expiringDomains' => Domain::whereBetween('expiry_date', [now(), now()->addDays(30)])->get(),
            'expiredDomains' => Domain::where('expiry_date', '<', now())->count(),

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

            // Trends (last 6 months)
            'revenueTrend' => $this->getMonthlyTrend('revenue', 6),
            'expenseTrend' => $this->getMonthlyTrend('expense', 6),

            // Financial Trends for current year (12 months) - for main chart
            'yearlyRevenueTrend' => $this->getYearlyTrend('revenue'),
            'yearlyExpenseTrend' => $this->getYearlyTrend('expense'),
            'yearlyProfitTrend' => $this->calculateProfitTrend(
                $this->getYearlyTrend('revenue'),
                $this->getYearlyTrend('expense')
            ),

            // Top Clients by Revenue
            'topClients' => $this->getTopClientsByRevenue(),

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

            // All clients for dropdowns
            'clients' => Client::with('status')->get(),

            // Client nomenclature for forms
            'clientStatuses' => SettingOption::clientStatuses()->get(),

            // Expense categories for forms
            'expenseCategories' => SettingOption::rootCategories()->with('children')->get(),

            // Subscription nomenclature for forms
            'billingCycles' => SettingOption::billingCycles()->get(),
            'statuses' => SettingOption::subscriptionStatuses()->get(),

            // Credential nomenclature for forms
            'platforms' => SettingOption::accessPlatforms()->get(),

            // Domain nomenclature for forms
            'registrars' => SettingOption::domainRegistrars()->get(),
            'domainStatuses' => SettingOption::domainStatuses()->get(),

            // Currency nomenclature for forms
            'currencies' => SettingOption::currencies()->get(),

            // Dashboard quick actions
            'quickActions' => SettingOption::dashboardQuickActions()->get(),
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

        // 3. Subscription Cost Summary
        $activeSubscriptions = Subscription::where('status', 'active')->get();
        $data['monthlySubscriptionCost'] = $activeSubscriptions->where('billing_cycle', 'monthly')->sum('price');
        $data['yearlySubscriptionCost'] = $activeSubscriptions->where('billing_cycle', 'yearly')->sum('price');
        $data['annualProjectedCost'] = ($data['monthlySubscriptionCost'] * 12) + $data['yearlySubscriptionCost'];
        $data['activeSubscriptionsCount'] = $activeSubscriptions->count();
        $data['pausedSubscriptionsCount'] = Subscription::where('status', 'paused')->count();
        $data['cancelledSubscriptionsCount'] = Subscription::where('status', 'cancelled')->count();

        // 4. Domain Renewal Costs
        $domains30Days = Domain::whereBetween('expiry_date', [now(), now()->addDays(30)])->get();
        $domains60Days = Domain::whereBetween('expiry_date', [now(), now()->addDays(60)])->get();
        $domains90Days = Domain::whereBetween('expiry_date', [now(), now()->addDays(90)])->get();

        $data['domainRenewals30Days'] = [
            'count' => $domains30Days->count(),
            'cost' => $domains30Days->sum('annual_cost'),
        ];
        $data['domainRenewals60Days'] = [
            'count' => $domains60Days->count(),
            'cost' => $domains60Days->sum('annual_cost'),
        ];
        $data['domainRenewals90Days'] = [
            'count' => $domains90Days->count(),
            'cost' => $domains90Days->sum('annual_cost'),
        ];

        // 5. Revenue Concentration Risk
        $topClientsRevenue = $data['topClients']->take(3)->sum('total_revenue');
        $totalRevenue = $data['yearlyRevenue'];
        $data['revenueConcentration'] = $totalRevenue > 0
            ? ($topClientsRevenue / $totalRevenue) * 100
            : 0;
        $data['topThreeClientsRevenue'] = $topClientsRevenue;

        return view('dashboard', $data);
    }

    /**
     * Get monthly trend data
     */
    private function getMonthlyTrend($type = 'revenue', $months = 6)
    {
        $data = [];
        $startDate = now()->subMonths($months - 1)->startOfMonth();
        $model = $type === 'revenue' ? FinancialRevenue::class : FinancialExpense::class;

        for ($i = 0; $i < $months; $i++) {
            $date = $startDate->copy()->addMonths($i);
            $amount = $model::where('year', $date->year)
                ->where('month', $date->month)
                ->sum('amount');

            $data[] = [
                'month' => $date->format('M'),
                'year' => $date->year,
                'amount' => $amount ?? 0,
                'formatted' => number_format($amount ?? 0, 2) . ' RON',
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
