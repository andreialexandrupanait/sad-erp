<?php

namespace App\Services\Offer;

use App\Models\Offer;
use Illuminate\Support\Facades\View;

/**
 * Server-side renderer for Simple Offer Builder blocks.
 *
 * Handles rendering of the 5 block types:
 * - text: Heading + body content
 * - services: Services list with prices
 * - summary: Totals display (auto-calculated)
 * - brands: Logo gallery
 * - acceptance: Accept/reject actions
 */
class SimpleBlockRenderer
{
    /**
     * Available block types in the simple builder.
     */
    public const BLOCK_TYPES = ['text', 'services', 'summary', 'brands', 'acceptance'];

    /**
     * Render context types.
     */
    public const CONTEXT_BUILDER = 'builder';
    public const CONTEXT_PUBLIC = 'public';
    public const CONTEXT_PDF = 'pdf';

    /**
     * Render all visible blocks for an offer.
     *
     * @param Offer $offer The offer to render
     * @param string $context The rendering context (builder, public, pdf)
     * @return string Rendered HTML
     */
    public function render(Offer $offer, string $context = self::CONTEXT_PUBLIC): string
    {
        $html = '';
        $blocks = $this->getBlocks($offer);
        $items = $this->getItems($offer);

        foreach ($blocks as $block) {
            // Skip invisible blocks
            if (!($block['visible'] ?? true)) {
                continue;
            }

            // Validate block type
            if (!in_array($block['type'] ?? '', self::BLOCK_TYPES)) {
                continue;
            }

            $html .= $this->renderBlock($block, $offer, $items, $context);
        }

        return $html;
    }

    /**
     * Render a single block.
     *
     * @param array $block Block data
     * @param Offer $offer The offer
     * @param array $items Service items array
     * @param string $context Rendering context
     * @return string Rendered HTML
     */
    public function renderBlock(array $block, Offer $offer, array $items, string $context): string
    {
        $viewName = $this->getViewName($block['type'], $context);

        if (!View::exists($viewName)) {
            return "<!-- Block view not found: {$viewName} -->";
        }

        return view($viewName, [
            'block' => $block,
            'offer' => $offer,
            'items' => $items,
        ])->render();
    }

    /**
     * Get the view name for a block type and context.
     *
     * @param string $type Block type
     * @param string $context Rendering context
     * @return string View name
     */
    protected function getViewName(string $type, string $context): string
    {
        $suffix = match ($context) {
            self::CONTEXT_PDF => '-pdf',
            self::CONTEXT_PUBLIC => '-public',
            default => '',
        };

        return "components.offer-simple.blocks.{$type}{$suffix}";
    }

    /**
     * Get blocks from offer, with defaults if empty.
     *
     * @param Offer $offer
     * @return array
     */
    protected function getBlocks(Offer $offer): array
    {
        $blocks = $offer->blocks ?? [];

        if (empty($blocks)) {
            return $this->getDefaultBlocks();
        }

        return is_string($blocks) ? json_decode($blocks, true) : $blocks;
    }

    /**
     * Get items from offer.
     *
     * @param Offer $offer
     * @return array
     */
    protected function getItems(Offer $offer): array
    {
        // Try to get items from relationship first
        if ($offer->relationLoaded('items')) {
            return $offer->items->toArray();
        }

        // Try items from offer JSON field
        $items = $offer->items ?? [];

        if (is_string($items)) {
            $items = json_decode($items, true) ?? [];
        }

        return $items;
    }

    /**
     * Get default block structure for new offers.
     *
     * @return array
     */
    public function getDefaultBlocks(): array
    {
        $timestamp = now()->timestamp * 1000;

        return [
            [
                'id' => 'text_' . $timestamp,
                'type' => 'text',
                'visible' => true,
                'data' => [
                    'heading' => __('About Our Services'),
                    'body' => '',
                ],
            ],
            [
                'id' => 'services_' . ($timestamp + 1),
                'type' => 'services',
                'visible' => true,
                'data' => [
                    'heading' => __('Proposed Services'),
                    'showDescriptions' => true,
                    'showPrices' => true,
                ],
            ],
            [
                'id' => 'summary_' . ($timestamp + 2),
                'type' => 'summary',
                'visible' => true,
                'data' => [
                    'heading' => __('Investment Summary'),
                    'showSubtotal' => true,
                    'showVAT' => true,
                    'vatPercent' => 19,
                    'showDiscount' => true,
                    'showGrandTotal' => true,
                ],
            ],
            [
                'id' => 'brands_' . ($timestamp + 3),
                'type' => 'brands',
                'visible' => false,
                'data' => [
                    'heading' => __('Trusted By'),
                    'logos' => [],
                    'columns' => 4,
                ],
            ],
            [
                'id' => 'acceptance_' . ($timestamp + 4),
                'type' => 'acceptance',
                'visible' => true,
                'data' => [
                    'heading' => __('Offer Acceptance'),
                    'paragraph' => __('By accepting this offer, you agree to the terms and conditions outlined above. This offer is valid until the date specified.'),
                    'acceptButtonText' => __('Accept Offer'),
                    'rejectButtonText' => __('Decline'),
                    'requireSignature' => false,
                    'sendConfirmation' => true,
                ],
            ],
        ];
    }

    /**
     * Calculate totals for an offer.
     *
     * @param Offer $offer
     * @param array|null $blocks Optional blocks with summary settings
     * @return array
     */
    public function calculateTotals(Offer $offer, ?array $blocks = null): array
    {
        $items = $this->getItems($offer);
        $blocks = $blocks ?? $this->getBlocks($offer);

        // Find summary block for VAT settings
        $summaryBlock = collect($blocks)->firstWhere('type', 'summary');
        $vatPercent = $summaryBlock['data']['vatPercent'] ?? 19;

        $subtotal = collect($items)->sum(fn($item) => floatval($item['total'] ?? 0));
        $discountPercent = floatval($offer->discount_percent ?? 0);
        $discountAmount = $subtotal * ($discountPercent / 100);
        $netTotal = $subtotal - $discountAmount;
        $vatAmount = $netTotal * ($vatPercent / 100);
        $grandTotal = $netTotal + $vatAmount;

        return [
            'subtotal' => round($subtotal, 2),
            'discount_percent' => $discountPercent,
            'discount_amount' => round($discountAmount, 2),
            'net_total' => round($netTotal, 2),
            'vat_percent' => $vatPercent,
            'vat_amount' => round($vatAmount, 2),
            'grand_total' => round($grandTotal, 2),
            'items_count' => count($items),
            'currency' => $offer->currency ?? 'EUR',
        ];
    }

    /**
     * Validate block structure.
     *
     * @param array $blocks
     * @return array Validation result with 'valid' and 'errors' keys
     */
    public function validateBlocks(array $blocks): array
    {
        $errors = [];

        foreach ($blocks as $index => $block) {
            if (!isset($block['id'])) {
                $errors[] = "Block at index {$index} is missing 'id'";
            }

            if (!isset($block['type'])) {
                $errors[] = "Block at index {$index} is missing 'type'";
            } elseif (!in_array($block['type'], self::BLOCK_TYPES)) {
                $errors[] = "Block at index {$index} has invalid type: {$block['type']}";
            }

            if (!isset($block['data']) || !is_array($block['data'])) {
                $errors[] = "Block at index {$index} is missing 'data' array";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Validate items structure.
     *
     * @param array $items
     * @return array Validation result with 'valid' and 'errors' keys
     */
    public function validateItems(array $items): array
    {
        $errors = [];
        $requiredFields = ['title', 'quantity', 'unit_price'];

        foreach ($items as $index => $item) {
            foreach ($requiredFields as $field) {
                if (!isset($item[$field]) || $item[$field] === '') {
                    $errors[] = "Item at index {$index} is missing required field: {$field}";
                }
            }

            if (isset($item['quantity']) && (!is_numeric($item['quantity']) || $item['quantity'] < 0)) {
                $errors[] = "Item at index {$index} has invalid quantity";
            }

            if (isset($item['unit_price']) && (!is_numeric($item['unit_price']) || $item['unit_price'] < 0)) {
                $errors[] = "Item at index {$index} has invalid unit_price";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
