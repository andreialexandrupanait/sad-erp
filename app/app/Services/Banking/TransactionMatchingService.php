<?php

namespace App\Services\Banking;

use App\Models\BankTransaction;
use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TransactionMatchingService
{
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
     * Attempt to match a single transaction
     */
    public function matchTransaction(BankTransaction $transaction): bool
    {
        if ($transaction->isMatched()) {
            return false; // Already matched
        }

        $candidates = $this->findMatchCandidates($transaction);

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
            // Match against revenues (invoices)
            $revenues = FinancialRevenue::where('organization_id', $transaction->organization_id)
                ->whereBetween('invoice_date', [$dateFrom, $dateTo])
                ->whereBetween('total', [
                    $transaction->amount - $amountTolerance,
                    $transaction->amount + $amountTolerance
                ])
                ->whereDoesntHave('matchedBankTransaction') // Not already matched
                ->get();

            foreach ($revenues as $revenue) {
                $confidence = $this->calculateMatchConfidence($transaction, $revenue);
                $candidates->push([
                    'entity' => $revenue,
                    'confidence' => $confidence,
                    'reason' => $this->generateMatchReason($transaction, $revenue, $confidence),
                ]);
            }
        } else {
            // Match against expenses
            $expenses = FinancialExpense::where('organization_id', $transaction->organization_id)
                ->whereBetween('expense_date', [$dateFrom, $dateTo])
                ->whereBetween('amount', [
                    $transaction->amount - $amountTolerance,
                    $transaction->amount + $amountTolerance
                ])
                ->whereDoesntHave('matchedBankTransaction') // Not already matched
                ->get();

            foreach ($expenses as $expense) {
                $confidence = $this->calculateMatchConfidence($transaction, $expense);
                $candidates->push([
                    'entity' => $expense,
                    'confidence' => $confidence,
                    'reason' => $this->generateMatchReason($transaction, $expense, $confidence),
                ]);
            }
        }

        // Sort by confidence descending
        return $candidates->sortByDesc('confidence')->values();
    }

    /**
     * Calculate match confidence score (0-100)
     */
    protected function calculateMatchConfidence(
        BankTransaction $transaction,
        FinancialRevenue|FinancialExpense $entity
    ): float {
        $weights = config('banking.matching.weights', [
            'exact_amount' => 40,
            'date_proximity' => 20,
            'description_similarity' => 25,
            'counterparty_match' => 15,
        ]);

        $score = 0;

        // 1. Amount match (40 points)
        $entityAmount = $entity instanceof FinancialRevenue ? $entity->total : $entity->amount;
        $amountDiff = abs($transaction->amount - $entityAmount);

        if ($amountDiff <= 0.01) {
            $score += $weights['exact_amount']; // Exact match
        } elseif ($amountDiff <= 1.0) {
            $score += $weights['exact_amount'] * 0.8; // Very close
        } elseif ($amountDiff <= 10.0) {
            $score += $weights['exact_amount'] * 0.5; // Close enough
        }

        // 2. Date proximity (20 points)
        $entityDate = $entity instanceof FinancialRevenue ? $entity->invoice_date : $entity->expense_date;
        $daysDiff = abs($transaction->booking_date->diffInDays($entityDate));

        if ($daysDiff === 0) {
            $score += $weights['date_proximity']; // Same day
        } elseif ($daysDiff === 1) {
            $score += $weights['date_proximity'] * 0.8; // 1 day
        } elseif ($daysDiff <= 3) {
            $score += $weights['date_proximity'] * 0.5; // Within tolerance
        }

        // 3. Description/Client similarity (25 points)
        if ($entity instanceof FinancialRevenue && $entity->client) {
            $similarity = $this->calculateStringSimilarity(
                $transaction->counterparty_name ?? $transaction->description ?? '',
                $entity->client->name ?? ''
            );
            $score += $weights['description_similarity'] * ($similarity / 100);
        } elseif ($entity instanceof FinancialExpense) {
            $similarity = $this->calculateStringSimilarity(
                $transaction->counterparty_name ?? $transaction->description ?? '',
                $entity->supplier ?? $entity->description ?? ''
            );
            $score += $weights['description_similarity'] * ($similarity / 100);
        }

        // 4. Counterparty match (15 points)
        if ($transaction->counterparty_name) {
            $entityCounterparty = '';

            if ($entity instanceof FinancialRevenue && $entity->client) {
                $entityCounterparty = $entity->client->name;
            } elseif ($entity instanceof FinancialExpense) {
                $entityCounterparty = $entity->supplier ?? '';
            }

            $similarity = $this->calculateStringSimilarity(
                $transaction->counterparty_name,
                $entityCounterparty
            );

            $score += $weights['counterparty_match'] * ($similarity / 100);
        }

        return round(min(100, $score), 2);
    }

    /**
     * Calculate string similarity percentage
     */
    protected function calculateStringSimilarity(string $str1, string $str2): float
    {
        if (empty($str1) || empty($str2)) {
            return 0;
        }

        // Normalize strings
        $str1 = mb_strtolower(trim($str1));
        $str2 = mb_strtolower(trim($str2));

        // Exact match
        if ($str1 === $str2) {
            return 100;
        }

        // Check if one contains the other
        if (str_contains($str1, $str2) || str_contains($str2, $str1)) {
            return 80;
        }

        // Use fuzzy matching if enabled
        if (config('banking.matching.fuzzy_matching', true)) {
            similar_text($str1, $str2, $percent);
            return $percent;
        }

        return 0;
    }

    /**
     * Generate match reason description
     */
    protected function generateMatchReason(
        BankTransaction $transaction,
        FinancialRevenue|FinancialExpense $entity,
        float $confidence
    ): string {
        $reasons = [];

        $entityAmount = $entity instanceof FinancialRevenue ? $entity->total : $entity->amount;
        $amountDiff = abs($transaction->amount - $entityAmount);

        if ($amountDiff <= 0.01) {
            $reasons[] = 'exact amount match';
        }

        $entityDate = $entity instanceof FinancialRevenue ? $entity->invoice_date : $entity->expense_date;
        $daysDiff = abs($transaction->booking_date->diffInDays($entityDate));

        if ($daysDiff === 0) {
            $reasons[] = 'same date';
        } elseif ($daysDiff <= 3) {
            $reasons[] = "within {$daysDiff} days";
        }

        if ($transaction->counterparty_name) {
            $entityCounterparty = '';

            if ($entity instanceof FinancialRevenue && $entity->client) {
                $entityCounterparty = $entity->client->name;
            } elseif ($entity instanceof FinancialExpense) {
                $entityCounterparty = $entity->supplier ?? '';
            }

            if (!empty($entityCounterparty)) {
                $similarity = $this->calculateStringSimilarity(
                    $transaction->counterparty_name,
                    $entityCounterparty
                );

                if ($similarity > 80) {
                    $reasons[] = 'counterparty match';
                }
            }
        }

        return implode(', ', $reasons) ?: 'confidence: ' . $confidence . '%';
    }

    /**
     * Manual match transaction to entity
     */
    public function manualMatch(
        BankTransaction $transaction,
        FinancialRevenue|FinancialExpense $entity,
        ?string $notes = null
    ): void {
        $confidence = $this->calculateMatchConfidence($transaction, $entity);

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
     * Get matching suggestions for a transaction
     */
    public function getSuggestions(BankTransaction $transaction, int $limit = 5): Collection
    {
        $candidates = $this->findMatchCandidates($transaction);

        return $candidates->take($limit);
    }
}
