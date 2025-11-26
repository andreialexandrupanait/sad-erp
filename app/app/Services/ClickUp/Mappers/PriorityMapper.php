<?php

namespace App\Services\ClickUp\Mappers;

use App\Models\SettingOption;
use App\Models\ClickUpMapping;

class PriorityMapper
{
    protected $organizationId;
    protected $priorityCache = [];

    public function __construct($organizationId)
    {
        $this->organizationId = $organizationId;
        $this->loadPriorities();
    }

    /**
     * Load all task priorities from settings_options
     */
    protected function loadPriorities()
    {
        $priorities = SettingOption::where('category', 'task_priorities')->get();

        foreach ($priorities as $priority) {
            $this->priorityCache[$priority->id] = strtolower($priority->label);
        }
    }

    /**
     * Map ClickUp priority to Laravel priority ID
     * ClickUp priorities: 1=Urgent, 2=High, 3=Normal, 4=Low, null=No priority
     *
     * @param int|null $clickUpPriority ClickUp priority number
     * @return int|null
     */
    public function mapToLaravel($clickUpPriority)
    {
        if ($clickUpPriority === null) {
            return null;
        }

        // ClickUp priority mapping
        $priorityMap = [
            1 => ['urgent', 'critical'],
            2 => ['high'],
            3 => ['normal', 'medium', 'default'],
            4 => ['low'],
        ];

        $targetLabels = $priorityMap[$clickUpPriority] ?? ['normal'];

        // Try to find matching priority
        foreach ($this->priorityCache as $priorityId => $priorityLabel) {
            if (in_array($priorityLabel, $targetLabels)) {
                return $priorityId;
            }
        }

        // Try partial match
        foreach ($targetLabels as $targetLabel) {
            foreach ($this->priorityCache as $priorityId => $priorityLabel) {
                if (strpos($priorityLabel, $targetLabel) !== false || strpos($targetLabel, $priorityLabel) !== false) {
                    return $priorityId;
                }
            }
        }

        // No match found - return default priority
        return $this->getDefaultPriority();
    }

    /**
     * Get default priority ID (normal/medium priority if exists)
     *
     * @return int|null
     */
    public function getDefaultPriority()
    {
        // Try to find "normal" or "medium" priority
        foreach ($this->priorityCache as $priorityId => $priorityLabel) {
            if (in_array($priorityLabel, ['normal', 'medium', 'default'])) {
                return $priorityId;
            }
        }

        // Return first available priority
        $priority = SettingOption::where('category', 'task_priorities')->first();
        return $priority ? $priority->id : null;
    }

    /**
     * Create mapping for ClickUp priority
     *
     * @param int $clickUpPriority
     * @param int $laravelPriorityId
     * @return void
     */
    public function createMapping($clickUpPriority, $laravelPriorityId)
    {
        ClickUpMapping::createMapping(
            $this->organizationId,
            'priority',
            (string)$clickUpPriority,
            $laravelPriorityId
        );
    }
}
