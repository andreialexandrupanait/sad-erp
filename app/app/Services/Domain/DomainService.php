<?php

namespace App\Services\Domain;

use App\Models\Domain;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class DomainService
{
    /**
     * Get paginated domains with filters applied.
     */
    public function getPaginatedDomains(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Domain::with('client');

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['client_id'])) {
            $query->client($filters['client_id']);
        }

        if (!empty($filters['registrar'])) {
            $query->registrar($filters['registrar']);
        }

        if (!empty($filters['expiry_status'])) {
            $query->expiryStatus($filters['expiry_status']);
        }

        $sortBy = $filters['sort'] ?? 'expiry_date';
        $sortDir = $filters['dir'] ?? 'asc';

        $allowedSorts = ['domain_name', 'registrar', 'expiry_date', 'annual_cost', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'expiry_date';
        }

        return $query->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get domain statistics for the current organization.
     */
    public function getStatistics(): array
    {
        return Domain::getStatistics();
    }

    /**
     * Get domains expiring within a given number of days.
     */
    public function getExpiringDomains(int $days = 30): Collection
    {
        return Domain::expiringSoon()
            ->with('client')
            ->orderBy('expiry_date')
            ->get();
    }

    /**
     * Get expired domains.
     */
    public function getExpiredDomains(): Collection
    {
        return Domain::expired()
            ->with('client')
            ->orderBy('expiry_date', 'desc')
            ->get();
    }

    /**
     * Create a new domain.
     */
    public function create(array $data): Domain
    {
        return Domain::create($data);
    }

    /**
     * Update an existing domain.
     */
    public function update(Domain $domain, array $data): Domain
    {
        $domain->update($data);
        return $domain->fresh();
    }

    /**
     * Delete a domain (soft delete).
     */
    public function delete(Domain $domain): bool
    {
        return $domain->delete();
    }

    /**
     * Get total annual cost of all domains.
     */
    public function getTotalAnnualCost(): float
    {
        return Domain::getTotalAnnualCost();
    }

    /**
     * Get domains grouped by registrar.
     */
    public function getDomainsGroupedByRegistrar(): Collection
    {
        return Domain::selectRaw('registrar, COUNT(*) as count, SUM(annual_cost) as total_cost')
            ->groupBy('registrar')
            ->orderByDesc('count')
            ->get();
    }

    /**
     * Check if a domain name is available (not already registered in the system).
     */
    public function isDomainNameAvailable(string $domainName, ?int $excludeId = null): bool
    {
        $query = Domain::withoutGlobalScopes()
            ->where('domain_name', strtolower(trim($domainName)));

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }
}
