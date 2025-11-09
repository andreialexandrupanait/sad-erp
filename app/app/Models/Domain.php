<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Domain extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'client_id',
        'domain_name',
        'registrar',
        'status',
        'registration_date',
        'expiry_date',
        'annual_cost',
        'auto_renew',
        'notes',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'expiry_date' => 'date',
        'annual_cost' => 'decimal:2',
        'auto_renew' => 'boolean',
    ];

    // Common registrars
    public const REGISTRARS = [
        'GoDaddy' => 'GoDaddy',
        'Namecheap' => 'Namecheap',
        'Google Domains' => 'Google Domains',
        'Cloudflare' => 'Cloudflare',
        'Name.com' => 'Name.com',
        'Hover' => 'Hover',
        'Domain.com' => 'Domain.com',
        'Dynadot' => 'Dynadot',
        'Gandi' => 'Gandi',
        'NameSilo' => 'NameSilo',
        '1&1 IONOS' => '1&1 IONOS',
        'Network Solutions' => 'Network Solutions',
        'Other' => 'Other',
    ];

    // Expiry thresholds
    public const EXPIRY_WARNING_DAYS = 30;

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically set organization_id when creating
        static::creating(function ($domain) {
            if (auth()->check() && empty($domain->organization_id)) {
                $domain->organization_id = auth()->user()->organization_id;
            }
        });

        // Global scope to filter by organization
        static::addGlobalScope('organization', function (Builder $builder) {
            if (auth()->check() && auth()->user()->organization_id) {
                $builder->where('organization_id', auth()->user()->organization_id);
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

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiryAttribute()
    {
        if (!$this->expiry_date) {
            return null;
        }

        return Carbon::now()->startOfDay()->diffInDays($this->expiry_date->startOfDay(), false);
    }

    /**
     * Check if domain is expired
     */
    public function getIsExpiredAttribute()
    {
        return $this->days_until_expiry !== null && $this->days_until_expiry < 0;
    }

    /**
     * Check if domain is expiring soon
     */
    public function getIsExpiringSoonAttribute()
    {
        return $this->days_until_expiry !== null
            && $this->days_until_expiry >= 0
            && $this->days_until_expiry <= self::EXPIRY_WARNING_DAYS;
    }

    /**
     * Get computed expiry status
     */
    public function getExpiryStatusAttribute()
    {
        if ($this->is_expired) {
            return 'Expired';
        }

        if ($this->is_expiring_soon) {
            return 'Expiring';
        }

        return 'Valid';
    }

    /**
     * Get expiry status badge color
     */
    public function getExpiryStatusColorAttribute()
    {
        return match($this->expiry_status) {
            'Expired' => 'red',
            'Expiring' => 'yellow',
            'Valid' => 'green',
            default => 'gray',
        };
    }

    /**
     * Get human-readable expiry text
     */
    public function getExpiryTextAttribute()
    {
        if ($this->is_expired) {
            $daysAgo = abs($this->days_until_expiry);
            return "Expired {$daysAgo} " . ($daysAgo === 1 ? 'day' : 'days') . " ago";
        }

        if ($this->is_expiring_soon) {
            return "Expires in {$this->days_until_expiry} " . ($this->days_until_expiry === 1 ? 'day' : 'days');
        }

        return "Expires in {$this->days_until_expiry} days";
    }

    /**
     * Get display name with TLD highlighting
     */
    public function getDisplayNameAttribute()
    {
        return $this->domain_name;
    }

    /**
     * Search scope
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('domain_name', 'like', "%{$search}%")
              ->orWhere('registrar', 'like', "%{$search}%")
              ->orWhereHas('client', function ($q) use ($search) {
                  $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Filter by client
     */
    public function scopeClient($query, $clientId)
    {
        if ($clientId === 'none') {
            return $query->whereNull('client_id');
        }

        if (!empty($clientId)) {
            return $query->where('client_id', $clientId);
        }

        return $query;
    }

    /**
     * Filter by registrar
     */
    public function scopeRegistrar($query, $registrar)
    {
        if (!empty($registrar)) {
            return $query->where('registrar', $registrar);
        }
        return $query;
    }

    /**
     * Filter by expiry status
     */
    public function scopeExpiryStatus($query, $status)
    {
        $now = Carbon::now()->startOfDay();
        $warningDate = $now->copy()->addDays(self::EXPIRY_WARNING_DAYS);

        return match($status) {
            'expired' => $query->where('expiry_date', '<', $now),
            'expiring' => $query->whereBetween('expiry_date', [$now, $warningDate]),
            'valid' => $query->where('expiry_date', '>', $warningDate),
            default => $query,
        };
    }

    /**
     * Scope for expired domains
     */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', Carbon::now()->startOfDay());
    }

    /**
     * Scope for expiring soon domains
     */
    public function scopeExpiringSoon($query)
    {
        $now = Carbon::now()->startOfDay();
        $warningDate = $now->copy()->addDays(self::EXPIRY_WARNING_DAYS);

        return $query->whereBetween('expiry_date', [$now, $warningDate]);
    }

    /**
     * Scope for valid domains
     */
    public function scopeValid($query)
    {
        $warningDate = Carbon::now()->addDays(self::EXPIRY_WARNING_DAYS);
        return $query->where('expiry_date', '>', $warningDate);
    }

    /**
     * Get total annual cost for all domains
     */
    public static function getTotalAnnualCost()
    {
        return self::sum('annual_cost') ?? 0;
    }

    /**
     * Get domain statistics
     */
    public static function getStatistics()
    {
        return [
            'total' => self::count(),
            'expired' => self::expired()->count(),
            'expiring_soon' => self::expiringSoon()->count(),
            'valid' => self::valid()->count(),
            'total_annual_cost' => self::getTotalAnnualCost(),
            'with_client' => self::whereNotNull('client_id')->count(),
            'without_client' => self::whereNull('client_id')->count(),
        ];
    }
}
