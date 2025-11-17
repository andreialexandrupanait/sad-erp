<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskCustomField extends Model
{
    protected $fillable = [
        'task_id',
        'field_name',
        'field_type',
        'field_value',
    ];

    protected $casts = [
        'task_id' => 'integer',
    ];

    // Relationships
    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
