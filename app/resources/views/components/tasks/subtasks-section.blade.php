<div class="space-y-3">
    <!-- Add Subtask -->
    <div x-data="{ adding: false, subtaskName: '' }">
        <button
            x-show="!adding"
            @click="adding = true; $nextTick(() => $refs.subtaskInput.focus())"
            class="flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('Add subtask') }}
        </button>

        <div x-show="adding" class="flex gap-2">
            <input
                x-ref="subtaskInput"
                x-model="subtaskName"
                @keydown.enter="
                    if (subtaskName.trim()) {
                        $el.closest('[x-data*=taskSidePanel]').__x.$data.addSubtask(subtaskName);
                        subtaskName = '';
                        adding = false;
                    }
                "
                @keydown.escape="adding = false; subtaskName = ''"
                type="text"
                placeholder="{{ __('Subtask name') }}"
                class="flex-1 px-3 py-2 text-sm border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <button
                @click="adding = false; subtaskName = ''"
                class="px-3 py-2 text-sm text-slate-600 hover:text-slate-900"
            >
                {{ __('Cancel') }}
            </button>
        </div>
    </div>

    <!-- Subtasks List -->
    <div class="space-y-2">
        <template x-for="subtask in $el.closest('[x-data*=taskSidePanel]').__x.$data.task.subtasks" :key="subtask.id">
            <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors group">
                <!-- Status Checkbox -->
                <input
                    type="checkbox"
                    :checked="subtask.status?.value === 'completed'"
                    @change="$el.closest('[x-data*=taskSidePanel]').__x.$data.toggleSubtaskStatus(subtask.id)"
                    class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                />

                <!-- Subtask Details -->
                <div class="flex-1 min-w-0">
                    <div
                        class="text-sm text-slate-900"
                        :class="subtask.status?.value === 'completed' ? 'line-through text-slate-500' : ''"
                        x-text="subtask.name"
                    ></div>
                    <template x-if="subtask.assigned_user">
                        <div class="text-xs text-slate-500 mt-1">
                            <span x-text="subtask.assigned_user.name"></span>
                        </div>
                    </template>
                </div>

                <!-- Actions -->
                <button
                    @click="if(confirm('Delete this subtask?')) $el.closest('[x-data*=taskSidePanel]').__x.$data.deleteSubtask(subtask.id)"
                    class="opacity-0 group-hover:opacity-100 text-red-600 hover:text-red-700"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </div>
        </template>

        <template x-if="!$el.closest('[x-data*=taskSidePanel]').__x.$data.task.subtasks?.length">
            <div class="text-center py-8 text-slate-400 text-sm">
                {{ __('No subtasks yet') }}
            </div>
        </template>
    </div>
</div>

<script>
// Extend taskSidePanel with subtask methods
document.addEventListener('alpine:init', () => {
    if (window.taskSidePanelExtended) return;
    window.taskSidePanelExtended = true;

    const originalFunction = window.taskSidePanel;
    window.taskSidePanel = function() {
        const instance = originalFunction();

        instance.addSubtask = async function(name) {
            if (!this.task) return;

            try {
                const response = await fetch(`/tasks/${this.task.id}/subtasks`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ name })
                });

                if (response.ok) {
                    const subtask = await response.json();
                    this.task.subtasks = this.task.subtasks || [];
                    this.task.subtasks.push(subtask);
                }
            } catch (error) {
                console.error('Error adding subtask:', error);
            }
        };

        instance.toggleSubtaskStatus = async function(subtaskId) {
            if (!this.task) return;

            try {
                const response = await fetch(`/tasks/${subtaskId}/toggle-status`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                if (response.ok) {
                    const updated = await response.json();
                    const index = this.task.subtasks.findIndex(s => s.id === subtaskId);
                    if (index !== -1) {
                        this.task.subtasks[index] = updated;
                    }
                }
            } catch (error) {
                console.error('Error toggling subtask:', error);
            }
        };

        instance.deleteSubtask = async function(subtaskId) {
            if (!this.task) return;

            try {
                const response = await fetch(`/tasks/${subtaskId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                if (response.ok) {
                    this.task.subtasks = this.task.subtasks.filter(s => s.id !== subtaskId);
                }
            } catch (error) {
                console.error('Error deleting subtask:', error);
            }
        };

        return instance;
    };
});
</script>
