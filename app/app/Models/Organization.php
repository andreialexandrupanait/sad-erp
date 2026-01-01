<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'email',
        'phone',
        'address',
        'tax_id',
        'billing_email',
        'settings',
        'status',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Keys within settings that should be encrypted.
     */
    protected static array $sensitiveSettingsKeys = [
        'smartbill.token',
        'smartbill.username',
    ];

    /**
     * Get Smartbill settings with decrypted sensitive values.
     */
    public function getSmartbillSettings(): array
    {
        $settings = $this->settings['smartbill'] ?? [];

        if (!empty($settings['token'])) {
            try {
                $settings['token'] = Crypt::decryptString($settings['token']);
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                // Legacy unencrypted value, use as-is
            }
        }

        return $settings;
    }

    /**
     * Set Smartbill settings with encrypted sensitive values.
     */
    public function setSmartbillSettings(array $smartbillSettings): void
    {
        // Encrypt the token before storing
        if (!empty($smartbillSettings['token'])) {
            $smartbillSettings['token'] = Crypt::encryptString($smartbillSettings['token']);
        }

        $settings = $this->settings ?? [];
        $settings['smartbill'] = $smartbillSettings;
        $this->settings = $settings;
    }

    /**
     * Boot function to auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($organization) {
            if (empty($organization->slug)) {
                $organization->slug = Str::slug($organization->name);
            }
        });
    }

    /**
     * Get all users belonging to this organization
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all clients belonging to this organization
     */
    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Get all expenses belonging to this organization
     */
    public function expenses()
    {
        return $this->hasMany(FinancialExpense::class);
    }

    /**
     * Get all revenues belonging to this organization
     */
    public function revenues()
    {
        return $this->hasMany(FinancialRevenue::class);
    }

    /**
     * Get all subscriptions belonging to this organization
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get all services belonging to this organization
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get all domains belonging to this organization
     */
    public function domains()
    {
        return $this->hasMany(Domain::class);
    }

    /**
     * Get all offers belonging to this organization
     */
    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    /**
     * Get all contracts belonging to this organization
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Get all credentials belonging to this organization
     */
    public function credentials()
    {
        return $this->hasMany(Credential::class);
    }

    /**
     * Get all internal accounts belonging to this organization
     */
    public function internalAccounts()
    {
        return $this->hasMany(InternalAccount::class);
    }

    /**
     * Scope to get only active organizations
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
