<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TaskService extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'user_id',
        'name',
        'default_hourly_rate',
        'description',
        'is_active',
    ];

    protected $casts = [
        'organization_id' => 'integer',
        'user_id' => 'integer',
        'default_hourly_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($service) {
            if (Auth::check()) {
                $service->organization_id = $service->organization_id ?? Auth::user()->organization_id;
                $service->user_id = $service->user_id ?? Auth::id();
            }
        });

        static::addGlobalScope('user_scope', function (Builder $query) {
            if (Auth::check()) {
                $query->where('organization_id', Auth::user()->organization_id)
                      ->where('user_id', Auth::id());
            }
        });
    }

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'service_id');
    }

    public function clientRates()
    {
        return $this->hasMany(ClientServiceRate::class, 'service_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    // Helper methods
    public function getRateForClient($clientId)
    {
        $clientRate = $this->clientRates()
            ->where('client_id', $clientId)
            ->first();

        return $clientRate ? $clientRate->hourly_rate : $this->default_hourly_rate;
    }
}
