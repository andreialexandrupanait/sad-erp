<x-app-layout>
    <x-slot name="pageTitle">{{ __('Tasks') }}</x-slot>

    <x-slot name="headerActions">
        <x-ui.button variant="default" onclick="window.location.href='{{ route('tasks.create') }}'">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('New Task') }}
        </x-ui.button>
    </x-slot>

    <!-- Main Content -->
    <div class="min-h-screen bg-white" x-data>
            <!-- ClickUp Views Bar Controller -->
            <x-tasks.views-bar-controller :currentView="$viewMode" />

            <!-- ClickUp Views Settings Bar -->
            <x-tasks.views-settings-bar
                currentGrouping="status"
                :showSubtasks="false"
                :showColumns="true"
                :activeFilters="0"
                :showClosed="false"
                :showAssignee="true"
                :meModeActive="false"
            />

        <!-- Success Message -->
        @if (session('success'))
            <x-ui.alert variant="success">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>{{ session('success') }}</div>
            </x-ui.alert>
        @endif

        <!-- ClickUp List View -->
        @if($viewMode === 'list')
            <x-tasks.clickup-list
                :tasksByStatus="$tasksByStatus"
                :taskStatuses="$taskStatuses"
                :lists="$lists"
                :users="$users"
                :services="$services"
                :taskPriorities="$taskPriorities"
            />
        @endif

        <!-- Table View -->
        @if($viewMode === 'table')
            <x-ui.card>
                @if($tasks->isEmpty())
                    <div class="px-6 py-16 text-center">
                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-slate-900">{{ __('No tasks') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Get started by creating your first task') }}</p>
                        <div class="mt-6">
                            <x-ui.button variant="default" onclick="window.location.href='{{ route('tasks.create') }}'">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('New Task') }}
                            </x-ui.button>
                        </div>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <!-- Inline Task Creator -->
                        <x-tasks.inline-create
                            :lists="$lists"
                            :taskStatuses="$taskStatuses"
                            :users="$users"
                            :services="$services"
                            :taskPriorities="$taskPriorities"
                        />

                        <table class="w-full caption-bottom text-sm" x-data="taskDragDrop()">
                            <thead class="[&_tr]:border-b">
                                <tr class="border-b transition-colors hover:bg-slate-50/50">
                                    <th class="h-12 w-12 px-4 text-left align-middle">
                                        <input
                                            type="checkbox"
                                            @change="$event.target.checked ? $store.taskBulk.selectAll(@json($tasks->pluck('id')->toArray())) : $store.taskBulk.clearSelection()"
                                            class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                        />
                                    </th>
                                    <th class="h-12 w-8 px-2 text-left align-middle"></th>
                                    <x-ui.sortable-header column="name" label="{{ __('Task') }}" />
                                    <th class="h-12 px-4 text-left align-middle font-medium text-slate-500">{{ __('List / Client') }}</th>
                                    <th class="h-12 px-4 text-left align-middle font-medium text-slate-500">{{ __('Status') }}</th>
                                    <th class="h-12 px-4 text-left align-middle font-medium text-slate-500">{{ __('Assigned To') }}</th>
                                    <th class="h-12 px-4 text-left align-middle font-medium text-slate-500">{{ __('Service') }}</th>
                                    <x-ui.sortable-header column="due_date" label="{{ __('Due Date') }}" />
                                    <th class="h-12 px-4 text-right align-middle font-medium text-slate-500">{{ __('Time') }}</th>
                                    <th class="h-12 px-4 text-right align-middle font-medium text-slate-500">{{ __('Total') }}</th>
                                    <th class="h-12 px-4 text-right align-middle font-medium text-slate-500">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="[&_tr:last-child]:border-0">
                                @foreach($tasks as $task)
                                    <tr
                                        draggable="true"
                                        @dragstart="dragStart($event, {{ $task->id }})"
                                        @dragend="dragEnd($event)"
                                        @dragover.prevent
                                        @dragenter="dragEnter($event, {{ $task->id }})"
                                        @dragleave="dragLeave($event)"
                                        @drop="drop($event, {{ $task->id }}, {{ $task->position }})"
                                        class="border-b transition-colors hover:bg-slate-50/50"
                                        :class="{ 'opacity-50': draggedTaskId === {{ $task->id }}, 'border-t-2 border-blue-500': dragOverTaskId === {{ $task->id }}, 'bg-blue-50/50': $store.taskBulk.isSelected({{ $task->id }}) }"
                                    >
                                        <td class="p-4 align-middle" @click.stop>
                                            <input
                                                type="checkbox"
                                                :checked="$store.taskBulk.isSelected({{ $task->id }})"
                                                @change="$store.taskBulk.toggleTask({{ $task->id }})"
                                                class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                            />
                                        </td>
                                        <td class="p-2 align-middle cursor-move" @click.stop>
                                            <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                            </svg>
                                        </td>
                                        <td class="p-4 align-middle cursor-pointer" @click="$dispatch('task-panel-open', {{ $task->id }})">
                                            <div class="font-medium">{{ $task->name }}</div>
                                            @if($task->description)
                                                <div class="text-sm text-slate-500 truncate max-w-md">{{ Str::limit($task->description, 60) }}</div>
                                            @endif
                                        </td>
                                        <td class="p-4 align-middle cursor-pointer" @click="$dispatch('task-panel-open', {{ $task->id }})">
                                            <div class="text-sm">{{ $task->list->name }}</div>
                                            @if($task->list->client)
                                                <div class="text-xs text-slate-500">{{ $task->list->client->name }}</div>
                                            @endif
                                        </td>
                                        <td class="p-4 align-middle cursor-pointer" @click="$dispatch('task-panel-open', {{ $task->id }})">
                                            @if($task->status)
                                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                                                      style="background-color: {{ $task->status->color }}20; color: {{ $task->status->color }}">
                                                    {{ $task->status->label }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="p-4 align-middle cursor-pointer" @click="$dispatch('task-panel-open', {{ $task->id }})">
                                            @if($task->assignedUser)
                                                <div class="text-sm">{{ $task->assignedUser->name }}</div>
                                            @else
                                                <span class="text-sm text-slate-400">{{ __('Unassigned') }}</span>
                                            @endif
                                        </td>
                                        <td class="p-4 align-middle cursor-pointer" @click="$dispatch('task-panel-open', {{ $task->id }})">
                                            @if($task->service)
                                                <div class="text-sm">{{ $task->service->name }}</div>
                                            @else
                                                <span class="text-sm text-slate-400">-</span>
                                            @endif
                                        </td>
                                        <td class="p-4 align-middle cursor-pointer" @click="$dispatch('task-panel-open', {{ $task->id }})">
                                            @if($task->due_date)
                                                <div class="text-sm">{{ $task->due_date->format('d.m.Y') }}</div>
                                                @if($task->due_date->isPast())
                                                    <span class="text-xs text-red-600">{{ __('Overdue') }}</span>
                                                @endif
                                            @else
                                                <span class="text-sm text-slate-400">-</span>
                                            @endif
                                        </td>
                                        <td class="p-4 align-middle text-right" @click.stop
                                            x-data="{
                                                editing: false,
                                                timeTracked: {{ $task->time_tracked }},
                                                displayTime: '{{ floor($task->time_tracked / 60) }}h {{ $task->time_tracked % 60 }}m',
                                                updateTime() {
                                                    fetch('{{ route('tasks.update-time', $task) }}', {
                                                        method: 'PATCH',
                                                        headers: {
                                                            'Content-Type': 'application/json',
                                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                            'Accept': 'application/json',
                                                        },
                                                        body: JSON.stringify({ time_tracked: this.timeTracked })
                                                    })
                                                    .then(response => response.json())
                                                    .then(data => {
                                                        if (data.success) {
                                                            const hours = Math.floor(this.timeTracked / 60);
                                                            const minutes = this.timeTracked % 60;
                                                            this.displayTime = hours + 'h ' + minutes + 'm';
                                                            this.editing = false;
                                                            // Update total amount cell
                                                            const totalCell = this.$el.nextElementSibling;
                                                            if (data.total_amount > 0) {
                                                                totalCell.querySelector('div').textContent = parseFloat(data.total_amount).toFixed(2) + ' RON';
                                                            }
                                                        }
                                                    })
                                                    .catch(error => console.error('Error:', error));
                                                }
                                            }">
                                            <div x-show="!editing"
                                                 @click="editing = true"
                                                 class="cursor-pointer inline-block hover:bg-slate-100 px-2 py-1 rounded"
                                                 title="{{ __('Click to edit time') }}">
                                                <span class="text-sm" x-text="displayTime || '-'"></span>
                                            </div>
                                            <div x-show="editing" x-cloak class="inline-block">
                                                <input
                                                    type="number"
                                                    x-model="timeTracked"
                                                    @blur="updateTime()"
                                                    @keydown.enter="updateTime()"
                                                    @keydown.escape="editing = false"
                                                    class="text-sm w-20 px-2 py-1 rounded border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                                    placeholder="0"
                                                    min="0"
                                                    x-init="$nextTick(() => { if (editing) $el.focus() })">
                                                <span class="text-xs text-slate-500 ml-1">min</span>
                                            </div>
                                        </td>
                                        <td class="p-4 align-middle text-right cursor-pointer" @click="$dispatch('task-panel-open', {{ $task->id }})">
                                            @if($task->total_amount > 0)
                                                <div class="text-sm font-medium">{{ number_format($task->total_amount, 2) }} RON</div>
                                            @else
                                                <span class="text-sm text-slate-400">-</span>
                                            @endif
                                        </td>
                                        <td class="p-4 align-middle text-right" @click.stop>
                                            <div class="flex justify-end gap-2">
                                                <x-ui.button variant="ghost" size="sm" onclick="window.location.href='{{ route('tasks.edit', $task) }}'">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </x-ui.button>
                                                <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this task?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-ui.button type="submit" variant="ghost" size="sm">
                                                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </x-ui.button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Drag & Drop Script -->
                        <script>
                        function taskDragDrop() {
                            return {
                                draggedTaskId: null,
                                dragOverTaskId: null,

                                dragStart(event, taskId) {
                                    this.draggedTaskId = taskId;
                                    event.dataTransfer.effectAllowed = 'move';
                                },

                                dragEnd(event) {
                                    this.draggedTaskId = null;
                                    this.dragOverTaskId = null;
                                },

                                dragEnter(event, taskId) {
                                    if (this.draggedTaskId !== taskId) {
                                        this.dragOverTaskId = taskId;
                                    }
                                },

                                dragLeave(event) {
                                    this.dragOverTaskId = null;
                                },

                                async drop(event, targetTaskId, targetPosition) {
                                    event.preventDefault();

                                    if (this.draggedTaskId === targetTaskId) {
                                        this.draggedTaskId = null;
                                        this.dragOverTaskId = null;
                                        return;
                                    }

                                    try {
                                        const response = await fetch(`/tasks/${this.draggedTaskId}/position`, {
                                            method: 'PATCH',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                                'Accept': 'application/json'
                                            },
                                            body: JSON.stringify({
                                                position: targetPosition
                                            })
                                        });

                                        const data = await response.json();

                                        if (data.success) {
                                            window.location.reload();
                                        } else {
                                            alert(data.message || 'Failed to reorder tasks');
                                        }
                                    } catch (error) {
                                        console.error('Error:', error);
                                        alert('An error occurred while reordering');
                                    }

                                    this.draggedTaskId = null;
                                    this.dragOverTaskId = null;
                                }
                            };
                        }
                        </script>
                    </div>

                    <!-- Pagination -->
                    <div class="px-4 py-3 border-t border-slate-200">
                        {{ $tasks->links() }}
                    </div>
                @endif
            </x-ui.card>
        @endif

        <!-- Kanban View -->
        @if($viewMode === 'kanban')
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" x-data="taskKanbanBoard()">
                @foreach($taskStatuses as $status)
                    <x-ui.card>
                        <x-ui.card-header>
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-slate-900">{{ $status->label }}</h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      style="background-color: {{ $status->color }}20; color: {{ $status->color }}">
                                    {{ $tasks->get($status->id, collect())->count() }}
                                </span>
                            </div>
                        </x-ui.card-header>
                        <x-ui.card-content>
                            <div class="space-y-2 kanban-column min-h-[200px]" data-status-id="{{ $status->id }}"
                                 @dragover="dragOver($event)"
                                 @drop="drop($event, {{ $status->id }})">
                                @foreach($tasks->get($status->id, collect()) as $task)
                                    <div class="kanban-card cursor-move p-3 bg-slate-50 hover:bg-slate-100 rounded-lg transition border border-transparent hover:border-slate-300"
                                         data-task-id="{{ $task->id }}"
                                         draggable="true"
                                         @dragstart="dragStart($event)"
                                         @dragend="dragEnd($event)">
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex-1">
                                                <div class="font-medium text-slate-900 text-sm">{{ $task->name }}</div>
                                                @if($task->list)
                                                    <div class="text-xs text-slate-500 mt-1">
                                                        {{ $task->list->name }}@if($task->list->client) - {{ $task->list->client->name }}@endif
                                                    </div>
                                                @endif
                                            </div>
                                            <a href="{{ route('tasks.edit', $task) }}" class="text-slate-400 hover:text-slate-600" onclick="event.stopPropagation()">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        </div>

                                        @if($task->description)
                                            <div class="text-xs text-slate-600 mb-2 line-clamp-2">
                                                {{ Str::limit($task->description, 80) }}
                                            </div>
                                        @endif

                                        <div class="space-y-1 text-xs">
                                            @if($task->assignedUser)
                                                <div class="flex items-center gap-1 text-slate-600">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                    </svg>
                                                    <span>{{ $task->assignedUser->name }}</span>
                                                </div>
                                            @endif

                                            @if($task->due_date)
                                                <div class="flex items-center gap-1 {{ $task->due_date->isPast() ? 'text-red-600' : 'text-slate-600' }}">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    <span>{{ $task->due_date->format('d.m.Y') }}</span>
                                                    @if($task->due_date->isPast())
                                                        <span class="font-medium">({{ __('Overdue') }})</span>
                                                    @endif
                                                </div>
                                            @endif

                                            @if($task->service)
                                                <div class="flex items-center gap-1 text-slate-600">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                    </svg>
                                                    <span>{{ $task->service->name }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        @if($task->time_tracked > 0 || $task->total_amount > 0)
                                            <div class="mt-2 pt-2 border-t border-slate-200 flex justify-between text-xs">
                                                @if($task->time_tracked > 0)
                                                    <div class="text-slate-600">
                                                        {{ floor($task->time_tracked / 60) }}h {{ $task->time_tracked % 60 }}m
                                                    </div>
                                                @endif
                                                @if($task->total_amount > 0)
                                                    <div class="text-slate-900 font-semibold">
                                                        {{ number_format($task->total_amount, 2) }} RON
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </x-ui.card-content>
                    </x-ui.card>
                @endforeach
            </div>

            <script>
                function taskKanbanBoard() {
                    return {
                        draggedElement: null,

                        dragStart(event) {
                            this.draggedElement = event.currentTarget;
                            event.currentTarget.classList.add('opacity-50');
                        },

                        dragEnd(event) {
                            event.currentTarget.classList.remove('opacity-50');
                        },

                        dragOver(event) {
                            event.preventDefault();
                        },

                        drop(event, newStatusId) {
                            event.preventDefault();

                            if (!this.draggedElement) return;

                            const taskId = this.draggedElement.dataset.taskId;
                            const oldColumn = this.draggedElement.closest('.kanban-column');
                            const oldStatusId = oldColumn ? oldColumn.dataset.statusId : null;
                            const targetColumn = event.currentTarget;

                            // Only proceed if status changed
                            if (oldStatusId && oldStatusId !== newStatusId.toString()) {
                                // Update status via AJAX
                                fetch(`/tasks/${taskId}/status`, {
                                    method: 'PATCH',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json',
                                    },
                                    body: JSON.stringify({ status_id: newStatusId })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        // Move the card to the new column
                                        targetColumn.appendChild(this.draggedElement);
                                        this.showNotification('{{ __("Task status updated successfully!") }}');

                                        // Update column counts
                                        this.updateColumnCounts();
                                    }
                                })
                                .catch(error => {
                                    console.error('Error updating status:', error);
                                    this.showNotification('{{ __("Error updating task") }}', 'error');
                                });
                            }

                            this.draggedElement = null;
                        },

                        updateColumnCounts() {
                            document.querySelectorAll('.kanban-column').forEach(column => {
                                const count = column.querySelectorAll('.kanban-card').length;
                                const badge = column.closest('.card').querySelector('[class*="rounded-full"]');
                                if (badge) {
                                    badge.textContent = count;
                                }
                            });
                        },

                        showNotification(message, type = 'success') {
                            const notification = document.createElement('div');
                            notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white`;
                            notification.textContent = message;
                            document.body.appendChild(notification);

                            setTimeout(() => {
                                notification.remove();
                            }, 3000);
                        }
                    }
                }
            </script>
        @endif

            <!-- Task Side Panel (ClickUp-style) -->
            <x-tasks.side-panel
                :taskStatuses="$taskStatuses"
                :taskPriorities="$taskPriorities"
                :users="$users"
            />

            <!-- Hierarchy Management Modals -->
            <x-tasks.modals.space-modal :spaces="$spaces" />
            <x-tasks.modals.folder-modal :spaces="$spaces" />
            <x-tasks.modals.list-modal :spaces="$spaces" :clients="$clients" />

            <!-- Bulk Actions Bar -->
            <x-tasks.bulk-actions-bar :taskStatuses="$taskStatuses" :lists="$lists" />

            <!-- Quick Switcher (Cmd+K) -->
            <x-tasks.quick-switcher :spaces="$spaces" :lists="$lists" :taskStatuses="$taskStatuses" />

            <!-- Keyboard Shortcuts Help -->
            <x-tasks.keyboard-shortcuts-help />

            <!-- Alpine.js Stores & Scripts -->
            <x-tasks.modals.alpine-store />
            <x-tasks.bulk-operations-store />
            <x-tasks.global-shortcuts />
    </div>
</x-app-layout>
