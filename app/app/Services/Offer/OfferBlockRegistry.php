<?php

namespace App\Services\Offer;

/**
 * Registry for offer block types.
 *
 * This service provides a central registry of all available block types
 * for the offer builder. New block types can be added by:
 * 1. Adding an entry to getBlockTypes()
 * 2. Creating a builder view at: components/offer/blocks/{type}.blade.php
 * 3. Creating a PDF view at: components/offer/blocks/{type}-pdf.blade.php
 */
class OfferBlockRegistry
{
    /**
     * Get all available block types with their metadata.
     */
    public static function getBlockTypes(): array
    {
        return [
            'header' => [
                'label' => __('Header'),
                'icon' => 'document-text',
                'iconBg' => 'bg-slate-100',
                'iconColor' => 'text-slate-600',
                'category' => 'structure',
                'description' => __('Company identity, client details, offer number, dates'),
                'defaultData' => [
                    'introTitle' => __('Your business partner for digital solutions.'),
                    'introText' => __('We deliver high-quality services tailored to your specific needs.'),
                    'showLogo' => true,
                    'showDates' => true,
                    'showCompanyInfo' => true,
                    'showClientInfo' => true,
                ],
            ],
            'services' => [
                'label' => __('Services'),
                'icon' => 'clipboard-list',
                'iconBg' => 'bg-blue-100',
                'iconColor' => 'text-blue-600',
                'category' => 'content',
                'description' => __('Selected services, optional upsells, and notes'),
                'defaultData' => [
                    'title' => __('Proposed Services'),
                    'showDescriptions' => true,
                    'showPrices' => true,
                    'optionalServices' => [],
                    'notes' => '',
                    'notesTitle' => __('Notes'),
                ],
            ],
            'summary' => [
                'label' => __('Summary'),
                'icon' => 'calculator',
                'iconBg' => 'bg-green-100',
                'iconColor' => 'text-green-600',
                'category' => 'content',
                'description' => __('Service breakdown, subtotal, VAT, discounts, total'),
                'defaultData' => [
                    'title' => __('Investment Summary'),
                    'showSubtotal' => true,
                    'showVAT' => false,
                    'vatPercent' => 19,
                    'showDiscount' => true,
                    'showGrandTotal' => true,
                ],
            ],
            'brands' => [
                'label' => __('Brands'),
                'icon' => 'star',
                'iconBg' => 'bg-amber-100',
                'iconColor' => 'text-amber-600',
                'category' => 'content',
                'description' => __('Logo gallery of trusted partners/clients'),
                'defaultData' => [
                    'title' => __('Trusted Partners'),
                    'logos' => [],
                    'layout' => 'grid',
                    'columns' => 4,
                ],
            ],
            'acceptance' => [
                'label' => __('Acceptance'),
                'icon' => 'check-circle',
                'iconBg' => 'bg-purple-100',
                'iconColor' => 'text-purple-600',
                'category' => 'structure',
                'description' => __('Acceptance text with Accept/Reject buttons'),
                'defaultData' => [
                    'title' => __('Offer Acceptance'),
                    'acceptanceText' => __('By approving this offer, I confirm that I have read and agree to the services and conditions described above.'),
                    'showClientInfo' => true,
                    'showDate' => true,
                    'acceptButtonText' => __('Accept Offer'),
                    'rejectButtonText' => __('Decline'),
                ],
            ],
            // Utility blocks
            'content' => [
                'label' => __('Content'),
                'icon' => 'document',
                'iconBg' => 'bg-slate-100',
                'iconColor' => 'text-slate-600',
                'category' => 'utility',
                'description' => __('Rich text content block'),
                'defaultData' => [
                    'title' => '',
                    'content' => '',
                ],
            ],
            'divider' => [
                'label' => __('Divider'),
                'icon' => 'minus',
                'iconBg' => 'bg-slate-100',
                'iconColor' => 'text-slate-600',
                'category' => 'utility',
                'description' => __('Horizontal divider line'),
                'defaultData' => [
                    'style' => 'solid',
                ],
            ],
            'spacer' => [
                'label' => __('Spacer'),
                'icon' => 'arrows-expand',
                'iconBg' => 'bg-slate-100',
                'iconColor' => 'text-slate-600',
                'category' => 'utility',
                'description' => __('Vertical spacing'),
                'defaultData' => [
                    'height' => 40,
                ],
            ],
            'image' => [
                'label' => __('Image'),
                'icon' => 'photograph',
                'iconBg' => 'bg-indigo-100',
                'iconColor' => 'text-indigo-600',
                'category' => 'utility',
                'description' => __('Image with optional caption'),
                'defaultData' => [
                    'src' => '',
                    'alt' => '',
                    'caption' => '',
                    'alignment' => 'center',
                ],
            ],
            'section' => [
                'label' => __('Section'),
                'icon' => 'view-boards',
                'iconBg' => 'bg-cyan-100',
                'iconColor' => 'text-cyan-600',
                'category' => 'layout',
                'description' => __('Container for drag-and-drop widgets'),
                'defaultData' => [
                    'title' => '',
                    'widgets' => [],
                ],
            ],
        ];
    }

    /**
     * Get a specific block type configuration.
     */
    public static function getBlockType(string $type): ?array
    {
        return self::getBlockTypes()[$type] ?? null;
    }

    /**
     * Get default data for a block type.
     */
    public static function getDefaultData(string $type): array
    {
        $blockType = self::getBlockType($type);
        return $blockType['defaultData'] ?? [];
    }

    /**
     * Get the label for a block type.
     */
    public static function getLabel(string $type): string
    {
        $blockType = self::getBlockType($type);
        return $blockType['label'] ?? ucfirst($type);
    }

    /**
     * Check if a builder view exists for this block type.
     */
    public static function hasBuilderView(string $type): bool
    {
        return view()->exists("components.offer.blocks.{$type}");
    }

    /**
     * Check if a PDF view exists for this block type.
     */
    public static function hasPdfView(string $type): bool
    {
        return view()->exists("components.offer.blocks.{$type}-pdf");
    }

    /**
     * Get the builder view name for a block type.
     */
    public static function getBuilderView(string $type): string
    {
        return "components.offer.blocks.{$type}";
    }

    /**
     * Get the PDF view name for a block type.
     */
    public static function getPdfView(string $type): string
    {
        return "components.offer.blocks.{$type}-pdf";
    }

    /**
     * Create a new block instance with default data.
     */
    public static function createBlock(string $type): array
    {
        return [
            'id' => $type . '_' . uniqid(),
            'type' => $type,
            'visible' => true,
            'data' => self::getDefaultData($type),
        ];
    }

    /**
     * Get blocks grouped by category.
     */
    public static function getBlocksByCategory(): array
    {
        $grouped = [];
        foreach (self::getBlockTypes() as $type => $config) {
            $category = $config['category'] ?? 'other';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][$type] = $config;
        }
        return $grouped;
    }

    /**
     * Get block type icons mapping for Alpine.js.
     */
    public static function getBlockIcons(): array
    {
        $icons = [];
        foreach (self::getBlockTypes() as $type => $config) {
            $icons[$type] = [
                'icon' => $config['icon'] ?? 'document',
                'bg' => $config['iconBg'] ?? 'bg-slate-100',
                'color' => $config['iconColor'] ?? 'text-slate-600',
            ];
        }
        return $icons;
    }
}
