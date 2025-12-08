<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

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
        'last_login_at',
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
            'last_login_at' => 'datetime',
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
     * Get the user's module permissions
     */
    public function modulePermissions(): HasMany
    {
        return $this->hasMany(UserModulePermission::class);
    }

    /**
     * Check if user is super admin (god mode)
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Check if user is organization admin
     */
    public function isOrgAdmin(): bool
    {
        return $this->role === 'admin';
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

    /**
     * Check if user can perform an action on a module.
     * Uses cached permissions for performance.
     */
    public function canAccessModule(string $moduleSlug, string $action = 'view'): bool
    {
        // Super admin bypasses all checks
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Org admin has full access within their org
        if ($this->isOrgAdmin()) {
            return true;
        }

        // Get cached permissions
        $permissions = $this->getCachedModulePermissions();

        if (!isset($permissions[$moduleSlug])) {
            // Fall back to role defaults
            return $this->getRoleDefaultPermission($moduleSlug, $action);
        }

        $permission = $permissions[$moduleSlug];

        return match ($action) {
            'view' => $permission['can_view'] ?? false,
            'create' => $permission['can_create'] ?? false,
            'update' => $permission['can_update'] ?? false,
            'delete' => $permission['can_delete'] ?? false,
            'export' => $permission['can_export'] ?? false,
            default => false,
        };
    }

    /**
     * Get all module permissions for user (cached).
     */
    public function getCachedModulePermissions(): array
    {
        return Cache::remember(
            "user:{$this->id}:module_permissions",
            3600,
            function () {
                return $this->modulePermissions()
                    ->with('module')
                    ->get()
                    ->filter(fn ($p) => $p->module !== null)
                    ->keyBy(fn ($p) => $p->module->slug)
                    ->map(fn ($p) => [
                        'can_view' => $p->can_view,
                        'can_create' => $p->can_create,
                        'can_update' => $p->can_update,
                        'can_delete' => $p->can_delete,
                        'can_export' => $p->can_export,
                    ])
                    ->toArray();
            }
        );
    }

    /**
     * Get default permission for role (cached).
     */
    protected function getRoleDefaultPermission(string $moduleSlug, string $action): bool
    {
        $defaults = RoleModuleDefault::getForRole($this->role ?? 'user');

        if (!isset($defaults[$moduleSlug])) {
            return false;
        }

        return $defaults[$moduleSlug]["can_{$action}"] ?? false;
    }

    /**
     * Clear permission cache when permissions change.
     */
    public function clearPermissionCache(): void
    {
        Cache::forget("user:{$this->id}:module_permissions");
    }

    /**
     * Get modules user can access (for sidebar).
     */
    public function getAccessibleModules(): Collection
    {
        if ($this->isSuperAdmin() || $this->isOrgAdmin()) {
            return Module::getAllCached();
        }

        $permissions = $this->getCachedModulePermissions();

        return Module::getAllCached()->filter(function ($module) use ($permissions) {
            if (isset($permissions[$module->slug])) {
                return $permissions[$module->slug]['can_view'];
            }
            return $this->getRoleDefaultPermission($module->slug, 'view');
        });
    }

    /**
     * Get detailed permission info for a module.
     */
    public function getModulePermissions(string $moduleSlug): array
    {
        if ($this->isSuperAdmin() || $this->isOrgAdmin()) {
            return [
                'can_view' => true,
                'can_create' => true,
                'can_update' => true,
                'can_delete' => true,
                'can_export' => true,
            ];
        }

        $permissions = $this->getCachedModulePermissions();

        if (isset($permissions[$moduleSlug])) {
            return $permissions[$moduleSlug];
        }

        $defaults = RoleModuleDefault::getForRole($this->role ?? 'user');

        return $defaults[$moduleSlug] ?? [
            'can_view' => false,
            'can_create' => false,
            'can_update' => false,
            'can_delete' => false,
            'can_export' => false,
        ];
    }
}
