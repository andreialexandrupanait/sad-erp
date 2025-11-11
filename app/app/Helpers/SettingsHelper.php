<?php

namespace App\Helpers;

use App\Models\SettingOption;
use Illuminate\Support\Facades\Cache;

class SettingsHelper
{
    /**
     * Get dropdown options for a specific setting group by key
     *
     * @param string $groupKey e.g., 'domain_registrars', 'client_statuses'
     * @param bool $withColors Include colors in the result
     * @return array
     */
    public static function getOptions(string $groupKey, bool $withColors = false): array
    {
        $cacheKey = "settings.{$groupKey}." . ($withColors ? 'with_colors' : 'simple');

        return Cache::remember($cacheKey, 3600, function () use ($groupKey, $withColors) {
            $options = SettingOption::where('category', $groupKey)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            if ($options->isEmpty()) {
                return [];
            }

            if ($withColors) {
                return $options->map(function ($option) {
                    return [
                        'value' => $option->value,
                        'label' => $option->label,
                        'color' => $option->color_class ?? '#6b7280',
                    ];
                })->toArray();
            }

            return $options->pluck('label', 'value')->toArray();
        });
    }

    /**
     * Get a single option by value
     *
     * @param string $groupKey
     * @param string $value
     * @return SettingOption|null
     */
    public static function getOption(string $groupKey, string $value): ?SettingOption
    {
        $cacheKey = "settings.{$groupKey}.{$value}";

        return Cache::remember($cacheKey, 3600, function () use ($groupKey, $value) {
            return SettingOption::where('category', $groupKey)
                ->where('value', $value)
                ->first();
        });
    }

    /**
     * Get the label for a specific option value
     *
     * @param string $groupKey
     * @param string $value
     * @return string
     */
    public static function getLabel(string $groupKey, string $value): string
    {
        $option = self::getOption($groupKey, $value);
        return $option?->label ?? $value;
    }

    /**
     * Get the color for a specific option value
     *
     * @param string $groupKey
     * @param string $value
     * @return string
     */
    public static function getColor(string $groupKey, string $value): string
    {
        $option = self::getOption($groupKey, $value);
        return $option?->color_class ?? '#6b7280';
    }

    /**
     * Clear cache for a specific group
     *
     * @param string|null $groupKey If null, clears all settings cache
     * @return void
     */
    public static function clearCache(?string $groupKey = null): void
    {
        if ($groupKey) {
            Cache::forget("settings.{$groupKey}.simple");
            Cache::forget("settings.{$groupKey}.with_colors");

            // Clear individual option caches
            $options = SettingOption::where('category', $groupKey)->get();
            foreach ($options as $option) {
                Cache::forget("settings.{$groupKey}.{$option->value}");
            }
        } else {
            // Clear all settings cache
            Cache::flush();
        }
    }

    /**
     * Check if a value exists in a group
     *
     * @param string $groupKey
     * @param string $value
     * @return bool
     */
    public static function valueExists(string $groupKey, string $value): bool
    {
        return self::getOption($groupKey, $value) !== null;
    }

    /**
     * Get default option for a group
     *
     * @param string $groupKey
     * @return SettingOption|null
     */
    public static function getDefault(string $groupKey): ?SettingOption
    {
        $cacheKey = "settings.{$groupKey}.default";

        return Cache::remember($cacheKey, 3600, function () use ($groupKey) {
            return SettingOption::where('category', $groupKey)
                ->where('is_default', true)
                ->where('is_active', true)
                ->first();
        });
    }
}
