<?php

namespace App\View\Components\Dashboard;

use Illuminate\View\Component;

class RevenueConcentrationWidget extends Component
{
    public float $revenueConcentration;
    public float $topThreeClientsRevenue;
    public float $yearlyRevenue;
    public string $riskLevel;
    public array $colorClasses;
    public string $riskLabel;

    /**
     * Create a new component instance.
     *
     * @param float $revenueConcentration Percentage of revenue from top 3 clients
     * @param float $topThreeClientsRevenue Total revenue from top 3 clients
     * @param float $yearlyRevenue Total yearly revenue
     */
    public function __construct(
        float $revenueConcentration,
        float $topThreeClientsRevenue,
        float $yearlyRevenue
    ) {
        $this->revenueConcentration = $revenueConcentration;
        $this->topThreeClientsRevenue = $topThreeClientsRevenue;
        $this->yearlyRevenue = $yearlyRevenue;

        $this->riskLevel = $this->determineRiskLevel($revenueConcentration);
        $this->colorClasses = $this->getColorClasses($this->riskLevel);
        $this->riskLabel = $this->getRiskLabel($this->riskLevel);
    }

    /**
     * Determine the risk level based on revenue concentration.
     *
     * @param float $concentration Revenue concentration percentage
     * @return string The risk level (high, medium, or low)
     */
    private function determineRiskLevel(float $concentration): string
    {
        $thresholds = config('dashboard.revenue_concentration.thresholds');

        if ($concentration >= $thresholds['high_risk']) {
            return 'high';
        }

        if ($concentration >= $thresholds['medium_risk']) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Get the CSS classes for the determined risk level.
     *
     * @param string $riskLevel The risk level
     * @return array Array of CSS class mappings
     */
    private function getColorClasses(string $riskLevel): array
    {
        return config("dashboard.revenue_concentration.colors.{$riskLevel}");
    }

    /**
     * Get the translated risk label.
     *
     * @param string $riskLevel The risk level
     * @return string The translated risk label
     */
    private function getRiskLabel(string $riskLevel): string
    {
        return match ($riskLevel) {
            'high' => __('app.High Risk'),
            'medium' => __('app.Medium Risk'),
            'low' => __('app.Low Risk'),
            default => __('app.Unknown'),
        };
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.dashboard.revenue-concentration-widget');
    }
}
