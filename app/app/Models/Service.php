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
        'color_class',
        'is_active',
        'sort_order',
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

        return number_format($this->default_rate, 2, ',', '.') . ' ' . $this->currency . '/h';
    }

    public function getBadgeClassAttribute(): string
    {
        if (!$this->color_class) {
            return 'bg-slate-100 text-slate-700 border-slate-300';
        }

        $colorMap = [
            'slate' => 'bg-slate-100 text-slate-700 border-slate-300',
            'blue' => 'bg-blue-100 text-blue-700 border-blue-300',
            'green' => 'bg-green-100 text-green-700 border-green-300',
            'red' => 'bg-red-100 text-red-700 border-red-300',
            'yellow' => 'bg-yellow-100 text-yellow-700 border-yellow-300',
            'purple' => 'bg-purple-100 text-purple-700 border-purple-300',
            'orange' => 'bg-orange-100 text-orange-700 border-orange-300',
            'pink' => 'bg-pink-100 text-pink-700 border-pink-300',
            'cyan' => 'bg-cyan-100 text-cyan-700 border-cyan-300',
            'amber' => 'bg-amber-100 text-amber-700 border-amber-300',
        ];

        return $colorMap[$this->color_class] ?? $colorMap['slate'];
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
