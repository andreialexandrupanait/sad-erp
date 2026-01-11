<?php

namespace App\Helpers;

use Carbon\Carbon;

class PeriodHelper
{
    public const CURRENT_MONTH = 'current_month';
    public const LAST_MONTH = 'last_month';
    public const CURRENT_YEAR = 'current_year';
    public const LAST_YEAR = 'last_year';
    public const LAST_30_DAYS = 'last_30_days';
    public const LAST_12_MONTHS = 'last_12_months';
    public const CUSTOM = 'custom';

    public const DEFAULT_PERIOD = self::CURRENT_YEAR;

    /**
     * Get all available period options with labels
     */
    public static function getOptions(): array
    {
        return [
            self::CURRENT_MONTH => __('Luna curentă'),
            self::LAST_MONTH => __('Luna trecută'),
            self::CURRENT_YEAR => __('Anul curent'),
            self::LAST_YEAR => __('Anul trecut'),
            self::LAST_30_DAYS => __('Ultimele 30 zile'),
            self::LAST_12_MONTHS => __('Ultimele 12 luni'),
            self::CUSTOM => __('Altă perioadă'),
        ];
    }

    /**
     * Get date range for a given period key
     */
    public static function getDateRange(
        string $period,
        ?string $customFrom = null,
        ?string $customTo = null
    ): array {
        $now = Carbon::now();

        switch ($period) {
            case self::CURRENT_MONTH:
                $from = $now->copy()->startOfMonth();
                $to = $now->copy()->endOfDay();
                $label = __('Luna curentă');
                break;

            case self::LAST_MONTH:
                $from = $now->copy()->subMonth()->startOfMonth();
                $to = $now->copy()->subMonth()->endOfMonth();
                $label = __('Luna trecută');
                break;

            case self::CURRENT_YEAR:
                $from = $now->copy()->startOfYear();
                $to = $now->copy()->endOfDay();
                $label = __('Anul curent');
                break;

            case self::LAST_YEAR:
                $from = $now->copy()->subYear()->startOfYear();
                $to = $now->copy()->subYear()->endOfYear();
                $label = __('Anul trecut');
                break;

            case self::LAST_30_DAYS:
                $from = $now->copy()->subDays(30)->startOfDay();
                $to = $now->copy()->endOfDay();
                $label = __('Ultimele 30 zile');
                break;

            case self::LAST_12_MONTHS:
                $from = $now->copy()->subMonths(12)->startOfDay();
                $to = $now->copy()->endOfDay();
                $label = __('Ultimele 12 luni');
                break;

            case self::CUSTOM:
                $from = $customFrom ? Carbon::parse($customFrom)->startOfDay() : $now->copy()->startOfYear();
                $to = $customTo ? Carbon::parse($customTo)->endOfDay() : $now->copy()->endOfDay();
                $label = __('Altă perioadă');
                break;

            default:
                $from = $now->copy()->startOfYear();
                $to = $now->copy()->endOfDay();
                $label = __('Anul curent');
        }

        return [
            'from' => $from,
            'to' => $to,
            'label' => $label,
            'range_text' => self::formatDateRange($from, $to),
        ];
    }

    /**
     * Format date range as human-readable text
     */
    public static function formatDateRange(Carbon $from, Carbon $to): string
    {
        $fromFormatted = $from->translatedFormat('j M Y');
        $toFormatted = $to->translatedFormat('j M Y');

        if ($from->isSameDay($to)) {
            return $fromFormatted;
        }

        return "{$fromFormatted} - {$toFormatted}";
    }

    /**
     * Validate period key
     */
    public static function isValidPeriod(string $period): bool
    {
        return array_key_exists($period, self::getOptions());
    }
}
