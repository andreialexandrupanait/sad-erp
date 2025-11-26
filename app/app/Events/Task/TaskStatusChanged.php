<?php

namespace App\Events\Task;

use App\Models\Task;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Task $task;
    public ?int $oldStatusId;
    public int $newStatusId;

    /**
     * Create a new event instance.
     */
    public function __construct(Task $task, ?int $oldStatusId, int $newStatusId)
    {
        $this->task = $task;
        $this->oldStatusId = $oldStatusId;
        $this->newStatusId = $newStatusId;
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
        return 'task.status-changed';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'task_id' => $this->task->id,
            'old_status_id' => $this->oldStatusId,
            'new_status_id' => $this->newStatusId,
            'position' => $this->task->position,
        ];
    }
}
