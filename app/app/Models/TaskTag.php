<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TaskTag extends Model
{
    protected $fillable = [
        'organization_id',
        'name',
        'color',
    ];

    protected $casts = [
        'organization_id' => 'integer',
    ];

    /**
     * Get the organization that owns the tag
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get all tasks that have this tag
     */
    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_tag_assignments', 'tag_id', 'task_id')
                    ->withTimestamps();
    }

    /**
     * Scope to filter tags by organization
     */
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Scope to order tags by name
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    /**
     * Validate color hex code
     */
    public function setColorAttribute($value)
    {
        // Ensure color starts with # and is valid hex
        if (!preg_match('/^#[0-9A-F]{6}$/i', $value)) {
            $value = '#808080'; // Default gray
        }
        $this->attributes['color'] = strtoupper($value);
    }
}
