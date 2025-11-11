<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Credential;
use App\Models\Domain;
use App\Models\Subscription;
use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;
use App\Models\FinancialSetting;
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

            // Financial Overview (current month)
            'currentMonthRevenue' => FinancialRevenue::where('year', now()->year)
                ->where('month', now()->month)
                ->sum('amount'),
            'currentMonthExpenses' => FinancialExpense::where('year', now()->year)
                ->where('month', now()->month)
                ->sum('amount'),

            // Financial Overview (current year)
            'yearlyRevenue' => FinancialRevenue::where('year', now()->year)->sum('amount'),
            'yearlyExpenses' => FinancialExpense::where('year', now()->year)->sum('amount'),

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

            // Expense categories for forms
            'expenseCategories' => FinancialSetting::expenseCategories()->get(),
        ];

        // Calculate profit
        $data['currentMonthProfit'] = $data['currentMonthRevenue'] - $data['currentMonthExpenses'];
        $data['yearlyProfit'] = $data['yearlyRevenue'] - $data['yearlyExpenses'];

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
     * Get top clients by total revenue
     */
    private function getTopClientsByRevenue($limit = 5)
    {
        return Client::select('clients.*')
            ->selectRaw('COALESCE(SUM(financial_revenues.amount), 0) as total_revenue')
            ->leftJoin('financial_revenues', function($join) {
                $join->on('clients.id', '=', 'financial_revenues.client_id')
                     ->where('financial_revenues.user_id', '=', DB::raw(auth()->id()));
            })
            ->where('clients.user_id', auth()->id())
            ->withoutGlobalScope('user')
            ->groupBy('clients.id')
            ->orderByDesc('total_revenue')
            ->take($limit)
            ->get();
    }
}
