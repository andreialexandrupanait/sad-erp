<?php

namespace App\Services\Banking;

use App\Models\BankTransaction;
use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;
use Illuminate\Support\Collection;

/**
 * Match Candidate Finder Service
 *
 * Finds potential matching candidates for bank transactions by searching
 * revenues and expenses with date and amount tolerance.
 */
class MatchCandidateFinder
{
    protected ConfidenceCalculator $confidenceCalculator;

    public function __construct(ConfidenceCalculator $confidenceCalculator)
    {
        $this->confidenceCalculator = $confidenceCalculator;
    }

    /**
     * Find potential match candidates for a transaction
     */
    public function findMatchCandidates(BankTransaction $transaction): Collection
    {
        $dateTolerance = config('banking.matching.date_tolerance_days', 3);
        $amountTolerance = config('banking.matching.amount_tolerance', 0.01);

        $dateFrom = $transaction->booking_date->copy()->subDays($dateTolerance);
        $dateTo = $transaction->booking_date->copy()->addDays($dateTolerance);

        $candidates = collect();

        if ($transaction->type === 'incoming') {
            $candidates = $this->findRevenueCandidates(
                $transaction,
                $dateFrom,
                $dateTo,
                $amountTolerance
            );
        } else {
            $candidates = $this->findExpenseCandidates(
                $transaction,
                $dateFrom,
                $dateTo,
                $amountTolerance
            );
        }

        // Sort by confidence descending
        return $candidates->sortByDesc('confidence')->values();
    }

    /**
     * Find revenue candidates for incoming transactions
     */
    protected function findRevenueCandidates(
        BankTransaction $transaction,
        \Carbon\Carbon $dateFrom,
        \Carbon\Carbon $dateTo,
        float $amountTolerance
    ): Collection {
        $revenues = FinancialRevenue::where('organization_id', $transaction->organization_id)
            ->whereBetween('invoice_date', [$dateFrom, $dateTo])
            ->whereBetween('total', [
                $transaction->amount - $amountTolerance,
                $transaction->amount + $amountTolerance
            ])
            ->whereDoesntHave('matchedBankTransaction') // Not already matched
            ->get();

        $candidates = collect();

        foreach ($revenues as $revenue) {
            $confidence = $this->confidenceCalculator->calculateMatchConfidence($transaction, $revenue);
            $candidates->push([
                'entity' => $revenue,
                'confidence' => $confidence,
                'reason' => $this->confidenceCalculator->generateMatchReason($transaction, $revenue, $confidence),
            ]);
        }

        return $candidates;
    }

    /**
     * Find expense candidates for outgoing transactions
     */
    protected function findExpenseCandidates(
        BankTransaction $transaction,
        \Carbon\Carbon $dateFrom,
        \Carbon\Carbon $dateTo,
        float $amountTolerance
    ): Collection {
        $expenses = FinancialExpense::where('organization_id', $transaction->organization_id)
            ->whereBetween('expense_date', [$dateFrom, $dateTo])
            ->whereBetween('amount', [
                $transaction->amount - $amountTolerance,
                $transaction->amount + $amountTolerance
            ])
            ->whereDoesntHave('matchedBankTransaction') // Not already matched
            ->get();

        $candidates = collect();

        foreach ($expenses as $expense) {
            $confidence = $this->confidenceCalculator->calculateMatchConfidence($transaction, $expense);
            $candidates->push([
                'entity' => $expense,
                'confidence' => $confidence,
                'reason' => $this->confidenceCalculator->generateMatchReason($transaction, $expense, $confidence),
            ]);
        }

        return $candidates;
    }

    /**
     * Get top N suggestions for a transaction
     */
    public function getSuggestions(BankTransaction $transaction, int $limit = 5): Collection
    {
        $candidates = $this->findMatchCandidates($transaction);
        return $candidates->take($limit);
    }
}
