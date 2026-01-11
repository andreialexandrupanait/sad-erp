<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use App\Services\Subscription\SubscriptionCalculationService;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'user_id',
        'created_by',
        'vendor_name',
        'price',
        'price_eur',
        'currency',
        'exchange_rate',
        'billing_cycle',
        'custom_days',
        'start_date',
        'next_renewal_date',
        'status',
        'auto_renew',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_eur' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'start_date' => 'date',
        'next_renewal_date' => 'date',
        'custom_days' => 'integer',
        'auto_renew' => 'boolean',
    ];

    /**
     * Boot method - apply global scopes and auto-assign organization_id
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-assign organization_id and created_by when creating
        static::creating(function ($subscription) {
            if (auth()->check()) {
                if (empty($subscription->organization_id)) {
                    $subscription->organization_id = auth()->user()->organization_id;
                }
                if (empty($subscription->created_by)) {
                    $subscription->created_by = auth()->id();
                }
                // Keep user_id for backwards compatibility
                if (empty($subscription->user_id)) {
                    $subscription->user_id = auth()->id();
                }
            }
        });

        // Organization-based scoping: Show all subscriptions in the user's organization
        static::addGlobalScope('organization', function (Builder $builder) {
            if (auth()->check() && auth()->user()->organization_id) {
                $builder->where('subscriptions.organization_id', auth()->user()->organization_id);
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs()
    {
        return $this->hasMany(SubscriptionLog::class)->orderBy('changed_at', 'desc');
    }

    /**
     * Computed Attributes - Days until renewal
     */
    public function getDaysUntilRenewalAttribute()
    {
        // Don't calculate days for paused or cancelled subscriptions
        if ($this->status !== 'active') return null;

        if (!$this->next_renewal_date) return null;
        return Carbon::now()->startOfDay()->diffInDays($this->next_renewal_date->startOfDay(), false);
    }

    /**
     * Computed Attributes - Renewal urgency (red, yellow, green)
     */
    public function getRenewalUrgencyAttribute()
    {
        // No urgency for non-active subscriptions
        if ($this->status !== 'active') return 'paused';

        $days = $this->days_until_renewal;

        if ($days === null) return 'unknown';
        if ($days < 0) return 'overdue'; // Past due
        if ($days <= 7) return 'urgent'; // Red (0-7 days)
        if ($days <= 14) return 'warning'; // Yellow (8-14 days)
        return 'normal'; // Green (>14 days)
    }

    /**
     * Computed Attributes - Renewal badge color
     */
    public function getRenewalBadgeColorAttribute()
    {
        switch ($this->renewal_urgency) {
            case 'overdue':
                return 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300';
            case 'urgent':
                return 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300';
            case 'warning':
                return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300';
            case 'normal':
                return 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300';
            case 'paused':
                return 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }

    /**
     * Computed Attributes - Renewal badge emoji
     */
    public function getRenewalBadgeEmojiAttribute()
    {
        switch ($this->renewal_urgency) {
            case 'overdue':
                return '🚨';
            case 'urgent':
                return '🔴';
            case 'warning':
                return '🟡';
            case 'normal':
                return '🟢';
            case 'paused':
                return '⏸️';
            default:
                return '';
        }
    }

    /**
     * Computed Attributes - Renewal text
     */
    public function getRenewalTextAttribute()
    {
        // For paused/cancelled subscriptions, show status instead of renewal date
        if ($this->status === 'paused') {
            return __('Paused');
        }
        if ($this->status === 'cancelled') {
            return __('Cancelled');
        }

        $days = $this->days_until_renewal;

        if ($days === null) return __('Unknown');

        // For subscriptions with auto-renew disabled, show "Expires in X days"
        if (!$this->auto_renew) {
            if ($days < 0) {
                $daysExpired = abs($days);
                return trans_choice('Expired :count day ago|Expired :count days ago', $daysExpired, ['count' => $daysExpired]);
            }
            if ($days === 0) return __('Expires today');
            if ($days === 1) return __('Expires tomorrow');
            return trans_choice('Expires in :count day|Expires in :count days', $days, ['count' => $days]);
        }

        // For auto-renewing subscriptions, show "Renews in X days"
        if ($days < 0) {
            $daysOverdue = abs($days);
            return trans_choice('Overdue :count day|Overdue :count days', $daysOverdue, ['count' => $daysOverdue]);
        }
        if ($days === 0) return __('Renews today');
        if ($days === 1) return __('Renews tomorrow');
        return trans_choice('Renews in :count day|Renews in :count days', $days, ['count' => $days]);
    }

    /**
     * Computed Attributes - Billing cycle label
     */
    public function getBillingCycleLabelAttribute()
    {
        $labels = [
            'weekly' => __('Weekly'),
            'monthly' => __('Monthly'),
            'annual' => __('Annual'),
            'custom' => __('Custom'),
        ];

        return $labels[$this->billing_cycle] ?? ucfirst($this->billing_cycle);
    }

    /**
     * Scope - Search by vendor name
     */
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where('vendor_name', 'like', "%{$search}%");
        }
        return $query;
    }

    /**
     * Scope - Filter by status
     */
    public function scopeStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * Scope - Filter by billing cycle
     */
    public function scopeBillingCycle($query, $cycle)
    {
        if ($cycle) {
            return $query->where('billing_cycle', $cycle);
        }
        return $query;
    }

    /**
     * Scope - Filter by renewal range
     */
    public function scopeRenewalRange($query, $range)
    {
        if (!$range) return $query;

        $today = Carbon::now()->startOfDay();

        switch ($range) {
            case 'overdue':
                return $query->where('next_renewal_date', '<', $today);
            case 'urgent': // 0-7 days
                return $query->whereBetween('next_renewal_date', [$today, $today->copy()->addDays(7)]);
            case 'warning': // 8-14 days
                return $query->whereBetween('next_renewal_date', [$today->copy()->addDays(8), $today->copy()->addDays(14)]);
            case 'normal': // >14 days
                return $query->where('next_renewal_date', '>', $today->copy()->addDays(14));
            default:
                return $query;
        }
    }

    /**
     * Calculate next renewal date based on billing cycle
     * Delegated to SubscriptionCalculationService
     */
    public function calculateNextRenewal($fromDate = null)
    {
        $service = app(SubscriptionCalculationService::class);
        return $service->calculateNextRenewal($this, $fromDate ? Carbon::parse($fromDate) : null);
    }

    /**
     * Update renewal date and log the change
     * Delegated to SubscriptionCalculationService
     */
    public function updateRenewalDate($newDate, $reason = null)
    {
        $service = app(SubscriptionCalculationService::class);
        $service->updateRenewalDate($this, Carbon::parse($newDate), $reason);
        return $this;
    }

    /**
     * Auto-advance overdue renewals
     * Delegated to SubscriptionCalculationService
     */
    public function advanceOverdueRenewals()
    {
        $service = app(SubscriptionCalculationService::class);
        $service->advanceOverdueRenewals($this);
        return $this;
    }

    /**
     * Statistics for user's subscriptions
     * Optimized to use database-level aggregations instead of loading all records
     */
    public static function getStatistics()
    {
        // Single query for all status counts
        $counts = self::selectRaw("
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count,
            SUM(CASE WHEN status = 'paused' THEN 1 ELSE 0 END) as paused_count,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count
        ")->first();

        // Single query for cost calculations (active subscriptions only)
        $costs = self::where('status', 'active')
            ->selectRaw("
                SUM(CASE
                    WHEN billing_cycle = 'weekly' THEN price * 4.33
                    WHEN billing_cycle = 'monthly' THEN price
                    WHEN billing_cycle = 'annual' THEN price / 12
                    WHEN billing_cycle = 'custom' THEN (price / COALESCE(NULLIF(custom_days, 0), 30)) * 30
                    ELSE 0
                END) as monthly_cost,
                SUM(CASE
                    WHEN billing_cycle = 'weekly' THEN price * 52
                    WHEN billing_cycle = 'monthly' THEN price * 12
                    WHEN billing_cycle = 'annual' THEN price
                    WHEN billing_cycle = 'custom' THEN (price / COALESCE(NULLIF(custom_days, 0), 30)) * 365
                    ELSE 0
                END) as annual_cost
            ")->first();

        // Upcoming renewals (next 30 days)
        $upcomingRenewals = self::where('status', 'active')
            ->whereBetween('next_renewal_date', [
                Carbon::now()->startOfDay(),
                Carbon::now()->addDays(30)->endOfDay()
            ])
            ->count();

        return [
            'active' => (int) ($counts->active_count ?? 0),
            'paused' => (int) ($counts->paused_count ?? 0),
            'cancelled' => (int) ($counts->cancelled_count ?? 0),
            'monthly_cost' => round($costs->monthly_cost ?? 0, 2),
            'annual_cost' => round($costs->annual_cost ?? 0, 2),
            'upcoming_renewals' => $upcomingRenewals,
        ];
    }

}
