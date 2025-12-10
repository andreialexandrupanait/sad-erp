<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class DocumentTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'type',
        'content',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Boot function - auto-scope by organization
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (auth()->check() && empty($template->organization_id)) {
                $template->organization_id = auth()->user()->organization_id;
            }
        });

        // If setting as default, unset other defaults of same type
        static::saving(function ($template) {
            if ($template->is_default && $template->isDirty('is_default')) {
                static::withoutGlobalScopes()
                    ->where('organization_id', $template->organization_id)
                    ->where('type', $template->type)
                    ->where('id', '!=', $template->id ?? 0)
                    ->update(['is_default' => false]);
            }
        });

        static::addGlobalScope('organization', function (Builder $builder) {
            if (auth()->check() && auth()->user()->organization_id) {
                $builder->where('document_templates.organization_id', auth()->user()->organization_id);
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

    public function offers()
    {
        return $this->hasMany(Offer::class, 'template_id');
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class, 'template_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Get the default template for a type
     */
    public static function getDefault($type)
    {
        return static::active()->ofType($type)->default()->first();
    }

    /**
     * Available template types
     */
    public static function getTypes()
    {
        return [
            'offer' => __('Offer'),
            'contract' => __('Contract'),
            'annex' => __('Annex'),
        ];
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute()
    {
        return static::getTypes()[$this->type] ?? ucfirst($this->type);
    }

    /**
     * Parse template content with variables
     */
    public function render(array $variables = [])
    {
        $content = $this->content;

        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }

        return $content;
    }

    /**
     * Get available variables for this template type
     */
    public function getAvailableVariables()
    {
        $common = [
            'client_name' => __('Client Name'),
            'client_company' => __('Client Company'),
            'client_email' => __('Client Email'),
            'client_phone' => __('Client Phone'),
            'client_address' => __('Client Address'),
            'client_tax_id' => __('Client Tax ID'),
            'organization_name' => __('Organization Name'),
            'current_date' => __('Current Date'),
        ];

        $specific = match ($this->type) {
            'offer' => [
                'offer_number' => __('Offer Number'),
                'offer_title' => __('Offer Title'),
                'offer_valid_until' => __('Valid Until'),
                'offer_subtotal' => __('Subtotal'),
                'offer_discount' => __('Discount'),
                'offer_total' => __('Total'),
                'offer_items_table' => __('Items Table'),
            ],
            'contract' => [
                'contract_number' => __('Contract Number'),
                'contract_title' => __('Contract Title'),
                'contract_start_date' => __('Start Date'),
                'contract_end_date' => __('End Date'),
                'contract_total_value' => __('Total Value'),
            ],
            'annex' => [
                'annex_number' => __('Annex Number'),
                'annex_code' => __('Annex Code'),
                'annex_title' => __('Annex Title'),
                'annex_effective_date' => __('Effective Date'),
                'annex_additional_value' => __('Additional Value'),
                'original_contract_number' => __('Original Contract Number'),
            ],
            default => [],
        };

        return array_merge($common, $specific);
    }
}
