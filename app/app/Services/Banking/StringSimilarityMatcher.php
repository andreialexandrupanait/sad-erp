<?php

namespace App\Services\Banking;

/**
 * String Similarity Matcher Service
 *
 * Provides fuzzy string matching capabilities for transaction matching,
 * including exact matches, substring matching, and similarity scoring.
 */
class StringSimilarityMatcher
{
    /**
     * Calculate string similarity percentage (0-100)
     */
    public function calculateSimilarity(string $str1, string $str2): float
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
     * Check if strings match exactly (case-insensitive)
     */
    public function isExactMatch(string $str1, string $str2): bool
    {
        return mb_strtolower(trim($str1)) === mb_strtolower(trim($str2));
    }

    /**
     * Check if one string contains another (case-insensitive)
     */
    public function contains(string $haystack, string $needle): bool
    {
        if (empty($needle)) {
            return false;
        }

        return str_contains(
            mb_strtolower($haystack),
            mb_strtolower($needle)
        );
    }

    /**
     * Calculate Levenshtein distance between strings
     */
    public function getLevenshteinDistance(string $str1, string $str2): int
    {
        return levenshtein(
            mb_strtolower(trim($str1)),
            mb_strtolower(trim($str2))
        );
    }

    /**
     * Check if similarity meets minimum threshold
     */
    public function meetsThreshold(string $str1, string $str2, float $threshold = 70.0): bool
    {
        return $this->calculateSimilarity($str1, $str2) >= $threshold;
    }
}
