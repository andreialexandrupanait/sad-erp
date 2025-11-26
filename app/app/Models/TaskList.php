<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TaskList extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'folder_id',
        'client_id',
        'organization_id',
        'user_id',
        'name',
        'icon',
        'color',
        'position',
        'clickup_metadata',
    ];

    protected $casts = [
        'folder_id' => 'integer',
        'client_id' => 'integer',
        'position' => 'integer',
        'clickup_metadata' => 'array',
    ];

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($list) {
            if (Auth::check()) {
                $list->organization_id = $list->organization_id ?? Auth::user()->organization_id;
                $list->user_id = $list->user_id ?? Auth::id();
            }
        });

        static::addGlobalScope('user_scope', function (Builder $query) {
            if (Auth::check()) {
                $query->where('organization_id', Auth::user()->organization_id)
                      ->where('user_id', Auth::id());
            }
        });
    }

    public function folder()
    {
        return $this->belongsTo(TaskFolder::class, 'folder_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'list_id')->orderBy('position');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position')->orderBy('name');
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}
