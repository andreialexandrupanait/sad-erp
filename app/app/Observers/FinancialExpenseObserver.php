<?php

namespace App\Observers;

use App\Models\FinancialExpense;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class FinancialExpenseObserver
{
    /**
     * Handle the FinancialExpense "saved" event.
     */
    public function saved(FinancialExpense $expense): void
    {
        $this->clearExpenseCache($expense->year);
    }

    /**
     * Handle the FinancialExpense "deleted" event.
     */
    public function deleted(FinancialExpense $expense): void
    {
        $this->clearExpenseCache($expense->year);
    }

    /**
     * Handle the FinancialExpense "restored" event.
     */
    public function restored(FinancialExpense $expense): void
    {
        $this->clearExpenseCache($expense->year);
    }

    /**
     * Handle the FinancialExpense "force deleted" event.
     */
    public function forceDeleted(FinancialExpense $expense): void
    {
        $this->clearExpenseCache($expense->year);
    }

    /**
     * Clear expense-related caches when data changes.
     */
    private function clearExpenseCache(?int $year): void
    {
        if ($year) {
            Cache::forget("financial.expenses.totals.{$year}");
            Cache::forget("financial.expenses.monthly.{$year}");
            Cache::forget("financial.expenses.categories.{$year}");
        }
        Cache::forget('financial.expenses.yearly_aggregates');
        Cache::forget('financial.available_years');
        
        // Clear dashboard cache for organization
        if (Auth::check()) {
            Cache::forget('dashboard_stats_' . Auth::user()->organization_id);
        }
    }
}
