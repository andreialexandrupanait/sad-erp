<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Service extends Model implements Sortable
{
    use SoftDeletes, SortableTrait;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'default_rate',
        'currency',
        'unit',
        'is_active',
        'sort_order',
    ];

    public const UNITS = [
        'ora' => 'oră',
        'zi' => 'zi',
        'luna' => 'lună',
        'buc' => 'bucată',
        'proiect' => 'proiect',
    ];

    protected $casts = [
        'default_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public $sortable = [
        'order_column_name' => 'sort_order',
        'sort_when_creating' => true,
    ];

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($service) {
            if (Auth::check() && Auth::user()->organization_id) {
                $service->organization_id = $service->organization_id ?? Auth::user()->organization_id;
            }
        });

        static::addGlobalScope('organization', function (Builder $query) {
            if (Auth::check() && Auth::user()->organization_id) {
                $query->where('organization_id', Auth::user()->organization_id);
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_services')
            ->withPivot(['hourly_rate', 'currency', 'is_active'])
            ->withTimestamps();
    }

    public function userServices(): HasMany
    {
        return $this->hasMany(UserService::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function getFormattedRateAttribute(): string
    {
        if (!$this->default_rate) {
            return '-';
        }

        $unitLabel = self::UNITS[$this->unit] ?? $this->unit;
        return number_format($this->default_rate, 2, ',', '.') . ' ' . $this->currency . '/' . $unitLabel;
    }

    public function getUnitLabelAttribute(): string
    {
        return self::UNITS[$this->unit] ?? $this->unit;
    }

    public function getRateForUser(User $user): ?float
    {
        $userService = $this->userServices()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        return $userService?->hourly_rate ?? $this->default_rate;
    }

    public function isOfferedBy(User $user): bool
    {
        return $this->userServices()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();
    }
}
