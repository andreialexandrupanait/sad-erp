<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TaskFolder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'space_id',
        'organization_id',
        'user_id',
        'name',
        'icon',
        'color',
        'position',
    ];

    protected $casts = [
        'space_id' => 'integer',
        'position' => 'integer',
    ];

    protected static function booted()
    {
        parent::booted();

        // Auto-fill organization_id and user_id
        static::creating(function ($folder) {
            if (Auth::check()) {
                $folder->organization_id = $folder->organization_id ?? Auth::user()->organization_id;
                $folder->user_id = $folder->user_id ?? Auth::id();
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
    public function space()
    {
        return $this->belongsTo(TaskSpace::class, 'space_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lists()
    {
        return $this->hasMany(TaskList::class, 'folder_id')->orderBy('position');
    }

    // Scopes
    public function scopeOrdered($query)
    {
        return $query->orderBy('position')->orderBy('name');
    }

    public function scopeInSpace($query, $spaceId)
    {
        return $query->where('space_id', $spaceId);
    }
}
