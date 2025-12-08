<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserService extends Model
{
    protected $fillable = [
        'user_id',
        'service_id',
        'hourly_rate',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function getFormattedRateAttribute(): string
    {
        return number_format($this->hourly_rate, 2, ',', '.') . ' ' . $this->currency . '/h';
    }
}
