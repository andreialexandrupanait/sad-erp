<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TaskAttachment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'task_id',
        'user_id',
        'organization_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    protected $casts = [
        'task_id' => 'integer',
        'user_id' => 'integer',
        'organization_id' => 'integer',
        'file_size' => 'integer',
    ];

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($attachment) {
            if (Auth::check()) {
                $attachment->organization_id = $attachment->organization_id ?? Auth::user()->organization_id;
                $attachment->user_id = $attachment->user_id ?? Auth::id();
            }
        });

        // Delete file when attachment is deleted
        static::deleting(function ($attachment) {
            if ($attachment->file_path && Storage::exists($attachment->file_path)) {
                Storage::delete($attachment->file_path);
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

    // Accessors
    public function getDownloadUrlAttribute()
    {
        return route('tasks.attachments.download', $this);
    }

    public function getFileSizeFormattedAttribute()
    {
        if (!$this->file_size) {
            return 'Unknown';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    // Scopes
    public function scopeForTask($query, $taskId)
    {
        return $query->where('task_id', $taskId);
    }
}
