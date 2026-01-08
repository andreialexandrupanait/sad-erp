<?php

namespace App\Services\Editor\Renderers;

use App\Services\Editor\BlockRenderer;

/**
 * Renders variable nodes.
 *
 * In the editor, variables appear as protected spans.
 * When rendering, they are replaced with their resolved values.
 */
class VariableRenderer implements BlockRendererInterface
{
    public function render(array $node, array $variables, string $context, array $documentContext): string
    {
        $attrs = $node['attrs'] ?? [];
        $name = $attrs['name'] ?? '';

        if (empty($name)) {
            return '';
        }

        // Get resolved value
        if (isset($variables[$name])) {
            $value = $variables[$name];

            // Some variables (like services_list) contain pre-formatted HTML
            // Check if this is a block-type variable that shouldn't be escaped
            if ($this->isBlockVariable($name)) {
                return $value;
            }

            return $value;
        }

        // Variable not resolved - show placeholder
        // In web context, show styled placeholder; in PDF/email, show empty or fallback
        if ($context === BlockRenderer::CONTEXT_WEB) {
            return '<span class="variable-placeholder text-red-500">[' . htmlspecialchars($name) . ']</span>';
        }

        // Use fallback value if provided
        if (!empty($attrs['fallback'])) {
            return htmlspecialchars($attrs['fallback']);
        }

        // Return empty string for PDF/email if no value
        return '';
    }

    /**
     * Check if variable is a block type (contains HTML).
     */
    protected function isBlockVariable(string $name): bool
    {
        return in_array($name, [
            'services_list',
            'offer_services_list',
        ]);
    }
}
