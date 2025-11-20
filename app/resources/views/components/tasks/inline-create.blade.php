@props(['lists' => [], 'taskStatuses' => [], 'users' => [], 'services' => [], 'taskPriorities' => []])

<div x-data="inlineTaskCreator()" class="border-b border-slate-200 bg-slate-50/50">
    <!-- Collapsed state: Just a button -->
    <div x-show="!creating" class="p-3">
        <button
            @click="creating = true; $nextTick(() => $refs.taskNameInput.focus())"
            class="w-full flex items-center gap-2 text-left text-slate-600 hover:text-slate-900 transition-colors"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span class="text-sm font-medium">{{ __('Add task') }}</span>
        </button>
    </div>

    <!-- Expanded state: Quick form -->
    <form x-show="creating" @submit.prevent="submitTask" class="p-3 space-y-3">
        <!-- Task Name -->
        <div>
            <input
                x-ref="taskNameInput"
                x-model="formData.name"
                type="text"
                placeholder="{{ __('Task name') }}"
                class="w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                required
            />
        </div>

        <!-- Expandable details -->
        <div x-show="showDetails" class="grid grid-cols-2 gap-3">
            <!-- List -->
            <div>
                <select
                    x-model="formData.list_id"
                    class="w-full px-3 py-2 text-sm border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                >
                    <option value="">{{ __('Select list') }}</option>
                    @foreach($lists as $list)
                        <option value="{{ $list->id }}">
                            {{ $list->name }}@if($list->client) - {{ $list->client->name }}@endif
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status -->
            <div>
                <select
                    x-model="formData.status_id"
                    class="w-full px-3 py-2 text-sm border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                >
                    <option value="">{{ __('Select status') }}</option>
                    @foreach($taskStatuses as $status)
                        <option value="{{ $status->id }}">{{ $status->label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Priority -->
            <div>
                <select
                    x-model="formData.priority_id"
                    class="w-full px-3 py-2 text-sm border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">{{ __('Priority') }}</option>
                    @foreach($taskPriorities as $priority)
                        <option value="{{ $priority->id }}">{{ $priority->label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Assigned To -->
            <div>
                <select
                    x-model="formData.assigned_to"
                    class="w-full px-3 py-2 text-sm border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">{{ __('Unassigned') }}</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Due Date -->
            <div>
                <input
                    x-model="formData.due_date"
                    type="date"
                    class="w-full px-3 py-2 text-sm border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
            <button
                type="button"
                @click="showDetails = !showDetails"
                class="text-xs text-slate-600 hover:text-slate-900"
            >
                <span x-show="!showDetails">{{ __('+ More details') }}</span>
                <span x-show="showDetails">{{ __('- Less details') }}</span>
            </button>

            <div class="flex gap-2">
                <button
                    type="button"
                    @click="cancel"
                    class="px-3 py-1.5 text-sm text-slate-600 hover:text-slate-900"
                >
                    {{ __('Cancel') }}
                </button>
                <button
                    type="submit"
                    :disabled="submitting || !formData.name"
                    class="px-4 py-1.5 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span x-show="!submitting">{{ __('Add Task') }}</span>
                    <span x-show="submitting">{{ __('Adding...') }}</span>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function inlineTaskCreator() {
    return {
        creating: false,
        showDetails: false,
        submitting: false,
        formData: {
            name: '',
            list_id: '',
            status_id: '{{ $taskStatuses->first()->id ?? "" }}',
            priority_id: '',
            assigned_to: '',
            due_date: '',
        },

        async submitTask() {
            if (!this.formData.name || !this.formData.list_id || !this.formData.status_id) {
                return;
            }

            this.submitting = true;

            try {
                const response = await fetch('{{ route("tasks.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.formData)
                });

                const data = await response.json();

                if (response.ok) {
                    // Reload page to show new task
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to create task');
                }
            } catch (error) {
                console.error('Error creating task:', error);
                alert('An error occurred while creating the task');
            } finally {
                this.submitting = false;
            }
        },

        cancel() {
            this.creating = false;
            this.showDetails = false;
            this.formData = {
                name: '',
                list_id: '',
                status_id: '{{ $taskStatuses->first()->id ?? "" }}',
                priority_id: '',
                assigned_to: '',
                due_date: '',
            };
        }
    }
}
</script>
