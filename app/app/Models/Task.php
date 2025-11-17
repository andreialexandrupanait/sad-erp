<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Task extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'list_id',
        'organization_id',
        'user_id',
        'assigned_to',
        'service_id',
        'status_id',
        'name',
        'description',
        'due_date',
        'time_tracked',
        'amount',
        'total_amount',
        'position',
    ];

    protected $casts = [
        'list_id' => 'integer',
        'organization_id' => 'integer',
        'user_id' => 'integer',
        'assigned_to' => 'integer',
        'service_id' => 'integer',
        'status_id' => 'integer',
        'time_tracked' => 'integer',
        'amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'position' => 'integer',
        'due_date' => 'date',
    ];

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($task) {
            if (Auth::check()) {
                $task->organization_id = $task->organization_id ?? Auth::user()->organization_id;
                $task->user_id = $task->user_id ?? Auth::id();
            }
        });

        // Auto-calculate total_amount before saving
        static::saving(function ($task) {
            // Calculate: total_amount = (time_tracked / 60) * amount
            if ($task->time_tracked && $task->amount) {
                $task->total_amount = ($task->time_tracked / 60) * $task->amount;
            } else {
                $task->total_amount = 0;
            }
        });

        static::addGlobalScope('user_scope', function (Builder $query) {
            if (Auth::check()) {
                $query->where('organization_id', Auth::user()->organization_id)
                      ->where('user_id', Auth::id());
            }
        });
    }

    // Relationships
    public function list()
    {
        return $this->belongsTo(TaskList::class, 'list_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function service()
    {
        return $this->belongsTo(TaskService::class, 'service_id');
    }

    public function status()
    {
        return $this->belongsTo(SettingsOption::class, 'status_id');
    }

    public function customFields()
    {
        return $this->hasMany(TaskCustomField::class);
    }

    // Scopes
    public function scopeOrdered($query)
    {
        return $query->orderBy('position')->orderBy('due_date');
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeWithStatus($query, $statusId)
    {
        return $query->where('status_id', $statusId);
    }

    public function scopeDueSoon($query, $days = 7)
    {
        return $query->where('due_date', '<=', now()->addDays($days))
                     ->where('due_date', '>=', now());
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now());
    }
}
