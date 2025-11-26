<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionLog extends Model
{
    use HasFactory;

    public $timestamps = false; // We use 'changed_at' instead

    protected $fillable = [
        'subscription_id',
        'organization_id',
        'old_renewal_date',
        'new_renewal_date',
        'change_reason',
        'changed_by_user_id',
        'changed_at',
    ];

    protected $casts = [
        'old_renewal_date' => 'date',
        'new_renewal_date' => 'date',
        'changed_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
