<?php

namespace App\Services\Editor\Renderers;

use App\Models\Contract;
use App\Models\Offer;
use App\Services\Editor\BlockRenderer;

/**
 * Renders the totals block showing subtotal, discount, VAT, and total.
 */
class TotalsBlockRenderer implements BlockRendererInterface
{
    public function render(array $node, array $variables, string $context, array $documentContext): string
    {
        $attrs = $node['attrs'] ?? [];
        $document = $documentContext['document'] ?? null;

        // Get values from document or variables
        $currency = $document?->currency ?? 'EUR';
        $subtotal = $this->getValue($document, 'subtotal', $variables);
        $discountPercent = $this->getValue($document, 'discount_percent', $variables);
        $discountAmount = $this->getValue($document, 'discount_amount', $variables);
        $total = $this->getValue($document, 'total', $variables, 'contract_total');

        // Options
        $showSubtotal = $attrs['showSubtotal'] ?? true;
        $showDiscount = $attrs['showDiscount'] ?? ($discountAmount > 0);
        $showVat = $attrs['showVat'] ?? false;
        $vatPercent = $attrs['vatPercent'] ?? 19;

        $isPdf = $context === BlockRenderer::CONTEXT_PDF;

        if ($isPdf) {
            return $this->renderForPdf($subtotal, $discountPercent, $discountAmount, $total, $currency, $showSubtotal, $showDiscount, $showVat, $vatPercent);
        }

        return $this->renderForWeb($subtotal, $discountPercent, $discountAmount, $total, $currency, $showSubtotal, $showDiscount, $showVat, $vatPercent);
    }

    /**
     * Get value from document or variables.
     */
    protected function getValue($document, string $field, array $variables, ?string $varName = null): float
    {
        if ($document) {
            $value = $document->{$field} ?? $document->{'total_' . $field} ?? null;
            if ($value !== null) {
                return (float) $value;
            }
        }

        // Try from variables
        $varKey = $varName ?? $field;
        if (isset($variables[$varKey])) {
            return (float) str_replace(['.', ','], ['', '.'], $variables[$varKey]);
        }

        return 0;
    }

    /**
     * Render for PDF context.
     */
    protected function renderForPdf(float $subtotal, float $discountPercent, float $discountAmount, float $total, string $currency, bool $showSubtotal, bool $showDiscount, bool $showVat, float $vatPercent): string
    {
        $currency = htmlspecialchars($currency);

        $html = '<table style="width: 300px; margin-left: auto; margin-top: 20px; border: none;">';

        if ($showSubtotal) {
            $html .= '<tr>';
            $html .= '<td style="padding: 5px 10px; text-align: right; border: none;">' . __('Subtotal') . ':</td>';
            $html .= '<td style="padding: 5px 10px; text-align: right; border: none; font-weight: 500;">' . number_format($subtotal, 2, ',', '.') . ' ' . $currency . '</td>';
            $html .= '</tr>';
        }

        if ($showDiscount && $discountAmount > 0) {
            $discountLabel = $discountPercent > 0
                ? __('Discount') . ' (' . number_format($discountPercent, 0) . '%)'
                : __('Discount');
            $html .= '<tr>';
            $html .= '<td style="padding: 5px 10px; text-align: right; border: none; color: #dc2626;">' . $discountLabel . ':</td>';
            $html .= '<td style="padding: 5px 10px; text-align: right; border: none; color: #dc2626;">-' . number_format($discountAmount, 2, ',', '.') . ' ' . $currency . '</td>';
            $html .= '</tr>';
        }

        if ($showVat) {
            $vatAmount = ($total - $discountAmount) * ($vatPercent / 100);
            $html .= '<tr>';
            $html .= '<td style="padding: 5px 10px; text-align: right; border: none;">' . __('TVA') . ' (' . $vatPercent . '%):</td>';
            $html .= '<td style="padding: 5px 10px; text-align: right; border: none;">' . number_format($vatAmount, 2, ',', '.') . ' ' . $currency . '</td>';
            $html .= '</tr>';
        }

        $html .= '<tr>';
        $html .= '<td style="padding: 10px; text-align: right; border-top: 2px solid #333; font-weight: 700; font-size: 1.1em;">' . __('TOTAL') . ':</td>';
        $html .= '<td style="padding: 10px; text-align: right; border-top: 2px solid #333; font-weight: 700; font-size: 1.1em;">' . number_format($total, 2, ',', '.') . ' ' . $currency . '</td>';
        $html .= '</tr>';

        $html .= '</table>';

        return $html;
    }

    /**
     * Render for web preview context.
     */
    protected function renderForWeb(float $subtotal, float $discountPercent, float $discountAmount, float $total, string $currency, bool $showSubtotal, bool $showDiscount, bool $showVat, float $vatPercent): string
    {
        $currency = htmlspecialchars($currency);

        $html = '<div class="totals-block w-72 ml-auto mt-5 bg-gray-50 rounded-lg p-4">';

        if ($showSubtotal) {
            $html .= '<div class="flex justify-between py-1">';
            $html .= '<span class="text-gray-600">' . __('Subtotal') . ':</span>';
            $html .= '<span class="font-medium">' . number_format($subtotal, 2, ',', '.') . ' ' . $currency . '</span>';
            $html .= '</div>';
        }

        if ($showDiscount && $discountAmount > 0) {
            $discountLabel = $discountPercent > 0
                ? __('Discount') . ' (' . number_format($discountPercent, 0) . '%)'
                : __('Discount');
            $html .= '<div class="flex justify-between py-1 text-red-600">';
            $html .= '<span>' . $discountLabel . ':</span>';
            $html .= '<span>-' . number_format($discountAmount, 2, ',', '.') . ' ' . $currency . '</span>';
            $html .= '</div>';
        }

        if ($showVat) {
            $vatAmount = ($total - $discountAmount) * ($vatPercent / 100);
            $html .= '<div class="flex justify-between py-1">';
            $html .= '<span class="text-gray-600">' . __('TVA') . ' (' . $vatPercent . '%):</span>';
            $html .= '<span>' . number_format($vatAmount, 2, ',', '.') . ' ' . $currency . '</span>';
            $html .= '</div>';
        }

        $html .= '<div class="flex justify-between py-2 mt-2 border-t-2 border-gray-300">';
        $html .= '<span class="font-bold text-lg">' . __('TOTAL') . ':</span>';
        $html .= '<span class="font-bold text-lg">' . number_format($total, 2, ',', '.') . ' ' . $currency . '</span>';
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }
}
