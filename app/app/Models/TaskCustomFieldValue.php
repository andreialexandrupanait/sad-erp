<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskCustomFieldValue extends Model
{
    protected $fillable = [
        'task_id',
        'custom_field_id',
        'value',
    ];

    protected $casts = [
        'task_id' => 'integer',
        'custom_field_id' => 'integer',
    ];

    /**
     * Get the task that owns this custom field value
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the custom field definition
     */
    public function customField(): BelongsTo
    {
        return $this->belongsTo(TaskCustomField::class, 'custom_field_id');
    }

    /**
     * Get the formatted value based on field type
     */
    public function getFormattedValueAttribute()
    {
        if (!$this->customField) {
            return $this->value;
        }

        switch ($this->customField->type) {
            case 'date':
                return $this->value ? \Carbon\Carbon::parse($this->value)->format('Y-m-d') : null;
            case 'number':
                return is_numeric($this->value) ? (float) $this->value : null;
            case 'checkbox':
                return (bool) $this->value;
            case 'dropdown':
                return $this->value;
            default:
                return $this->value;
        }
    }
}
