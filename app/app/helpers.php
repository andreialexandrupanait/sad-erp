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
