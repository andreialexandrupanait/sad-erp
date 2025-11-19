<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TaskComment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'task_id',
        'user_id',
        'organization_id',
        'comment',
        'parent_comment_id',
    ];

    protected $casts = [
        'task_id' => 'integer',
        'user_id' => 'integer',
        'organization_id' => 'integer',
        'parent_comment_id' => 'integer',
    ];

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($comment) {
            if (Auth::check()) {
                $comment->organization_id = $comment->organization_id ?? Auth::user()->organization_id;
                $comment->user_id = $comment->user_id ?? Auth::id();
            }
        });

        static::addGlobalScope('organization', function (Builder $query) {
            if (Auth::check() && Auth::user()->organization_id) {
                $query->where('organization_id', Auth::user()->organization_id);
            }
        });
    }

    // Relationships
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // Self-referencing for threaded comments
    public function parentComment()
    {
        return $this->belongsTo(TaskComment::class, 'parent_comment_id');
    }

    public function replies()
    {
        return $this->hasMany(TaskComment::class, 'parent_comment_id')->with('user')->latest();
    }

    // Scopes
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_comment_id');
    }

    public function scopeForTask($query, $taskId)
    {
        return $query->where('task_id', $taskId);
    }
}
