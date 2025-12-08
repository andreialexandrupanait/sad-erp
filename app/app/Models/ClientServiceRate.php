<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientServiceRate extends Model
{
    protected $fillable = [
        'client_id',
        'service_id',
        'hourly_rate',
    ];

    protected $casts = [
        'client_id' => 'integer',
        'service_id' => 'integer',
        'hourly_rate' => 'decimal:2',
    ];

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    // Scopes
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeForService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }
}
