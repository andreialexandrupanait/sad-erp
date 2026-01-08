<?php

namespace App\Services\Banking;

use App\Models\BankTransaction;
use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;

/**
 * Confidence Calculator Service
 *
 * Calculates match confidence scores (0-100%) for bank transaction matching
 * using weighted factors: amount match, date proximity, description similarity, and counterparty match.
 */
class ConfidenceCalculator
{
    protected StringSimilarityMatcher $similarityMatcher;

    public function __construct(StringSimilarityMatcher $similarityMatcher)
    {
        $this->similarityMatcher = $similarityMatcher;
    }

    /**
     * Calculate match confidence score (0-100)
     */
    public function calculateMatchConfidence(
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
        $score += $this->calculateAmountScore($transaction, $entity, $weights['exact_amount']);

        // 2. Date proximity (20 points)
        $score += $this->calculateDateScore($transaction, $entity, $weights['date_proximity']);

        // 3. Description/Client similarity (25 points)
        $score += $this->calculateDescriptionScore($transaction, $entity, $weights['description_similarity']);

        // 4. Counterparty match (15 points)
        $score += $this->calculateCounterpartyScore($transaction, $entity, $weights['counterparty_match']);

        return round(min(100, $score), 2);
    }

    /**
     * Calculate amount matching score
     */
    protected function calculateAmountScore(
        BankTransaction $transaction,
        FinancialRevenue|FinancialExpense $entity,
        float $maxPoints
    ): float {
        $entityAmount = $entity instanceof FinancialRevenue ? $entity->total : $entity->amount;
        $amountDiff = abs($transaction->amount - $entityAmount);

        if ($amountDiff <= 0.01) {
            return $maxPoints; // Exact match
        } elseif ($amountDiff <= 1.0) {
            return $maxPoints * 0.8; // Very close
        } elseif ($amountDiff <= 10.0) {
            return $maxPoints * 0.5; // Close enough
        }

        return 0;
    }

    /**
     * Calculate date proximity score
     */
    protected function calculateDateScore(
        BankTransaction $transaction,
        FinancialRevenue|FinancialExpense $entity,
        float $maxPoints
    ): float {
        $entityDate = $entity instanceof FinancialRevenue ? $entity->invoice_date : $entity->expense_date;
        $daysDiff = abs($transaction->booking_date->diffInDays($entityDate));

        if ($daysDiff === 0) {
            return $maxPoints; // Same day
        } elseif ($daysDiff === 1) {
            return $maxPoints * 0.8; // 1 day
        } elseif ($daysDiff <= 3) {
            return $maxPoints * 0.5; // Within tolerance
        }

        return 0;
    }

    /**
     * Calculate description similarity score
     */
    protected function calculateDescriptionScore(
        BankTransaction $transaction,
        FinancialRevenue|FinancialExpense $entity,
        float $maxPoints
    ): float {
        if ($entity instanceof FinancialRevenue && $entity->client) {
            $similarity = $this->similarityMatcher->calculateSimilarity(
                $transaction->counterparty_name ?? $transaction->description ?? '',
                $entity->client->name ?? ''
            );
            return $maxPoints * ($similarity / 100);
        } elseif ($entity instanceof FinancialExpense) {
            $similarity = $this->similarityMatcher->calculateSimilarity(
                $transaction->counterparty_name ?? $transaction->description ?? '',
                $entity->supplier ?? $entity->description ?? ''
            );
            return $maxPoints * ($similarity / 100);
        }

        return 0;
    }

    /**
     * Calculate counterparty matching score
     */
    protected function calculateCounterpartyScore(
        BankTransaction $transaction,
        FinancialRevenue|FinancialExpense $entity,
        float $maxPoints
    ): float {
        if (!$transaction->counterparty_name) {
            return 0;
        }

        $entityCounterparty = '';

        if ($entity instanceof FinancialRevenue && $entity->client) {
            $entityCounterparty = $entity->client->name;
        } elseif ($entity instanceof FinancialExpense) {
            $entityCounterparty = $entity->supplier ?? '';
        }

        if (empty($entityCounterparty)) {
            return 0;
        }

        $similarity = $this->similarityMatcher->calculateSimilarity(
            $transaction->counterparty_name,
            $entityCounterparty
        );

        return $maxPoints * ($similarity / 100);
    }

    /**
     * Generate match reason description
     */
    public function generateMatchReason(
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
                $similarity = $this->similarityMatcher->calculateSimilarity(
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
}
