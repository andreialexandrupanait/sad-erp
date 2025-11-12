<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'vendor_name',
        'price',
        'billing_cycle',
        'custom_days',
        'start_date',
        'next_renewal_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'start_date' => 'date',
        'next_renewal_date' => 'date',
        'custom_days' => 'integer',
    ];

    /**
     * Boot method - apply global scopes and auto-assign user_id
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-assign user_id when creating
        static::creating(function ($subscription) {
            if (auth()->check() && empty($subscription->user_id)) {
                $subscription->user_id = auth()->id();
            }
        });

        // User-based scoping: Only show subscriptions owned by current user
        static::addGlobalScope('user', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('user_id', auth()->id());
            }
        });
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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
        if (!$this->next_renewal_date) return null;
        return Carbon::now()->startOfDay()->diffInDays($this->next_renewal_date->startOfDay(), false);
    }

    /**
     * Computed Attributes - Renewal urgency (red, yellow, green)
     */
    public function getRenewalUrgencyAttribute()
    {
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
            default:
                return '';
        }
    }

    /**
     * Computed Attributes - Renewal text
     */
    public function getRenewalTextAttribute()
    {
        $days = $this->days_until_renewal;

        if ($days === null) return 'Unknown';
        if ($days < 0) {
            $daysOverdue = abs($days);
            return "Overdue by {$daysOverdue} " . ($daysOverdue === 1 ? 'day' : 'days');
        }
        if ($days === 0) return 'Renews today';
        if ($days === 1) return 'Renews tomorrow';
        return "Renews in {$days} days";
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
     */
    public function calculateNextRenewal($fromDate = null)
    {
        $baseDate = $fromDate ? Carbon::parse($fromDate) : $this->next_renewal_date;

        switch ($this->billing_cycle) {
            case 'monthly':
                return $baseDate->addMonth();
            case 'annual':
                return $baseDate->addYear();
            case 'custom':
                return $baseDate->addDays($this->custom_days ?? 30);
            default:
                return $baseDate->addMonth();
        }
    }

    /**
     * Update renewal date and log the change
     */
    public function updateRenewalDate($newDate, $reason = 'Manual update')
    {
        $oldDate = $this->next_renewal_date;

        // Update the subscription
        $this->next_renewal_date = $newDate;
        $this->save();

        // Create audit log if SubscriptionLog model exists
        if (class_exists(SubscriptionLog::class)) {
            SubscriptionLog::create([
                'subscription_id' => $this->id,
                'user_id' => $this->user_id,
                'old_renewal_date' => $oldDate,
                'new_renewal_date' => $newDate,
                'change_reason' => $reason,
                'changed_by_user_id' => auth()->id(),
                'changed_at' => now(),
            ]);
        }

        return $this;
    }

    /**
     * Auto-advance overdue renewals
     */
    public function advanceOverdueRenewals()
    {
        $today = Carbon::now()->startOfDay();

        // Keep advancing until next_renewal_date is in the future
        while ($this->next_renewal_date->startOfDay()->lt($today)) {
            $oldDate = $this->next_renewal_date;
            $newDate = $this->calculateNextRenewal();

            $this->next_renewal_date = $newDate;
            $this->save();

            // Log each advancement if SubscriptionLog model exists
            if (class_exists(SubscriptionLog::class)) {
                SubscriptionLog::create([
                    'subscription_id' => $this->id,
                    'user_id' => $this->user_id,
                    'old_renewal_date' => $oldDate,
                    'new_renewal_date' => $newDate,
                    'change_reason' => 'Auto-advanced overdue renewal',
                    'changed_by_user_id' => null, // System action
                    'changed_at' => now(),
                ]);
            }
        }

        return $this;
    }

    /**
     * Statistics for user's subscriptions
     */
    public static function getStatistics()
    {
        $activeCount = self::where('status', 'active')->count();
        $pausedCount = self::where('status', 'paused')->count();
        $cancelledCount = self::where('status', 'cancelled')->count();

        // Monthly cost projection
        $monthlyCost = self::where('status', 'active')
            ->get()
            ->sum(function ($subscription) {
                switch ($subscription->billing_cycle) {
                    case 'monthly':
                        return $subscription->price;
                    case 'annual':
                        return $subscription->price / 12;
                    case 'custom':
                        $daysPerMonth = 30;
                        $customDays = $subscription->custom_days ?? 30;
                        return ($subscription->price / $customDays) * $daysPerMonth;
                    default:
                        return 0;
                }
            });

        // Annual cost projection
        $annualCost = self::where('status', 'active')
            ->get()
            ->sum(function ($subscription) {
                switch ($subscription->billing_cycle) {
                    case 'monthly':
                        return $subscription->price * 12;
                    case 'annual':
                        return $subscription->price;
                    case 'custom':
                        $daysPerYear = 365;
                        $customDays = $subscription->custom_days ?? 30;
                        return ($subscription->price / $customDays) * $daysPerYear;
                    default:
                        return 0;
                }
            });

        // Upcoming renewals (next 30 days)
        $upcomingRenewals = self::where('status', 'active')
            ->whereBetween('next_renewal_date', [
                Carbon::now()->startOfDay(),
                Carbon::now()->addDays(30)->endOfDay()
            ])
            ->count();

        return [
            'active' => $activeCount,
            'paused' => $pausedCount,
            'cancelled' => $cancelledCount,
            'monthly_cost' => round($monthlyCost, 2),
            'annual_cost' => round($annualCost, 2),
            'upcoming_renewals' => $upcomingRenewals,
        ];
    }

}
