<?php

namespace App\Services\Banking;

use App\Models\BankTransaction;
use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Transaction Matching Service - Automatic bank transaction reconciliation.
 *
 * Orchestrates the automatic matching of bank transactions with revenues and expenses
 * by delegating to specialized matcher services.
 *
 * ## Matching Process:
 * - Finds candidates using MatchCandidateFinder
 * - Calculates confidence using ConfidenceCalculator
 * - Auto-matches if confidence >= threshold
 *
 * ## Usage Example:
 * ```php
 * $matchingService = app(TransactionMatchingService::class);
 * $stats = $matchingService->autoMatchTransactions();
 * ```
 *
 * @see BankTransaction
 * @see MatchCandidateFinder
 * @see ConfidenceCalculator
 */
class TransactionMatchingService
{
    protected MatchCandidateFinder $candidateFinder;
    protected ConfidenceCalculator $confidenceCalculator;

    public function __construct(
        MatchCandidateFinder $candidateFinder,
        ConfidenceCalculator $confidenceCalculator
    ) {
        $this->candidateFinder = $candidateFinder;
        $this->confidenceCalculator = $confidenceCalculator;
    }
    /**
     * Attempt to automatically match unmatched transactions
     */
    public function autoMatchTransactions(?int $limit = null): array
    {
        if (!config('banking.matching.auto_match_enabled')) {
            return ['matched' => 0, 'skipped' => 0];
        }

        $unmatchedTransactions = BankTransaction::unmatched()
            ->orderBy('booking_date', 'desc')
            ->when($limit, fn($q) => $q->limit($limit))
            ->get();

        $stats = [
            'matched' => 0,
            'skipped' => 0,
        ];

        foreach ($unmatchedTransactions as $transaction) {
            try {
                $matched = $this->matchTransaction($transaction);

                if ($matched) {
                    $stats['matched']++;
                } else {
                    $stats['skipped']++;
                }
            } catch (\Exception $e) {
                Log::error('Auto-match failed for transaction', [
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage(),
                ]);
                $stats['skipped']++;
            }
        }

        return $stats;
    }

    /**
     * Attempt to match a single transaction.
     * Delegates candidate finding and confidence calculation to specialized services.
     */
    public function matchTransaction(BankTransaction $transaction): bool
    {
        if ($transaction->isMatched()) {
            return false; // Already matched
        }

        $candidates = $this->candidateFinder->findMatchCandidates($transaction);

        if ($candidates->isEmpty()) {
            return false;
        }

        // Get best match
        $bestMatch = $candidates->first();

        // Check if confidence meets threshold
        $threshold = config('banking.matching.auto_match_threshold', 85.0);

        if ($bestMatch['confidence'] >= $threshold) {
            // Auto-match
            if ($transaction->type === 'incoming') {
                $transaction->matchToRevenue(
                    $bestMatch['entity'],
                    $bestMatch['confidence'],
                    true,
                    'Auto-matched: ' . $bestMatch['reason']
                );
            } else {
                $transaction->matchToExpense(
                    $bestMatch['entity'],
                    $bestMatch['confidence'],
                    true,
                    'Auto-matched: ' . $bestMatch['reason']
                );
            }

            Log::info('Transaction auto-matched', [
                'transaction_id' => $transaction->id,
                'entity_type' => get_class($bestMatch['entity']),
                'entity_id' => $bestMatch['entity']->id,
                'confidence' => $bestMatch['confidence'],
            ]);

            return true;
        }

        return false;
    }

    /**
     * Find potential match candidates for a transaction.
     * Delegates to MatchCandidateFinder.
     */
    public function findMatchCandidates(BankTransaction $transaction): Collection
    {
        return $this->candidateFinder->findMatchCandidates($transaction);
    }

    /**
     * Manual match transaction to entity.
     * Delegates confidence calculation to ConfidenceCalculator.
     */
    public function manualMatch(
        BankTransaction $transaction,
        FinancialRevenue|FinancialExpense $entity,
        ?string $notes = null
    ): void {
        $confidence = $this->confidenceCalculator->calculateMatchConfidence($transaction, $entity);

        if ($entity instanceof FinancialRevenue) {
            $transaction->matchToRevenue($entity, $confidence, false, $notes);
        } else {
            $transaction->matchToExpense($entity, $confidence, false, $notes);
        }

        Log::info('Transaction manually matched', [
            'transaction_id' => $transaction->id,
            'entity_type' => get_class($entity),
            'entity_id' => $entity->id,
            'confidence' => $confidence,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Get matching suggestions for a transaction.
     * Delegates to MatchCandidateFinder.
     */
    public function getSuggestions(BankTransaction $transaction, int $limit = 5): Collection
    {
        return $this->candidateFinder->getSuggestions($transaction, $limit);
    }
}
