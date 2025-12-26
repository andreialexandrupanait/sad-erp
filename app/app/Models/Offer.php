<?php

namespace App\Models;

use App\Services\Offer\OfferBlockRegistry;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Offer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'client_id',
        'temp_client_name',
        'temp_client_email',
        'temp_client_phone',
        'temp_client_company',
        'temp_client_address',
        'temp_client_tax_id',
        'temp_client_registration_number',
        'created_by_user_id',
        'template_id',
        'contract_id',
        'offer_number',
        'title',
        'introduction',
        'terms',
        'blocks',
        'header_data',
        'status',
        'valid_until',
        'public_token',
        'subtotal',
        'discount_amount',
        'discount_percent',
        'total',
        'currency',
        'sent_at',
        'viewed_at',
        'accepted_at',
        'rejected_at',
        'accepted_from_ip',
        'verification_code',
        'verification_code_expires_at',
        'notes',
        'rejection_reason',
        'current_version',
        'expiry_reminder_sent_at',
        'expiry_reminder_enabled',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'verification_code_expires_at' => 'datetime',
        'expiry_reminder_sent_at' => 'datetime',
        'expiry_reminder_enabled' => 'boolean',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'total' => 'decimal:2',
        'blocks' => 'array',
        'header_data' => 'array',
    ];

    /**
     * Get default blocks structure for new offers.
     * Uses OfferBlockRegistry to create blocks with proper default data.
     */
    public static function getDefaultBlocks(): array
    {
        return [
            OfferBlockRegistry::createBlock('header'),
            OfferBlockRegistry::createBlock('services'),
            OfferBlockRegistry::createBlock('summary'),
            OfferBlockRegistry::createBlock('acceptance'),
        ];
    }

    /**
     * Get all available block types from the registry.
     */
    public static function getAvailableBlockTypes(): array
    {
        return OfferBlockRegistry::getBlockTypes();
    }

    /**
     * Boot function - auto-scope and auto-generate fields
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($offer) {
            if (auth()->check()) {
                if (empty($offer->organization_id)) {
                    $offer->organization_id = auth()->user()->organization_id;
                }
                if (empty($offer->created_by_user_id)) {
                    $offer->created_by_user_id = auth()->id();
                }
            }

            // Generate offer number if not provided
            if (empty($offer->offer_number)) {
                $offer->offer_number = static::generateOfferNumber($offer->organization_id);
            }

            // Generate public token for sharing
            if (empty($offer->public_token)) {
                $offer->public_token = Str::random(64);
            }
        });

        static::addGlobalScope('organization', function (Builder $builder) {
            if (auth()->check() && auth()->user()->organization_id) {
                $builder->where('offers.organization_id', auth()->user()->organization_id);
            }
        });
    }

    /**
     * Generate unique offer number
     * Format: PREFIX + sequential number (e.g., "OFRSAD0001")
     */
    public static function generateOfferNumber($organizationId = null)
    {
        $organizationId = $organizationId ?? (auth()->check() ? auth()->user()->organization_id : 1);
        $prefix = 'OFRSAD';

        // Get prefix from organization settings
        $org = Organization::find($organizationId);
        if ($org && isset($org->settings['offer_prefix']) && !empty($org->settings['offer_prefix'])) {
            $prefix = $org->settings['offer_prefix'];
        }

        // Get all offers with this prefix and find the max number
        // This handles various formats like "PREFIX0001", "PREFIX-2025-001", etc.
        $offers = static::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->where('offer_number', 'LIKE', $prefix . '%')
            ->pluck('offer_number');

        $maxNumber = 0;
        foreach ($offers as $offerNumber) {
            // Extract all numbers from the offer number after the prefix
            if (preg_match_all('/(\d+)/', $offerNumber, $matches)) {
                // Take the last number found (e.g., "003" from "PREFIX-2025-003" or "0004" from "PREFIX0004")
                $lastNum = intval(end($matches[1]));
                if ($lastNum > $maxNumber) {
                    $maxNumber = $lastNum;
                }
            }
        }

        $nextNumber = $maxNumber + 1;

        return sprintf('%s%04d', $prefix, $nextNumber);
    }

    /**
     * Relationships
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get client display name (from client relationship or temp fields).
     */
    public function getClientDisplayNameAttribute(): string
    {
        if ($this->client) {
            return $this->client->display_name ?? $this->client->name;
        }
        return $this->temp_client_name ?? __('Unknown Client');
    }

    /**
     * Get client email (from client relationship or temp fields).
     */
    public function getClientEmailAttribute(): ?string
    {
        if ($this->client) {
            return $this->client->email;
        }
        return $this->temp_client_email;
    }

    /**
     * Check if this offer uses a temporary client.
     */
    public function hasTemporaryClient(): bool
    {
        return is_null($this->client_id) && !empty($this->temp_client_name);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function template()
    {
        return $this->belongsTo(DocumentTemplate::class, 'template_id');
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function items()
    {
        return $this->hasMany(OfferItem::class)->orderBy('sort_order');
    }

    public function activities()
    {
        return $this->hasMany(OfferActivity::class)->orderBy('created_at', 'desc');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable')->orderBy('created_at', 'desc');
    }

    public function versions()
    {
        return $this->hasMany(OfferVersion::class)->orderBy('version_number', 'desc');
    }

    /**
     * Scopes
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('offer_number', 'like', "%{$search}%")
              ->orWhere('title', 'like', "%{$search}%")
              ->orWhereHas('client', function ($q) use ($search) {
                  $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
              });
        });
    }

    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->where('status', 'sent')
            ->where('valid_until', '>=', now())
            ->where('valid_until', '<=', now()->addDays($days));
    }

    /**
     * Status helpers
     */
    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isSent()
    {
        return $this->status === 'sent';
    }

    public function isAccepted()
    {
        return $this->status === 'accepted';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isExpired()
    {
        return $this->status === 'expired' ||
               ($this->status === 'sent' && $this->valid_until < now());
    }

    public function canBeEdited()
    {
        // Allow editing in all statuses including accepted
        return true;
    }

    public function canBeSent()
    {
        return in_array($this->status, ['draft', 'rejected', 'expired']);
    }

    public function canBeAccepted()
    {
        // Allow acceptance for sent or viewed offers that haven't expired
        return in_array($this->status, ['sent', 'viewed']) && $this->valid_until >= now();
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'draft' => __('Draft'),
            'sent' => __('Sent'),
            'viewed' => __('Viewed'),
            'accepted' => __('Accepted'),
            'rejected' => __('Rejected'),
            'expired' => __('Expired'),
        ];

        return $labels[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'draft' => 'gray',
            'sent' => 'blue',
            'viewed' => 'purple',
            'accepted' => 'green',
            'rejected' => 'red',
            'expired' => 'yellow',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Calculate totals from items
     * Uses already loaded items if available, avoiding N+1 query
     * Only sums items where is_selected is true (or null for backwards compatibility)
     */
    public function calculateTotals()
    {
        // Use loaded relationship if available to avoid N+1
        // Only count selected items (is_selected = true or is_selected is null for backwards compatibility)
        if ($this->relationLoaded('items')) {
            $subtotal = $this->items
                ->filter(fn($item) => $item->is_selected !== false)
                ->sum('total_price');
        } else {
            $subtotal = $this->items()
                ->where(function ($q) {
                    $q->where('is_selected', true)
                      ->orWhereNull('is_selected');
                })
                ->sum('total_price');
        }

        $this->subtotal = $subtotal;

        if ($this->discount_percent) {
            $this->discount_amount = round($subtotal * ($this->discount_percent / 100), 2);
        }

        $this->total = $subtotal - ($this->discount_amount ?? 0);
        $this->save();

        // Clear offer statistics cache since totals changed
        cache()->forget('offer_stats_' . $this->organization_id);

        return $this;
    }

    /**
     * Alias for calculateTotals (used by OfferService)
     */
    public function recalculateTotals()
    {
        return $this->calculateTotals();
    }

    /**
     * Check if offer can be rejected
     */
    public function canBeRejected(): bool
    {
        return in_array($this->status, ['sent', 'viewed']);
    }

    /**
     * Mark offer as sent (state change only)
     */
    public function markAsSent()
    {
        $this->status = 'sent';
        $this->sent_at = now();
        $this->save();

        return $this;
    }

    /**
     * Mark as viewed (state change only)
     */
    public function markAsViewed($ipAddress = null, $userAgent = null)
    {
        if (!$this->viewed_at) {
            $this->viewed_at = now();

            if ($this->status === 'sent') {
                $this->status = 'viewed';
            }

            $this->save();
        }

        return $this;
    }

    /**
     * Generate verification code for acceptance
     */
    public function generateVerificationCode()
    {
        $this->verification_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->verification_code_expires_at = now()->addMinutes(30);
        $this->save();

        return $this->verification_code;
    }

    /**
     * Mark offer as accepted (state change only)
     */
    public function markAsAccepted($ipAddress = null)
    {
        $this->status = 'accepted';
        $this->accepted_at = now();
        $this->accepted_from_ip = $ipAddress;
        $this->verification_code = null;
        $this->verification_code_expires_at = null;
        $this->save();

        return $this;
    }

    /**
     * Mark offer as rejected (state change only)
     */
    public function markAsRejected($reason = null)
    {
        $this->status = 'rejected';
        $this->rejected_at = now();
        $this->rejection_reason = $reason;
        $this->save();

        return $this;
    }

    /**
     * Legacy aliases for backward compatibility
     * @deprecated Use markAsSent(), markAsAccepted(), markAsRejected() instead
     */
    public function send() { return $this->markAsSent(); }
    public function accept($ipAddress = null) { return $this->markAsAccepted($ipAddress); }
    public function reject($reason = null) { return $this->markAsRejected($reason); }

    /**
     * Get public URL
     */
    public function getPublicUrlAttribute()
    {
        return route('offers.public', $this->public_token);
    }

    /**
     * Log activity
     */
    public function logActivity($action, array $metadata = [])
    {
        return $this->activities()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata ?: null,
        ]);
    }

    /**
     * Convert to contract with items.
     *
     * @deprecated Use ContractService::createDraftFromOffer() instead for better
     *             template handling and client conversion. This method is kept
     *             for backwards compatibility but delegates to the service.
     */
    public function convertToContract(array $options = []): Contract
    {
        // Delegate to ContractService for consistent behavior
        $contractService = app(\App\Services\Contract\ContractService::class);

        // Get template if specified
        $template = null;
        if (!empty($options['template_id'])) {
            $template = \App\Models\ContractTemplate::find($options['template_id']);
        }

        return $contractService->createDraftFromOffer($this, $template);
    }

    /**
     * Get statistics
     */
    public static function getStatistics()
    {
        $counts = self::selectRaw("
            SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_count,
            SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent_count,
            SUM(CASE WHEN status = 'viewed' THEN 1 ELSE 0 END) as viewed_count,
            SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted_count,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
            SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired_count
        ")->first();

        $expiringSoon = self::expiringSoon()->count();

        // Get totals by status and currency
        $totalsByCurrency = self::selectRaw("
            status,
            currency,
            SUM(total) as total_value,
            COUNT(*) as count
        ")
            ->groupBy('status', 'currency')
            ->get();

        // Organize totals by status
        $moneyByStatus = [
            'draft' => [],
            'sent' => [],
            'viewed' => [],
            'accepted' => [],
            'rejected' => [],
            'expired' => [],
        ];

        foreach ($totalsByCurrency as $row) {
            if (isset($moneyByStatus[$row->status])) {
                $moneyByStatus[$row->status][$row->currency] = (float) $row->total_value;
            }
        }

        // Calculate total pending (draft + sent + viewed)
        $pendingTotals = [];
        foreach (['draft', 'sent', 'viewed'] as $status) {
            foreach ($moneyByStatus[$status] as $currency => $amount) {
                if (!isset($pendingTotals[$currency])) {
                    $pendingTotals[$currency] = 0;
                }
                $pendingTotals[$currency] += $amount;
            }
        }

        return [
            'draft' => (int) ($counts->draft_count ?? 0),
            'sent' => (int) ($counts->sent_count ?? 0),
            'viewed' => (int) ($counts->viewed_count ?? 0),
            'accepted' => (int) ($counts->accepted_count ?? 0),
            'rejected' => (int) ($counts->rejected_count ?? 0),
            'expired' => (int) ($counts->expired_count ?? 0),
            'expiring_soon' => $expiringSoon,
            'money' => $moneyByStatus,
            'pending_totals' => $pendingTotals,
        ];
    }

    /**
     * Create a snapshot of the current offer state for versioning.
     */
    public function createSnapshot(): array
    {
        $this->loadMissing('items');

        return [
            'title' => $this->title,
            'introduction' => $this->introduction,
            'terms' => $this->terms,
            'blocks' => $this->blocks,
            'header_data' => $this->header_data,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'discount_percent' => $this->discount_percent,
            'total' => $this->total,
            'currency' => $this->currency,
            'valid_until' => $this->valid_until?->format('Y-m-d'),
            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'service_id' => $item->service_id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'unit_price' => $item->unit_price,
                    'discount_percent' => $item->discount_percent,
                    'total_price' => $item->total_price,
                ];
            })->toArray(),
        ];
    }

    /**
     * Create a new version snapshot before making changes.
     * Only creates version if offer is sent or viewed.
     */
    public function createVersion(string $reason = null): ?OfferVersion
    {
        // Only version if offer is already sent/viewed
        if (!in_array($this->status, ['sent', 'viewed'])) {
            return null;
        }

        $version = $this->versions()->create([
            'version_number' => $this->current_version,
            'snapshot' => $this->createSnapshot(),
            'reason' => $reason,
            'created_by' => auth()->id(),
        ]);

        // Increment version number for next save
        $this->increment('current_version');

        return $version;
    }

    /**
     * Get the latest version snapshot.
     */
    public function getLatestVersion(): ?OfferVersion
    {
        return $this->versions()->first();
    }

    /**
     * Check if the offer has multiple versions.
     */
    public function hasMultipleVersions(): bool
    {
        return $this->current_version > 1;
    }

    /**
     * Calculate changes summary between two snapshots.
     */
    public static function calculateChangesSummary(array $oldSnapshot, array $newSnapshot): array
    {
        $changes = [];

        // Compare scalar fields
        $fieldsToCompare = [
            'title' => __('Title'),
            'introduction' => __('Introduction'),
            'terms' => __('Terms'),
            'subtotal' => __('Subtotal'),
            'discount_amount' => __('Discount Amount'),
            'discount_percent' => __('Discount Percent'),
            'total' => __('Total'),
            'valid_until' => __('Valid Until'),
        ];

        foreach ($fieldsToCompare as $field => $label) {
            $oldValue = $oldSnapshot[$field] ?? null;
            $newValue = $newSnapshot[$field] ?? null;

            if ($oldValue !== $newValue) {
                $changes[] = [
                    'field' => $field,
                    'label' => $label,
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        // Compare items
        $oldItems = $oldSnapshot['items'] ?? [];
        $newItems = $newSnapshot['items'] ?? [];

        if (count($oldItems) !== count($newItems)) {
            $changes[] = [
                'field' => 'items_count',
                'label' => __('Number of Items'),
                'old' => count($oldItems),
                'new' => count($newItems),
            ];
        }

        return $changes;
    }
}
