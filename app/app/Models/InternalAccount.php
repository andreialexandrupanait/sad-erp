<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Crypt;

class InternalAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'user_id',
        'nume_cont_aplicatie',
        'platforma',
        'url',
        'username',
        'password',
        'accesibil_echipei',
        'notes',
    ];

    protected $casts = [
        'accesibil_echipei' => 'boolean',
    ];

    // Platform options - internal business tools and services
    public const PLATFORMS = [
        'Bank Account' => 'Bank Account',
        'Payment Gateway' => 'Payment Gateway',
        'Accounting Software' => 'Accounting Software',
        'CRM System' => 'CRM System',
        'Email Service' => 'Email Service',
        'Cloud Storage' => 'Cloud Storage',
        'Domain Registrar' => 'Domain Registrar',
        'Hosting Provider' => 'Hosting Provider',
        'SSL Certificate' => 'SSL Certificate',
        'Analytics Tool' => 'Analytics Tool',
        'Project Management' => 'Project Management',
        'HR Software' => 'HR Software',
        'Invoicing System' => 'Invoicing System',
        'API Service' => 'API Service',
        'Database Service' => 'Database Service',
        'Backup Service' => 'Backup Service',
        'Security Tool' => 'Security Tool',
        'Communication Tool' => 'Communication Tool',
        'Design Software' => 'Design Software',
        'Development Tool' => 'Development Tool',
        'Other' => 'Other',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically set organization_id and user_id when creating
        static::creating(function ($account) {
            if (auth()->check()) {
                if (empty($account->organization_id)) {
                    $account->organization_id = auth()->user()->organization_id;
                }
                if (empty($account->user_id)) {
                    $account->user_id = auth()->id();
                }
            }
        });

        // Global scope: Show only user's own accounts OR team-accessible accounts
        static::addGlobalScope('accessible', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where(function ($query) {
                    $query->where('user_id', auth()->id())
                          ->orWhere('accesibil_echipei', true);
                })
                ->where('organization_id', auth()->user()->organization_id);
            }
        });
    }

    /**
     * Relationships
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Encrypt password before saving
     */
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = Crypt::encryptString($value);
        }
    }

    /**
     * Decrypt password when retrieving
     */
    public function getPasswordAttribute($value)
    {
        if (!empty($value)) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Get masked password (e.g., "***********")
     */
    public function getMaskedPasswordAttribute()
    {
        if (!empty($this->attributes['password'])) {
            return str_repeat('•', 12);
        }
        return '';
    }

    /**
     * Check if current user is the owner
     */
    public function isOwner()
    {
        return auth()->check() && $this->user_id === auth()->id();
    }

    /**
     * Check if account is accessible to current user
     */
    public function isAccessible()
    {
        if (!auth()->check()) {
            return false;
        }

        return $this->user_id === auth()->id() || $this->accesibil_echipei;
    }

    /**
     * Search scope
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nume_cont_aplicatie', 'like', "%{$search}%")
              ->orWhere('platforma', 'like', "%{$search}%")
              ->orWhere('username', 'like', "%{$search}%")
              ->orWhere('url', 'like', "%{$search}%");
        });
    }

    /**
     * Filter by platform
     */
    public function scopePlatform($query, $platform)
    {
        if (!empty($platform)) {
            return $query->where('platforma', $platform);
        }
        return $query;
    }

    /**
     * Filter by team accessibility
     */
    public function scopeTeamAccessible($query, $teamAccessible = null)
    {
        if ($teamAccessible !== null) {
            return $query->where('accesibil_echipei', (bool) $teamAccessible);
        }
        return $query;
    }

    /**
     * Filter by ownership (my accounts only)
     */
    public function scopeOwnedByMe($query)
    {
        if (auth()->check()) {
            return $query->where('user_id', auth()->id());
        }
        return $query;
    }

    /**
     * Get display name with platform
     */
    public function getDisplayNameAttribute()
    {
        return $this->nume_cont_aplicatie . ' (' . $this->platforma . ')';
    }

    /**
     * Get ownership status text
     */
    public function getOwnershipStatusAttribute()
    {
        if ($this->isOwner()) {
            return 'Your Account';
        }
        if ($this->accesibil_echipei) {
            return 'Team Account (by ' . $this->user->name . ')';
        }
        return 'Unknown';
    }
}
