<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'company',
        'email',
        'phone',
        'tax_id',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'website',
        'notes',
        'status',
    ];

    /**
     * Boot function to automatically scope by organization
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically set organization_id when creating
        static::creating(function ($client) {
            if (auth()->check() && empty($client->organization_id)) {
                $client->organization_id = auth()->user()->organization_id;
            }
        });

        // Automatically scope all queries by organization
        static::addGlobalScope('organization', function (Builder $builder) {
            if (auth()->check() && auth()->user()->organization_id) {
                $builder->where('organization_id', auth()->user()->organization_id);
            }
        });
    }

    /**
     * Get the organization that owns the client
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get all offers for this client
     */
    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    /**
     * Get all contracts for this client
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Get all subscriptions for this client
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get all access credentials for this client
     */
    public function accessCredentials()
    {
        return $this->hasMany(AccessCredential::class);
    }

    /**
     * Get all files for this client (polymorphic)
     */
    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    /**
     * Get all revenues for this client
     */
    public function revenues()
    {
        return $this->hasMany(Revenue::class);
    }

    /**
     * Scope to get only active clients
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to search clients by name, company, or email
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('company', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    /**
     * Get full name with company
     */
    public function getFullNameAttribute()
    {
        return $this->company ? "{$this->name} ({$this->company})" : $this->name;
    }

    /**
     * Get display name for lists
     */
    public function getDisplayNameAttribute()
    {
        return $this->company ?: $this->name;
    }
}
