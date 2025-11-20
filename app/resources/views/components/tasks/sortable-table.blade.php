@props(['tasks'])

<script>
// Sortable tasks functionality
function sortableTasks() {
    return {
        draggedTask: null,
        draggedOver: null,

        dragStart(event, taskId) {
            this.draggedTask = taskId;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/html', event.target.innerHTML);
            event.target.classList.add('opacity-50');
        },

        dragEnd(event) {
            event.target.classList.remove('opacity-50');
            this.draggedTask = null;
            this.draggedOver = null;
        },

        dragOver(event) {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
        },

        dragEnter(event, taskId) {
            if (this.draggedTask !== taskId) {
                this.draggedOver = taskId;
                event.target.closest('tr').classList.add('border-t-2', 'border-blue-500');
            }
        },

        dragLeave(event) {
            event.target.closest('tr').classList.remove('border-t-2', 'border-blue-500');
        },

        async drop(event, targetTaskId, targetPosition) {
            event.preventDefault();
            event.target.closest('tr').classList.remove('border-t-2', 'border-blue-500');

            if (this.draggedTask === targetTaskId) return;

            try {
                // Update task position
                const response = await fetch(`/tasks/${this.draggedTask}/position`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        position: targetPosition,
                        list_id: null // Keep in same list
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Reload page to reflect new order
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to reorder tasks');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while reordering');
            }
        }
    };
}
</script>

<div x-data="sortableTasks()">
    <table class="w-full table-auto border-collapse bg-white shadow-sm rounded-lg overflow-hidden">
        <thead class="bg-slate-50">
            <tr>
                <th class="w-12 p-4 text-left">
                    <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                    </svg>
                </th>
                <th class="p-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">{{ __('Task') }}</th>
                <th class="p-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">{{ __('List / Client') }}</th>
                <th class="p-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">{{ __('Status') }}</th>
                <th class="p-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">{{ __('Assigned') }}</th>
                <th class="p-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">{{ __('Service') }}</th>
                <th class="p-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">{{ __('Due Date') }}</th>
                <th class="p-4 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">{{ __('Time') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @foreach($tasks as $task)
                <tr
                    draggable="true"
                    @dragstart="dragStart($event, {{ $task->id }})"
                    @dragend="dragEnd($event)"
                    @dragover="dragOver($event)"
                    @dragenter="dragEnter($event, {{ $task->id }})"
                    @dragleave="dragLeave($event)"
                    @drop="drop($event, {{ $task->id }}, {{ $task->position }})"
                    @click="$dispatch('task-panel-open', {{ $task->id }})"
                    class="hover:bg-slate-50 cursor-pointer transition-colors"
                >
                    <td class="p-4 align-middle">
                        <svg class="w-4 h-4 text-slate-400 cursor-move" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                        </svg>
                    </td>
                    {{ $slot }}
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
