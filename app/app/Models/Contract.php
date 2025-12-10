<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'client_id',
        'offer_id',
        'template_id',
        'contract_number',
        'title',
        'content',
        'status',
        'start_date',
        'end_date',
        'auto_renew',
        'total_value',
        'currency',
        'pdf_path',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'auto_renew' => 'boolean',
        'total_value' => 'decimal:2',
    ];

    /**
     * Boot function - auto-scope and auto-generate fields
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($contract) {
            if (auth()->check() && empty($contract->organization_id)) {
                $contract->organization_id = auth()->user()->organization_id;
            }

            // Generate contract number if not provided
            if (empty($contract->contract_number)) {
                $contract->contract_number = static::generateContractNumber($contract->organization_id);
            }
        });

        static::addGlobalScope('organization', function (Builder $builder) {
            if (auth()->check() && auth()->user()->organization_id) {
                $builder->where('contracts.organization_id', auth()->user()->organization_id);
            }
        });
    }

    /**
     * Generate unique contract number
     */
    public static function generateContractNumber($organizationId = null)
    {
        $organizationId = $organizationId ?? (auth()->check() ? auth()->user()->organization_id : 1);
        $year = date('Y');
        $prefix = 'CTR';

        // Get organization prefix if available
        $org = Organization::find($organizationId);
        if ($org && $org->code) {
            $prefix = 'CTR-' . $org->code;
        }

        $lastContract = static::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->whereYear('created_at', $year)
            ->orderByRaw('CAST(SUBSTRING_INDEX(contract_number, "-", -1) AS UNSIGNED) DESC')
            ->first();

        if ($lastContract && preg_match('/-(\d+)$/', $lastContract->contract_number, $matches)) {
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

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function template()
    {
        return $this->belongsTo(DocumentTemplate::class, 'template_id');
    }

    public function annexes()
    {
        return $this->hasMany(ContractAnnex::class)->orderBy('annex_number');
    }

    public function additionalOffers()
    {
        return $this->hasMany(Offer::class);
    }

    /**
     * Scopes
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('contract_number', 'like', "%{$search}%")
              ->orWhere('title', 'like', "%{$search}%")
              ->orWhereHas('client', function ($q) use ($search) {
                  $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
              });
        });
    }

    public function scopeExpiringBetween($query, $startDate, $endDate)
    {
        return $query->where('status', 'active')
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [$startDate, $endDate]);
    }

    /**
     * Status helpers
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isExpired()
    {
        return $this->status === 'expired' ||
               ($this->end_date && $this->end_date < now());
    }

    public function isIndefinite()
    {
        return is_null($this->end_date);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'draft' => __('Draft'),
            'active' => __('Active'),
            'completed' => __('Completed'),
            'terminated' => __('Terminated'),
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
            'active' => 'green',
            'completed' => 'blue',
            'terminated' => 'red',
            'expired' => 'yellow',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiryAttribute()
    {
        if (!$this->end_date) {
            return null; // Indefinite
        }

        return now()->startOfDay()->diffInDays($this->end_date->startOfDay(), false);
    }

    /**
     * Get expiry urgency
     */
    public function getExpiryUrgencyAttribute()
    {
        $days = $this->days_until_expiry;

        if ($days === null) return 'indefinite';
        if ($days < 0) return 'expired';
        if ($days <= 30) return 'urgent';
        if ($days <= 60) return 'warning';
        return 'normal';
    }

    /**
     * Get total contract value including annexes
     */
    public function getTotalValueWithAnnexesAttribute()
    {
        return $this->total_value + $this->annexes()->sum('additional_value');
    }

    /**
     * Add an annex from an accepted offer
     */
    public function addAnnexFromOffer(Offer $offer)
    {
        if (!$offer->isAccepted()) {
            throw new \Exception(__('Only accepted offers can be added as annexes.'));
        }

        $nextNumber = $this->annexes()->max('annex_number') + 1;

        $annex = $this->annexes()->create([
            'offer_id' => $offer->id,
            'template_id' => null,
            'annex_number' => $nextNumber,
            'annex_code' => sprintf('AN-%s-%d', $this->contract_number, $nextNumber),
            'title' => $offer->title,
            'content' => '', // Will be filled from template
            'effective_date' => now(),
            'additional_value' => $offer->total,
            'currency' => $offer->currency,
        ]);

        // Link offer to this contract
        $offer->contract_id = $this->id;
        $offer->save();

        return $annex;
    }

    /**
     * Terminate the contract
     */
    public function terminate()
    {
        $this->status = 'terminated';
        $this->save();

        return $this;
    }

    /**
     * Get statistics
     */
    public static function getStatistics()
    {
        $counts = self::selectRaw("
            SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_count,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
            SUM(CASE WHEN status = 'terminated' THEN 1 ELSE 0 END) as terminated_count,
            SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired_count
        ")->first();

        $values = self::where('status', 'active')
            ->selectRaw('SUM(total_value) as total_active_value')
            ->first();

        $expiringSoon = self::expiringBetween(now(), now()->addDays(30))->count();

        return [
            'draft' => (int) ($counts->draft_count ?? 0),
            'active' => (int) ($counts->active_count ?? 0),
            'completed' => (int) ($counts->completed_count ?? 0),
            'terminated' => (int) ($counts->terminated_count ?? 0),
            'expired' => (int) ($counts->expired_count ?? 0),
            'total_active_value' => (float) ($values->total_active_value ?? 0),
            'expiring_soon' => $expiringSoon,
        ];
    }
}
