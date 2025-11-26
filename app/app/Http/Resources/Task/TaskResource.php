<?php

namespace App\Http\Resources\Task;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'description' => $this->description,
            'position' => $this->position,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'time_tracked' => $this->time_tracked,
            'time_tracked_formatted' => $this->time_tracked > 0
                ? floor($this->time_tracked / 60) . 'h ' . ($this->time_tracked % 60) . 'm'
                : null,
            'amount' => $this->amount,
            'total_amount' => $this->total_amount,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relationships
            'list' => $this->whenLoaded('list', function () {
                return [
                    'id' => $this->list->id,
                    'name' => $this->list->name,
                    'client' => $this->whenLoaded('list.client', function () {
                        return [
                            'id' => $this->list->client->id,
                            'name' => $this->list->client->name,
                        ];
                    }),
                ];
            }),

            'status' => $this->whenLoaded('status', function () {
                return [
                    'id' => $this->status->id,
                    'label' => $this->status->label,
                    'value' => $this->status->value,
                    'color' => $this->status->color,
                ];
            }),

            'priority' => $this->whenLoaded('priority', function () {
                return [
                    'id' => $this->priority->id,
                    'label' => $this->priority->label,
                    'value' => $this->priority->value,
                    'color' => $this->priority->color,
                ];
            }),

            'service' => $this->whenLoaded('service', function () {
                return [
                    'id' => $this->service->id,
                    'name' => $this->service->name,
                ];
            }),

            'assigned_user' => $this->whenLoaded('assignedUser', function () {
                return $this->assignedUser ? [
                    'id' => $this->assignedUser->id,
                    'name' => $this->assignedUser->name,
                    'email' => $this->assignedUser->email,
                    'avatar' => $this->assignedUser->avatar,
                ] : null;
            }),

            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                ];
            }),

            'parent_task' => $this->whenLoaded('parentTask', function () {
                return $this->parentTask ? [
                    'id' => $this->parentTask->id,
                    'name' => $this->parentTask->name,
                ] : null;
            }),

            'subtasks' => TaskMinimalResource::collection($this->whenLoaded('subtasks')),
            'comments_count' => $this->when($this->relationLoaded('comments'), fn() => $this->comments->count()),
            'attachments_count' => $this->when($this->relationLoaded('attachments'), fn() => $this->attachments->count()),
        ];
    }
}
