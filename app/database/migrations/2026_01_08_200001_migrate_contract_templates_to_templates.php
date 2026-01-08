<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Big Bang Migration: Consolidate all template tables into unified 'templates' table.
 *
 * This migration:
 * 1. Migrates contract_templates → templates (type='contract')
 * 2. Migrates document_templates → templates (type based on document_templates.type)
 * 3. Transforms old variable names to new standardized names
 * 4. Drops old tables after successful migration
 *
 * @see /home/andrei/.claude/plans/purrfect-stargazing-rainbow.md
 */
return new class extends Migration
{
    /**
     * Variable name mappings from old to new format.
     * These are the transformations applied to template content.
     */
    protected array $variableMapping = [
        // Legacy ContractTemplate/DocumentTemplate names → New VariableRegistry names
        '{{client_company}}' => '{{client_company_name}}',
        '{{client_name}}' => '{{client_representative}}',
        '{{client_reg_number}}' => '{{client_trade_register_number}}',
        '{{client_registration_number}}' => '{{client_trade_register_number}}',
        '{{client_cui}}' => '{{client_tax_id}}',
        '{{client_fiscal_code}}' => '{{client_tax_id}}',
        '{{client_bank}}' => '{{client_bank_account}}',
        '{{client_contact}}' => '{{client_representative}}',
        '{{client_contact_person}}' => '{{client_representative}}',

        // Organization (provider) legacy names
        '{{provider_name}}' => '{{org_name}}',
        '{{provider_company}}' => '{{org_name}}',
        '{{provider_address}}' => '{{org_address}}',
        '{{provider_cui}}' => '{{org_tax_id}}',
        '{{provider_fiscal_code}}' => '{{org_tax_id}}',
        '{{provider_reg_number}}' => '{{org_trade_register}}',
        '{{provider_bank}}' => '{{org_bank_account}}',
        '{{provider_representative}}' => '{{org_representative}}',
        '{{provider_email}}' => '{{org_email}}',
        '{{provider_phone}}' => '{{org_phone}}',
        '{{company_name}}' => '{{org_name}}',
        '{{company_address}}' => '{{org_address}}',

        // Contract legacy names
        '{{contract_value}}' => '{{contract_total}}',
        '{{contract_sum}}' => '{{contract_total}}',
        '{{contract_amount}}' => '{{contract_total}}',
        '{{contract_period_start}}' => '{{contract_start_date}}',
        '{{contract_period_end}}' => '{{contract_end_date}}',

        // Special blocks - keep uppercase versions for backwards compatibility
        '{{SERVICES_TABLE}}' => '{{services_list}}',
        '{{SERVICES_LIST}}' => '{{services_list}}',
        '{{SIGNATURES}}' => '{{signatures}}',
        '{{CURRENT_DATE}}' => '{{current_date}}',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Migrate contract_templates
        $this->migrateContractTemplates();

        // Step 2: Migrate document_templates
        $this->migrateDocumentTemplates();

        // Step 3: Drop old tables (big bang - no rollback)
        // NOTE: Commented out for safety - uncomment after verifying migration
        // Schema::dropIfExists('contract_templates');
        // Schema::dropIfExists('document_templates');
    }

    /**
     * Migrate contract_templates to templates table.
     */
    protected function migrateContractTemplates(): void
    {
        if (!Schema::hasTable('contract_templates')) {
            return;
        }

        $contractTemplates = DB::table('contract_templates')
            ->whereNull('deleted_at')
            ->get();

        foreach ($contractTemplates as $template) {
            // Transform content with new variable names
            $content = $this->transformVariables($template->content ?? '');

            // Generate unique slug
            $slug = $this->generateUniqueSlug($template->name, $template->organization_id);

            // Extract variables used
            $variablesUsed = $this->extractVariables($content);

            // Skip if already migrated (check by org + name + type)
            $exists = DB::table('templates')
                ->where('organization_id', $template->organization_id)
                ->where('name', $template->name)
                ->where('type', 'contract')
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('templates')->insert([
                'organization_id' => $template->organization_id,
                'type' => 'contract',
                'category' => $template->category ?? 'general',
                'name' => $template->name,
                'slug' => $slug,
                'blocks' => json_encode($this->htmlToBlocks($content)),
                'schema_version' => '1.0',
                'theme' => null,
                'content' => $content, // Keep transformed HTML for legacy support
                'variables_used' => json_encode($variablesUsed),
                'editor_type' => 'legacy', // Mark as migrated from legacy
                'is_default' => $template->is_default ?? false,
                'is_active' => $template->is_active ?? true,
                'current_version' => 1,
                'created_by' => null,
                'updated_by' => null,
                'created_at' => $template->created_at,
                'updated_at' => $template->updated_at,
                'deleted_at' => null,
            ]);
        }
    }

    /**
     * Migrate document_templates to templates table.
     */
    protected function migrateDocumentTemplates(): void
    {
        if (!Schema::hasTable('document_templates')) {
            return;
        }

        $documentTemplates = DB::table('document_templates')
            ->whereNull('deleted_at')
            ->get();

        foreach ($documentTemplates as $template) {
            // Determine type from document_templates.type
            $type = match ($template->type ?? 'contract') {
                'offer' => 'offer',
                'annex' => 'annex',
                default => 'contract',
            };

            // Transform content with new variable names
            $content = $this->transformVariables($template->content ?? '');

            // Generate unique slug
            $slug = $this->generateUniqueSlug($template->name, $template->organization_id);

            // Extract variables used
            $variablesUsed = $this->extractVariables($content);

            // Skip if already migrated
            $exists = DB::table('templates')
                ->where('organization_id', $template->organization_id)
                ->where('name', $template->name)
                ->where('type', $type)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('templates')->insert([
                'organization_id' => $template->organization_id,
                'type' => $type,
                'category' => 'general',
                'name' => $template->name,
                'slug' => $slug,
                'blocks' => json_encode($this->htmlToBlocks($content)),
                'schema_version' => '1.0',
                'theme' => null,
                'content' => $content,
                'variables_used' => json_encode($variablesUsed),
                'editor_type' => 'legacy',
                'is_default' => $template->is_default ?? false,
                'is_active' => $template->is_active ?? true,
                'current_version' => 1,
                'created_by' => null,
                'updated_by' => null,
                'created_at' => $template->created_at,
                'updated_at' => $template->updated_at,
                'deleted_at' => null,
            ]);
        }
    }

    /**
     * Transform old variable names to new standardized names.
     */
    protected function transformVariables(string $content): string
    {
        foreach ($this->variableMapping as $old => $new) {
            $content = str_replace($old, $new, $content);
        }

        return $content;
    }

    /**
     * Extract variable names from content.
     */
    protected function extractVariables(string $content): array
    {
        preg_match_all('/\{\{([a-z_]+)\}\}/', $content, $matches);
        return array_unique($matches[1] ?? []);
    }

    /**
     * Generate unique slug for template.
     */
    protected function generateUniqueSlug(string $name, int $organizationId): string
    {
        $slug = Str::slug($name);

        $count = DB::table('templates')
            ->where('organization_id', $organizationId)
            ->where('slug', 'like', $slug . '%')
            ->count();

        return $count > 0 ? "{$slug}-{$count}" : $slug;
    }

    /**
     * Convert HTML content to TipTap-compatible blocks structure.
     * This creates a minimal structure that wraps HTML content.
     */
    protected function htmlToBlocks(string $html): array
    {
        if (empty($html)) {
            return [
                'type' => 'doc',
                'schemaVersion' => '1.0',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [],
                    ],
                ],
            ];
        }

        // For migrated content, we store it as a single "html" node
        // that can be rendered directly. The editor can convert this
        // to proper TipTap nodes on edit.
        return [
            'type' => 'doc',
            'schemaVersion' => '1.0',
            'migratedFromLegacy' => true,
            'content' => [
                [
                    'type' => 'paragraph',
                    'attrs' => [
                        'class' => 'legacy-content',
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $html,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Reverse the migrations.
     * NOTE: This is a big bang migration - we don't restore old tables.
     */
    public function down(): void
    {
        // Delete migrated templates (those with editor_type='legacy')
        DB::table('templates')
            ->where('editor_type', 'legacy')
            ->delete();
    }
};
