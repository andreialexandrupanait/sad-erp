<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Organization extends Model
{
    use SoftDeletes;

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
     * Scope to get only active organizations
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
