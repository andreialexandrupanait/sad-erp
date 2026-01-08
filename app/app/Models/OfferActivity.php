<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferActivity extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'offer_id',
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Boot function
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($activity) {
            $activity->created_at = $activity->created_at ?? now();
        });
    }

    /**
     * Relationships
     */
    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get action label
     */
    public function getActionLabelAttribute()
    {
        $labels = [
            'created' => __('Created'),
            'updated' => __('Updated'),
            'sent' => __('Sent to client'),
            'viewed' => __('Viewed by client'),
            'accepted' => __('Accepted'),
            'rejected' => __('Rejected'),
            'expired' => __('Expired'),
            'converted' => __('Converted to contract'),
        ];

        return $labels[$this->action] ?? ucfirst($this->action);
    }

    /**
     * Get action icon
     */
    public function getActionIconAttribute()
    {
        $icons = [
            'created' => 'document-plus',
            'updated' => 'pencil',
            'sent' => 'paper-airplane',
            'viewed' => 'eye',
            'accepted' => 'check-circle',
            'rejected' => 'x-circle',
            'expired' => 'clock',
            'converted' => 'arrow-right-circle',
        ];

        return $icons[$this->action] ?? 'information-circle';
    }

    /**
     * Get action color
     */
    public function getActionColorAttribute()
    {
        $colors = [
            'created' => 'gray',
            'updated' => 'blue',
            'sent' => 'blue',
            'viewed' => 'purple',
            'accepted' => 'green',
            'rejected' => 'red',
            'expired' => 'yellow',
            'converted' => 'green',
        ];

        return $colors[$this->action] ?? 'gray';
    }

    /**
     * Get performer name
     */
    public function getPerformerNameAttribute()
    {
        if ($this->user) {
            return $this->user->name;
        }

        return __('Client');
    }

    /**
     * Get formatted description
     */
    public function getDescriptionAttribute()
    {
        $performer = $this->performer_name;
        $metadata = $this->metadata ?? [];

        switch ($this->action) {
            case 'sent':
                return __(':name sent the offer to the client', ['name' => $performer]);
            case 'viewed':
                $ip = $metadata['ip_address'] ?? __('unknown IP');
                return __('Client viewed the offer from :ip', ['ip' => $ip]);
            case 'accepted':
                return __('Client accepted the offer');
            case 'rejected':
                $reason = $metadata['reason'] ?? null;
                return $reason
                    ? __('Client rejected the offer: :reason', ['reason' => $reason])
                    : __('Client rejected the offer');
            default:
                return __(':name :action the offer', ['name' => $performer, 'action' => strtolower($this->action_label)]);
        }
    }
}
