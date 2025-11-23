<?php

namespace App\Events\Task;

use App\Models\Task;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Task $task;
    public ?int $oldAssigneeId;
    public ?int $newAssigneeId;

    /**
     * Create a new event instance.
     */
    public function __construct(Task $task, ?int $oldAssigneeId, ?int $newAssigneeId)
    {
        $this->task = $task;
        $this->oldAssigneeId = $oldAssigneeId;
        $this->newAssigneeId = $newAssigneeId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('organization.' . $this->task->organization_id),
            new PrivateChannel('list.' . $this->task->list_id),
        ];

        // Also broadcast to the new assignee's personal channel
        if ($this->newAssigneeId) {
            $channels[] = new PrivateChannel('user.' . $this->newAssigneeId);
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'task.assigned';
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
            ],
            'old_assignee_id' => $this->oldAssigneeId,
            'new_assignee_id' => $this->newAssigneeId,
        ];
    }
}
