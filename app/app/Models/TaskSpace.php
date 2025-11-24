<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TaskSpace extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'user_id',
        'name',
        'icon',
        'color',
        'position',
        'clickup_metadata',
    ];

    protected $casts = [
        'position' => 'integer',
        'clickup_metadata' => 'array',
    ];

    protected static function booted()
    {
        parent::booted();

        // Auto-fill organization_id and user_id
        static::creating(function ($space) {
            if (Auth::check()) {
                $space->organization_id = $space->organization_id ?? Auth::user()->organization_id;
                $space->user_id = $space->user_id ?? Auth::id();
            }
        });

        // Global scope for organization and user isolation
        static::addGlobalScope('user_scope', function (Builder $query) {
            if (Auth::check()) {
                $query->where('organization_id', Auth::user()->organization_id)
                      ->where('user_id', Auth::id());
            }
        });
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

    public function folders()
    {
        return $this->hasMany(TaskFolder::class, 'space_id')->orderBy('position');
    }

    // Scopes
    public function scopeOrdered($query)
    {
        return $query->orderBy('position')->orderBy('name');
    }
}
