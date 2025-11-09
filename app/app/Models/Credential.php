<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Crypt;

class Credential extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'access_credentials';

    protected $fillable = [
        'organization_id',
        'client_id',
        'platform',
        'username',
        'password',
        'url',
        'notes',
        'last_accessed_at',
        'access_count',
    ];

    protected $casts = [
        'last_accessed_at' => 'datetime',
        'access_count' => 'integer',
    ];

    // Platform options
    public const PLATFORMS = [
        'Facebook' => 'Facebook',
        'Google Ads' => 'Google Ads',
        'Google Analytics' => 'Google Analytics',
        'WordPress' => 'WordPress',
        'LinkedIn' => 'LinkedIn',
        'Instagram' => 'Instagram',
        'Twitter/X' => 'Twitter/X',
        'TikTok' => 'TikTok',
        'YouTube' => 'YouTube',
        'Mailchimp' => 'Mailchimp',
        'AWS' => 'AWS',
        'Azure' => 'Azure',
        'cPanel' => 'cPanel',
        'FTP' => 'FTP',
        'SSH' => 'SSH',
        'Other' => 'Other',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically set organization_id when creating
        static::creating(function ($credential) {
            if (auth()->check() && empty($credential->organization_id)) {
                $credential->organization_id = auth()->user()->organization_id;
            }
        });

        // Global scope to filter by organization
        static::addGlobalScope('organization', function (Builder $builder) {
            if (auth()->check() && auth()->user()->organization_id) {
                $builder->where('organization_id', auth()->user()->organization_id);
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

    public function client()
    {
        return $this->belongsTo(Client::class);
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
     * Search scope
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('platform', 'like', "%{$search}%")
              ->orWhere('username', 'like', "%{$search}%")
              ->orWhere('url', 'like', "%{$search}%")
              ->orWhereHas('client', function ($q) use ($search) {
                  $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Filter by platform
     */
    public function scopePlatform($query, $platform)
    {
        if (!empty($platform)) {
            return $query->where('platform', $platform);
        }
        return $query;
    }

    /**
     * Filter by client
     */
    public function scopeClient($query, $clientId)
    {
        if (!empty($clientId)) {
            return $query->where('client_id', $clientId);
        }
        return $query;
    }

    /**
     * Track access
     */
    public function trackAccess()
    {
        $this->increment('access_count');
        $this->update(['last_accessed_at' => now()]);
    }

    /**
     * Get display name
     */
    public function getDisplayNameAttribute()
    {
        return $this->client->display_name . ' - ' . $this->platform;
    }
}
