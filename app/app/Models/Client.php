<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Client extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $fillable = [
        'organization_id',
        'user_id',
        'created_by',
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
        'total_incomes',
        'notes',
        'order_index',
    ];

    protected $casts = [
        'vat_payer' => 'boolean',
        'total_incomes' => 'decimal:2',
        'order_index' => 'integer',
    ];

    /**
     * Boot function to automatically scope by organization
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically set organization_id and created_by when creating
        static::creating(function ($client) {
            // Normalize tax_id: convert empty or '-' to null for unique constraint
            if (empty($client->tax_id) || $client->tax_id === '-') {
                $client->tax_id = null;
            }

            if (auth()->check()) {
                if (empty($client->organization_id)) {
                    $client->organization_id = auth()->user()->organization_id;
                }
                if (empty($client->created_by)) {
                    $client->created_by = auth()->id();
                }
                // Keep user_id for backwards compatibility
                if (empty($client->user_id)) {
                    $client->user_id = auth()->id();
                }
            }

            // Auto-generate slug if not provided
            if (empty($client->slug)) {
                $client->slug = Str::slug($client->name);

                // Ensure unique slug within organization
                $originalSlug = $client->slug;
                $counter = 1;
                while (static::withoutGlobalScopes()
                    ->where('organization_id', $client->organization_id)
                    ->where('slug', $client->slug)
                    ->exists()) {
                    $client->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });

        // Normalize tax_id on update as well
        static::updating(function ($client) {
            if (empty($client->tax_id) || $client->tax_id === '-') {
                $client->tax_id = null;
            }
        });

        // Automatically scope all queries by organization (RLS)
        static::addGlobalScope('organization', function (Builder $builder) {
            if (auth()->check() && auth()->user()->organization_id) {
                $builder->where('clients.organization_id', auth()->user()->organization_id);
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
     * Get the user that owns the client (legacy, kept for compatibility)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who created this client
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the status of the client
     */
    public function status()
    {
        return $this->belongsTo(SettingOption::class, 'status_id');
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
        return $this->hasMany(Credential::class);
    }

    /**
     * Get all revenues for this client
     */
    public function revenues()
    {
        return $this->hasMany(FinancialRevenue::class);
    }

    /**
     * Get all domains for this client
     */
    public function domains()
    {
        return $this->hasMany(Domain::class);
    }

    /**
     * Get all notes for this client
     * Named clientNotes to avoid conflict with 'notes' database column
     */
    public function clientNotes()
    {
        return $this->hasMany(ClientNote::class)->latest();
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
     * Uses FULLTEXT search for indexed columns on MySQL (10-100x faster than LIKE)
     * Falls back to LIKE search for SQLite compatibility
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $driver = $q->getConnection()->getDriverName();

            if ($driver === 'mysql') {
                // FULLTEXT search on indexed columns (name, company_name, email)
                // BOOLEAN MODE allows: +required -excluded "exact phrase"
                $q->whereRaw('MATCH(name, company_name, email) AGAINST(? IN BOOLEAN MODE)', [$search]);
            } else {
                // SQLite fallback: use LIKE for all searchable columns
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            }

            // LIKE search for non-indexed fields (works on all databases)
            $q->orWhere('tax_id', 'like', "%{$search}%")
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
        return $this->name ?: $this->company_name;
    }

    /**
     * Get full address (alias for address field for compatibility)
     */
    public function getFullAddressAttribute()
    {
        return $this->address;
    }

    /**
     * Get fiscal code (alias for tax_id for compatibility)
     */
    public function getFiscalCodeAttribute()
    {
        return $this->tax_id;
    }

    /**
     * Get the route key for the model
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Get total revenue for this client.
     * Uses the cached total_incomes column which is synced by FinancialRevenueObserver.
     * This avoids N+1 queries when accessing total_revenue in lists.
     */
    public function getTotalRevenueAttribute()
    {
        return $this->total_incomes ?? 0;
    }

    /**
     * Get count of active domains.
     * Note: Use withCount('domains') or eager load for lists to avoid N+1.
     */
    public function getActiveDomainsCountAttribute()
    {
        // Check if domains are already loaded to avoid N+1
        if ($this->relationLoaded('domains')) {
            return $this->domains->where('status', 'active')->count();
        }
        return $this->domains()->where('status', 'active')->count();
    }

    /**
     * Get count of access credentials.
     * Note: Use withCount('accessCredentials') for lists to avoid N+1.
     */
    public function getCredentialsCountAttribute()
    {
        // Check if credentials are already loaded to avoid N+1
        if ($this->relationLoaded('accessCredentials')) {
            return $this->accessCredentials->count();
        }
        return $this->accessCredentials()->count();
    }
}
