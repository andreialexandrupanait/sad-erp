<?php

namespace App\Services\Editor\Renderers;

use App\Models\Contract;
use App\Models\Offer;
use App\Services\Editor\BlockRenderer;

/**
 * Renders the services block - a dynamic table of services/items.
 *
 * This is a special block that renders the services from the document
 * (Contract items or Offer items) as a formatted table.
 */
class ServicesBlockRenderer implements BlockRendererInterface
{
    public function render(array $node, array $variables, string $context, array $documentContext): string
    {
        $attrs = $node['attrs'] ?? [];
        $document = $documentContext['document'] ?? null;

        // Get items from document context
        $items = $documentContext['items'] ?? collect();

        // If no items in context, try to get from document
        if ($items->isEmpty() && $document) {
            $items = $this->getItemsFromDocument($document);
        }

        if ($items->isEmpty()) {
            return $this->renderEmptyState($context);
        }

        $showPrices = $attrs['showPrices'] ?? true;
        $showDescriptions = $attrs['showDescriptions'] ?? true;
        $showQuantity = $attrs['showQuantity'] ?? true;
        $title = $attrs['title'] ?? __('Servicii');
        $currency = $document?->currency ?? 'EUR';

        return $this->renderTable($items, [
            'showPrices' => $showPrices,
            'showDescriptions' => $showDescriptions,
            'showQuantity' => $showQuantity,
            'title' => $title,
            'currency' => $currency,
            'context' => $context,
        ]);
    }

    /**
     * Get items from a document model.
     */
    protected function getItemsFromDocument(Contract|Offer $document): \Illuminate\Support\Collection
    {
        if ($document instanceof Contract) {
            $items = $document->items ?? collect();
            if ($items->isEmpty() && $document->offer) {
                $items = $document->offer->items ?? collect();
            }
        } else {
            $items = $document->items ?? collect();
        }

        // Filter to selected items only
        return $items->filter(function ($item) {
            return $item->is_selected !== false;
        })->sortBy([
            ['type', 'desc'],
            ['sort_order', 'asc'],
        ]);
    }

    /**
     * Render the services table.
     */
    protected function renderTable($items, array $options): string
    {
        $isPdf = $options['context'] === BlockRenderer::CONTEXT_PDF;
        $currency = htmlspecialchars($options['currency']);

        // Table styles
        $tableStyle = $isPdf
            ? 'width: 100%; border-collapse: collapse; margin: 16px 0;'
            : '';

        $thStyle = $isPdf
            ? 'border: 1px solid #cbd5e1; padding: 10px 12px; background-color: #f1f5f9; font-weight: 600; text-align: left;'
            : '';

        $tdStyle = $isPdf
            ? 'border: 1px solid #cbd5e1; padding: 8px 12px;'
            : '';

        $tdRightStyle = $isPdf
            ? 'border: 1px solid #cbd5e1; padding: 8px 12px; text-align: right;'
            : '';

        // Build table
        $html = '<table' . ($tableStyle ? ' style="' . $tableStyle . '"' : ' class="table"') . '>';

        // Header
        $html .= '<thead><tr>';
        $html .= '<th' . ($thStyle ? ' style="' . $thStyle . '"' : '') . '>' . __('Serviciu') . '</th>';

        if ($options['showQuantity']) {
            $html .= '<th' . ($thStyle ? ' style="' . $thStyle . ' text-align: center;"' : '') . '>' . __('Cant.') . '</th>';
        }

        if ($options['showPrices']) {
            $html .= '<th' . ($thStyle ? ' style="' . $thStyle . ' text-align: right;"' : '') . '>' . __('Preț unitar') . '</th>';
            $html .= '<th' . ($thStyle ? ' style="' . $thStyle . ' text-align: right;"' : '') . '>' . __('Total') . '</th>';
        }

        $html .= '</tr></thead>';

        // Body
        $html .= '<tbody>';
        $grandTotal = 0;

        foreach ($items as $item) {
            $name = htmlspecialchars($item->title ?? $item->name ?? __('Serviciu'));
            $description = $options['showDescriptions'] ? htmlspecialchars($item->description ?? '') : '';
            $quantity = (float) ($item->quantity ?? 1);
            $unitPrice = (float) ($item->unit_price ?? 0);
            $total = (float) ($item->total_price ?? $item->total ?? $unitPrice * $quantity);
            $grandTotal += $total;

            $html .= '<tr>';

            // Service name and description
            $html .= '<td' . ($tdStyle ? ' style="' . $tdStyle . '"' : '') . '>';
            $html .= '<strong>' . $name . '</strong>';
            if ($description) {
                $html .= '<br><small style="color: #64748b;">' . nl2br($description) . '</small>';
            }
            $html .= '</td>';

            if ($options['showQuantity']) {
                $html .= '<td' . ($tdStyle ? ' style="' . $tdStyle . ' text-align: center;"' : '') . '>' . number_format($quantity, 0) . '</td>';
            }

            if ($options['showPrices']) {
                $html .= '<td' . ($tdRightStyle ? ' style="' . $tdRightStyle . '"' : '') . '>' . number_format($unitPrice, 2, ',', '.') . ' ' . $currency . '</td>';
                $html .= '<td' . ($tdRightStyle ? ' style="' . $tdRightStyle . '"' : '') . '><strong>' . number_format($total, 2, ',', '.') . ' ' . $currency . '</strong></td>';
            }

            $html .= '</tr>';
        }

        $html .= '</tbody>';

        // Footer with total
        if ($options['showPrices']) {
            $colspan = 1 + ($options['showQuantity'] ? 1 : 0) + 1;
            $html .= '<tfoot><tr>';
            $html .= '<td colspan="' . $colspan . '"' . ($tdRightStyle ? ' style="' . $tdRightStyle . ' font-weight: 600;"' : '') . '>' . __('TOTAL') . '</td>';
            $html .= '<td' . ($tdRightStyle ? ' style="' . $tdRightStyle . ' font-weight: 700; font-size: 1.1em;"' : '') . '>' . number_format($grandTotal, 2, ',', '.') . ' ' . $currency . '</td>';
            $html .= '</tr></tfoot>';
        }

        $html .= '</table>';

        return $html;
    }

    /**
     * Render empty state.
     */
    protected function renderEmptyState(string $context): string
    {
        if ($context === BlockRenderer::CONTEXT_WEB) {
            return '<div class="services-block-placeholder p-4 border-2 border-dashed border-gray-300 rounded text-gray-500 text-center">'
                . '<p>' . __('Nu sunt servicii disponibile') . '</p>'
                . '</div>';
        }

        return '<p><em>' . __('Nu sunt specificate servicii') . '</em></p>';
    }
}
