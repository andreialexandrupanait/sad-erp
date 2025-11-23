<?php

namespace App\Http\Resources\Task;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Minimal task resource for list views and relations
 * Returns only essential fields to reduce payload size
 */
class TaskMinimalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'position' => $this->position,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'time_tracked' => $this->time_tracked,
            'amount' => $this->amount,
            'total_amount' => $this->total_amount,

            // Minimal relationships - only IDs and names
            'list_id' => $this->list_id,
            'list_name' => $this->whenLoaded('list', fn() => $this->list->name),

            'status' => $this->whenLoaded('status', function () {
                return [
                    'id' => $this->status->id,
                    'label' => $this->status->label,
                    'color' => $this->status->color,
                ];
            }),

            'priority' => $this->whenLoaded('priority', function () {
                return $this->priority ? [
                    'id' => $this->priority->id,
                    'label' => $this->priority->label,
                    'color' => $this->priority->color,
                ] : null;
            }),

            'service_id' => $this->service_id,
            'service_name' => $this->whenLoaded('service', fn() => $this->service?->name),

            'assigned_to' => $this->assigned_to,
            'assigned_user_name' => $this->whenLoaded('assignedUser', fn() => $this->assignedUser?->name),
            'assigned_user_avatar' => $this->whenLoaded('assignedUser', fn() => $this->assignedUser?->avatar),
        ];
    }
}
