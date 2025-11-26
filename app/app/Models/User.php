<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'organization_id',
        'role',
        'phone',
        'status',
        'settings',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_recovery_codes' => 'encrypted:array',
            'settings' => 'array',
        ];
    }

    /**
     * Get the organization that owns the user
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the services this user offers with their rates
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'user_services')
            ->withPivot(['hourly_rate', 'currency', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Get the user service records
     */
    public function userServices(): HasMany
    {
        return $this->hasMany(UserService::class);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is manager
     */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Check if user can manage (admin or manager)
     */
    public function canManage(): bool
    {
        return in_array($this->role, ['admin', 'manager']);
    }

    /**
     * Check if 2FA is enabled and confirmed
     */
    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_secret) && !is_null($this->two_factor_confirmed_at);
    }

    /**
     * Get hourly rate for a specific service
     */
    public function getRateForService(Service $service): ?float
    {
        $userService = $this->userServices()
            ->where('service_id', $service->id)
            ->where('is_active', true)
            ->first();

        return $userService?->hourly_rate ?? $service->default_rate;
    }

    /**
     * Get all active services this user offers
     */
    public function getActiveServices()
    {
        return $this->services()
            ->wherePivot('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get a setting value by key
     */
    public function getSetting(string $key, $default = null)
    {
        $settings = $this->settings ?? [];
        return $settings[$key] ?? $default;
    }

    /**
     * Set a setting value
     */
    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
        $this->save();
    }

    /**
     * Get budget thresholds for financial dashboard
     */
    public function getBudgetThresholds(): array
    {
        return [
            'expense_budget_ron' => $this->getSetting('expense_budget_ron'),
            'expense_budget_eur' => $this->getSetting('expense_budget_eur'),
            'revenue_target_ron' => $this->getSetting('revenue_target_ron'),
            'revenue_target_eur' => $this->getSetting('revenue_target_eur'),
            'profit_margin_min' => $this->getSetting('profit_margin_min'),
        ];
    }

    /**
     * Save budget thresholds
     */
    public function saveBudgetThresholds(array $thresholds): void
    {
        $settings = $this->settings ?? [];
        foreach ($thresholds as $key => $value) {
            if (in_array($key, ['expense_budget_ron', 'expense_budget_eur', 'revenue_target_ron', 'revenue_target_eur', 'profit_margin_min'])) {
                $settings[$key] = $value ? (float) $value : null;
            }
        }
        $this->settings = $settings;
        $this->save();
    }
}
