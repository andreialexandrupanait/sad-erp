<?php

namespace App\Helpers;

use App\Models\SettingOption;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SettingsHelper
{
    /**
     * Cache TTL in seconds (1 hour)
     */
    private const CACHE_TTL = 3600;

    /**
     * Get the organization-scoped cache key prefix.
     */
    private static function getCachePrefix(): string
    {
        $orgId = Auth::check() ? (Auth::user()->organization_id ?? 'global') : 'guest';
        return "settings.org.{$orgId}";
    }

    /**
     * Get dropdown options for a specific setting group by key
     *
     * @param string $groupKey e.g., 'domain_registrars', 'client_statuses'
     * @param bool $withColors Include colors in the result
     * @return array
     */
    public static function getOptions(string $groupKey, bool $withColors = false): array
    {
        $prefix = self::getCachePrefix();
        $cacheKey = "{$prefix}.{$groupKey}." . ($withColors ? 'with_colors' : 'simple');

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($groupKey, $withColors) {
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
        $prefix = self::getCachePrefix();
        $cacheKey = "{$prefix}.{$groupKey}.{$value}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($groupKey, $value) {
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
     * @param string|null $groupKey If null, clears all settings cache for the current organization
     * @param int|null $organizationId Optional organization ID to clear cache for
     * @return void
     */
    public static function clearCache(?string $groupKey = null, ?int $organizationId = null): void
    {
        $orgId = $organizationId ?? (Auth::check() ? Auth::user()->organization_id : null);
        $prefix = $orgId ? "settings.org.{$orgId}" : "settings.org";

        if ($groupKey) {
            Cache::forget("{$prefix}.{$groupKey}.simple");
            Cache::forget("{$prefix}.{$groupKey}.with_colors");
            Cache::forget("{$prefix}.{$groupKey}.default");

            // Clear individual option caches
            $options = SettingOption::withoutGlobalScopes()
                ->where('category', $groupKey)
                ->when($orgId, fn($q) => $q->where('organization_id', $orgId))
                ->get();
            foreach ($options as $option) {
                Cache::forget("{$prefix}.{$groupKey}.{$option->value}");
            }
        } else {
            // Use cache tags if available, otherwise use pattern-based clearing
            // For now, we'll clear what we can without flushing everything
            $categories = [
                'client_statuses', 'domain_registrars', 'domain_statuses',
                'billing_cycles', 'subscription_statuses', 'payment_methods',
                'access_platforms', 'expense_categories', 'currencies',
                'dashboard_quick_actions', 'task_statuses', 'task_priorities'
            ];
            foreach ($categories as $category) {
                self::clearCache($category, $orgId);
            }
        }
    }

}
