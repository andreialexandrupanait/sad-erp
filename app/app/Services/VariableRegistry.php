<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Offer;
use App\Models\Organization;
use App\Services\Context\PartyContextFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * VariableRegistry - UNIFIED Single Source of Truth for ALL document variables.
 *
 * This registry:
 * - Defines all available variables with their labels, types, and requirements
 * - Supports contracts, offers, annexes, and email templates
 * - Provides consistent {{variable}} format everywhere
 * - Validates content for missing/unknown variables
 * - Renders content with all variables replaced
 * - Ensures XSS prevention via HTML escaping
 *
 * @see /home/andrei/.claude/plans/purrfect-stargazing-rainbow.md for architecture
 */
class VariableRegistry
{
    /**
     * Document types supported by the registry.
     */
    public const TYPE_CONTRACT = 'contract';
    public const TYPE_OFFER = 'offer';
    public const TYPE_ANNEX = 'annex';
    public const TYPE_EMAIL = 'email';

    /**
     * Variable format pattern for matching {{variable_name}}
     */
    public const FORMAT_PATTERN = '/\{\{([a-z_]+)\}\}/';
    public const FORMAT_PREFIX = '{{';
    public const FORMAT_SUFFIX = '}}';

    /**
     * Variables that output raw HTML (not escaped).
     */
    protected const BLOCK_VARIABLES = [
        'services_list',
        'offer_services_list', // Alias for backwards compatibility
    ];

    /**
     * Get all variable definitions grouped by category.
     * This is the SINGLE SOURCE OF TRUTH for all template variables.
     *
     * @return array<string, array<string, array>>
     */
    public static function getDefinitions(): array
    {
        return [
            'client' => [
                'client_company_name' => [
                    'label' => 'Denumire firmă client',
                    'label_en' => 'Client Company Name',
                    'required' => true,
                    'type' => 'string',
                    'applies_to' => [self::TYPE_CONTRACT, self::TYPE_OFFER, self::TYPE_ANNEX, self::TYPE_EMAIL],
                ],
                'client_address' => [
                    'label' => 'Adresa client',
                    'label_en' => 'Client Address',
                    'required' => false,
                    'type' => 'string',
                    'applies_to' => [self::TYPE_CONTRACT, self::TYPE_OFFER, self::TYPE_ANNEX, self::TYPE_EMAIL],
                ],
                'client_trade_register_number' => [
                    'label' => 'Nr. Reg. Com. client',
                    'label_en' => 'Client Trade Register Number',
                    'required' => false,
                    'type' => 'string',
                    'applies_to' => [self::TYPE_CONTRACT, self::TYPE_OFFER, self::TYPE_ANNEX],
                ],
                'client_tax_id' => [
                    'label' => 'CUI client',
                    'label_en' => 'Client Tax ID (CUI)',
                    'required' => false,
                    'type' => 'string',
                    'applies_to' => [self::TYPE_CONTRACT, self::TYPE_OFFER, self::TYPE_ANNEX],
                ],
                'client_bank_account' => [
                    'label' => 'Cont bancar client',
                    'label_en' => 'Client Bank Account',
                    'required' => false,
                    'type' => 'string',
                    'applies_to' => [self::TYPE_CONTRACT, self::TYPE_ANNEX],
                ],
                'client_representative' => [
                    'label' => 'Reprezentant client',
                    'label_en' => 'Client Representative',
                    'required' => false,
                    'type' => 'string',
                    'applies_to' => [self::TYPE_CONTRACT, self::TYPE_OFFER, self::TYPE_ANNEX, self::TYPE_EMAIL],
                ],
                'client_email' => [
                    'label' => 'Email client',
                    'label_en' => 'Client Email',
                    'required' => false,
                    'type' => 'string',
                    'applies_to' => [self::TYPE_CONTRACT, self::TYPE_OFFER, self::TYPE_ANNEX, self::TYPE_EMAIL],
                ],
                'client_phone' => [
                    'label' => 'Telefon client',
                    'label_en' => 'Client Phone',
                    'required' => false,
                    'type' => 'string',
                    'applies_to' => [self::TYPE_CONTRACT, self::TYPE_OFFER, self::TYPE_ANNEX, self::TYPE_EMAIL],
                ],
            ],
            'contract' => [
                'contract_number' => [
                    'label' => 'Număr contract',
                    'label_en' => 'Contract Number',
                    'required' => true,
                    'type' => 'string',
                    'applies_to' => [self::TYPE_CONTRACT, self::TYPE_ANNEX],
                ],
                'contract_date' => [
                    'label' => 'Data contract',
                    'label_en' => 'Contract Date',
                    'required' => true,
                    'type' => 'date',
                    'applies_to' => [self::TYPE_CONTRACT, self::TYPE_ANNEX],
                ],
                'contract_start_date' => [
                    'label' => 'Data început',
                    'label_en' => 'Start Date',
                    'required' => false,
                    'type' => 'date',
                    'applies_to' => [self::TYPE_CONTRACT, self::TYPE_ANNEX],
                ],
                'contract_end_date' => [
                    'label' => 'Data sfârșit',
                    'label_en' => 'End Date',
                    'required' => false,
                    'type' => 'date',
                    'applies_to' => [self::TYPE_CONTRACT, self::TYPE_ANNEX],
                ],
                'contract_total' => [
                    'label' => 'Valoare totală contract',
                    'label_en' => 'Contract Total',
                    'required' => true,
                    'type' => 'currency',
                    'applies_to' => [self::TYPE_CONTRACT, self::TYPE_ANNEX],
                ],
                'contract_currency' => [
                    'label' => 'Monedă',
                    'label_en' => 'Currency',
                    'required' => true,
                    'type' => 'string',
                    'applies_to' => [self::TYPE_CONTRACT, self::TYPE_ANNEX],
                ],
                'contract_title' => [
                    'label' => 'Titlu contract',
                    'label_en' => 'Contract Title',
                    'required' => false,
                    'type' => 'string',
                    'applies_to' => [self::TYPE_CONTRACT, self::TYPE_ANNEX],
                ],
            ],
            'offer' => [
                'offer_number' => [
                    'label' => 'Număr ofertă',
                    'label_en' => 'Offer Number',
                    'required' => true,
                    'type' => 'string',
                    'applies_to' => [self::TYPE_OFFER],
                ],
                'offer_date' => [
                    'label' => 'Data ofertă',
                    'label_en' => 'Offer Date',
                    'required' => true,
                    'type' => 'date',
                    'applies_to' => [self::TYPE_OFFER],
                ],
                'offer_valid_until' => [
                    'label' => 'Valabilă până la',
                    'label_en' => 'Valid Until',
                    'required' => false,
                    'type' => 'date',
                    'applies_to' => [self::TYPE_OFFER],
                ],
                'offer_title' => [
                    'label' => 'Titlu ofertă',
                    'label_en' => 'Offer Title',
                    'required' => false,
                    'type' => 'string',
                    'applies_to' => [self::TYPE_OFFER],
                ],
                'offer_subtotal' => [
                    'label' => 'Subtotal ofertă',
                    'label_en' => 'Offer Subtotal',
                    'required' => false,
                    'type' => 'currency',
                    'applies_to' => [self::TYPE_OFFER],
                ],
                'offer_discount' => [
                    'label' => 'Discount ofertă',
                    'label_en' => 'Offer Discount',
                    'required' => false,
                    'type' => 'currency',
                    'applies_to' => [self::TYPE_OFFER],
                ],
                'offer_total' => [
                    'label' => 'Total ofertă',
                    'label_en' => 'Offer Total',
                    'required' => true,
                    'type' => 'currency',
                    'applies_to' => [self::TYPE_OFFER],
                ],
            ],
            'organization' => [
                'org_name' => [
                    'label' => 'Denumire firmă',
                    'label_en' => 'Organization Name',
                    'required' => true,
                    'type' => 'string',
                    'applies_to' => [self::TYPE_CONTRACT, self::TYPE_OFFER, self::TYPE_ANNEX, self::TYPE_EMAIL],
                ],
                'org_address' => [
                    'label' => 'Adresă firmă',
                    'label_en' => 'Organization Address',
                    'required' => false,
                    'type' => 'string',
                    'applies_to' => [self::TYPE_CONTRACT, self::TYPE_OFFER, self::TYPE_ANNEX, self::TYPE_EMAIL],
                ],
                'org_tax_id' => [
                    'label' => 'CUI firmă',
                    'label_en' => 'Organization Tax ID',
                    'required' => false,
                    'type' => 'string',
                    'applies_to' => [self::TYPE_CONTRACT, self::TYPE_OFFER, self::TYPE_ANNEX],
                ],
                'org_trade_register' => [
                    'label' => 'Nr. Reg. Com. firmă',
                    'label_en' => 'Organization Trade Register',
                    'required' => false,
                    'type' => 'string',
                    'applies_to' => [self::TYPE_CONTRACT, self::TYPE_OFFER, self::TYPE_ANNEX],
                ],
                'org_representative' => [
                    'label' => 'Reprezentant legal',
                    'label_en' => 'Legal Representative',
                    'required' => false,
                    'type' => 'string',
                    'applies_to' => [self::TYPE_CONTRACT, self::TYPE_OFFER, self::TYPE_ANNEX],
                ],
                'org_bank_account' => [
                    'label' => 'Cont bancar firmă',
                    'label_en' => 'Organization Bank Account',
                    'required' => false,
                    'type' => 'string',
                    'applies_to' => [self::TYPE_CONTRACT, self::TYPE_ANNEX],
                ],
                'org_email' => [
                    'label' => 'Email firmă',
                    'label_en' => 'Organization Email',
                    'required' => false,
                    'type' => 'string',
                    'applies_to' => [self::TYPE_CONTRACT, self::TYPE_OFFER, self::TYPE_ANNEX, self::TYPE_EMAIL],
                ],
                'org_phone' => [
                    'label' => 'Telefon firmă',
                    'label_en' => 'Organization Phone',
                    'required' => false,
                    'type' => 'string',
                    'applies_to' => [self::TYPE_CONTRACT, self::TYPE_OFFER, self::TYPE_ANNEX, self::TYPE_EMAIL],
                ],
            ],
            'special' => [
                'services_list' => [
                    'label' => 'Lista servicii',
                    'label_en' => 'Services List',
                    'required' => false,
                    'type' => 'block',
                    'applies_to' => [self::TYPE_CONTRACT, self::TYPE_OFFER, self::TYPE_ANNEX],
                ],
                'offer_services_list' => [
                    'label' => 'Lista servicii (din ofertă)',
                    'label_en' => 'Services List (from Offer)',
                    'required' => false,
                    'type' => 'block',
                    'applies_to' => [self::TYPE_CONTRACT, self::TYPE_ANNEX],
                    'alias_of' => 'services_list', // For backwards compatibility
                ],
                'current_date' => [
                    'label' => 'Data curentă',
                    'label_en' => 'Current Date',
                    'required' => false,
                    'type' => 'date',
                    'applies_to' => [self::TYPE_CONTRACT, self::TYPE_OFFER, self::TYPE_ANNEX, self::TYPE_EMAIL],
                ],
            ],
        ];
    }

    // =========================================================================
    // RESOLUTION - Convert variables to actual values
    // =========================================================================

    /**
     * Resolve all variables for a document.
     *
     * SECURITY: All text values are HTML-escaped to prevent XSS attacks.
     * Exception: Block variables (services_list) contain pre-sanitized HTML.
     *
     * @param Contract|Offer $document The document to resolve variables for
     * @return array<string, string> Key-value pairs of resolved variables
     */
    public static function resolve(Contract|Offer $document): array
    {
        if ($document instanceof Contract) {
            return static::resolveForContract($document);
        }

        return static::resolveForOffer($document);
    }

    /**
     * Resolve variables for a contract.
     */
    protected static function resolveForContract(Contract $contract): array
    {
        $client = $contract->client;
        $offer = $contract->offer;
        $org = $contract->organization ?? Organization::find($contract->organization_id);
        $orgSettings = $org?->settings ?? [];

        $servicesList = static::renderServicesList($contract);

        return array_merge(
            static::resolveClientVariables($client, $contract, $offer),
            static::resolveContractVariables($contract),
            static::resolveOrganizationVariables($org, $orgSettings),
            [
                'services_list' => $servicesList,
                'offer_services_list' => $servicesList, // Alias
                'current_date' => static::e(now()->format('d.m.Y')),
            ]
        );
    }

    /**
     * Resolve variables for an offer.
     */
    protected static function resolveForOffer(Offer $offer): array
    {
        $client = $offer->client;
        $org = $offer->organization ?? Organization::find($offer->organization_id);
        $orgSettings = $org?->settings ?? [];

        return array_merge(
            static::resolveClientVariables($client, null, $offer),
            static::resolveOfferVariables($offer),
            static::resolveOrganizationVariables($org, $orgSettings),
            [
                'services_list' => static::renderServicesListForOffer($offer),
                'current_date' => static::e(now()->format('d.m.Y')),
            ]
        );
    }

    /**
     * Helper to escape HTML for XSS prevention.
     */
    protected static function e(?string $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Resolve client-related variables using PartyContext.
     *
     * Uses PartyContextFactory to build a unified context from either:
     * - Existing Client record
     * - Contract temp fields (prospect)
     * - Offer temp fields (prospect)
     *
     * This ensures variables resolve correctly regardless of client state.
     */
    protected static function resolveClientVariables($client, ?Contract $contract, ?Offer $offer): array
    {
        // Build unified context - single source of truth for party data
        $party = PartyContextFactory::resolve($client, $contract, $offer);

        return [
            'client_company_name' => static::e($party->companyName ?? ''),
            'client_address' => static::e($party->address ?? ''),
            'client_trade_register_number' => static::e($party->registrationNumber ?? ''),
            'client_tax_id' => static::e($party->taxId ?? ''),
            'client_bank_account' => static::e($party->bankAccount ?? ''),
            'client_representative' => static::e($party->representative ?? ''),
            'client_email' => static::e($party->email ?? ''),
            'client_phone' => static::e($party->phone ?? ''),
        ];
    }

    /**
     * Resolve contract-related variables.
     */
    protected static function resolveContractVariables(Contract $contract): array
    {
        return [
            'contract_number' => static::e($contract->contract_number ?? ''),
            'contract_date' => static::e($contract->created_at?->format('d.m.Y') ?? ''),
            'contract_start_date' => static::e($contract->start_date?->format('d.m.Y') ?? ''),
            'contract_end_date' => static::e($contract->end_date?->format('d.m.Y') ?? __('Nedeterminat')),
            'contract_total' => static::e(number_format($contract->total_value ?? 0, 2, ',', '.')),
            'contract_currency' => static::e($contract->currency ?? 'EUR'),
            'contract_title' => static::e($contract->title ?? ''),
        ];
    }

    /**
     * Resolve offer-related variables.
     */
    protected static function resolveOfferVariables(Offer $offer): array
    {
        return [
            'offer_number' => static::e($offer->offer_number ?? ''),
            'offer_date' => static::e($offer->created_at?->format('d.m.Y') ?? ''),
            'offer_valid_until' => static::e($offer->valid_until?->format('d.m.Y') ?? ''),
            'offer_title' => static::e($offer->title ?? ''),
            'offer_subtotal' => static::e(number_format($offer->subtotal ?? 0, 2, ',', '.')),
            'offer_discount' => static::e(number_format($offer->discount_amount ?? 0, 2, ',', '.')),
            'offer_total' => static::e(number_format($offer->total ?? 0, 2, ',', '.')),
        ];
    }

    /**
     * Resolve organization-related variables.
     */
    protected static function resolveOrganizationVariables(?Organization $org, array $orgSettings): array
    {
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

        return [
            'org_name' => static::e($org?->name ?? config('app.name')),
            'org_address' => static::e($orgAddress),
            'org_tax_id' => static::e($org?->tax_id ?? ''),
            'org_trade_register' => static::e($orgSettings['trade_registry'] ?? ''),
            'org_representative' => static::e($orgSettings['representative'] ?? ''),
            'org_bank_account' => static::e($primaryBank),
            'org_email' => static::e($org?->email ?? config('mail.from.address')),
            'org_phone' => static::e($org?->phone ?? ''),
        ];
    }

    // =========================================================================
    // SERVICES LIST RENDERING
    // =========================================================================

    /**
     * Render services list for a contract.
     * Returns pre-sanitized HTML (not double-escaped).
     */
    public static function renderServicesList(Contract $contract): string
    {
        $items = $contract->items;

        // Fallback to OfferItems if ContractItems don't exist
        if ($items->isEmpty() && $contract->offer) {
            $offerItems = $contract->offer->items ?? collect();
            $items = $offerItems->filter(fn($item) => $item->is_selected === true)
                ->sortBy([['type', 'desc'], ['sort_order', 'asc']]);
        }

        if ($items->isEmpty()) {
            return '<p><em>' . __('Nu sunt specificate servicii') . '</em></p>';
        }

        return static::formatServicesList($items, $contract->currency ?? 'EUR');
    }

    /**
     * Render services list for an offer.
     * Returns pre-sanitized HTML (not double-escaped).
     */
    public static function renderServicesListForOffer(Offer $offer): string
    {
        $items = ($offer->items ?? collect())
            ->filter(fn($item) => $item->is_selected !== false)
            ->sortBy([['type', 'desc'], ['sort_order', 'asc']]);

        if ($items->isEmpty()) {
            return '<p><em>' . __('Nu sunt specificate servicii') . '</em></p>';
        }

        return static::formatServicesList($items, $offer->currency ?? 'EUR');
    }

    /**
     * Format services as HTML list.
     */
    protected static function formatServicesList($items, string $currency): string
    {
        $total = 0;
        $html = '<ul style="list-style-type: disc; margin-left: 20px; padding-left: 0; margin-top: 0;">';

        foreach ($items as $item) {
            $name = $item->title ?? $item->name ?? $item->description ?? __('Serviciu');
            $itemTotal = (float) ($item->total_price ?? $item->total ?? 0);
            $total += $itemTotal;
            $price = number_format($itemTotal, 2, ',', '.');
            $html .= '<li style="margin-bottom: 4px;"><strong>' . static::e($name) . '</strong> - ' . $price . ' ' . static::e($currency) . '</li>';
        }

        $html .= '</ul>';

        if ($items->count() > 1) {
            $html .= '<p style="margin-top: 10px;"><strong>' . __('Total') . ': ' . number_format($total, 2, ',', '.') . ' ' . static::e($currency) . '</strong></p>';
        }

        return $html;
    }

    // =========================================================================
    // RENDERING - Replace placeholders with values
    // =========================================================================

    /**
     * Replace all variables in content with resolved values.
     *
     * @param string $content The content with {{variable}} placeholders
     * @param Contract|Offer $document The document to resolve variables from
     * @return string Content with all variables replaced
     */
    public static function render(string $content, Contract|Offer $document): string
    {
        if (empty($content)) {
            return '';
        }

        $values = static::resolve($document);
        $knownKeys = static::getAllKeys();

        // Find all variables in content
        preg_match_all(static::FORMAT_PATTERN, $content, $matches);
        $usedVars = array_unique($matches[1] ?? []);

        // Replace known variables, leave unknown ones visible
        foreach ($usedVars as $varKey) {
            $placeholder = static::FORMAT_PREFIX . $varKey . static::FORMAT_SUFFIX;

            if (in_array($varKey, $knownKeys)) {
                // Known variable - replace with value
                $value = $values[$varKey] ?? '';

                // Log warning for required variables with empty values
                if ($value === '' && static::isRequiredVariable($varKey)) {
                    \Log::warning('Variable resolution: required variable is empty', [
                        'variable' => $varKey,
                        'document_type' => $document instanceof Contract ? 'contract' : 'offer',
                        'document_id' => $document->id,
                    ]);
                }

                $content = str_replace($placeholder, (string) $value, $content);
            } else {
                // Unknown variable - leave as-is and log error
                \Log::error('Variable resolution: unknown variable', [
                    'variable' => $varKey,
                    'document_type' => $document instanceof Contract ? 'contract' : 'offer',
                    'document_id' => $document->id,
                ]);
                // Keep placeholder visible: {{unknown_var}} stays as {{unknown_var}}
            }
        }

        return $content;
    }

    /**
     * Check if a variable is required.
     */
    protected static function isRequiredVariable(string $key): bool
    {
        foreach (static::getDefinitions() as $vars) {
            if (isset($vars[$key])) {
                return $vars[$key]['required'] ?? false;
            }
        }
        return false;
    }

    /**
     * Render content from TipTap blocks JSON.
     *
     * @param array $blocks TipTap JSON structure
     * @param Contract|Offer $document The document to resolve variables from
     * @return array Blocks with variables resolved
     */
    public static function renderBlocks(array $blocks, Contract|Offer $document): array
    {
        $values = static::resolve($document);
        return static::replaceVariablesInBlocks($blocks, $values);
    }

    /**
     * Recursively replace variables in block structure.
     */
    protected static function replaceVariablesInBlocks(array $node, array $values): array
    {
        // Handle variable nodes
        if (($node['type'] ?? '') === 'variable') {
            $name = $node['attrs']['name'] ?? '';
            $value = $values[$name] ?? $node['attrs']['fallback'] ?? '';

            // Convert variable node to text node with resolved value
            return [
                'type' => 'text',
                'text' => $value,
            ];
        }

        // Handle text nodes - replace {{variable}} in text content
        if (($node['type'] ?? '') === 'text' && isset($node['text'])) {
            foreach ($values as $key => $value) {
                $placeholder = static::FORMAT_PREFIX . $key . static::FORMAT_SUFFIX;
                $node['text'] = str_replace($placeholder, (string) $value, $node['text']);
            }
            return $node;
        }

        // Recursively process children
        if (isset($node['content']) && is_array($node['content'])) {
            $node['content'] = array_map(
                fn($child) => is_array($child) ? static::replaceVariablesInBlocks($child, $values) : $child,
                $node['content']
            );
        }

        return $node;
    }

    // =========================================================================
    // VALIDATION
    // =========================================================================

    /**
     * Validate content for unknown or missing required variables.
     *
     * @param string $content Content with {{variable}} placeholders
     * @param Contract|Offer $document Document to check values against
     * @return ValidationResult
     */
    public static function validate(string $content, Contract|Offer $document): ValidationResult
    {
        $errors = [];
        $warnings = [];
        $values = static::resolve($document);
        $definitions = static::getFlatDefinitions();

        // Find all variables in content
        preg_match_all(static::FORMAT_PATTERN, $content, $matches);
        $usedVars = array_unique($matches[1] ?? []);

        foreach ($usedVars as $varName) {
            // Check if variable exists
            if (!isset($definitions[$varName])) {
                $errors[] = new ValidationError(
                    'unknown_variable',
                    $varName,
                    __('Variabilă necunoscută: :var', ['var' => $varName])
                );
                continue;
            }

            $definition = $definitions[$varName];

            // Check if required variable has value
            if (($definition['required'] ?? false) && empty($values[$varName])) {
                $errors[] = new ValidationError(
                    'required_empty',
                    $varName,
                    __('Variabila obligatorie :var este goală', ['var' => $definition['label'] ?? $varName])
                );
            }
        }

        return new ValidationResult($errors, $warnings);
    }

    /**
     * Validate blocks (TipTap JSON) for unknown or missing required variables.
     */
    public static function validateBlocks(array $blocks, Contract|Offer $document): ValidationResult
    {
        $errors = [];
        $warnings = [];
        $values = static::resolve($document);
        $usedVars = static::extractVariablesFromBlocks($blocks);
        $definitions = static::getFlatDefinitions();

        foreach ($usedVars as $varName) {
            if (!isset($definitions[$varName])) {
                $errors[] = new ValidationError(
                    'unknown_variable',
                    $varName,
                    __('Variabilă necunoscută: :var', ['var' => $varName])
                );
                continue;
            }

            $definition = $definitions[$varName];

            if (($definition['required'] ?? false) && empty($values[$varName])) {
                $errors[] = new ValidationError(
                    'required_empty',
                    $varName,
                    __('Variabila obligatorie :var este goală', ['var' => $definition['label'] ?? $varName])
                );
            }
        }

        return new ValidationResult($errors, $warnings);
    }

    /**
     * Get warnings (non-blocking issues) for a contract.
     */
    public static function getWarnings(Contract $contract): array
    {
        $warnings = [];

        if (!$contract->client_id && !$contract->temp_client_name) {
            $warnings[] = __('Niciun client asignat contractului');
        }

        if ($contract->items->isEmpty() && (!$contract->offer || $contract->offer->items->isEmpty())) {
            $warnings[] = __('Nu sunt atașate servicii/produse la acest contract');
        }

        if (empty($contract->content)) {
            $warnings[] = __('Contractul nu are conținut. Aplică un șablon.');
        }

        return $warnings;
    }

    /**
     * Extract variable names from blocks (TipTap JSON).
     */
    public static function extractVariablesFromBlocks(array $node): array
    {
        $variables = [];
        static::extractVariablesRecursive($node, $variables);
        return array_unique($variables);
    }

    /**
     * Recursively extract variables from blocks.
     */
    protected static function extractVariablesRecursive(array $node, array &$variables): void
    {
        // Check for variable nodes
        if (($node['type'] ?? '') === 'variable') {
            $name = $node['attrs']['name'] ?? null;
            if ($name) {
                $variables[] = $name;
            }
        }

        // Check for {{variable}} in text
        if (($node['type'] ?? '') === 'text' && isset($node['text'])) {
            preg_match_all(static::FORMAT_PATTERN, $node['text'], $matches);
            if (!empty($matches[1])) {
                $variables = array_merge($variables, $matches[1]);
            }
        }

        // Process children
        foreach ($node['content'] ?? [] as $child) {
            if (is_array($child)) {
                static::extractVariablesRecursive($child, $variables);
            }
        }
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Get flat list of all variable definitions.
     */
    public static function getFlatDefinitions(): array
    {
        $flat = [];
        foreach (static::getDefinitions() as $vars) {
            $flat = array_merge($flat, $vars);
        }
        return $flat;
    }

    /**
     * Get all variable keys.
     */
    public static function getAllKeys(): array
    {
        return array_keys(static::getFlatDefinitions());
    }

    /**
     * Get variables for UI display (for variable picker in editor).
     *
     * @param string|null $documentType Filter by document type
     * @return array Grouped variables with labels
     */
    public static function getForUI(?string $documentType = null): array
    {
        $locale = app()->getLocale();
        $result = [];

        foreach (static::getDefinitions() as $category => $vars) {
            $result[$category] = [];
            foreach ($vars as $key => $config) {
                // Skip aliases in UI
                if (isset($config['alias_of'])) {
                    continue;
                }

                // Filter by document type if specified
                if ($documentType && isset($config['applies_to'])) {
                    if (!in_array($documentType, $config['applies_to'])) {
                        continue;
                    }
                }

                $label = $locale === 'ro'
                    ? ($config['label'] ?? $config['label_en'])
                    : ($config['label_en'] ?? $config['label']);

                $result[$category][$key] = [
                    'label' => __($label),
                    'required' => $config['required'] ?? false,
                    'type' => $config['type'] ?? 'string',
                ];
            }
        }

        // Remove empty categories
        return array_filter($result, fn($vars) => !empty($vars));
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
        $definitions = static::getFlatDefinitions();
        if (!isset($definitions[$key])) {
            return null;
        }

        $config = $definitions[$key];
        $locale = app()->getLocale();

        return $locale === 'ro'
            ? ($config['label'] ?? $config['label_en'])
            : ($config['label_en'] ?? $config['label']);
    }

    /**
     * Check if a variable outputs block (HTML) content.
     */
    public static function isBlockVariable(string $key): bool
    {
        return in_array($key, static::BLOCK_VARIABLES);
    }
}

// =========================================================================
// VALIDATION CLASSES
// =========================================================================

/**
 * Represents a validation error.
 */
class ValidationError
{
    public function __construct(
        public string $type,
        public string $variable,
        public string $message
    ) {}
}

/**
 * Represents the result of validation.
 */
class ValidationResult
{
    public function __construct(
        public array $errors = [],
        public array $warnings = []
    ) {}

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }

    /**
     * Check if content can be rendered (no required_empty errors).
     */
    public function canRender(): bool
    {
        foreach ($this->errors as $error) {
            if ($error->type === 'required_empty') {
                return false;
            }
        }
        return true;
    }

    public function getErrorMessages(): array
    {
        return array_map(fn($e) => $e->message, $this->errors);
    }

    public function getWarningMessages(): array
    {
        return array_map(fn($w) => is_string($w) ? $w : $w->message, $this->warnings);
    }
}
