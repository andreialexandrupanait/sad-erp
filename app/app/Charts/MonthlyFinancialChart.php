<?php

namespace App\Charts;

use ConsoleTVs\Charts\Classes\Chartjs\Chart;
use Balping\JsonRaw\Raw;

class MonthlyFinancialChart extends Chart
{
    /**
     * Initializes the chart.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create a monthly financial chart for revenues and expenses
     *
     * @param array $revenueData - Monthly revenue data [['month' => 'Ian', 'amount' => 1000], ...]
     * @param array $expenseData - Monthly expense data [['month' => 'Feb', 'amount' => 500], ...]
     * @param string $type - 'revenue' or 'expense'
     * @param float $maxValue - Maximum value to use for scaling both charts
     * @return Chart
     */
    public static function createMonthlyChart($data, $type = 'revenue', $maxValue = null)
    {
        $chart = new self();

        // Extract labels and values
        $labels = array_column($data, 'month');
        $amounts = array_column($data, 'amount');

        // Determine colors based on type
        if ($type === 'revenue') {
            $backgroundColor = 'rgba(34, 197, 94, 0.8)'; // green-600 with opacity
            $label = 'Venituri (RON)';
        } else {
            $backgroundColor = 'rgba(239, 68, 68, 0.8)'; // red-600 with opacity
            $label = 'Cheltuieli (RON)';
        }

        $chart->labels($labels);
        $chart->dataset($label, 'bar', $amounts)
            ->backgroundColor($backgroundColor);

        // Don't set options via the library - we'll do it manually in JavaScript

        return $chart;
    }
}
