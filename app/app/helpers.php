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
     * @param bool $withPrefix Include number prefix (e.g., "01-Ianuarie")
     * @return string
     */
    function romanian_month(int $monthNumber, bool $withPrefix = true): string
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

        $name = $months[$monthNumber] ?? 'Necunoscut';

        if ($withPrefix && $monthNumber >= 1 && $monthNumber <= 12) {
            return sprintf('%02d-%s', $monthNumber, $name);
        }

        return $name;
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

if (!function_exists('sanitize_html')) {
    /**
     * Sanitize HTML input while allowing specific safe tags
     *
     * @param string|null $input
     * @param array<string> $allowedTags Tags to allow (e.g., ['p', 'br', 'strong', 'em'])
     * @return string|null
     */
    function sanitize_html(?string $input, array $allowedTags = ['p', 'br', 'strong', 'em', 'ul', 'ol', 'li']): ?string
    {
        if ($input === null) {
            return null;
        }

        $tagString = '<' . implode('><', $allowedTags) . '>';
        return strip_tags(trim($input), $tagString);
    }
}

if (!function_exists('is_valid_email')) {
    /**
     * Validate email format
     *
     * @param string|null $email
     * @return bool
     */
    function is_valid_email(?string $email): bool
    {
        if ($email === null || $email === '') {
            return false;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('is_valid_url')) {
    /**
     * Validate URL format
     *
     * @param string|null $url
     * @return bool
     */
    function is_valid_url(?string $url): bool
    {
        if ($url === null || $url === '') {
            return false;
        }

        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}

if (!function_exists('normalize_phone')) {
    /**
     * Normalize phone number by removing non-digit characters except leading +
     *
     * @param string|null $phone
     * @return string|null
     */
    function normalize_phone(?string $phone): ?string
    {
        if ($phone === null || $phone === '') {
            return null;
        }

        // Preserve leading + for international format
        $hasPlus = str_starts_with($phone, '+');
        $normalized = preg_replace('/[^0-9]/', '', $phone);

        return $hasPlus ? '+' . $normalized : $normalized;
    }
}

if (!function_exists('safe_json_decode')) {
    /**
     * Safely decode JSON with error handling
     *
     * @param string|null $json
     * @param bool $associative Return associative array instead of object
     * @param mixed $default Default value if decode fails
     * @return mixed
     */
    function safe_json_decode(?string $json, bool $associative = true, mixed $default = null): mixed
    {
        if ($json === null || $json === '') {
            return $default;
        }

        try {
            $decoded = json_decode($json, $associative, 512, JSON_THROW_ON_ERROR);
            return $decoded ?? $default;
        } catch (JsonException) {
            return $default;
        }
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format amount as currency string
     *
     * @param float|int|null $amount
     * @param string $currency Currency code (RON, EUR, USD)
     * @param int $decimals Number of decimal places
     * @return string
     */
    function format_currency(float|int|null $amount, string $currency = 'RON', int $decimals = 2): string
    {
        if ($amount === null) {
            $amount = 0;
        }

        return number_format($amount, $decimals, ',', '.') . ' ' . $currency;
    }
}

if (!function_exists('truncate_string')) {
    /**
     * Truncate string to specified length with ellipsis
     *
     * @param string|null $string
     * @param int $length Maximum length
     * @param string $suffix Suffix to add when truncated
     * @return string
     */
    function truncate_string(?string $string, int $length = 100, string $suffix = '...'): string
    {
        if ($string === null) {
            return '';
        }

        if (mb_strlen($string) <= $length) {
            return $string;
        }

        return mb_substr($string, 0, $length - mb_strlen($suffix)) . $suffix;
    }
}

if (!function_exists('csp_nonce')) {
    /**
     * Get the CSP nonce for the current request.
     *
     * Use this in blade templates for inline scripts and styles:
     * <script nonce="{{ csp_nonce() }}">...</script>
     * <style nonce="{{ csp_nonce() }}">...</style>
     *
     * @return string
     */
    function csp_nonce(): string
    {
        return request()->attributes->get('csp_nonce', '');
    }
}
