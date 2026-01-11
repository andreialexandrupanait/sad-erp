<?php

namespace App\Http\Controllers\Api;

use App\Helpers\PeriodHelper;
use App\Http\Controllers\Controller;
use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;
use App\Services\Dashboard\TrendsCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WidgetController extends Controller
{
    public function __construct(
        private TrendsCalculator $trendsCalculator
    ) {}

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
     * Get top clients data with period filtering
     */
    public function topClients(Request $request): JsonResponse
    {
        $period = $request->get('period', PeriodHelper::DEFAULT_PERIOD);
        $customFrom = $request->get('from');
        $customTo = $request->get('to');

        if (!PeriodHelper::isValidPeriod($period)) {
            $period = PeriodHelper::DEFAULT_PERIOD;
        }

        $dateRange = PeriodHelper::getDateRange($period, $customFrom, $customTo);

        $clients = $this->trendsCalculator->getTopClientsByRevenue(
            limit: 5,
            from: $dateRange['from'],
            to: $dateRange['to']
        );

        return response()->json([
            'clients' => $clients->map(fn($client) => [
                'id' => $client->id,
                'name' => $client->display_name,
                'email' => $client->email,
                'total_revenue' => $client->total_revenue,
                'total_revenue_formatted' => number_format($client->total_revenue, 2) . ' RON',
            ]),
            'period' => $period,
            'period_label' => $dateRange['label'],
            'date_range' => $dateRange['range_text'],
        ]);
    }

    /**
     * Get financial summary (revenue, expenses, profit) with period filtering
     */
    public function financialSummary(Request $request): JsonResponse
    {
        $period = $request->get('period', PeriodHelper::DEFAULT_PERIOD);
        $customFrom = $request->get('from');
        $customTo = $request->get('to');

        if (!PeriodHelper::isValidPeriod($period)) {
            $period = PeriodHelper::DEFAULT_PERIOD;
        }

        $dateRange = PeriodHelper::getDateRange($period, $customFrom, $customTo);
        $from = $dateRange['from'];
        $to = $dateRange['to'];

        // Include RON records and converted EUR records
        $revenue = $this->applyRonFilter(FinancialRevenue::query())
            ->whereBetween('occurred_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->sum('amount');

        $expenses = $this->applyRonFilter(FinancialExpense::query())
            ->whereBetween('occurred_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->sum('amount');

        $profit = $revenue - $expenses;
        $profitMargin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

        return response()->json([
            'revenue' => $revenue,
            'revenue_formatted' => number_format($revenue, 2) . ' RON',
            'expenses' => $expenses,
            'expenses_formatted' => number_format($expenses, 2) . ' RON',
            'profit' => $profit,
            'profit_formatted' => number_format($profit, 2) . ' RON',
            'profit_margin' => round($profitMargin, 1),
            'period' => $period,
            'period_label' => $dateRange['label'],
            'date_range' => $dateRange['range_text'],
        ]);
    }

    /**
     * Get expense breakdown by category with period filtering
     */
    public function expenseCategories(Request $request): JsonResponse
    {
        $period = $request->get('period', PeriodHelper::DEFAULT_PERIOD);
        $customFrom = $request->get('from');
        $customTo = $request->get('to');

        if (!PeriodHelper::isValidPeriod($period)) {
            $period = PeriodHelper::DEFAULT_PERIOD;
        }

        $dateRange = PeriodHelper::getDateRange($period, $customFrom, $customTo);
        $from = $dateRange['from'];
        $to = $dateRange['to'];

        // Include RON records and converted EUR records
        $categories = $this->applyRonFilter(FinancialExpense::query())
            ->whereBetween('occurred_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->whereNotNull('category_option_id')
            ->select('category_option_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category_option_id')
            ->orderByDesc('total')
            ->limit(8)
            ->with('category')
            ->get();

        $total = $categories->sum('total');

        return response()->json([
            'categories' => $categories->map(fn($cat) => [
                'id' => $cat->category_option_id,
                'name' => $cat->category?->label ?? __('Uncategorized'),
                'total' => $cat->total,
                'total_formatted' => number_format($cat->total, 2) . ' RON',
                'count' => $cat->count,
                'percentage' => $total > 0 ? round(($cat->total / $total) * 100, 1) : 0,
            ]),
            'total' => $total,
            'total_formatted' => number_format($total, 2) . ' RON',
            'period' => $period,
            'period_label' => $dateRange['label'],
            'date_range' => $dateRange['range_text'],
        ]);
    }

    /**
     * Get revenue concentration data with period filtering
     */
    public function revenueConcentration(Request $request): JsonResponse
    {
        $period = $request->get('period', PeriodHelper::DEFAULT_PERIOD);
        $customFrom = $request->get('from');
        $customTo = $request->get('to');

        if (!PeriodHelper::isValidPeriod($period)) {
            $period = PeriodHelper::DEFAULT_PERIOD;
        }

        $dateRange = PeriodHelper::getDateRange($period, $customFrom, $customTo);
        $from = $dateRange['from'];
        $to = $dateRange['to'];

        // Get total revenue for period
        $totalRevenue = $this->applyRonFilter(FinancialRevenue::query())
            ->whereBetween('occurred_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->sum('amount');

        // Get top 3 clients revenue
        $topClients = $this->trendsCalculator->getTopClientsByRevenue(
            limit: 3,
            from: $from,
            to: $to
        );

        $topThreeRevenue = $topClients->sum('total_revenue');
        $concentration = $totalRevenue > 0 ? ($topThreeRevenue / $totalRevenue) * 100 : 0;

        // Determine risk level
        $thresholds = config('dashboard.revenue_concentration.thresholds');
        $riskLevel = 'low';
        if ($concentration >= $thresholds['high_risk']) {
            $riskLevel = 'high';
        } elseif ($concentration >= $thresholds['medium_risk']) {
            $riskLevel = 'medium';
        }

        $riskLabels = [
            'high' => __('app.High Risk'),
            'medium' => __('app.Medium Risk'),
            'low' => __('app.Low Risk'),
        ];

        return response()->json([
            'concentration' => round($concentration, 1),
            'top_three_revenue' => $topThreeRevenue,
            'top_three_revenue_formatted' => number_format($topThreeRevenue, 0) . ' RON',
            'total_revenue' => $totalRevenue,
            'total_revenue_formatted' => number_format($totalRevenue, 0) . ' RON',
            'risk_level' => $riskLevel,
            'risk_label' => $riskLabels[$riskLevel],
            'period' => $period,
            'period_label' => $dateRange['label'],
        ]);
    }

    /**
     * Get financial trend data with period filtering
     */
    public function financialTrend(Request $request): JsonResponse
    {
        $period = $request->get('period', PeriodHelper::DEFAULT_PERIOD);
        $customFrom = $request->get('from');
        $customTo = $request->get('to');

        if (!PeriodHelper::isValidPeriod($period)) {
            $period = PeriodHelper::DEFAULT_PERIOD;
        }

        $dateRange = PeriodHelper::getDateRange($period, $customFrom, $customTo);
        $from = $dateRange['from'];
        $to = $dateRange['to'];

        // Get monthly trends for the selected period
        $revenueTrend = $this->applyRonFilter(FinancialRevenue::query())
            ->whereBetween('occurred_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->selectRaw("DATE_FORMAT(occurred_at, '%Y-%m') as month_key, SUM(amount) as amount")
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->get()
            ->map(fn($r) => ['month' => $r->month_key, 'amount' => (float) $r->amount])
            ->values()
            ->toArray();

        $expenseTrend = $this->applyRonFilter(FinancialExpense::query())
            ->whereBetween('occurred_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->selectRaw("DATE_FORMAT(occurred_at, '%Y-%m') as month_key, SUM(amount) as amount")
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->get()
            ->map(fn($r) => ['month' => $r->month_key, 'amount' => (float) $r->amount])
            ->values()
            ->toArray();

        // Calculate profit trend
        $profitTrend = [];
        $revenueByMonth = collect($revenueTrend)->keyBy('month');
        $expenseByMonth = collect($expenseTrend)->keyBy('month');
        $allMonths = $revenueByMonth->keys()->merge($expenseByMonth->keys())->unique()->sort()->values();

        foreach ($allMonths as $month) {
            $revenue = $revenueByMonth->get($month)['amount'] ?? 0;
            $expense = $expenseByMonth->get($month)['amount'] ?? 0;
            $profitTrend[] = ['month' => $month, 'amount' => $revenue - $expense];
        }

        return response()->json([
            'revenue_trend' => $revenueTrend,
            'expense_trend' => $expenseTrend,
            'profit_trend' => $profitTrend,
            'labels' => $allMonths->toArray(),
            'period' => $period,
            'period_label' => $dateRange['label'],
        ]);
    }
}
