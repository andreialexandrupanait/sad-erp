<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class BankingCredential extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'user_id',
        'bank_name',
        'account_iban',
        'account_name',
        'currency',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'refresh_token_expires_at',
        'consent_id',
        'consent_granted_at',
        'consent_expires_at',
        'consent_scopes',
        'consent_status',
        'last_sync_at',
        'last_successful_sync_at',
        'sync_from_date',
        'consecutive_failures',
        'status',
        'error_message',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'refresh_token_expires_at' => 'datetime',
        'consent_granted_at' => 'datetime',
        'consent_expires_at' => 'datetime',
        'consent_scopes' => 'array',
        'last_sync_at' => 'datetime',
        'last_successful_sync_at' => 'datetime',
        'sync_from_date' => 'date',
        'consecutive_failures' => 'integer',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    protected static function booted()
    {
        // Auto-fill organization_id
        static::creating(function ($credential) {
            if (Auth::check()) {
                $credential->organization_id = $credential->organization_id ?? Auth::user()->organization_id;
                $credential->user_id = $credential->user_id ?? Auth::id();
            }
        });

        // Global scope for organization isolation
        static::addGlobalScope('organization', function (Builder $query) {
            if (Auth::check() && Auth::user()->organization_id) {
                $query->where('banking_credentials.organization_id', Auth::user()->organization_id);
            }
        });
    }

    // Accessors & Mutators
    public function setAccessTokenAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['access_token'] = Crypt::encryptString($value);
        }
    }

    public function getAccessTokenAttribute($value)
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

    public function setRefreshTokenAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['refresh_token'] = Crypt::encryptString($value);
        }
    }

    public function getRefreshTokenAttribute($value)
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

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(BankTransaction::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeWithValidConsent($query)
    {
        return $query->where('consent_status', 'active')
                     ->where('consent_expires_at', '>', now());
    }

    public function scopeNeedingRenewal($query)
    {
        return $query->where('consent_status', 'active')
                     ->where('consent_expires_at', '<=', now()->addDays(7));
    }

    // Accessors
    public function getIsTokenValidAttribute()
    {
        return $this->token_expires_at && $this->token_expires_at->isFuture();
    }

    public function getIsConsentValidAttribute()
    {
        return $this->consent_status === 'active'
               && $this->consent_expires_at
               && $this->consent_expires_at->isFuture();
    }

    public function getNeedsConsentRenewalAttribute()
    {
        return $this->consent_expires_at
               && $this->consent_expires_at->lte(now()->addDays(7));
    }

    // Methods
    public function updateTokens(string $accessToken, string $refreshToken, int $expiresIn)
    {
        $this->update([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_expires_at' => now()->addSeconds($expiresIn),
            'consecutive_failures' => 0,
            'status' => 'active',
            'error_message' => null,
        ]);
    }

    public function markSyncSuccess()
    {
        $this->update([
            'last_sync_at' => now(),
            'last_successful_sync_at' => now(),
            'consecutive_failures' => 0,
            'status' => 'active',
            'error_message' => null,
        ]);
    }

    public function markSyncFailure(string $errorMessage)
    {
        $this->increment('consecutive_failures');
        $this->update([
            'last_sync_at' => now(),
            'error_message' => $errorMessage,
            'status' => $this->consecutive_failures >= 3 ? 'error' : 'active',
        ]);
    }

    public function revokeConsent()
    {
        $this->update([
            'consent_status' => 'revoked',
            'access_token' => null,
            'refresh_token' => null,
            'status' => 'inactive',
        ]);
    }
}
