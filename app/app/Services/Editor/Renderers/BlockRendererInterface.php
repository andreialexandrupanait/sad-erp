<?php

namespace App\Services\Editor\Renderers;

/**
 * Interface for block renderers.
 */
interface BlockRendererInterface
{
    /**
     * Render a block node to HTML.
     *
     * @param array $node The block node data
     * @param array $variables Resolved variable values
     * @param string $context Rendering context (web, email, pdf)
     * @param array $documentContext Additional document context
     * @return string Rendered HTML
     */
    public function render(array $node, array $variables, string $context, array $documentContext): string;
}
