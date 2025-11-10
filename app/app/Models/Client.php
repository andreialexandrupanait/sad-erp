<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'status_id',
        'name',
        'company_name',
        'slug',
        'tax_id',
        'registration_number',
        'contact_person',
        'email',
        'phone',
        'address',
        'vat_payer',
        'notes',
        'order_index',
    ];

    protected $casts = [
        'vat_payer' => 'boolean',
        'order_index' => 'integer',
    ];

    /**
     * Boot function to automatically scope by user
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically set user_id when creating
        static::creating(function ($client) {
            if (auth()->check() && empty($client->user_id)) {
                $client->user_id = auth()->id();
            }

            // Auto-generate slug if not provided
            if (empty($client->slug)) {
                $client->slug = Str::slug($client->name);

                // Ensure unique slug
                $originalSlug = $client->slug;
                $counter = 1;
                while (static::where('slug', $client->slug)->exists()) {
                    $client->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });

        // Automatically scope all queries by user (RLS)
        static::addGlobalScope('user', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('user_id', auth()->id());
            }
        });
    }

    /**
     * Get the user that owns the client
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the status of the client
     */
    public function status()
    {
        return $this->belongsTo(ClientSetting::class, 'status_id');
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
     * Get all domains for this client
     */
    public function domains()
    {
        return $this->hasMany(Domain::class);
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, $statusId)
    {
        return $query->where('status_id', $statusId);
    }

    /**
     * Scope to search clients by name, company, tax_id or email
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('company_name', 'like', "%{$search}%")
              ->orWhere('tax_id', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('contact_person', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to order by custom order index
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index')->orderBy('name');
    }

    /**
     * Get full name with company
     */
    public function getFullNameAttribute()
    {
        return $this->company_name ? "{$this->name} ({$this->company_name})" : $this->name;
    }

    /**
     * Get display name for lists
     */
    public function getDisplayNameAttribute()
    {
        return $this->company_name ?: $this->name;
    }

    /**
     * Get the route key for the model
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Calculate total revenue for this client
     */
    public function getTotalRevenueAttribute()
    {
        return $this->revenues()->sum('amount');
    }

    /**
     * Get count of active domains
     */
    public function getActiveDomainsCountAttribute()
    {
        return $this->domains()->where('status', 'active')->count();
    }

    /**
     * Get count of access credentials
     */
    public function getCredentialsCountAttribute()
    {
        return $this->accessCredentials()->count();
    }
}
