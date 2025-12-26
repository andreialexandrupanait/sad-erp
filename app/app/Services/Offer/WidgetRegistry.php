<?php

namespace App\Services\Offer;

/**
 * Registry for offer builder widgets.
 *
 * Widgets are smaller content elements that can be placed inside blocks.
 * They support drag-and-drop reordering within and between blocks.
 */
class WidgetRegistry
{
    /**
     * Get all available widget types with their metadata.
     */
    public static function getWidgetTypes(): array
    {
        return [
            // Content widgets
            'text' => [
                'label' => __('Text'),
                'icon' => 'document-text',
                'iconBg' => 'bg-slate-100',
                'iconColor' => 'text-slate-600',
                'category' => 'content',
                'description' => __('Simple text paragraph'),
                'defaultData' => [
                    'content' => '',
                ],
            ],
            'heading' => [
                'label' => __('Heading'),
                'icon' => 'bookmark',
                'iconBg' => 'bg-purple-100',
                'iconColor' => 'text-purple-600',
                'category' => 'content',
                'description' => __('Section heading'),
                'defaultData' => [
                    'text' => '',
                    'level' => 'h3', // h2, h3, h4
                ],
            ],
            'image' => [
                'label' => __('Image'),
                'icon' => 'photograph',
                'iconBg' => 'bg-blue-100',
                'iconColor' => 'text-blue-600',
                'category' => 'content',
                'description' => __('Image with optional caption'),
                'defaultData' => [
                    'src' => '',
                    'alt' => '',
                    'caption' => '',
                    'width' => '100%',
                ],
            ],
            'list' => [
                'label' => __('List'),
                'icon' => 'view-list',
                'iconBg' => 'bg-orange-100',
                'iconColor' => 'text-orange-600',
                'category' => 'content',
                'description' => __('Bullet or numbered list'),
                'defaultData' => [
                    'type' => 'bullet', // bullet, numbered
                    'items' => [''],
                ],
            ],
            'icon_text' => [
                'label' => __('Icon + Text'),
                'icon' => 'annotation',
                'iconBg' => 'bg-indigo-100',
                'iconColor' => 'text-indigo-600',
                'category' => 'content',
                'description' => __('Icon with text beside it'),
                'defaultData' => [
                    'icon' => 'check-circle',
                    'iconColor' => 'text-green-500',
                    'text' => '',
                ],
            ],

            // Data widgets
            'stat_card' => [
                'label' => __('Stat Card'),
                'icon' => 'chart-bar',
                'iconBg' => 'bg-green-100',
                'iconColor' => 'text-green-600',
                'category' => 'data',
                'description' => __('Display a key metric with label'),
                'defaultData' => [
                    'value' => '99%',
                    'label' => __('Success Rate'),
                    'icon' => 'trending-up',
                    'color' => 'green',
                ],
            ],
            'feature_box' => [
                'label' => __('Feature Box'),
                'icon' => 'light-bulb',
                'iconBg' => 'bg-amber-100',
                'iconColor' => 'text-amber-600',
                'category' => 'data',
                'description' => __('Feature with icon, title, and description'),
                'defaultData' => [
                    'icon' => 'star',
                    'title' => '',
                    'description' => '',
                    'color' => 'blue',
                ],
            ],
            'testimonial' => [
                'label' => __('Testimonial'),
                'icon' => 'chat-alt-2',
                'iconBg' => 'bg-pink-100',
                'iconColor' => 'text-pink-600',
                'category' => 'data',
                'description' => __('Customer quote with attribution'),
                'defaultData' => [
                    'quote' => '',
                    'author' => '',
                    'role' => '',
                    'avatar' => '',
                ],
            ],
            'price_box' => [
                'label' => __('Price Box'),
                'icon' => 'currency-dollar',
                'iconBg' => 'bg-emerald-100',
                'iconColor' => 'text-emerald-600',
                'category' => 'data',
                'description' => __('Pricing display with features'),
                'defaultData' => [
                    'title' => '',
                    'price' => '',
                    'period' => __('/month'),
                    'features' => [],
                    'highlighted' => false,
                ],
            ],

            // Layout widgets
            'divider' => [
                'label' => __('Divider'),
                'icon' => 'minus',
                'iconBg' => 'bg-gray-100',
                'iconColor' => 'text-gray-600',
                'category' => 'layout',
                'description' => __('Horizontal line separator'),
                'defaultData' => [
                    'style' => 'solid', // solid, dashed, dotted
                    'color' => 'gray',
                ],
            ],
            'spacer' => [
                'label' => __('Spacer'),
                'icon' => 'switch-vertical',
                'iconBg' => 'bg-gray-100',
                'iconColor' => 'text-gray-600',
                'category' => 'layout',
                'description' => __('Vertical spacing'),
                'defaultData' => [
                    'height' => 24, // pixels
                ],
            ],
            'button' => [
                'label' => __('Button'),
                'icon' => 'cursor-click',
                'iconBg' => 'bg-blue-100',
                'iconColor' => 'text-blue-600',
                'category' => 'layout',
                'description' => __('Call-to-action button'),
                'defaultData' => [
                    'text' => __('Click here'),
                    'url' => '',
                    'style' => 'primary', // primary, secondary, outline
                    'align' => 'left',
                ],
            ],
        ];
    }

    /**
     * Get a specific widget type configuration.
     */
    public static function getWidgetType(string $type): ?array
    {
        return self::getWidgetTypes()[$type] ?? null;
    }

    /**
     * Get default data for a widget type.
     */
    public static function getDefaultData(string $type): array
    {
        $widgetType = self::getWidgetType($type);
        return $widgetType['defaultData'] ?? [];
    }

    /**
     * Create a new widget instance with default data.
     */
    public static function createWidget(string $type): array
    {
        return [
            'id' => 'widget_' . $type . '_' . uniqid(),
            'type' => $type,
            'data' => self::getDefaultData($type),
        ];
    }

    /**
     * Get widgets grouped by category.
     */
    public static function getWidgetsByCategory(): array
    {
        $grouped = [
            'content' => [],
            'data' => [],
            'layout' => [],
        ];

        foreach (self::getWidgetTypes() as $type => $config) {
            $category = $config['category'] ?? 'content';
            $grouped[$category][$type] = $config;
        }

        return $grouped;
    }

    /**
     * Get available icons for widgets.
     */
    public static function getAvailableIcons(): array
    {
        return [
            'check-circle', 'x-circle', 'star', 'heart', 'light-bulb',
            'trending-up', 'trending-down', 'chart-bar', 'chart-pie',
            'clock', 'calendar', 'users', 'user', 'mail', 'phone',
            'location-marker', 'globe', 'shield-check', 'badge-check',
            'currency-dollar', 'currency-euro', 'credit-card',
            'document', 'folder', 'cloud', 'server', 'code',
            'cog', 'adjustments', 'refresh', 'download', 'upload',
            'link', 'eye', 'lock-closed', 'key', 'sparkles',
        ];
    }

    /**
     * Get color options for widgets.
     */
    public static function getColorOptions(): array
    {
        return [
            'slate' => __('Slate'),
            'gray' => __('Gray'),
            'red' => __('Red'),
            'orange' => __('Orange'),
            'amber' => __('Amber'),
            'yellow' => __('Yellow'),
            'lime' => __('Lime'),
            'green' => __('Green'),
            'emerald' => __('Emerald'),
            'teal' => __('Teal'),
            'cyan' => __('Cyan'),
            'sky' => __('Sky'),
            'blue' => __('Blue'),
            'indigo' => __('Indigo'),
            'violet' => __('Violet'),
            'purple' => __('Purple'),
            'fuchsia' => __('Fuchsia'),
            'pink' => __('Pink'),
            'rose' => __('Rose'),
        ];
    }
}
