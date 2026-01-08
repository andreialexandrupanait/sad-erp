<?php

namespace App\Services\Editor\Renderers;

use App\Services\Editor\BlockRenderer;

class ParagraphRenderer implements BlockRendererInterface
{
    public function render(array $node, array $variables, string $context, array $documentContext): string
    {
        $attrs = $node['attrs'] ?? [];
        $style = [];
        $class = [];

        // Handle text alignment
        if (!empty($attrs['textAlign'])) {
            $style[] = 'text-align: ' . htmlspecialchars($attrs['textAlign']);
        }

        // Handle indent
        if (!empty($attrs['indent'])) {
            $indent = (int) $attrs['indent'];
            $style[] = "margin-left: {$indent}em";
        }

        // Build attributes
        $attrStr = '';
        if (!empty($style)) {
            $attrStr .= ' style="' . implode('; ', $style) . '"';
        }
        if (!empty($class)) {
            $attrStr .= ' class="' . implode(' ', $class) . '"';
        }

        // Render content
        $content = $this->renderContent($node, $variables);

        // Empty paragraph handling
        if (empty(trim(strip_tags($content)))) {
            $content = '&nbsp;';
        }

        return "<p{$attrStr}>{$content}</p>";
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
            } elseif ($type === 'hardBreak') {
                $html .= '<br>';
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
            $text = $this->applyMark($text, $mark);
        }

        return $text;
    }

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
            default => $text,
        };
    }

    protected function renderLink(string $text, array $attrs): string
    {
        $href = htmlspecialchars($attrs['href'] ?? '#', ENT_QUOTES);
        return "<a href=\"{$href}\">{$text}</a>";
    }

    protected function applyTextStyle(string $text, array $attrs): string
    {
        $styles = [];
        if (!empty($attrs['color'])) {
            $styles[] = 'color: ' . htmlspecialchars($attrs['color']);
        }
        if (empty($styles)) {
            return $text;
        }
        return '<span style="' . implode('; ', $styles) . '">' . $text . '</span>';
    }
}
