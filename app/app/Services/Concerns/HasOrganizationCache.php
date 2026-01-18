<?php

namespace App\Services\Concerns;

trait HasOrganizationCache
{
    /**
     * The organization ID for cache scoping.
     * Can be set via constructor to support background jobs.
     */
    protected ?int $organizationId = null;

    /**
     * Set the organization ID for cache scoping.
     */
    public function setOrganizationId(?int $organizationId): static
    {
        $this->organizationId = $organizationId;
        return $this;
    }

    /**
     * Get the organization ID for cache key generation.
     * Falls back to authenticated user's organization if not explicitly set.
     */
    protected function getOrganizationId(): int|string
    {
        return $this->organizationId
            ?? auth()->user()?->organization_id
            ?? 'default';
    }

    /**
     * Generate an organization-scoped cache key.
     */
    protected function cacheKey(string $key): string
    {
        return "org.{$this->getOrganizationId()}.{$key}";
    }
}
