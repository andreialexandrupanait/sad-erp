<?php

namespace App\Services\Editor;

use App\Models\Contract;
use App\Models\Offer;
use App\Services\Editor\Renderers\HeadingRenderer;
use App\Services\Editor\Renderers\ParagraphRenderer;
use App\Services\Editor\Renderers\ServicesBlockRenderer;
use App\Services\Editor\Renderers\SignatureBlockRenderer;
use App\Services\Editor\Renderers\TotalsBlockRenderer;
use App\Services\Editor\Renderers\VariableRenderer;
use Illuminate\Support\Facades\Log;

/**
 * BlockRenderer - Converts JSON blocks to HTML.
 *
 * Supports different rendering contexts:
 * - 'web': For web preview in the editor
 * - 'email': For email rendering (inline styles)
 * - 'pdf': For PDF generation (DOMPDF compatible)
 */
class BlockRenderer
{
    public const CONTEXT_WEB = 'web';
    public const CONTEXT_EMAIL = 'email';
    public const CONTEXT_PDF = 'pdf';

    protected string $context;
    protected array $renderers = [];
    protected array $variables = [];
    protected array $documentContext = [];

    public function __construct(string $context = self::CONTEXT_WEB)
    {
        $this->context = $context;
        $this->registerDefaultRenderers();
    }

    /**
     * Register default block renderers.
     */
    protected function registerDefaultRenderers(): void
    {
        $this->renderers = [
            'doc' => fn($node) => $this->renderChildren($node),
            'paragraph' => new ParagraphRenderer(),
            'heading' => new HeadingRenderer(),
            'text' => fn($node) => $this->renderText($node),
            'variable' => new VariableRenderer(),
            'servicesBlock' => new ServicesBlockRenderer(),
            'signatureBlock' => new SignatureBlockRenderer(),
            'totalsBlock' => new TotalsBlockRenderer(),
            'bulletList' => fn($node) => $this->renderList($node, 'ul'),
            'orderedList' => fn($node) => $this->renderList($node, 'ol'),
            'listItem' => fn($node) => '<li>' . $this->renderChildren($node) . '</li>',
            'hardBreak' => fn($node) => '<br>',
            'horizontalRule' => fn($node) => '<hr>',
            'blockquote' => fn($node) => '<blockquote>' . $this->renderChildren($node) . '</blockquote>',
            'codeBlock' => fn($node) => '<pre><code>' . htmlspecialchars($this->getTextContent($node)) . '</code></pre>',
            'table' => fn($node) => $this->renderTable($node),
            'tableRow' => fn($node) => '<tr>' . $this->renderChildren($node) . '</tr>',
            'tableCell' => fn($node) => $this->renderTableCell($node, 'td'),
            'tableHeader' => fn($node) => $this->renderTableCell($node, 'th'),
            'image' => fn($node) => $this->renderImage($node),
        ];
    }

    /**
     * Register a custom renderer.
     */
    public function registerRenderer(string $type, callable|object $renderer): self
    {
        $this->renderers[$type] = $renderer;
        return $this;
    }

    /**
     * Render a JSON document to HTML.
     *
     * @param array $document The TipTap JSON document
     * @param array $variables Resolved variable values
     * @param array $context Additional context (document model, items, etc.)
     * @return string Rendered HTML
     */
    public function render(array $document, array $variables = [], array $context = []): string
    {
        $this->variables = $variables;
        $this->documentContext = $context;

        try {
            return $this->renderNode($document);
        } catch (\Throwable $e) {
            Log::error('BlockRenderer error', [
                'message' => $e->getMessage(),
                'context' => $this->context,
            ]);
            return '';
        }
    }

    /**
     * Render a single node.
     */
    public function renderNode(array $node): string
    {
        $type = $node['type'] ?? 'text';

        if (!isset($this->renderers[$type])) {
            // Unknown node type - try to render children if any
            Log::warning("BlockRenderer: Unknown node type '{$type}'");
            return $this->renderChildren($node);
        }

        $renderer = $this->renderers[$type];

        if (is_callable($renderer)) {
            return $renderer($node);
        }

        if (is_object($renderer) && method_exists($renderer, 'render')) {
            return $renderer->render($node, $this->variables, $this->context, $this->documentContext);
        }

        return '';
    }

    /**
     * Render all children of a node.
     */
    public function renderChildren(array $node): string
    {
        if (empty($node['content'])) {
            return '';
        }

        $html = '';
        foreach ($node['content'] as $child) {
            if (is_array($child)) {
                $html .= $this->renderNode($child);
            }
        }
        return $html;
    }

    /**
     * Render a text node with marks (bold, italic, etc.).
     */
    protected function renderText(array $node): string
    {
        $text = htmlspecialchars($node['text'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (empty($node['marks'])) {
            return $text;
        }

        // Apply marks in reverse order (innermost first)
        foreach (array_reverse($node['marks']) as $mark) {
            $text = $this->applyMark($text, $mark);
        }

        return $text;
    }

    /**
     * Apply a mark to text.
     */
    protected function applyMark(string $text, array $mark): string
    {
        $type = $mark['type'] ?? '';

        return match ($type) {
            'bold' => "<strong>{$text}</strong>",
            'italic' => "<em>{$text}</em>",
            'underline' => "<u>{$text}</u>",
            'strike' => "<s>{$text}</s>",
            'code' => "<code>{$text}</code>",
            'link' => $this->renderLink($text, $mark['attrs'] ?? []),
            'textStyle' => $this->applyTextStyle($text, $mark['attrs'] ?? []),
            'highlight' => $this->applyHighlight($text, $mark['attrs'] ?? []),
            default => $text,
        };
    }

    /**
     * Render a link.
     */
    protected function renderLink(string $text, array $attrs): string
    {
        $href = htmlspecialchars($attrs['href'] ?? '#', ENT_QUOTES);
        $target = isset($attrs['target']) ? ' target="' . htmlspecialchars($attrs['target']) . '"' : '';
        return "<a href=\"{$href}\"{$target}>{$text}</a>";
    }

    /**
     * Apply text style (color, font size, etc.).
     */
    protected function applyTextStyle(string $text, array $attrs): string
    {
        $styles = [];

        if (!empty($attrs['color'])) {
            $styles[] = 'color: ' . htmlspecialchars($attrs['color']);
        }
        if (!empty($attrs['fontSize'])) {
            $styles[] = 'font-size: ' . htmlspecialchars($attrs['fontSize']);
        }

        if (empty($styles)) {
            return $text;
        }

        return '<span style="' . implode('; ', $styles) . '">' . $text . '</span>';
    }

    /**
     * Apply highlight (background color).
     */
    protected function applyHighlight(string $text, array $attrs): string
    {
        $color = $attrs['color'] ?? '#ffff00';
        return '<span style="background-color: ' . htmlspecialchars($color) . '">' . $text . '</span>';
    }

    /**
     * Render a list.
     */
    protected function renderList(array $node, string $tag): string
    {
        $style = $this->context === self::CONTEXT_PDF
            ? ' style="margin-left: 20px; padding-left: 0;"'
            : '';
        return "<{$tag}{$style}>" . $this->renderChildren($node) . "</{$tag}>";
    }

    /**
     * Render a table.
     */
    protected function renderTable(array $node): string
    {
        $style = $this->context === self::CONTEXT_PDF
            ? ' style="width: 100%; border-collapse: collapse; margin: 16px 0;"'
            : ' class="table"';
        return "<table{$style}>" . $this->renderChildren($node) . "</table>";
    }

    /**
     * Render a table cell.
     */
    protected function renderTableCell(array $node, string $tag): string
    {
        $attrs = $node['attrs'] ?? [];
        $style = [];

        if ($this->context === self::CONTEXT_PDF) {
            $style[] = 'border: 1px solid #cbd5e1';
            $style[] = 'padding: 8px 12px';
            if ($tag === 'th') {
                $style[] = 'background-color: #f1f5f9';
                $style[] = 'font-weight: 600';
            }
        }

        if (!empty($attrs['colspan'])) {
            $colspan = ' colspan="' . (int) $attrs['colspan'] . '"';
        } else {
            $colspan = '';
        }

        if (!empty($attrs['rowspan'])) {
            $rowspan = ' rowspan="' . (int) $attrs['rowspan'] . '"';
        } else {
            $rowspan = '';
        }

        $styleAttr = !empty($style) ? ' style="' . implode('; ', $style) . '"' : '';

        return "<{$tag}{$colspan}{$rowspan}{$styleAttr}>" . $this->renderChildren($node) . "</{$tag}>";
    }

    /**
     * Render an image.
     */
    protected function renderImage(array $node): string
    {
        $attrs = $node['attrs'] ?? [];
        $src = htmlspecialchars($attrs['src'] ?? '', ENT_QUOTES);
        $alt = htmlspecialchars($attrs['alt'] ?? '', ENT_QUOTES);
        $title = isset($attrs['title']) ? ' title="' . htmlspecialchars($attrs['title']) . '"' : '';

        $style = '';
        if ($this->context === self::CONTEXT_PDF && !empty($attrs['width'])) {
            $style = ' style="max-width: ' . (int) $attrs['width'] . 'px"';
        }

        return "<img src=\"{$src}\" alt=\"{$alt}\"{$title}{$style}>";
    }

    /**
     * Get plain text content from a node (recursive).
     */
    protected function getTextContent(array $node): string
    {
        if (isset($node['text'])) {
            return $node['text'];
        }

        $text = '';
        foreach ($node['content'] ?? [] as $child) {
            if (is_array($child)) {
                $text .= $this->getTextContent($child);
            }
        }
        return $text;
    }

    /**
     * Get the current rendering context.
     */
    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * Get resolved variables.
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * Get document context.
     */
    public function getDocumentContext(): array
    {
        return $this->documentContext;
    }
}
