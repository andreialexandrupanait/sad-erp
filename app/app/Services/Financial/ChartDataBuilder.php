<?php

namespace App\Services\Financial;

use Illuminate\Support\Collection;

/**
 * Chart Data Builder Service
 *
 * Handles formatting and preparation of financial data for charts and visualizations.
 */
class ChartDataBuilder
{
    /**
     * Romanian month names (short)
     */
    private const SHORT_MONTHS = ['Ian', 'Feb', 'Mar', 'Apr', 'Mai', 'Iun', 'Iul', 'Aug', 'Sep', 'Oct', 'Noi', 'Dec'];

    /**
     * Romanian month names (full)
     */
    private const FULL_MONTHS = ['Ianuarie', 'Februarie', 'Martie', 'Aprilie', 'Mai', 'Iunie', 'Iulie', 'August', 'Septembrie', 'Octombrie', 'Noiembrie', 'Decembrie'];

    /**
     * Prepare chart data for all 12 months
     *
     * @param Collection $monthlyRevenues
     * @param Collection $monthlyExpenses
     * @return array
     */
    public function prepareChartData(Collection $monthlyRevenues, Collection $monthlyExpenses): array
    {
        $chartRevenuesRON = [];
        $chartExpensesRON = [];

        for ($month = 1; $month <= 12; $month++) {
            $revenueAmount = $monthlyRevenues->get($month, 0);
            $expenseAmount = $monthlyExpenses->get($month, 0);

            $chartRevenuesRON[] = [
                'month' => self::SHORT_MONTHS[$month - 1],
                'amount' => $revenueAmount,
                'formatted' => number_format($revenueAmount, 2),
            ];

            $chartExpensesRON[] = [
                'month' => self::SHORT_MONTHS[$month - 1],
                'amount' => $expenseAmount,
                'formatted' => number_format($expenseAmount, 2),
            ];
        }

        return [
            'revenues' => $chartRevenuesRON,
            'expenses' => $chartExpensesRON,
        ];
    }

    /**
     * Calculate common max value for chart scaling
     *
     * @param array $chartRevenues
     * @param array $chartExpenses
     * @return float
     */
    public function calculateChartMaxValue(array $chartRevenues, array $chartExpenses): float
    {
        $maxRevenueAmount = collect($chartRevenues)->max('amount') ?: 0;
        $maxExpenseAmount = collect($chartExpenses)->max('amount') ?: 0;
        $commonMaxValue = max($maxRevenueAmount, $maxExpenseAmount);

        // Add 10% padding for better visualization
        return $commonMaxValue * 1.1;
    }

    /**
     * Build monthly breakdown table data
     *
     * @param Collection $monthlyRevenues
     * @param Collection $monthlyExpenses
     * @return array
     */
    public function buildMonthlyBreakdown(Collection $monthlyRevenues, Collection $monthlyExpenses): array
    {
        $monthlyBreakdown = [];

        for ($month = 1; $month <= 12; $month++) {
            $revenueRON = $monthlyRevenues->get($month, 0);
            $expenseRON = $monthlyExpenses->get($month, 0);
            $profitRON = $revenueRON - $expenseRON;

            $monthlyBreakdown[] = [
                'month' => $month,
                'month_name' => self::FULL_MONTHS[$month - 1],
                'revenue' => $revenueRON,
                'expense' => $expenseRON,
                'profit' => $profitRON,
                'has_data' => $revenueRON > 0 || $expenseRON > 0,
            ];
        }

        return $monthlyBreakdown;
    }

    /**
     * Prepare cashflow chart data
     *
     * @param array $cashflowData
     * @param int $year
     * @return array
     */
    public function prepareCashflowChartData(array $cashflowData, int $year): array
    {
        $chartData = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthData = $cashflowData[$month] ?? ['revenue' => 0, 'expense' => 0, 'profit' => 0];

            $chartData[] = [
                'month' => self::SHORT_MONTHS[$month - 1],
                'revenue' => $monthData['revenue'],
                'expense' => $monthData['expense'],
                'profit' => $monthData['profit'],
            ];
        }

        return $chartData;
    }

    /**
     * Get short month name
     *
     * @param int $month
     * @return string
     */
    public function getShortMonthName(int $month): string
    {
        return self::SHORT_MONTHS[$month - 1] ?? '';
    }

    /**
     * Get full month name
     *
     * @param int $month
     * @return string
     */
    public function getFullMonthName(int $month): string
    {
        return self::FULL_MONTHS[$month - 1] ?? '';
    }

    /**
     * Get all short month names
     *
     * @return array
     */
    public function getAllShortMonthNames(): array
    {
        return self::SHORT_MONTHS;
    }

    /**
     * Get all full month names
     *
     * @return array
     */
    public function getAllFullMonthNames(): array
    {
        return self::FULL_MONTHS;
    }
}
