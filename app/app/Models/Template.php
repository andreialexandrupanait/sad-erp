<?php

namespace App\Models;

use App\Services\VariableRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Template extends Model
{
    use HasFactory, SoftDeletes;

    // Template types
    public const TYPE_OFFER = 'offer';
    public const TYPE_CONTRACT = 'contract';
    public const TYPE_ANNEX = 'annex';
    public const TYPE_EMAIL = 'email';

    // Editor types for migration tracking
    public const EDITOR_TIPTAP = 'tiptap';
    public const EDITOR_QUILL = 'quill';
    public const EDITOR_LEGACY = 'legacy';

    protected $fillable = [
        'organization_id',
        'type',
        'category',
        'name',
        'slug',
        'blocks',
        'schema_version',
        'theme',
        'content',
        'variables_used',
        'editor_type',
        'is_default',
        'is_active',
        'current_version',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'blocks' => 'array',
        'theme' => 'array',
        'variables_used' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'current_version' => 'integer',
    ];

    protected $attributes = [
        'schema_version' => '1.0',
        'editor_type' => self::EDITOR_TIPTAP,
        'is_default' => false,
        'is_active' => true,
        'current_version' => 1,
    ];

    /**
     * Boot function - auto-scope to organization and generate slug.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $template) {
            // Auto-assign organization
            if (auth()->check() && empty($template->organization_id)) {
                $template->organization_id = auth()->user()->organization_id;
            }

            // Auto-assign creator
            if (auth()->check() && empty($template->created_by)) {
                $template->created_by = auth()->id();
            }

            // Generate slug if not provided
            if (empty($template->slug)) {
                $template->slug = static::generateUniqueSlug($template->name, $template->organization_id);
            }

            // Initialize blocks if empty
            if (empty($template->blocks)) {
                $template->blocks = static::getDefaultBlocks();
            }
        });

        static::updating(function (self $template) {
            // Track who updated
            if (auth()->check()) {
                $template->updated_by = auth()->id();
            }
        });

        // Auto-extract variables on save
        static::saving(function (self $template) {
            $template->variables_used = $template->extractVariables();
        });

        // Organization scope
        static::addGlobalScope('organization', function (Builder $builder) {
            if (auth()->check() && auth()->user()->organization_id) {
                $builder->where('templates.organization_id', auth()->user()->organization_id);
            }
        });
    }

    /**
     * Generate a unique slug for the template.
     */
    public static function generateUniqueSlug(string $name, ?int $organizationId = null): string
    {
        $slug = Str::slug($name);
        $organizationId = $organizationId ?? auth()->user()?->organization_id;

        $count = static::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->where('slug', 'like', $slug . '%')
            ->count();

        return $count > 0 ? "{$slug}-{$count}" : $slug;
    }

    /**
     * Get default empty document structure.
     */
    public static function getDefaultBlocks(): array
    {
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

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(TemplateVersion::class)->orderByDesc('version_number');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeContracts(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_CONTRACT);
    }

    public function scopeOffers(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_OFFER);
    }

    public function scopeAnnexes(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_ANNEX);
    }

    public function scopeEmails(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_EMAIL);
    }

    // =========================================================================
    // VERSIONING
    // =========================================================================

    /**
     * Create a new version snapshot of this template.
     */
    public function createVersion(?string $reason = null): TemplateVersion
    {
        $version = $this->versions()->create([
            'version_number' => $this->current_version,
            'blocks' => $this->blocks,
            'theme' => $this->theme,
            'content_hash' => $this->getContentHash(),
            'reason' => $reason,
            'created_by' => auth()->id(),
        ]);

        $this->increment('current_version');

        return $version;
    }

    /**
     * Restore template to a specific version.
     */
    public function restoreToVersion(int $versionNumber): bool
    {
        $version = $this->versions()->where('version_number', $versionNumber)->first();

        if (!$version) {
            return false;
        }

        // Create a version of current state before restoring
        $this->createVersion("Restored to version {$versionNumber}");

        // Restore the content
        $this->update([
            'blocks' => $version->blocks,
            'theme' => $version->theme,
        ]);

        return true;
    }

    /**
     * Get content hash for change detection.
     */
    public function getContentHash(): string
    {
        return hash('sha256', json_encode($this->blocks) . json_encode($this->theme));
    }

    /**
     * Check if content has changed since last version.
     */
    public function hasUnsavedChanges(): bool
    {
        $lastVersion = $this->versions()->first();

        if (!$lastVersion) {
            return true;
        }

        return $lastVersion->content_hash !== $this->getContentHash();
    }

    // =========================================================================
    // STATIC HELPERS
    // =========================================================================

    /**
     * Get available template types.
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_OFFER => __('Offer'),
            self::TYPE_CONTRACT => __('Contract'),
            self::TYPE_ANNEX => __('Annex'),
            self::TYPE_EMAIL => __('Email'),
        ];
    }

    /**
     * Get available categories.
     */
    public static function getCategories(): array
    {
        return [
            'general' => __('General'),
            'servicii' => __('Services'),
            'consultanta' => __('Consulting'),
            'dezvoltare' => __('Development'),
            'mentenanta' => __('Maintenance'),
            'marketing' => __('Marketing'),
            'altele' => __('Other'),
        ];
    }

    /**
     * Get default template for a specific type.
     */
    public static function getDefault(string $type, ?int $organizationId = null): ?self
    {
        $organizationId = $organizationId ?? auth()->user()?->organization_id;

        return static::where('organization_id', $organizationId)
            ->where('type', $type)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Set this template as the default for its type.
     */
    public function setAsDefault(): void
    {
        // Unset other defaults of same type
        static::where('organization_id', $this->organization_id)
            ->where('type', $this->type)
            ->where('id', '!=', $this->id)
            ->where('is_default', true)
            ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }

    // =========================================================================
    // CONTENT HELPERS
    // =========================================================================

    /**
     * Check if template uses new TipTap editor.
     */
    public function usesTipTap(): bool
    {
        return $this->editor_type === self::EDITOR_TIPTAP;
    }

    /**
     * Check if template uses legacy editor.
     */
    public function usesLegacyEditor(): bool
    {
        return in_array($this->editor_type, [self::EDITOR_QUILL, self::EDITOR_LEGACY]);
    }

    /**
     * Get content for editing - returns blocks for TipTap, content for legacy.
     */
    public function getEditableContent(): array|string
    {
        if ($this->usesTipTap()) {
            return $this->blocks ?? static::getDefaultBlocks();
        }

        return $this->content ?? '';
    }

    /**
     * Extract all variable names used in the template.
     */
    public function extractVariables(): array
    {
        $variables = [];

        if ($this->usesTipTap()) {
            $this->extractVariablesFromBlocks($this->blocks, $variables);
        } else {
            // Extract from HTML content
            preg_match_all('/\{\{([a-z_]+)\}\}/', $this->content ?? '', $matches);
            $variables = array_unique($matches[1] ?? []);
        }

        return $variables;
    }

    /**
     * Recursively extract variables from block structure.
     */
    protected function extractVariablesFromBlocks(array $node, array &$variables): void
    {
        if (($node['type'] ?? '') === 'variable') {
            $name = $node['attrs']['name'] ?? null;
            if ($name && !in_array($name, $variables)) {
                $variables[] = $name;
            }
        }

        // Also check for {{variable}} in text nodes
        if (($node['type'] ?? '') === 'text' && isset($node['text'])) {
            preg_match_all('/\{\{([a-z_]+)\}\}/', $node['text'], $matches);
            foreach ($matches[1] ?? [] as $var) {
                if (!in_array($var, $variables)) {
                    $variables[] = $var;
                }
            }
        }

        foreach ($node['content'] ?? [] as $child) {
            if (is_array($child)) {
                $this->extractVariablesFromBlocks($child, $variables);
            }
        }
    }

    // =========================================================================
    // RENDERING WITH VARIABLE REGISTRY
    // =========================================================================

    /**
     * Render template content with resolved variables.
     *
     * @param Contract|Offer $document The document to resolve variables from
     * @return string Rendered HTML content with all variables replaced
     */
    public function render(Contract|Offer $document): string
    {
        $content = $this->content ?? '';

        if (empty($content)) {
            return '';
        }

        return VariableRegistry::render($content, $document);
    }

    /**
     * Validate template against a document.
     *
     * @param Contract|Offer $document The document to validate against
     * @return \App\Services\ValidationResult
     */
    public function validate(Contract|Offer $document): \App\Services\ValidationResult
    {
        $content = $this->content ?? '';
        return VariableRegistry::validate($content, $document);
    }

    /**
     * Get available variables for this template type.
     *
     * @return array Variables grouped by category
     */
    public function getAvailableVariables(): array
    {
        return VariableRegistry::getForUI($this->type);
    }

    /**
     * Check if a specific variable is used in this template.
     */
    public function usesVariable(string $variableKey): bool
    {
        return in_array($variableKey, $this->variables_used ?? []);
    }

    /**
     * Get list of missing required variables for a document.
     *
     * @param Contract|Offer $document
     * @return array List of missing variable keys
     */
    public function getMissingRequiredVariables(Contract|Offer $document): array
    {
        $result = $this->validate($document);
        $missing = [];

        foreach ($result->errors as $error) {
            if ($error->type === 'required_empty') {
                $missing[] = $error->variable;
            }
        }

        return $missing;
    }
}
