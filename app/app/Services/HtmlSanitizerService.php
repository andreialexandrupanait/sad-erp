<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

class HtmlSanitizerService
{
    /**
     * Sanitize HTML content for offers and contracts.
     * Removes dangerous scripts while preserving formatting.
     *
     * @param string|null $html
     * @return string|null
     */
    public function sanitize(?string $html): ?string
    {
        if (empty($html)) {
            return $html;
        }

        // Pre-process: Remove legacy TipTap data-* attributes that HTMLPurifier doesn't understand
        // These were used by the old editor but are no longer needed
        $html = preg_replace('/\s+data-(?:variable|required|fallback|type|label|node-type|id)="[^"]*"/i', '', $html);
        $html = preg_replace('/\s+contenteditable="[^"]*"/i', '', $html);

        // Create HTMLPurifier config directly for better control
        $config = HTMLPurifier_Config::createDefault();

        // Basic settings
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');

        // Allow specific elements and attributes
        $config->set('HTML.Allowed', implode(',', [
            // Headings
            'h1[style|class]', 'h2[style|class]', 'h3[style|class]', 'h4[style|class]', 'h5[style|class]', 'h6[style|class]',

            // Paragraphs and breaks
            'p[style|class]', 'br', 'hr',

            // Text formatting
            'b', 'strong', 'i', 'em', 'u', 's', 'strike', 'sub', 'sup', 'small',

            // Lists
            'ul[style|class]', 'ol[style|class]', 'li[style|class]', 'dl', 'dt', 'dd',

            // Links
            'a[href|title|target]',

            // Tables
            'table[border|cellpadding|cellspacing|width|style|class]',
            'thead', 'tbody', 'tfoot',
            'tr[style|class]',
            'th[colspan|rowspan|style|class]',
            'td[colspan|rowspan|style|class]',

            // Block elements
            'div[style|class|id]', 'span[style|class|id]', 'blockquote', 'pre', 'code',

            // Images (for company logos in offers/contracts)
            'img[src|alt|title|width|height|style|class]',
        ]));

        // Allow safe protocols for links
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);

        // Allow safe CSS properties
        $config->set('CSS.AllowedProperties', 'color,background-color,font-size,font-weight,font-family,text-align,text-decoration,margin,margin-top,margin-bottom,margin-left,margin-right,padding,padding-top,padding-bottom,padding-left,padding-right,border,border-collapse,width,height,line-height');

        // Create purifier and clean
        $purifier = new HTMLPurifier($config);

        return $purifier->purify($html);
    }

    /**
     * Sanitize HTML for display in public views.
     * More restrictive than regular sanitization.
     *
     * @param string|null $html
     * @return string|null
     */
    public function sanitizeForPublic(?string $html): ?string
    {
        if (empty($html)) {
            return $html;
        }

        // More restrictive configuration for public views
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('HTML.Allowed', implode(',', [
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'p', 'br', 'hr',
            'b', 'strong', 'i', 'em', 'u',
            'ul', 'ol', 'li',
            'table', 'thead', 'tbody', 'tr', 'th', 'td',
            'div', 'span',
        ]));

        $purifier = new HTMLPurifier($config);

        return $purifier->purify($html);
    }

    /**
     * Strip all HTML tags (for plain text extraction).
     *
     * @param string|null $html
     * @return string|null
     */
    public function stripAllTags(?string $html): ?string
    {
        if (empty($html)) {
            return $html;
        }

        return strip_tags($html);
    }

    /**
     * Check if HTML contains potentially dangerous content.
     *
     * @param string|null $html
     * @return bool
     */
    public function containsDangerousContent(?string $html): bool
    {
        if (empty($html)) {
            return false;
        }

        $dangerousPatterns = [
            '/<script/i',
            '/javascript:/i',
            '/on\w+\s*=/i',  // onclick, onload, etc.
            '/<iframe/i',
            '/<object/i',
            '/<embed/i',
            '/<applet/i',
            '/vbscript:/i',
            '/data:text\/html/i',
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $html)) {
                return true;
            }
        }

        return false;
    }
}
