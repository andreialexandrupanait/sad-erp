<?php

namespace App\Events\Task;

use App\Models\Task;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Task $task;
    public array $changes;

    /**
     * Create a new event instance.
     */
    public function __construct(Task $task, array $changes = [])
    {
        $this->task = $task;
        $this->changes = $changes;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('organization.' . $this->task->organization_id),
            new PrivateChannel('list.' . $this->task->list_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'task.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'task' => [
                'id' => $this->task->id,
                'name' => $this->task->name,
                'list_id' => $this->task->list_id,
                'status_id' => $this->task->status_id,
                'priority_id' => $this->task->priority_id,
                'assigned_to' => $this->task->assigned_to,
                'position' => $this->task->position,
                'due_date' => $this->task->due_date?->format('Y-m-d'),
                'time_tracked' => $this->task->time_tracked,
                'amount' => $this->task->amount,
                'total_amount' => $this->task->total_amount,
            ],
            'changes' => $this->changes,
        ];
    }
}
