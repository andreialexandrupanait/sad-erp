<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Organization;

/**
 * Trait for models that belong to an organization (multi-tenancy).
 *
 * This trait provides:
 * - Automatic organization_id assignment on create
 * - Global scope to filter by current user's organization
 * - Organization relationship
 *
 * Usage: Add `use HasOrganization;` to your model.
 */
trait HasOrganization
{
    /**
     * Boot the trait.
     */
    public static function bootHasOrganization(): void
    {
        // Automatically set organization_id when creating
        static::creating(function (Model $model) {
            if (auth()->check() && empty($model->organization_id)) {
                $orgId = auth()->user()->organization_id;
                if (!$orgId) {
                    throw new \RuntimeException(
                        sprintf('User must belong to an organization to create %s.', class_basename($model))
                    );
                }
                $model->organization_id = $orgId;
            }
        });

        // Global scope to filter by organization
        static::addGlobalScope('organization', function (Builder $builder) {
            if (auth()->check() && auth()->user()->organization_id) {
                $builder->where(
                    $builder->getModel()->getTable() . '.organization_id',
                    auth()->user()->organization_id
                );
            }
        });
    }

    /**
     * Get the organization that owns this model.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Scope to filter by a specific organization.
     */
    public function scopeForOrganization(Builder $query, int $organizationId): Builder
    {
        return $query->where($this->getTable() . '.organization_id', $organizationId);
    }

    /**
     * Check if this model belongs to the given organization.
     */
    public function belongsToOrganization(int $organizationId): bool
    {
        return $this->organization_id === $organizationId;
    }

    /**
     * Check if this model belongs to the current user's organization.
     */
    public function belongsToCurrentOrganization(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        return $this->organization_id === auth()->user()->organization_id;
    }
}
