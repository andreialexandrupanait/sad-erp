<?php

namespace App\Models;

use App\Traits\EncryptsPasswords;
use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Credential extends Model
{
    use HasFactory, SoftDeletes, EncryptsPasswords, HasOrganization;

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

    // Organization scoping handled by HasOrganization trait
    // Password encryption handled by EncryptsPasswords trait

    /**
     * Relationships
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Password encryption/decryption handled by EncryptsPasswords trait

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
