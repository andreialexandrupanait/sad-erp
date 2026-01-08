<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class OfferTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'slug',
        'blocks',
        'theme',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'blocks' => 'array',
        'theme' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Default EditorJS blocks structure
     */
    public static function getDefaultBlocks(): array
    {
        return [
            'time' => now()->timestamp * 1000,
            'blocks' => [
                [
                    'id' => 'header_' . Str::random(8),
                    'type' => 'offerHeader',
                    'data' => [
                        'introTitle' => __('Your business partner for digital solutions.'),
                        'introText' => __('We deliver high-quality services tailored to your specific needs.'),
                        'showLogo' => true,
                        'showDates' => true,
                        'showCompanyInfo' => true,
                        'showClientInfo' => true,
                    ],
                ],
                [
                    'id' => 'services_' . Str::random(8),
                    'type' => 'offerServices',
                    'data' => [
                        'title' => __('Proposed Services'),
                        'showDescriptions' => true,
                        'showPrices' => true,
                        'optionalServices' => [],
                        'showNotes' => true,
                        'notesTitle' => __('Notes'),
                        'notes' => '',
                    ],
                ],
                [
                    'id' => 'summary_' . Str::random(8),
                    'type' => 'offerSummary',
                    'data' => [
                        'title' => __('Investment Summary'),
                        'showSubtotal' => true,
                        'showVAT' => false,
                        'vatPercent' => 19,
                        'showDiscount' => true,
                        'showGrandTotal' => true,
                    ],
                ],
                [
                    'id' => 'acceptance_' . Str::random(8),
                    'type' => 'offerAcceptance',
                    'data' => [
                        'title' => __('Offer Acceptance'),
                        'acceptanceText' => __('By approving this offer, I confirm that I have read and agree to the services and conditions described above.'),
                        'showClientInfo' => true,
                        'showDate' => true,
                        'acceptButtonText' => __('Accept Offer'),
                        'rejectButtonText' => __('Decline'),
                    ],
                ],
            ],
            'version' => '2.28.2',
        ];
    }

    /**
     * Default theme configuration
     */
    public static function getDefaultTheme(): array
    {
        return [
            'colors' => [
                'primary' => '#1e293b',
                'secondary' => '#3b82f6',
                'accent' => '#10b981',
                'background' => '#ffffff',
                'text' => '#334155',
            ],
            'typography' => [
                'headingFont' => 'Inter',
                'bodyFont' => 'Inter',
                'baseFontSize' => 14,
            ],
            'blocks' => [
                'header' => ['background' => '#1e293b'],
                'services' => ['background' => '#ffffff'],
                'summary' => ['background' => '#f8fafc'],
                'brands' => ['background' => '#ffffff'],
                'acceptance' => ['background' => '#ecfdf5'],
            ],
        ];
    }

    /**
     * Boot function
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (auth()->check() && empty($template->organization_id)) {
                $template->organization_id = auth()->user()->organization_id;
            }

            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name) . '-' . Str::random(6);
            }

            if (empty($template->blocks)) {
                $template->blocks = static::getDefaultBlocks();
            }

            if (empty($template->theme)) {
                $template->theme = static::getDefaultTheme();
            }
        });

        static::saving(function ($template) {
            // If this template is being set as default, unset others
            if ($template->is_default && $template->isDirty('is_default')) {
                static::where('organization_id', $template->organization_id)
                    ->where('id', '!=', $template->id ?? 0)
                    ->update(['is_default' => false]);
            }
        });

        static::addGlobalScope('organization', function (Builder $builder) {
            if (auth()->check() && auth()->user()->organization_id) {
                $builder->where('offer_templates.organization_id', auth()->user()->organization_id);
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

    /**
     * Scopes
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * Get the default template for the current organization
     */
    public static function getDefault(): ?self
    {
        return static::active()->default()->first();
    }

    /**
     * Clone this template
     */
    public function duplicate(string $name = null): self
    {
        $clone = $this->replicate();
        $clone->name = $name ?? $this->name . ' (' . __('Copy') . ')';
        $clone->slug = null; // Will be auto-generated
        $clone->is_default = false;
        $clone->save();

        return $clone;
    }

    /**
     * Apply this template to an offer
     */
    public function applyToOffer(Offer $offer): Offer
    {
        $offer->blocks = $this->blocks;
        $offer->template_id = $this->id;
        $offer->editor_version = $this->blocks['version'] ?? '2.28.2';
        $offer->save();

        return $offer;
    }
}
