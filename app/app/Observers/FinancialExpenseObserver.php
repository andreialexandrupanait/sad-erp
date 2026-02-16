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
        $this->clearExpenseCache($expense->year, $expense->organization_id, $expense->month);
    }

    /**
     * Handle the FinancialExpense "deleted" event.
     */
    public function deleted(FinancialExpense $expense): void
    {
        $this->clearExpenseCache($expense->year, $expense->organization_id, $expense->month);
    }

    /**
     * Handle the FinancialExpense "restored" event.
     */
    public function restored(FinancialExpense $expense): void
    {
        $this->clearExpenseCache($expense->year, $expense->organization_id, $expense->month);
    }

    /**
     * Handle the FinancialExpense "force deleted" event.
     */
    public function forceDeleted(FinancialExpense $expense): void
    {
        $this->clearExpenseCache($expense->year, $expense->organization_id, $expense->month);
    }

    /**
     * Clear expense-related caches when data changes.
     */
    private function clearExpenseCache(?int $year, ?int $organizationId, ?int $month): void
    {
        // Get organization ID from expense or authenticated user
        $orgId = $organizationId ?? (Auth::check() ? Auth::user()->organization_id : null);

        if (!$orgId) {
            return;
        }

        $currentMonth = $month ?? now()->month;
        $currentYear = $year ?? now()->year;

        // Clear expense aggregator caches (org-prefixed)
        if ($year) {
            Cache::forget("org.{$orgId}.financial.expenses.totals.{$year}");
            Cache::forget("org.{$orgId}.financial.expenses.monthly.{$year}.all");
            Cache::forget("org.{$orgId}.financial.expenses.monthly.{$year}.RON");
            Cache::forget("org.{$orgId}.financial.expenses.monthly.{$year}.EUR");
            Cache::forget("org.{$orgId}.financial.expenses.categories.{$year}");
            Cache::forget("org.{$orgId}.financial.expenses.count.{$year}");
        }
        Cache::forget("org.{$orgId}.financial.expenses.all_years");

        // Clear financial dashboard caches
        Cache::forget("org.{$orgId}.financial.available_years");
        Cache::forget("org.{$orgId}.financial.cashflow.expenses.{$currentYear}");
        Cache::forget("org.{$orgId}.financial.analytics.expense_categories");

        // Clear dashboard trend caches
        Cache::forget("org.{$orgId}.dashboard.expense_trend_6m");
        Cache::forget("org.{$orgId}.dashboard.yearly_expense");
        Cache::forget("org.{$orgId}.dashboard.yearly_profit");

        // Clear dashboard metrics
        Cache::forget("org.{$orgId}.dashboard.metrics");
        Cache::forget("org.{$orgId}.dashboard.activity");
        Cache::forget("org.{$orgId}.dashboard.financial.{$currentYear}.{$currentMonth}");
    }
}
