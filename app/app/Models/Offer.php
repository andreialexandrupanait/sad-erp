<?php

namespace App\Models;

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
        'created_by_user_id',
        'template_id',
        'contract_id',
        'offer_number',
        'title',
        'introduction',
        'terms',
        'blocks',
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
    ];

    protected $casts = [
        'valid_until' => 'date',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'verification_code_expires_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'total' => 'decimal:2',
        'blocks' => 'array',
    ];

    /**
     * Get default blocks structure for new offers
     */
    public static function getDefaultBlocks(): array
    {
        return [
            [
                'id' => 'header_' . uniqid(),
                'type' => 'header',
                'data' => [
                    'show_logo' => true,
                    'show_offer_number' => true,
                    'show_client_info' => true,
                    'show_date' => true,
                ],
            ],
            [
                'id' => 'intro_' . uniqid(),
                'type' => 'content',
                'data' => [
                    'title' => '',
                    'content' => '',
                ],
            ],
            [
                'id' => 'services_' . uniqid(),
                'type' => 'services',
                'data' => [
                    'title' => 'Services',
                    'show_descriptions' => true,
                    'show_prices' => true,
                ],
            ],
            [
                'id' => 'summary_' . uniqid(),
                'type' => 'summary',
                'data' => [
                    'title' => 'Investment Summary',
                    'show_subtotal' => true,
                    'show_discount' => true,
                    'show_total' => true,
                ],
            ],
            [
                'id' => 'signature_' . uniqid(),
                'type' => 'signature',
                'data' => [
                    'title' => 'Agreement',
                    'content' => '',
                    'show_signature_field' => true,
                ],
            ],
        ];
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
     */
    public static function generateOfferNumber($organizationId = null)
    {
        $organizationId = $organizationId ?? (auth()->check() ? auth()->user()->organization_id : 1);
        $year = date('Y');
        $prefix = 'OFR';

        // Get organization prefix if available
        $org = Organization::find($organizationId);
        if ($org && $org->code) {
            $prefix = $org->code;
        }

        $lastOffer = static::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->whereYear('created_at', $year)
            ->orderByRaw('CAST(SUBSTRING_INDEX(offer_number, "-", -1) AS UNSIGNED) DESC')
            ->first();

        if ($lastOffer && preg_match('/-(\d+)$/', $lastOffer->offer_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%s-%d-%03d', $prefix, $year, $nextNumber);
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
        return in_array($this->status, ['draft']);
    }

    public function canBeSent()
    {
        return in_array($this->status, ['draft', 'rejected', 'expired']);
    }

    public function canBeAccepted()
    {
        return $this->status === 'sent' && $this->valid_until >= now();
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
     */
    public function calculateTotals()
    {
        $subtotal = $this->items()->sum('total_price');
        $this->subtotal = $subtotal;

        if ($this->discount_percent) {
            $this->discount_amount = round($subtotal * ($this->discount_percent / 100), 2);
        }

        $this->total = $subtotal - ($this->discount_amount ?? 0);
        $this->save();

        return $this;
    }

    /**
     * Send offer to client
     */
    public function send()
    {
        $this->status = 'sent';
        $this->sent_at = now();
        $this->save();

        $this->logActivity('sent');

        return $this;
    }

    /**
     * Mark as viewed
     */
    public function markAsViewed($ipAddress = null, $userAgent = null)
    {
        if (!$this->viewed_at) {
            $this->viewed_at = now();

            if ($this->status === 'sent') {
                $this->status = 'viewed';
            }

            $this->save();

            $this->logActivity('viewed', [
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);
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
     * Accept the offer
     */
    public function accept($ipAddress = null)
    {
        $this->status = 'accepted';
        $this->accepted_at = now();
        $this->accepted_from_ip = $ipAddress;
        $this->verification_code = null;
        $this->verification_code_expires_at = null;
        $this->save();

        $this->logActivity('accepted', [
            'ip_address' => $ipAddress,
        ]);

        return $this;
    }

    /**
     * Reject the offer
     */
    public function reject($reason = null)
    {
        $this->status = 'rejected';
        $this->rejected_at = now();
        $this->rejection_reason = $reason;
        $this->save();

        $this->logActivity('rejected', [
            'reason' => $reason,
        ]);

        return $this;
    }

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
     * Convert to contract
     */
    public function convertToContract()
    {
        if (!$this->isAccepted()) {
            throw new \Exception(__('Only accepted offers can be converted to contracts.'));
        }

        $contract = Contract::create([
            'organization_id' => $this->organization_id,
            'client_id' => $this->client_id,
            'offer_id' => $this->id,
            'template_id' => null,
            'contract_number' => Contract::generateContractNumber($this->organization_id),
            'title' => $this->title,
            'content' => '', // Will be filled from template
            'status' => 'active',
            'start_date' => now(),
            'total_value' => $this->total,
            'currency' => $this->currency,
        ]);

        // Link offer to contract
        $this->contract_id = $contract->id;
        $this->save();

        return $contract;
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

        $totals = self::where('status', 'accepted')
            ->selectRaw('SUM(total) as total_accepted_value')
            ->first();

        $expiringSoon = self::expiringSoon()->count();

        return [
            'draft' => (int) ($counts->draft_count ?? 0),
            'sent' => (int) ($counts->sent_count ?? 0),
            'viewed' => (int) ($counts->viewed_count ?? 0),
            'accepted' => (int) ($counts->accepted_count ?? 0),
            'rejected' => (int) ($counts->rejected_count ?? 0),
            'expired' => (int) ($counts->expired_count ?? 0),
            'total_accepted_value' => (float) ($totals->total_accepted_value ?? 0),
            'expiring_soon' => $expiringSoon,
        ];
    }
}
