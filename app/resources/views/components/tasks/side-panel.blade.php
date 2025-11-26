<!-- Task Side Panel - ClickUp Style -->
<div
    x-data="taskSidePanel()"
    x-show="open"
    @keydown.escape.window="close()"
    @task-panel-open.window="openTask($event.detail)"
    class="relative z-50"
    style="display: none;"
>
    <!-- Backdrop -->
    <div
        x-show="open"
        x-transition:enter="transition-opacity ease-linear duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/50"
        @click="close()"
    ></div>

    <!-- Panel -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute inset-0 overflow-hidden">
            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                <div
                    x-show="open"
                    x-transition:enter="transform transition ease-in-out duration-300"
                    x-transition:enter-start="translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transform transition ease-in-out duration-300"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="translate-x-full"
                    class="pointer-events-auto w-screen max-w-2xl"
                >
                    <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl">
                        <!-- Loading State -->
                        <div x-show="loading" class="flex items-center justify-center h-full">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                        </div>

                        <!-- Content -->
                        <div x-show="!loading && task" class="flex-1 overflow-y-auto">
                            <!-- Header -->
                            <div class="sticky top-0 z-10 bg-white border-b border-[#e6e6e6] px-6 py-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <!-- Task Name (Editable) -->
                                        <div x-show="!editingName" @click="editingName = true" class="cursor-pointer group">
                                            <h2 class="text-xl font-semibold text-slate-900 group-hover:text-blue-600" x-text="task.name"></h2>
                                        </div>
                                        <div x-show="editingName" class="flex gap-2">
                                            <input
                                                x-ref="nameInput"
                                                x-model="task.name"
                                                @keydown.enter="updateField('name'); editingName = false"
                                                @keydown.escape="editingName = false"
                                                @blur="updateField('name'); editingName = false"
                                                class="flex-1 text-xl font-semibold border-0 border-b-2 border-blue-500 focus:outline-none focus:ring-0"
                                            />
                                        </div>

                                        <!-- Breadcrumb -->
                                        <div class="mt-1 flex items-center gap-2 text-sm text-slate-500">
                                            <span x-text="task.list?.name"></span>
                                            <template x-if="task.list?.client">
                                                <span>• <span x-text="task.list.client.name"></span></span>
                                            </template>
                                        </div>
                                    </div>

                                    <button @click="close()" class="ml-3 text-slate-400 hover:text-slate-500">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Body -->
                            <div class="px-6 py-6 space-y-6">
                                <!-- Quick Actions Row -->
                                <div class="grid grid-cols-4 gap-3">
                                    <!-- Status -->
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Status') }}</label>
                                        <select
                                            x-model="task.status_id"
                                            @change="updateField('status_id')"
                                            class="w-full px-2 py-1.5 text-sm border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        >
                                            <template x-for="status in taskStatuses" :key="status.id">
                                                <option :value="status.id" x-text="status.label"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <!-- Priority -->
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Priority') }}</label>
                                        <select
                                            x-model="task.priority_id"
                                            @change="updateField('priority_id')"
                                            class="w-full px-2 py-1.5 text-sm border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        >
                                            <option value="">{{ __('None') }}</option>
                                            <template x-for="priority in taskPriorities" :key="priority.id">
                                                <option :value="priority.id" x-text="priority.label"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <!-- Assigned To -->
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Assignee') }}</label>
                                        <select
                                            x-model="task.assigned_to"
                                            @change="updateField('assigned_to')"
                                            class="w-full px-2 py-1.5 text-sm border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        >
                                            <option value="">{{ __('Unassigned') }}</option>
                                            <template x-for="user in users" :key="user.id">
                                                <option :value="user.id" x-text="user.name"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <!-- Due Date -->
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Due Date') }}</label>
                                        <input
                                            type="date"
                                            x-model="task.due_date"
                                            @change="updateField('due_date')"
                                            class="w-full px-2 py-1.5 text-sm border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                </div>

                                <!-- Description -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Description') }}</label>
                                    <textarea
                                        x-model="task.description"
                                        @blur="updateField('description')"
                                        rows="4"
                                        placeholder="{{ __('Add a description...') }}"
                                        class="w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    ></textarea>
                                </div>

                                <!-- Time Tracking & Billing -->
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Time (minutes)') }}</label>
                                        <input
                                            type="number"
                                            x-model="task.time_tracked"
                                            @blur="updateField('time_tracked')"
                                            min="0"
                                            class="w-full px-2 py-1.5 text-sm border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Rate (RON/h)') }}</label>
                                        <input
                                            type="number"
                                            x-model="task.amount"
                                            @blur="updateField('amount')"
                                            step="0.01"
                                            min="0"
                                            class="w-full px-2 py-1.5 text-sm border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Total') }}</label>
                                        <div class="px-2 py-1.5 text-sm bg-slate-50 border border-[#e6e6e6] rounded-md">
                                            <span x-text="((task.time_tracked / 60) * task.amount).toFixed(2)"></span> RON
                                        </div>
                                    </div>
                                </div>

                                <!-- Tabs: Subtasks, Checklists, Comments, Attachments -->
                                <div x-data="{ activeTab: 'subtasks' }">
                                    <!-- Tab Headers -->
                                    <div class="border-b border-[#e6e6e6]">
                                        <nav class="-mb-px flex space-x-6">
                                            <button
                                                @click="activeTab = 'subtasks'"
                                                :class="activeTab === 'subtasks' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                                                class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm"
                                            >
                                                {{ __('Subtasks') }}
                                                <span class="ml-2 py-0.5 px-2 rounded-full text-xs bg-slate-100" x-text="task.subtasks?.length || 0"></span>
                                            </button>
                                            <button
                                                @click="activeTab = 'checklists'"
                                                :class="activeTab === 'checklists' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                                                class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm"
                                            >
                                                {{ __('Checklists') }}
                                                <span class="ml-2 py-0.5 px-2 rounded-full text-xs bg-slate-100" x-text="task.checklists?.length || 0"></span>
                                            </button>
                                            <button
                                                @click="activeTab = 'dependencies'"
                                                :class="activeTab === 'dependencies' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                                                class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm"
                                            >
                                                {{ __('Dependencies') }}
                                                <span class="ml-2 py-0.5 px-2 rounded-full text-xs bg-slate-100" x-text="(task.dependencies?.length || 0) + (task.dependents?.length || 0)"></span>
                                            </button>
                                            <button
                                                @click="activeTab = 'comments'"
                                                :class="activeTab === 'comments' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                                                class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm"
                                            >
                                                {{ __('Comments') }}
                                                <span class="ml-2 py-0.5 px-2 rounded-full text-xs bg-slate-100" x-text="task.comments?.length || 0"></span>
                                            </button>
                                            <button
                                                @click="activeTab = 'attachments'"
                                                :class="activeTab === 'attachments' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                                                class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm"
                                            >
                                                {{ __('Attachments') }}
                                                <span class="ml-2 py-0.5 px-2 rounded-full text-xs bg-slate-100" x-text="task.attachments?.length || 0"></span>
                                            </button>
                                            <button
                                                @click="activeTab = 'activity'"
                                                :class="activeTab === 'activity' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                                                class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm"
                                            >
                                                {{ __('Activity') }}
                                                <span class="ml-2 py-0.5 px-2 rounded-full text-xs bg-slate-100" x-text="task.activities?.length || 0"></span>
                                            </button>
                                            <button
                                                @click="activeTab = 'time-tracking'"
                                                :class="activeTab === 'time-tracking' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                                                class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm"
                                            >
                                                {{ __('Time Tracking') }}
                                                <span class="ml-2 py-0.5 px-2 rounded-full text-xs bg-slate-100" x-text="task.time_tracked || 0"></span>
                                            </button>
                                        </nav>
                                    </div>

                                    <!-- Tab Content -->
                                    <div class="mt-4">
                                        <!-- Subtasks Tab -->
                                        <div x-show="activeTab === 'subtasks'">
                                            <x-tasks.subtasks-section />
                                        </div>

                                        <!-- Checklists Tab -->
                                        <div x-show="activeTab === 'checklists'">
                                            <!-- Add Checklist Button -->
                                            <div class="mb-4">
                                                <button @click="addChecklist" type="button"
                                                        class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-md hover:bg-slate-50 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                    {{ __('Add Checklist') }}
                                                </button>
                                            </div>

                                            <!-- Checklists List -->
                                            <div class="space-y-3">
                                                <template x-for="checklistItem in task.checklists || []" :key="checklistItem.id">
                                                    <div class="border border-[#e6e6e6] rounded-lg bg-white p-3">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <div class="flex-1">
                                                                <h4 class="font-medium text-slate-900" x-text="checklistItem.name"></h4>
                                                            </div>
                                                            <div class="text-xs text-slate-600">
                                                                <span x-text="checklistItem.items?.filter(i => i.is_completed).length || 0"></span>/<span x-text="checklistItem.items?.length || 0"></span>
                                                            </div>
                                                        </div>
                                                        <template x-for="item in checklistItem.items || []" :key="item.id">
                                                            <div class="flex items-center gap-2 py-1">
                                                                <input type="checkbox"
                                                                       :checked="item.is_completed"
                                                                       class="w-4 h-4 rounded border-slate-300">
                                                                <span class="text-sm" :class="item.is_completed ? 'line-through text-slate-500' : 'text-slate-700'" x-text="item.text"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                                <template x-if="!task.checklists || task.checklists.length === 0">
                                                    <div class="text-center py-8 text-slate-500 text-sm">
                                                        {{ __('No checklists yet') }}
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Dependencies Tab -->
                                        <div x-show="activeTab === 'dependencies'" class="p-4">
                                            <x-tasks.dependencies-section />
                                        </div>

                                        <!-- Comments Tab -->
                                        <div x-show="activeTab === 'comments'">
                                            <x-tasks.comments-section />
                                        </div>

                                        <!-- Attachments Tab -->
                                        <div x-show="activeTab === 'attachments'">
                                            <x-tasks.attachments-section />
                                        </div>

                                        <!-- Activity Tab -->
                                        <div x-show="activeTab === 'activity'">
                                            <x-tasks.activity-section />
                                        </div>

                                        <!-- Time Tracking Tab -->
                                        <div x-show="activeTab === 'time-tracking'">
                                            <x-tasks.time-tracking-section />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function taskSidePanel() {
    return {
        open: false,
        loading: false,
        editingName: false,
        task: null,
        taskStatuses: @json($taskStatuses ?? []),
        taskPriorities: @json($taskPriorities ?? []),
        users: @json($users ?? []),

        async openTask(taskId) {
            this.open = true;
            this.loading = true;

            try {
                const response = await fetch(`/tasks/${taskId}/details`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                if (response.ok) {
                    this.task = await response.json();
                } else {
                    console.error('Failed to load task');
                    this.close();
                }
            } catch (error) {
                console.error('Error loading task:', error);
                this.close();
            } finally {
                this.loading = false;
            }
        },

        async updateField(field) {
            if (!this.task) return;

            const data = { [field]: this.task[field] };

            try {
                const response = await fetch(`/tasks/${this.task.id}/quick-update`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                });

                if (!response.ok) {
                    console.error('Failed to update task');
                }
            } catch (error) {
                console.error('Error updating task:', error);
            }
        },

        async addChecklist() {
            const checklistName = prompt('Enter checklist name:');
            if (!checklistName || !checklistName.trim()) {
                return;
            }

            try {
                const response = await fetch(`/tasks/${this.task.id}/checklists`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ name: checklistName })
                });

                if (response.ok) {
                    const data = await response.json();
                    if (!this.task.checklists) {
                        this.task.checklists = [];
                    }
                    this.task.checklists.push(data.checklist);
                }
            } catch (error) {
                console.error('Error adding checklist:', error);
            }
        },

        close() {
            this.open = false;
            this.task = null;
            this.editingName = false;

            // Reload page to reflect changes
            setTimeout(() => {
                window.location.reload();
            }, 300);
        }
    }
}
</script>
