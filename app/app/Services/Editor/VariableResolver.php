<?php

namespace App\Services\Editor;

use App\Models\Contract;
use App\Models\Offer;
use App\Models\Organization;
use Illuminate\Support\Facades\Log;

/**
 * VariableResolver - Unified variable resolution for all document types.
 *
 * This service:
 * - Defines all available variables with their types and requirements
 * - Resolves variables to actual values for any document type
 * - Validates content for missing/unknown variables
 * - Prevents rendering if required variables are missing
 */
class VariableResolver
{
    /**
     * Variable format pattern for matching {{variable_name}}
     */
    public const FORMAT_PATTERN = '/\{\{([a-z_]+)\}\}/';
    public const FORMAT_PREFIX = '{{';
    public const FORMAT_SUFFIX = '}}';

    /**
     * Get all variable definitions grouped by category.
     * This is the SINGLE SOURCE OF TRUTH for all template variables.
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
                ],
                'client_address' => [
                    'label' => 'Adresa client',
                    'label_en' => 'Client Address',
                    'required' => false,
                    'type' => 'string',
                ],
                'client_trade_register_number' => [
                    'label' => 'Nr. Reg. Com. client',
                    'label_en' => 'Client Trade Register Number',
                    'required' => false,
                    'type' => 'string',
                ],
                'client_tax_id' => [
                    'label' => 'CUI client',
                    'label_en' => 'Client Tax ID (CUI)',
                    'required' => false,
                    'type' => 'string',
                ],
                'client_bank_account' => [
                    'label' => 'Cont bancar client',
                    'label_en' => 'Client Bank Account',
                    'required' => false,
                    'type' => 'string',
                ],
                'client_representative' => [
                    'label' => 'Reprezentant client',
                    'label_en' => 'Client Representative',
                    'required' => false,
                    'type' => 'string',
                ],
                'client_email' => [
                    'label' => 'Email client',
                    'label_en' => 'Client Email',
                    'required' => false,
                    'type' => 'string',
                ],
                'client_phone' => [
                    'label' => 'Telefon client',
                    'label_en' => 'Client Phone',
                    'required' => false,
                    'type' => 'string',
                ],
            ],
            'contract' => [
                'contract_number' => [
                    'label' => 'Număr contract',
                    'label_en' => 'Contract Number',
                    'required' => true,
                    'type' => 'string',
                    'applies_to' => ['contract', 'annex'],
                ],
                'contract_date' => [
                    'label' => 'Data contract',
                    'label_en' => 'Contract Date',
                    'required' => true,
                    'type' => 'date',
                    'applies_to' => ['contract', 'annex'],
                ],
                'contract_start_date' => [
                    'label' => 'Data început',
                    'label_en' => 'Start Date',
                    'required' => false,
                    'type' => 'date',
                    'applies_to' => ['contract', 'annex'],
                ],
                'contract_end_date' => [
                    'label' => 'Data sfârșit',
                    'label_en' => 'End Date',
                    'required' => false,
                    'type' => 'date',
                    'applies_to' => ['contract', 'annex'],
                ],
                'contract_total' => [
                    'label' => 'Valoare totală contract',
                    'label_en' => 'Contract Total',
                    'required' => true,
                    'type' => 'currency',
                    'applies_to' => ['contract', 'annex'],
                ],
                'contract_currency' => [
                    'label' => 'Monedă',
                    'label_en' => 'Currency',
                    'required' => true,
                    'type' => 'string',
                    'applies_to' => ['contract', 'annex'],
                ],
                'contract_title' => [
                    'label' => 'Titlu contract',
                    'label_en' => 'Contract Title',
                    'required' => false,
                    'type' => 'string',
                    'applies_to' => ['contract', 'annex'],
                ],
            ],
            'offer' => [
                'offer_number' => [
                    'label' => 'Număr ofertă',
                    'label_en' => 'Offer Number',
                    'required' => true,
                    'type' => 'string',
                    'applies_to' => ['offer'],
                ],
                'offer_date' => [
                    'label' => 'Data ofertă',
                    'label_en' => 'Offer Date',
                    'required' => true,
                    'type' => 'date',
                    'applies_to' => ['offer'],
                ],
                'offer_valid_until' => [
                    'label' => 'Valabilă până la',
                    'label_en' => 'Valid Until',
                    'required' => false,
                    'type' => 'date',
                    'applies_to' => ['offer'],
                ],
                'offer_title' => [
                    'label' => 'Titlu ofertă',
                    'label_en' => 'Offer Title',
                    'required' => false,
                    'type' => 'string',
                    'applies_to' => ['offer'],
                ],
                'offer_subtotal' => [
                    'label' => 'Subtotal ofertă',
                    'label_en' => 'Offer Subtotal',
                    'required' => false,
                    'type' => 'currency',
                    'applies_to' => ['offer'],
                ],
                'offer_discount' => [
                    'label' => 'Discount ofertă',
                    'label_en' => 'Offer Discount',
                    'required' => false,
                    'type' => 'currency',
                    'applies_to' => ['offer'],
                ],
                'offer_total' => [
                    'label' => 'Total ofertă',
                    'label_en' => 'Offer Total',
                    'required' => true,
                    'type' => 'currency',
                    'applies_to' => ['offer'],
                ],
            ],
            'organization' => [
                'org_name' => [
                    'label' => 'Denumire firmă',
                    'label_en' => 'Organization Name',
                    'required' => true,
                    'type' => 'string',
                ],
                'org_address' => [
                    'label' => 'Adresă firmă',
                    'label_en' => 'Organization Address',
                    'required' => false,
                    'type' => 'string',
                ],
                'org_tax_id' => [
                    'label' => 'CUI firmă',
                    'label_en' => 'Organization Tax ID',
                    'required' => false,
                    'type' => 'string',
                ],
                'org_trade_register' => [
                    'label' => 'Nr. Reg. Com. firmă',
                    'label_en' => 'Organization Trade Register',
                    'required' => false,
                    'type' => 'string',
                ],
                'org_representative' => [
                    'label' => 'Reprezentant legal',
                    'label_en' => 'Legal Representative',
                    'required' => false,
                    'type' => 'string',
                ],
                'org_bank_account' => [
                    'label' => 'Cont bancar firmă',
                    'label_en' => 'Organization Bank Account',
                    'required' => false,
                    'type' => 'string',
                ],
                'org_email' => [
                    'label' => 'Email firmă',
                    'label_en' => 'Organization Email',
                    'required' => false,
                    'type' => 'string',
                ],
                'org_phone' => [
                    'label' => 'Telefon firmă',
                    'label_en' => 'Organization Phone',
                    'required' => false,
                    'type' => 'string',
                ],
            ],
            'special' => [
                'services_list' => [
                    'label' => 'Lista servicii',
                    'label_en' => 'Services List',
                    'required' => false,
                    'type' => 'block',
                ],
                'current_date' => [
                    'label' => 'Data curentă',
                    'label_en' => 'Current Date',
                    'required' => false,
                    'type' => 'date',
                ],
            ],
        ];
    }

    /**
     * Resolve all variables for a document.
     *
     * @param Contract|Offer $document
     * @return array<string, string>
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

        return array_merge(
            static::resolveClientVariables($client, $contract, $offer),
            static::resolveContractVariables($contract),
            static::resolveOrganizationVariables($org, $orgSettings),
            static::resolveSpecialVariables($contract)
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
            static::resolveSpecialVariablesForOffer($offer)
        );
    }

    /**
     * Helper to escape HTML.
     */
    protected static function e(?string $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Resolve client-related variables.
     */
    protected static function resolveClientVariables($client, ?Contract $contract, ?Offer $offer): array
    {
        return [
            'client_company_name' => static::e(
                $client?->company_name
                ?? $client?->name
                ?? $contract?->temp_client_company
                ?? $contract?->temp_client_name
                ?? $offer?->temp_client_company
                ?? $offer?->temp_client_name
                ?? ''
            ),
            'client_address' => static::e($client?->address ?? $offer?->temp_client_address ?? ''),
            'client_trade_register_number' => static::e($client?->registration_number ?? $offer?->temp_client_registration_number ?? ''),
            'client_tax_id' => static::e($client?->tax_id ?? $offer?->temp_client_tax_id ?? ''),
            'client_bank_account' => static::e($client?->bank_account ?? ''),
            'client_representative' => static::e($client?->contact_person ?? $offer?->temp_client_name ?? ''),
            'client_email' => static::e($client?->email ?? $contract?->temp_client_email ?? $offer?->temp_client_email ?? ''),
            'client_phone' => static::e($client?->phone ?? $offer?->temp_client_phone ?? ''),
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

    /**
     * Resolve special variables for contracts.
     */
    protected static function resolveSpecialVariables(Contract $contract): array
    {
        return [
            'services_list' => static::renderServicesList($contract),
            'current_date' => static::e(now()->format('d.m.Y')),
        ];
    }

    /**
     * Resolve special variables for offers.
     */
    protected static function resolveSpecialVariablesForOffer(Offer $offer): array
    {
        return [
            'services_list' => static::renderServicesListForOffer($offer),
            'current_date' => static::e(now()->format('d.m.Y')),
        ];
    }

    /**
     * Render services list for a contract.
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
    // VALIDATION
    // =========================================================================

    /**
     * Validate that all required variables can be resolved.
     *
     * @return ValidationResult
     */
    public static function validate(array $blocks, Contract|Offer $document): ValidationResult
    {
        $errors = [];
        $warnings = [];
        $resolvedValues = static::resolve($document);
        $usedVariables = static::extractVariablesFromBlocks($blocks);
        $definitions = static::getFlatDefinitions();

        foreach ($usedVariables as $varName) {
            // Check if variable exists
            if (!isset($definitions[$varName])) {
                $errors[] = new ValidationError('unknown_variable', $varName, __('Variabilă necunoscută: :var', ['var' => $varName]));
                continue;
            }

            $definition = $definitions[$varName];

            // Check if required variable has value
            if ($definition['required'] && empty($resolvedValues[$varName])) {
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
     * Check if content can be rendered (no required variable errors).
     */
    public static function canRender(array $blocks, Contract|Offer $document): bool
    {
        $result = static::validate($blocks, $document);
        return $result->canRender();
    }

    /**
     * Extract variable names from JSON block structure.
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
        if (($node['type'] ?? '') === 'variable') {
            $name = $node['attrs']['name'] ?? null;
            if ($name) {
                $variables[] = $name;
            }
        }

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
     * Get variables for UI display (for variable picker).
     */
    public static function getForUI(?string $documentType = null): array
    {
        $locale = app()->getLocale();
        $result = [];

        foreach (static::getDefinitions() as $category => $vars) {
            $result[$category] = [];
            foreach ($vars as $key => $config) {
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
     * Format a variable key for display.
     */
    public static function formatVariable(string $key): string
    {
        return static::FORMAT_PREFIX . $key . static::FORMAT_SUFFIX;
    }

    /**
     * Get label for a variable.
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
}

/**
 * Validation error class.
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
 * Validation result class.
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

    public function canRender(): bool
    {
        // Can render if no required_empty errors
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
}
