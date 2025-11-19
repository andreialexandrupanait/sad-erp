<?php

namespace App\View\Components\Dashboard;

use Illuminate\View\Component;

class ProfitMarginWidget extends Component
{
    public float $currentMonthProfitMargin;
    public float $yearlyProfitMargin;
    public string $currentColor;
    public array $colorClasses;
    public string $statusLabel;

    /**
     * Create a new component instance.
     *
     * @param float $currentMonthProfitMargin The profit margin for the current month
     * @param float $yearlyProfitMargin The yearly average profit margin
     */
    public function __construct(float $currentMonthProfitMargin, float $yearlyProfitMargin)
    {
        $this->currentMonthProfitMargin = $currentMonthProfitMargin;
        $this->yearlyProfitMargin = $yearlyProfitMargin;

        $this->currentColor = $this->determineColor($currentMonthProfitMargin);
        $this->colorClasses = $this->getColorClasses($this->currentColor);
        $this->statusLabel = $this->getStatusLabel($currentMonthProfitMargin);
    }

    /**
     * Determine the color level based on profit margin.
     *
     * @param float $margin The profit margin percentage
     * @return string The color level (excellent, good, or low)
     */
    private function determineColor(float $margin): string
    {
        $thresholds = config('dashboard.profit_margin.thresholds');

        if ($margin >= $thresholds['excellent']) {
            return 'excellent';
        }

        if ($margin >= $thresholds['good']) {
            return 'good';
        }

        return 'low';
    }

    /**
     * Get the CSS classes for the determined color level.
     *
     * @param string $colorLevel The color level
     * @return array Array of CSS class mappings
     */
    private function getColorClasses(string $colorLevel): array
    {
        return config("dashboard.profit_margin.colors.{$colorLevel}");
    }

    /**
     * Get the status label for the profit margin.
     *
     * @param float $margin The profit margin percentage
     * @return string The translated status label
     */
    private function getStatusLabel(float $margin): string
    {
        $thresholds = config('dashboard.profit_margin.thresholds');

        if ($margin >= $thresholds['excellent']) {
            return __('app.Excellent');
        }

        if ($margin >= $thresholds['good']) {
            return __('app.Good');
        }

        return __('app.Low');
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.dashboard.profit-margin-widget');
    }
}
