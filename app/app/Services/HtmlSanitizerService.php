<?php

namespace App\Services;

use Mews\Purifier\Facades\Purifier;

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

        // Configuration for HTMLPurifier
        $config = [
            'HTML.Allowed' => implode(',', [
                // Headings
                'h1', 'h2', 'h3', 'h4', 'h5', 'h6',

                // Paragraphs and breaks
                'p', 'br', 'hr',

                // Text formatting
                'b', 'strong', 'i', 'em', 'u', 's', 'strike', 'sub', 'sup', 'small',

                // Lists
                'ul', 'ol', 'li', 'dl', 'dt', 'dd',

                // Links
                'a[href|title|target]',

                // Tables
                'table[border|cellpadding|cellspacing|width]',
                'thead', 'tbody', 'tfoot', 'tr', 'th[colspan|rowspan]', 'td[colspan|rowspan]',

                // Block elements
                'div[class|id]', 'span[class|id]', 'blockquote', 'pre', 'code',

                // Images (for company logos in offers/contracts)
                'img[src|alt|title|width|height]',
            ]),

            // Allow specific attributes
            'HTML.AllowedAttributes' => 'href,title,target,src,alt,width,height,class,id,colspan,rowspan,border,cellpadding,cellspacing',

            // Allow safe protocols for links
            'URI.AllowedSchemes' => ['http' => true, 'https' => true, 'mailto' => true],

            // Disable links to javascript
            'URI.DisableExternalResources' => false,
            'URI.DisableResources' => false,

            // Convert relative URLs to absolute (optional)
            'URI.MakeAbsolute' => false,

            // Character encoding
            'Core.Encoding' => 'UTF-8',

            // Allow safe CSS for styling
            'CSS.AllowedProperties' => 'color,background-color,font-size,font-weight,text-align,margin,padding',
        ];

        return Purifier::clean($html, $config);
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
        $config = [
            'HTML.Allowed' => implode(',', [
                'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
                'p', 'br', 'hr',
                'b', 'strong', 'i', 'em', 'u',
                'ul', 'ol', 'li',
                'table', 'thead', 'tbody', 'tr', 'th', 'td',
                'div', 'span',
            ]),

            'HTML.AllowedAttributes' => '',  // No attributes allowed in public view
            'URI.AllowedSchemes' => [],      // No links allowed
            'Core.Encoding' => 'UTF-8',
        ];

        return Purifier::clean($html, $config);
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
