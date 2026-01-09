<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'organization_id',
        'endpoint',
        'endpoint_hash',
        'p256dh_key',
        'auth_key',
        'content_encoding',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($subscription) {
            if (!$subscription->endpoint_hash && $subscription->endpoint) {
                $subscription->endpoint_hash = hash('sha256', $subscription->endpoint);
            }
        });
        
        static::updating(function ($subscription) {
            if ($subscription->isDirty('endpoint')) {
                $subscription->endpoint_hash = hash('sha256', $subscription->endpoint);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public static function findByEndpoint(string $endpoint): ?self
    {
        return static::where('endpoint_hash', hash('sha256', $endpoint))->first();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }
}
