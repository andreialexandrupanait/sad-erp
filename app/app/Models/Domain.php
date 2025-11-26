<?php

namespace App\Models;

use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Domain extends Model
{
    use HasFactory, SoftDeletes, HasOrganization;

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

    // Expiry thresholds
    public const EXPIRY_WARNING_DAYS = 30;

    // Organization scoping handled by HasOrganization trait

    /**
     * Relationships
     */
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
            return trans_choice('Expired :count day ago|Expired :count days ago', $daysAgo, ['count' => $daysAgo]);
        }

        if ($this->is_expiring_soon) {
            return trans_choice('Expires in :count day|Expires in :count days', $this->days_until_expiry, ['count' => $this->days_until_expiry]);
        }

        return trans_choice('Expires in :count day|Expires in :count days', $this->days_until_expiry, ['count' => $this->days_until_expiry]);
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
     * Optimized to use single query with database-level aggregations
     */
    public static function getStatistics()
    {
        $now = Carbon::now()->startOfDay()->toDateString();
        $warningDate = Carbon::now()->addDays(self::EXPIRY_WARNING_DAYS)->toDateString();

        $stats = self::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN expiry_date < ? THEN 1 ELSE 0 END) as expired,
            SUM(CASE WHEN expiry_date >= ? AND expiry_date <= ? THEN 1 ELSE 0 END) as expiring_soon,
            SUM(CASE WHEN expiry_date > ? THEN 1 ELSE 0 END) as valid,
            COALESCE(SUM(annual_cost), 0) as total_annual_cost,
            SUM(CASE WHEN client_id IS NOT NULL THEN 1 ELSE 0 END) as with_client,
            SUM(CASE WHEN client_id IS NULL THEN 1 ELSE 0 END) as without_client
        ", [$now, $now, $warningDate, $warningDate])->first();

        return [
            'total' => (int) ($stats->total ?? 0),
            'expired' => (int) ($stats->expired ?? 0),
            'expiring_soon' => (int) ($stats->expiring_soon ?? 0),
            'valid' => (int) ($stats->valid ?? 0),
            'total_annual_cost' => round($stats->total_annual_cost ?? 0, 2),
            'with_client' => (int) ($stats->with_client ?? 0),
            'without_client' => (int) ($stats->without_client ?? 0),
        ];
    }
}
