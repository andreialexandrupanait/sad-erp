<?php

use App\Helpers\SettingsHelper;

if (!function_exists('setting_options')) {
    /**
     * Get dropdown options for a setting group
     *
     * @param string $groupKey
     * @param bool $withColors
     * @return array
     */
    function setting_options(string $groupKey, bool $withColors = false): array
    {
        return SettingsHelper::getOptions($groupKey, $withColors);
    }
}

if (!function_exists('setting_label')) {
    /**
     * Get the label for a setting option value
     *
     * @param string $groupKey
     * @param string $value
     * @return string
     */
    function setting_label(string $groupKey, string $value): string
    {
        return SettingsHelper::getLabel($groupKey, $value);
    }
}

if (!function_exists('setting_color')) {
    /**
     * Get the color for a setting option value
     *
     * @param string $groupKey
     * @param string $value
     * @return string
     */
    function setting_color(string $groupKey, string $value): string
    {
        return SettingsHelper::getColor($groupKey, $value);
    }
}

if (!function_exists('romanian_month')) {
    /**
     * Get Romanian month name from month number
     *
     * @param int $monthNumber (1-12)
     * @return string
     */
    function romanian_month(int $monthNumber): string
    {
        $months = [
            1 => 'Ianuarie',
            2 => 'Februarie',
            3 => 'Martie',
            4 => 'Aprilie',
            5 => 'Mai',
            6 => 'Iunie',
            7 => 'Iulie',
            8 => 'August',
            9 => 'Septembrie',
            10 => 'Octombrie',
            11 => 'Noiembrie',
            12 => 'Decembrie',
        ];

        return $months[$monthNumber] ?? 'Necunoscut';
    }
}

if (!function_exists('sanitize_filename')) {
    /**
     * Sanitize filename for safe storage
     *
     * @param string $filename
     * @return string
     */
    function sanitize_filename(string $filename): string
    {
        // Remove accents
        $filename = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $filename);
        // Keep only alphanumeric, dash, underscore
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '-', $filename);
        // Remove consecutive dashes
        $filename = preg_replace('/-+/', '-', $filename);
        // Trim dashes from ends
        $filename = trim($filename, '-');

        return $filename ?: 'file';
    }
}

if (!function_exists('sanitize_input')) {
    /**
     * Sanitize user input to prevent XSS
     *
     * @param string|null $input
     * @return string|null
     */
    function sanitize_input(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        return strip_tags(trim($input));
    }
}
