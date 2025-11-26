<?php

namespace App\Observers;

use App\Models\FinancialExpense;
use Illuminate\Support\Facades\Cache;

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
    }
}
