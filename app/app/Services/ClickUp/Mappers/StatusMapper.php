<?php

namespace App\Services\ClickUp\Mappers;

use App\Models\SettingOption;
use App\Models\ClickUpMapping;

class StatusMapper
{
    protected $organizationId;
    protected $statusCache = [];

    public function __construct($organizationId)
    {
        $this->organizationId = $organizationId;
        $this->loadStatuses();
    }

    /**
     * Load all task statuses from settings_options
     */
    protected function loadStatuses()
    {
        $statuses = SettingOption::where('category', 'task_statuses')->get();

        foreach ($statuses as $status) {
            $this->statusCache[$status->id] = strtolower($status->label);
        }
    }

    /**
     * Map ClickUp status to Laravel status ID
     *
     * @param string $clickUpStatus ClickUp status name
     * @return int|null
     */
    public function mapToLaravel($clickUpStatus)
    {
        if (!$clickUpStatus) {
            return null;
        }

        $clickUpStatusLower = strtolower($clickUpStatus);

        // First check if mapping exists
        $existingMapping = ClickUpMapping::getLaravelId(
            $this->organizationId,
            'status',
            $clickUpStatusLower
        );

        if ($existingMapping) {
            return $existingMapping;
        }

        // Try to find by label match
        foreach ($this->statusCache as $statusId => $statusLabel) {
            if ($statusLabel === $clickUpStatusLower) {
                // Create mapping for future use
                ClickUpMapping::createMapping(
                    $this->organizationId,
                    'status',
                    $clickUpStatusLower,
                    $statusId
                );

                return $statusId;
            }
        }

        // Try partial match (e.g., "in progress" matches "in_progress" or "in-progress")
        $normalizedClickUp = str_replace([' ', '_', '-'], '', $clickUpStatusLower);

        foreach ($this->statusCache as $statusId => $statusLabel) {
            $normalizedLabel = str_replace([' ', '_', '-'], '', $statusLabel);

            if ($normalizedLabel === $normalizedClickUp) {
                // Create mapping for future use
                ClickUpMapping::createMapping(
                    $this->organizationId,
                    'status',
                    $clickUpStatusLower,
                    $statusId
                );

                return $statusId;
            }
        }

        // No match found - return default status
        return $this->getDefaultStatus();
    }

    /**
     * Get default status ID (first available status)
     *
     * @return int|null
     */
    public function getDefaultStatus()
    {
        $status = SettingOption::where('category', 'task_statuses')->first();
        return $status ? $status->id : null;
    }

    /**
     * Create mapping for ClickUp status
     *
     * @param string $clickUpStatus
     * @param int $laravelStatusId
     * @return void
     */
    public function createMapping($clickUpStatus, $laravelStatusId)
    {
        ClickUpMapping::createMapping(
            $this->organizationId,
            'status',
            strtolower($clickUpStatus),
            $laravelStatusId
        );
    }
}
