<?php

namespace App\View\Components\Dashboard;

use Illuminate\View\Component;
use Illuminate\Support\Collection;

class ExpenseCategoryChart extends Component
{
    public Collection $categoryData;
    public float $total;
    public array $chartColors;
    public bool $hasData;
    public array $processedCategories;

    /**
     * Create a new component instance.
     *
     * @param Collection $categoryData Collection of expense categories with totals
     */
    public function __construct(Collection $categoryData)
    {
        $this->categoryData = $categoryData;
        $this->total = $categoryData->sum('total');
        $this->hasData = $categoryData->isNotEmpty();
        $this->chartColors = config('dashboard.chart_colors.expense_categories');

        // Pre-process category data with percentages and colors
        $this->processedCategories = $categoryData->map(function($item, $index) {
            return [
                'item' => $item,
                'percentage' => $this->getPercentage($item->total),
                'color' => $this->getColor($index),
            ];
        })->toArray();
    }

    /**
     * Calculate the percentage for a given amount.
     *
     * @param float $amount The amount to calculate percentage for
     * @return float The calculated percentage
     */
    public function getPercentage(float $amount): float
    {
        return $this->total > 0 ? ($amount / $this->total) * 100 : 0;
    }

    /**
     * Get the color for a specific index.
     *
     * @param int $index The index in the category list
     * @return string The color value
     */
    public function getColor(int $index): string
    {
        return $this->chartColors[$index % count($this->chartColors)];
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.dashboard.expense-category-chart');
    }
}
