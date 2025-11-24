<?php

namespace App\Livewire\Tasks;

use App\Models\Task;
use App\Models\SettingOption;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class TaskList extends Component
{
    #[Url]
    public $listId = null;

    #[Url]
    public $search = '';

    #[Url]
    public $statusFilter = [];

    public $selectedTasks = [];
    public $expandedStatuses = [];

    public function mount()
    {
        // Initialize expanded statuses from localStorage (will be handled by Alpine)
        // Load initial data
    }

    /**
     * Load tasks for a specific status (lazy loading)
     */
    public function getTasksForStatus($statusId)
    {
        return Task::where('status_id', $statusId)
            ->when($this->listId, fn($q) => $q->where('list_id', $this->listId))
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->with(['assignees', 'priority', 'service', 'status', 'list', 'tags'])
            ->orderBy('position')
            ->orderBy('due_date')
            ->limit(100) // Limit per status to 100 tasks
            ->get();
    }

    /**
     * Get task counts grouped by status
     */
    public function getTaskCountsProperty()
    {
        return Task::selectRaw('status_id, count(*) as count')
            ->when($this->listId, fn($q) => $q->where('list_id', $this->listId))
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->groupBy('status_id')
            ->pluck('count', 'status_id');
    }

    /**
     * Toggle task selection
     */
    public function toggleTask($taskId)
    {
        if (in_array($taskId, $this->selectedTasks)) {
            $this->selectedTasks = array_diff($this->selectedTasks, [$taskId]);
        } else {
            $this->selectedTasks[] = $taskId;
        }
    }

    /**
     * Clear all selected tasks
     */
    public function clearSelection()
    {
        $this->selectedTasks = [];
    }

    /**
     * Listen for task updates from child components
     */
    #[On('task-updated')]
    public function refreshTask($taskId)
    {
        // Dispatch event to specific task row to refresh
        $this->dispatch('task-row-refresh-' . $taskId);
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus($statusId)
    {
        Task::whereIn('id', $this->selectedTasks)
            ->update(['status_id' => $statusId]);

        $this->selectedTasks = [];
        $this->dispatch('tasks-updated');
    }

    /**
     * Bulk update priority
     */
    public function bulkUpdatePriority($priorityId)
    {
        Task::whereIn('id', $this->selectedTasks)
            ->update(['priority_id' => $priorityId]);

        $this->selectedTasks = [];
        $this->dispatch('tasks-updated');
    }

    /**
     * Bulk delete tasks
     */
    public function bulkDelete()
    {
        Task::whereIn('id', $this->selectedTasks)->delete();
        $this->selectedTasks = [];
        $this->dispatch('tasks-updated');
    }

    public function render()
    {
        $statuses = SettingOption::taskStatuses()->get();

        return view('livewire.tasks.task-list', [
            'statuses' => $statuses,
            'taskCounts' => $this->taskCounts,
        ])->layout('layouts.app', [
            'pageTitle' => 'Tasks',
        ]);
    }
}
