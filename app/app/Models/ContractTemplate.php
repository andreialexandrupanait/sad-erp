<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'category',
        'content',
        'blocks',
        'variables_schema',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'blocks' => 'array',
        'variables_schema' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Boot function - auto-scope to organization.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (auth()->check() && empty($template->organization_id)) {
                $template->organization_id = auth()->user()->organization_id;
            }
        });

        static::addGlobalScope('organization', function (Builder $builder) {
            if (auth()->check() && auth()->user()->organization_id) {
                $builder->where('contract_templates.organization_id', auth()->user()->organization_id);
            }
        });
    }

    /**
     * Relationships
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get available template categories.
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
            'annex' => __('Contract Annex'),
            'altele' => __('Other'),
        ];
    }

    /**
     * Get available template variables with descriptions.
     */
    public static function getAvailableVariables(): array
    {
        return [
            'client' => [
                'client_name' => __('Client display name'),
                'client_company' => __('Client company name'),
                'client_email' => __('Client email address'),
                'client_phone' => __('Client phone number'),
                'client_address' => __('Client full address'),
                'client_tax_id' => __('Client tax/fiscal code'),
                'client_reg_number' => __('Client registration number'),
            ],
            'contract' => [
                'contract_number' => __('Contract number'),
                'contract_title' => __('Contract title'),
                'contract_date' => __('Contract creation date'),
                'contract_start_date' => __('Contract start date'),
                'contract_end_date' => __('Contract end date'),
                'contract_total' => __('Contract total value'),
                'contract_currency' => __('Contract currency'),
            ],
            'offer' => [
                'offer_number' => __('Original offer number'),
                'offer_title' => __('Original offer title'),
                'offer_date' => __('Offer creation date'),
                'offer_subtotal' => __('Offer subtotal'),
                'offer_discount' => __('Offer discount amount'),
                'offer_total' => __('Offer total'),
            ],
            'organization' => [
                'org_name' => __('Organization name'),
                'org_address' => __('Organization address'),
                'org_email' => __('Organization email'),
                'org_phone' => __('Organization phone'),
                'org_tax_id' => __('Organization tax ID'),
                'org_reg_number' => __('Organization registration number'),
                'org_bank_account' => __('Organization bank account'),
            ],
            'special' => [
                'SERVICES_TABLE' => __('Auto-generated services table'),
                'SIGNATURES' => __('Signature block'),
                'CURRENT_DATE' => __('Current date'),
            ],
        ];
    }

    /**
     * Render template content with variables replaced.
     */
    public function render(array $variables = []): string
    {
        $content = $this->content ?? '';

        foreach ($variables as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                $content = str_replace('{{' . $key . '}}', (string) $value, $content);
            }
        }

        return $content;
    }

    /**
     * Get default template for organization.
     */
    public static function getDefault(?int $organizationId = null): ?self
    {
        $organizationId = $organizationId ?? auth()->user()?->organization_id;

        return static::where('organization_id', $organizationId)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }
}
