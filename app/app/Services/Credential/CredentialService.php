<?php

namespace App\Services\Credential;

use App\Models\Credential;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class CredentialService
{
    /**
     * Get paginated credentials with filters applied.
     */
    public function getPaginatedCredentials(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Credential::with('client');

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['platform'])) {
            $query->platform($filters['platform']);
        }

        if (!empty($filters['client_id'])) {
            $query->client($filters['client_id']);
        }

        $sortBy = $filters['sort'] ?? 'created_at';
        $sortDir = $filters['dir'] ?? 'desc';

        $allowedSorts = ['client_id', 'platform', 'username', 'created_at', 'updated_at', 'last_accessed_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        return $query->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Create a new credential.
     */
    public function create(array $data): Credential
    {
        return Credential::create($data);
    }

    /**
     * Update an existing credential.
     */
    public function update(Credential $credential, array $data): Credential
    {
        // Only update password if a new one is provided
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $credential->update($data);
        return $credential->fresh();
    }

    /**
     * Delete a credential (soft delete).
     */
    public function delete(Credential $credential): bool
    {
        return $credential->delete();
    }

    /**
     * Reveal password and log the access.
     */
    public function revealPassword(Credential $credential): ?string
    {
        // Track access in database
        $credential->trackAccess();

        // Log the access for audit purposes
        Log::info('Password revealed for credential', [
            'credential_id' => $credential->id,
            'platform' => $credential->platform,
            'client_id' => $credential->client_id,
            'user_id' => auth()->id(),
            'user_email' => auth()->user()->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $credential->password;
    }

    /**
     * Get credentials for a specific client.
     */
    public function getCredentialsForClient(int $clientId): Collection
    {
        return Credential::where('client_id', $clientId)
            ->orderBy('platform')
            ->get();
    }

    /**
     * Get credentials grouped by platform.
     */
    public function getCredentialsGroupedByPlatform(): Collection
    {
        return Credential::selectRaw('platform, COUNT(*) as count')
            ->groupBy('platform')
            ->orderByDesc('count')
            ->get();
    }

    /**
     * Get most accessed credentials.
     */
    public function getMostAccessedCredentials(int $limit = 10): Collection
    {
        return Credential::with('client')
            ->where('access_count', '>', 0)
            ->orderByDesc('access_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recently accessed credentials.
     */
    public function getRecentlyAccessedCredentials(int $limit = 10): Collection
    {
        return Credential::with('client')
            ->whereNotNull('last_accessed_at')
            ->orderByDesc('last_accessed_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get statistics for credentials.
     */
    public function getStatistics(): array
    {
        $total = Credential::count();
        $byPlatform = $this->getCredentialsGroupedByPlatform();
        $totalAccesses = Credential::sum('access_count');

        return [
            'total' => $total,
            'by_platform' => $byPlatform,
            'total_accesses' => $totalAccesses,
        ];
    }
}
