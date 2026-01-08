<?php

namespace App\Services\Editor\Renderers;

use App\Services\Editor\BlockRenderer;

/**
 * Renders the signature block for contracts and documents.
 */
class SignatureBlockRenderer implements BlockRendererInterface
{
    public function render(array $node, array $variables, string $context, array $documentContext): string
    {
        $attrs = $node['attrs'] ?? [];
        $showProvider = $attrs['showProviderSignature'] ?? true;
        $showClient = $attrs['showClientSignature'] ?? true;
        $providerLabel = $attrs['providerLabel'] ?? __('Prestator');
        $clientLabel = $attrs['clientLabel'] ?? __('Beneficiar');

        $isPdf = $context === BlockRenderer::CONTEXT_PDF;

        // Get names from variables
        $providerName = $variables['org_representative'] ?? $variables['org_name'] ?? '';
        $clientName = $variables['client_representative'] ?? $variables['client_company_name'] ?? '';

        if ($isPdf) {
            return $this->renderForPdf($showProvider, $showClient, $providerLabel, $clientLabel, $providerName, $clientName);
        }

        return $this->renderForWeb($showProvider, $showClient, $providerLabel, $clientLabel, $providerName, $clientName);
    }

    /**
     * Render for PDF context.
     */
    protected function renderForPdf(bool $showProvider, bool $showClient, string $providerLabel, string $clientLabel, string $providerName, string $clientName): string
    {
        $html = '<table style="width: 100%; margin-top: 40px; border: none;">';
        $html .= '<tr>';

        if ($showProvider) {
            $html .= '<td style="width: 50%; vertical-align: top; padding: 20px; border: none;">';
            $html .= '<p style="font-weight: 600; margin-bottom: 10px;">' . htmlspecialchars($providerLabel) . '</p>';
            $html .= '<p style="margin-bottom: 30px;">' . htmlspecialchars($providerName) . '</p>';
            $html .= '<p style="border-top: 1px solid #333; padding-top: 5px; margin-top: 50px;">Semnătură</p>';
            $html .= '</td>';
        }

        if ($showClient) {
            $html .= '<td style="width: 50%; vertical-align: top; padding: 20px; border: none;">';
            $html .= '<p style="font-weight: 600; margin-bottom: 10px;">' . htmlspecialchars($clientLabel) . '</p>';
            $html .= '<p style="margin-bottom: 30px;">' . htmlspecialchars($clientName) . '</p>';
            $html .= '<p style="border-top: 1px solid #333; padding-top: 5px; margin-top: 50px;">Semnătură</p>';
            $html .= '</td>';
        }

        $html .= '</tr></table>';

        return $html;
    }

    /**
     * Render for web preview context.
     */
    protected function renderForWeb(bool $showProvider, bool $showClient, string $providerLabel, string $clientLabel, string $providerName, string $clientName): string
    {
        $html = '<div class="signature-block grid grid-cols-2 gap-8 mt-10 p-4 border-t border-gray-200">';

        if ($showProvider) {
            $html .= '<div class="signature-provider">';
            $html .= '<p class="font-semibold text-gray-900 mb-2">' . htmlspecialchars($providerLabel) . '</p>';
            $html .= '<p class="text-gray-600 mb-8">' . htmlspecialchars($providerName) . '</p>';
            $html .= '<div class="border-t border-gray-400 pt-2 mt-12">';
            $html .= '<span class="text-sm text-gray-500">Semnătură</span>';
            $html .= '</div></div>';
        }

        if ($showClient) {
            $html .= '<div class="signature-client">';
            $html .= '<p class="font-semibold text-gray-900 mb-2">' . htmlspecialchars($clientLabel) . '</p>';
            $html .= '<p class="text-gray-600 mb-8">' . htmlspecialchars($clientName) . '</p>';
            $html .= '<div class="border-t border-gray-400 pt-2 mt-12">';
            $html .= '<span class="text-sm text-gray-500">Semnătură</span>';
            $html .= '</div></div>';
        }

        $html .= '</div>';

        return $html;
    }
}
