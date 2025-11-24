<?php

namespace App\Services\ClickUp\Mappers;

use App\Models\User;
use App\Models\ClickUpMapping;

class UserMapper
{
    protected $organizationId;

    public function __construct($organizationId)
    {
        $this->organizationId = $organizationId;
    }

    /**
     * Map ClickUp user ID to Laravel user ID
     *
     * @param string|int $clickUpUserId
     * @return int|null
     */
    public function mapToLaravel($clickUpUserId)
    {
        if (!$clickUpUserId) {
            return null;
        }

        return ClickUpMapping::getLaravelId(
            $this->organizationId,
            'user',
            (string)$clickUpUserId
        );
    }

    /**
     * Create mapping for ClickUp user
     *
     * @param array $clickUpUser ClickUp user data
     * @param int $laravelUserId Laravel user ID to map to
     * @return void
     */
    public function createMapping($clickUpUser, $laravelUserId)
    {
        ClickUpMapping::createMapping(
            $this->organizationId,
            'user',
            (string)$clickUpUser['id'],
            $laravelUserId,
            [
                'username' => $clickUpUser['username'] ?? null,
                'email' => $clickUpUser['email'] ?? null,
                'color' => $clickUpUser['color'] ?? null,
                'initials' => $clickUpUser['initials'] ?? null,
            ]
        );
    }

    /**
     * Find Laravel user by email or create mapping
     *
     * @param array $clickUpUser ClickUp user data
     * @return int|null Laravel user ID
     */
    public function findOrMapUser($clickUpUser)
    {
        // First check if mapping exists
        $existingMapping = $this->mapToLaravel($clickUpUser['id']);
        if ($existingMapping) {
            return $existingMapping;
        }

        // Try to find by email
        if (isset($clickUpUser['email'])) {
            $user = User::where('email', $clickUpUser['email'])
                ->where('organization_id', $this->organizationId)
                ->first();

            if ($user) {
                $this->createMapping($clickUpUser, $user->id);
                return $user->id;
            }
        }

        // No user found - caller should decide what to do
        return null;
    }

    /**
     * Get default user ID for organization (for unassigned tasks)
     *
     * @return int|null
     */
    public function getDefaultUserId()
    {
        $user = User::where('organization_id', $this->organizationId)
            ->where('role', 'admin')
            ->first();

        return $user ? $user->id : null;
    }
}
