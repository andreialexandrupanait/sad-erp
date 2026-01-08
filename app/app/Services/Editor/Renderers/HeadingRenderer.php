<?php

namespace App\Services\Editor\Renderers;

use App\Services\Editor\BlockRenderer;

class HeadingRenderer implements BlockRendererInterface
{
    public function render(array $node, array $variables, string $context, array $documentContext): string
    {
        $attrs = $node['attrs'] ?? [];
        $level = min(max((int) ($attrs['level'] ?? 1), 1), 6);
        $tag = "h{$level}";

        $style = [];
        $attrStr = '';

        // Handle text alignment
        if (!empty($attrs['textAlign'])) {
            $style[] = 'text-align: ' . htmlspecialchars($attrs['textAlign']);
        }

        // PDF-specific styling
        if ($context === BlockRenderer::CONTEXT_PDF) {
            $style[] = match ($level) {
                1 => 'font-size: 1.75rem; font-weight: 700; margin-bottom: 1rem; color: #1e293b',
                2 => 'font-size: 1.5rem; font-weight: 600; margin-bottom: 0.75rem; color: #334155',
                3 => 'font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem; color: #475569',
                default => 'font-weight: 600; margin-bottom: 0.5rem',
            };
        }

        if (!empty($style)) {
            $attrStr = ' style="' . implode('; ', $style) . '"';
        }

        $content = $this->renderContent($node, $variables);

        return "<{$tag}{$attrStr}>{$content}</{$tag}>";
    }

    protected function renderContent(array $node, array $variables): string
    {
        if (empty($node['content'])) {
            return '';
        }

        $html = '';
        foreach ($node['content'] as $child) {
            if (!is_array($child)) {
                continue;
            }

            $type = $child['type'] ?? 'text';

            if ($type === 'text') {
                $html .= $this->renderText($child);
            } elseif ($type === 'variable') {
                $varName = $child['attrs']['name'] ?? '';
                $html .= $variables[$varName] ?? "{{$varName}}";
            }
        }

        return $html;
    }

    protected function renderText(array $node): string
    {
        $text = htmlspecialchars($node['text'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (empty($node['marks'])) {
            return $text;
        }

        foreach (array_reverse($node['marks']) as $mark) {
            $type = $mark['type'] ?? '';
            $text = match ($type) {
                'bold' => "<strong>{$text}</strong>",
                'italic' => "<em>{$text}</em>",
                'underline' => "<u>{$text}</u>",
                default => $text,
            };
        }

        return $text;
    }
}
