<?php

namespace App\Services\Currency;

use App\Models\ExchangeRate;
use Illuminate\Support\Carbon;

class CurrencyConversionService
{
    /**
     * Supported currencies
     */
    public const SUPPORTED_CURRENCIES = ['RON', 'EUR', 'USD', 'GBP'];

    /**
     * Convert an amount between currencies
     */
    public function convert(float $amount, string $from, string $to, ?Carbon $date = null): ?array
    {
        return ExchangeRate::convert($amount, $from, $to, $date);
    }

    /**
     * Get exchange rate between currencies
     */
    public function getRate(string $from, string $to, ?Carbon $date = null): ?float
    {
        return ExchangeRate::getRate($from, $to, $date);
    }

    /**
     * Check if conversion is needed and rate is available
     */
    public function needsConversion(string $from, string $to): bool
    {
        return strtoupper($from) !== strtoupper($to);
    }

    /**
     * Check if a rate exists for the conversion
     */
    public function hasRate(string $from, string $to): bool
    {
        return ExchangeRate::hasRate($from, $to);
    }

    /**
     * Get all available rates to a target currency
     */
    public function getAvailableRates(string $targetCurrency): array
    {
        return ExchangeRate::getAvailableRates($targetCurrency);
    }

    /**
     * Should warn user about currency conversion
     */
    public function shouldWarnUser(string $serviceCurrency, string $offerCurrency): bool
    {
        return $this->needsConversion($serviceCurrency, $offerCurrency);
    }

    /**
     * Get conversion info for frontend (JSON friendly)
     */
    public function getConversionInfo(float $amount, string $from, string $to): array
    {
        $from = strtoupper($from);
        $to = strtoupper($to);

        if ($from === $to) {
            return [
                'needs_conversion' => false,
                'original_amount' => $amount,
                'original_currency' => $from,
                'converted_amount' => $amount,
                'target_currency' => $to,
                'exchange_rate' => 1.0,
                'rate_available' => true,
            ];
        }

        $rate = $this->getRate($from, $to);

        return [
            'needs_conversion' => true,
            'original_amount' => $amount,
            'original_currency' => $from,
            'converted_amount' => $rate !== null ? round($amount * $rate, 2) : null,
            'target_currency' => $to,
            'exchange_rate' => $rate,
            'rate_available' => $rate !== null,
        ];
    }

    /**
     * Format currency amount
     */
    public function format(float $amount, string $currency): string
    {
        return number_format($amount, 2, ',', '.') . ' ' . strtoupper($currency);
    }

    /**
     * Get default exchange rates for common pairs
     * Used to seed initial rates if needed
     */
    public static function getDefaultRates(): array
    {
        return [
            ['from' => 'EUR', 'to' => 'RON', 'rate' => 4.97],
            ['from' => 'USD', 'to' => 'RON', 'rate' => 4.65],
            ['from' => 'GBP', 'to' => 'RON', 'rate' => 5.90],
            ['from' => 'EUR', 'to' => 'USD', 'rate' => 1.07],
        ];
    }
}
