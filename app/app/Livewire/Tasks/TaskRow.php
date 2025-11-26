<?php

namespace App\Livewire\Tasks;

use App\Models\Task;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Modelable;

class TaskRow extends Component
{
    public Task $task;
    public $editing = [];

    public function mount()
    {
        // Initialize editing state
        $this->editing = [
            'name' => false,
            'amount' => false,
        ];
    }

    #[On('task-row-refresh-{task.id}')]
    public function refresh()
    {
        $this->task->refresh();
    }

    /**
     * Update a task field
     */
    public function updateField($field, $value)
    {
        \Log::info('TaskRow updateField called', [
            'field' => $field,
            'value' => $value,
            'task_id' => $this->task->id,
            'old_value' => $this->task->$field
        ]);

        try {
            $this->validate([
                $field => $this->getValidationRule($field),
            ]);

            $this->task->update([$field => $value]);
            $this->task->refresh();

            \Log::info('TaskRow updated successfully', [
                'field' => $field,
                'new_value' => $this->task->$field,
                'task_id' => $this->task->id
            ]);

            $this->dispatch('task-updated', taskId: $this->task->id);

            // Close editing mode
            if (isset($this->editing[$field])) {
                $this->editing[$field] = false;
            }

            // Show success feedback
            $this->dispatch('task-saved');
        } catch (\Exception $e) {
            \Log::error('Task update failed', [
                'field' => $field,
                'value' => $value,
                'task_id' => $this->task->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('task-error', message: 'Failed to update task');
        }
    }

    /**
     * Update task status
     */
    public function updateStatus($statusId)
    {
        \Log::info('TaskRow updateStatus called', [
            'status_id' => $statusId,
            'task_id' => $this->task->id
        ]);

        $this->task->update(['status_id' => $statusId]);
        $this->task->refresh();
        $this->dispatch('task-updated', taskId: $this->task->id);
    }

    /**
     * Update task priority
     */
    public function updatePriority($priorityId)
    {
        \Log::info('TaskRow updatePriority called', [
            'priority_id' => $priorityId,
            'task_id' => $this->task->id
        ]);

        $this->task->update(['priority_id' => $priorityId]);
        $this->task->refresh();
        $this->dispatch('task-updated', taskId: $this->task->id);
    }

    /**
     * Update task service
     */
    public function updateService($serviceId)
    {
        \Log::info('TaskRow updateService called', [
            'service_id' => $serviceId,
            'task_id' => $this->task->id
        ]);

        $this->task->update(['service_id' => $serviceId]);
        $this->task->refresh();
        $this->dispatch('task-updated', taskId: $this->task->id);
    }

    /**
     * Update task list
     */
    public function updateList($listId)
    {
        \Log::info('TaskRow updateList called', [
            'list_id' => $listId,
            'task_id' => $this->task->id
        ]);

        $this->task->update(['list_id' => $listId]);
        $this->task->refresh();
        $this->dispatch('task-updated', taskId: $this->task->id);
    }

    /**
     * Toggle assignee
     */
    public function toggleAssignee($userId)
    {
        \Log::info('TaskRow toggleAssignee called', [
            'user_id' => $userId,
            'task_id' => $this->task->id,
            'action' => $this->task->assignees->contains($userId) ? 'detach' : 'attach'
        ]);

        if ($this->task->assignees->contains($userId)) {
            $this->task->assignees()->detach($userId);
        } else {
            $this->task->assignees()->attach($userId);
        }

        $this->task->load('assignees');
        $this->task->refresh();
        $this->dispatch('task-updated', taskId: $this->task->id);
    }

    /**
     * Update task dates
     */
    public function updateDates($dates)
    {
        \Log::info('TaskRow updateDates called', [
            'dates' => $dates,
            'task_id' => $this->task->id
        ]);

        $this->task->update([
            'start_date' => $dates['start_date'] ?? null,
            'due_date' => $dates['due_date'] ?? null,
        ]);
        $this->task->refresh();
        $this->dispatch('task-updated', taskId: $this->task->id);
    }

    /**
     * Get validation rule for field
     */
    protected function getValidationRule($field)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'amount' => 'nullable|numeric|min:0',
            'time_tracked' => 'nullable|integer|min:0',
            'time_estimate' => 'nullable|integer|min:0',
        ];

        return $rules[$field] ?? 'nullable';
    }

    public function render()
    {
        return view('livewire.tasks.task-row');
    }
}
