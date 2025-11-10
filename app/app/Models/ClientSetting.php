<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ClientSetting extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'color',
        'color_background',
        'color_text',
        'order_index',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order_index' => 'integer',
    ];

    /**
     * Boot function to automatically scope by user
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically set user_id when creating
        static::creating(function ($setting) {
            if (auth()->check() && empty($setting->user_id)) {
                $setting->user_id = auth()->id();
            }
        });

        // Automatically scope all queries by user
        static::addGlobalScope('user', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('user_id', auth()->id());
            }
        });
    }

    /**
     * Get the user that owns the setting
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all clients with this status
     */
    public function clients()
    {
        return $this->hasMany(Client::class, 'status_id');
    }

    /**
     * Scope to get only active settings
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by index
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index');
    }
}
