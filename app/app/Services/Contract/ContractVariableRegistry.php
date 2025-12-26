<?php

namespace App\Services\Contract;

use App\Models\Contract;
use App\Models\Organization;

/**
 * ContractVariableRegistry - Single source of truth for all contract variables.
 *
 * This registry:
 * - Defines all available variables with their labels and resolvers
 * - Provides consistent {{variable}} format everywhere
 * - Validates content for missing/unknown variables
 * - Renders content with all variables replaced
 */
class ContractVariableRegistry
{
    /**
     * Variable format pattern for matching {{variable_name}}
     */
    public const FORMAT_PATTERN = '/\{\{([a-z_]+)\}\}/';

    /**
     * Variable prefix and suffix
     */
    public const FORMAT_PREFIX = '{{';
    public const FORMAT_SUFFIX = '}}';

    /**
     * Get all variable definitions grouped by category.
     * This is the SINGLE SOURCE OF TRUTH for all contract variables.
     */
    public static function getDefinitions(): array
    {
        return [
            'client' => [
                'client_company_name' => [
                    'label' => 'Denumire firmă client',
                    'label_en' => 'Client Company Name',
                    'required' => true,
                ],
                'client_address' => [
                    'label' => 'Adresa client',
                    'label_en' => 'Client Address',
                    'required' => false,
                ],
                'client_trade_register_number' => [
                    'label' => 'Nr. Reg. Com. client',
                    'label_en' => 'Client Trade Register Number',
                    'required' => false,
                ],
                'client_tax_id' => [
                    'label' => 'CUI client',
                    'label_en' => 'Client Tax ID (CUI)',
                    'required' => false,
                ],
                'client_bank_account' => [
                    'label' => 'Cont bancar client',
                    'label_en' => 'Client Bank Account',
                    'required' => false,
                ],
                'client_representative' => [
                    'label' => 'Reprezentant client',
                    'label_en' => 'Client Representative',
                    'required' => false,
                ],
                'client_email' => [
                    'label' => 'Email client',
                    'label_en' => 'Client Email',
                    'required' => false,
                ],
                'client_phone' => [
                    'label' => 'Telefon client',
                    'label_en' => 'Client Phone',
                    'required' => false,
                ],
            ],
            'contract' => [
                'contract_number' => [
                    'label' => 'Număr contract',
                    'label_en' => 'Contract Number',
                    'required' => true,
                ],
                'contract_date' => [
                    'label' => 'Data contract',
                    'label_en' => 'Contract Date',
                    'required' => true,
                ],
                'contract_start_date' => [
                    'label' => 'Data început',
                    'label_en' => 'Start Date',
                    'required' => false,
                ],
                'contract_end_date' => [
                    'label' => 'Data sfârșit',
                    'label_en' => 'End Date',
                    'required' => false,
                ],
                'contract_total' => [
                    'label' => 'Valoare totală contract',
                    'label_en' => 'Contract Total',
                    'required' => true,
                ],
                'contract_currency' => [
                    'label' => 'Monedă',
                    'label_en' => 'Currency',
                    'required' => true,
                ],
                'contract_title' => [
                    'label' => 'Titlu contract',
                    'label_en' => 'Contract Title',
                    'required' => false,
                ],
            ],
            'organization' => [
                'org_name' => [
                    'label' => 'Denumire firmă',
                    'label_en' => 'Organization Name',
                    'required' => true,
                ],
                'org_address' => [
                    'label' => 'Adresă firmă',
                    'label_en' => 'Organization Address',
                    'required' => false,
                ],
                'org_tax_id' => [
                    'label' => 'CUI firmă',
                    'label_en' => 'Organization Tax ID',
                    'required' => false,
                ],
                'org_trade_register' => [
                    'label' => 'Nr. Reg. Com. firmă',
                    'label_en' => 'Organization Trade Register',
                    'required' => false,
                ],
                'org_representative' => [
                    'label' => 'Reprezentant legal',
                    'label_en' => 'Legal Representative',
                    'required' => false,
                ],
                'org_bank_account' => [
                    'label' => 'Cont bancar firmă',
                    'label_en' => 'Organization Bank Account',
                    'required' => false,
                ],
                'org_email' => [
                    'label' => 'Email firmă',
                    'label_en' => 'Organization Email',
                    'required' => false,
                ],
                'org_phone' => [
                    'label' => 'Telefon firmă',
                    'label_en' => 'Organization Phone',
                    'required' => false,
                ],
            ],
            'special' => [
                'offer_services_list' => [
                    'label' => 'Lista servicii (din ofertă)',
                    'label_en' => 'Services List (from Offer)',
                    'required' => false,
                    'type' => 'block',
                ],
                'current_date' => [
                    'label' => 'Data curentă',
                    'label_en' => 'Current Date',
                    'required' => false,
                ],
            ],
        ];
    }

    /**
     * Resolve all variables for a contract.
     * Returns array of [variable_key => resolved_value]
     *
     * SECURITY: All text values are HTML-escaped to prevent XSS attacks
     * when variables are replaced in contract content. The only exception
     * is 'offer_services_list' which contains pre-sanitized HTML.
     */
    public static function resolve(Contract $contract): array
    {
        $client = $contract->client;
        $offer = $contract->offer;
        $org = $contract->organization ?? Organization::find($contract->organization_id);
        $orgSettings = $org?->settings ?? [];

        // Build full organization address
        $orgAddress = $org?->address ?? '';
        if (!empty($orgSettings['city'])) {
            $orgAddress .= ($orgAddress ? ', ' : '') . $orgSettings['city'];
        }
        if (!empty($orgSettings['county'])) {
            $orgAddress .= ($orgAddress ? ', ' : '') . $orgSettings['county'];
        }

        // Get primary bank account
        $bankAccounts = $orgSettings['bank_accounts'] ?? [];
        $primaryBank = !empty($bankAccounts) ? ($bankAccounts[0]['iban'] ?? '') : '';
        if (!empty($bankAccounts[0]['bank'])) {
            $primaryBank .= ' - ' . $bankAccounts[0]['bank'];
        }

        // Helper function to safely escape values for HTML output
        $e = fn($value) => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return [
            // Client variables (all escaped for XSS prevention)
            'client_company_name' => $e($client?->company_name ?? $client?->name ?? $contract->temp_client_company ?? $contract->temp_client_name ?? $offer?->temp_client_company ?? $offer?->temp_client_name ?? ''),
            'client_address' => $e($client?->address ?? $offer?->temp_client_address ?? ''),
            'client_trade_register_number' => $e($client?->registration_number ?? $offer?->temp_client_registration_number ?? ''),
            'client_tax_id' => $e($client?->tax_id ?? $offer?->temp_client_tax_id ?? ''),
            'client_bank_account' => '', // Client bank account not stored yet
            'client_representative' => $e($client?->contact_person ?? $offer?->temp_client_name ?? ''),
            'client_email' => $e($client?->email ?? $contract->temp_client_email ?? $offer?->temp_client_email ?? ''),
            'client_phone' => $e($client?->phone ?? $offer?->temp_client_phone ?? ''),

            // Contract variables (all escaped for XSS prevention)
            'contract_number' => $e($contract->contract_number ?? ''),
            'contract_date' => $e($contract->created_at?->format('d.m.Y') ?? ''),
            'contract_start_date' => $e($contract->start_date?->format('d.m.Y') ?? ''),
            'contract_end_date' => $e($contract->end_date?->format('d.m.Y') ?? __('Nedeterminat')),
            'contract_total' => $e(number_format($contract->total_value ?? 0, 2, ',', '.')),
            'contract_currency' => $e($contract->currency ?? 'EUR'),
            'contract_title' => $e($contract->title ?? ''),

            // Organization variables (all escaped for XSS prevention)
            'org_name' => $e($org?->name ?? config('app.name')),
            'org_address' => $e($orgAddress),
            'org_tax_id' => $e($org?->tax_id ?? ''),
            'org_trade_register' => $e($orgSettings['trade_registry'] ?? ''),
            'org_representative' => $e($orgSettings['representative'] ?? ''),
            'org_bank_account' => $e($primaryBank),
            'org_email' => $e($org?->email ?? config('mail.from.address')),
            'org_phone' => $e($org?->phone ?? ''),

            // Special variables
            // NOTE: offer_services_list contains pre-sanitized HTML (uses e() internally)
            'offer_services_list' => static::renderServicesList($contract),
            'current_date' => $e(now()->format('d.m.Y')),
        ];
    }

    /**
     * Render services as bullet list from ContractItems (or fallback to OfferItems).
     * Uses bold styling for professional document output.
     * Only includes SELECTED items from offers.
     */
    public static function renderServicesList(Contract $contract): string
    {
        // Prefer ContractItems (self-contained)
        $items = $contract->items;

        // Fallback to OfferItems if ContractItems don't exist
        // IMPORTANT: Only include items where is_selected = true
        if ($items->isEmpty() && $contract->offer) {
            $offerItems = $contract->offer->items ?? collect();
            // Filter to only selected items
            $items = $offerItems->filter(function ($item) {
                return $item->is_selected === true;
            });
        }

        if ($items->isEmpty()) {
            return '<p><em>' . __('Nu sunt specificate servicii') . '</em></p>';
        }

        $currency = e($contract->currency ?? 'EUR');
        $total = 0;

        // Build as bullet list with bold styling (no blue - for clean PDF export)
        // Use inline styling to ensure bullets render correctly in PDF
        $html = '<ul style="list-style-type: disc; margin-left: 20px; padding-left: 0;">';
        foreach ($items as $item) {
            $name = $item->title ?? $item->name ?? $item->description ?? __('Serviciu');
            $itemTotal = (float) ($item->total_price ?? $item->total ?? 0);
            $total += $itemTotal;
            $price = number_format($itemTotal, 2, ',', '.');
            $html .= '<li style="margin-bottom: 4px;"><strong>' . e($name) . '</strong> - ' . $price . ' ' . $currency . '</li>';
        }
        $html .= '</ul>';

        // Add total if there are multiple items
        if ($items->count() > 1) {
            $html .= '<p style="margin-top: 10px;"><strong>' . __('Total') . ': ' . number_format($total, 2, ',', '.') . ' ' . $currency . '</strong></p>';
        }

        return $html;
    }

    /**
     * Get variables for UI display (grouped with labels).
     */
    public static function getForUI(): array
    {
        $locale = app()->getLocale();
        $result = [];

        foreach (static::getDefinitions() as $category => $vars) {
            $result[$category] = [];
            foreach ($vars as $key => $config) {
                // Use localized label or fallback to English
                $label = $locale === 'ro' ? ($config['label'] ?? $config['label_en']) : ($config['label_en'] ?? $config['label']);
                $result[$category][$key] = __($label);
            }
        }

        return $result;
    }

    /**
     * Get flat list of all variable keys.
     */
    public static function getAllKeys(): array
    {
        $keys = [];
        foreach (static::getDefinitions() as $vars) {
            $keys = array_merge($keys, array_keys($vars));
        }
        return $keys;
    }

    /**
     * Validate content for missing required variables and unknown variables.
     *
     * @return array Array of errors with 'variable' and 'message' keys
     */
    public static function validateContent(string $content, Contract $contract): array
    {
        $errors = [];
        $warnings = [];
        $values = static::resolve($contract);
        $definitions = static::getDefinitions();
        $knownKeys = static::getAllKeys();

        // Find all variables in content
        preg_match_all(static::FORMAT_PATTERN, $content, $matches);
        $usedVars = $matches[1] ?? [];

        // Check for unknown variables in content
        foreach ($usedVars as $var) {
            if (!in_array($var, $knownKeys)) {
                $errors[] = [
                    'variable' => $var,
                    'type' => 'unknown',
                    'message' => __('Variabilă necunoscută: :var', ['var' => $var]),
                ];
            }
        }

        // Check required variables that are used but have empty values
        foreach ($definitions as $category => $vars) {
            foreach ($vars as $key => $config) {
                // Only check if variable is used in content
                if (in_array($key, $usedVars)) {
                    if ($config['required'] && empty($values[$key])) {
                        $errors[] = [
                            'variable' => $key,
                            'type' => 'required_empty',
                            'message' => __('Variabila obligatorie :var este goală', ['var' => $config['label'] ?? $key]),
                        ];
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Get warnings (non-blocking issues) for a contract.
     */
    public static function getWarnings(Contract $contract): array
    {
        $warnings = [];

        // No client assigned
        if (!$contract->client_id && !$contract->temp_client_name) {
            $warnings[] = __('Niciun client asignat contractului');
        }

        // No services/items
        if ($contract->items->isEmpty() && (!$contract->offer || $contract->offer->items->isEmpty())) {
            $warnings[] = __('Nu sunt atașate servicii/produse la acest contract');
        }

        // No content
        if (empty($contract->content)) {
            $warnings[] = __('Contractul nu are conținut. Aplică un șablon.');
        }

        return $warnings;
    }

    /**
     * Replace all variables in content with resolved values.
     */
    public static function render(string $content, Contract $contract): string
    {
        if (empty($content)) {
            return '';
        }

        $values = static::resolve($contract);

        foreach ($values as $key => $value) {
            $placeholder = static::FORMAT_PREFIX . $key . static::FORMAT_SUFFIX;
            $content = str_replace($placeholder, (string) $value, $content);
        }

        return $content;
    }

    /**
     * Format a variable key for display/insertion.
     */
    public static function formatVariable(string $key): string
    {
        return static::FORMAT_PREFIX . $key . static::FORMAT_SUFFIX;
    }

    /**
     * Check if a string is a valid variable key.
     */
    public static function isValidKey(string $key): bool
    {
        return in_array($key, static::getAllKeys());
    }

    /**
     * Get the label for a variable key.
     */
    public static function getLabel(string $key): ?string
    {
        foreach (static::getDefinitions() as $vars) {
            if (isset($vars[$key])) {
                $locale = app()->getLocale();
                return $locale === 'ro'
                    ? ($vars[$key]['label'] ?? $vars[$key]['label_en'])
                    : ($vars[$key]['label_en'] ?? $vars[$key]['label']);
            }
        }
        return null;
    }
}
