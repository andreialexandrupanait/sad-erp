{{-- ClickUp List View - Pixel Perfect Replica --}}
@props(['tasksByStatus', 'taskCountsByStatus', 'taskStatuses', 'lists', 'users', 'services', 'taskPriorities'])

<div class="bg-white overflow-hidden p-4" x-data="clickupListView()" x-init="init()">

    {{-- Bulk Actions Toolbar --}}
    <div x-show="selectedTasks.length > 0"
         x-transition
         x-cloak
         class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="text-sm font-medium text-blue-900" x-text="`${selectedTasks.length} task${selectedTasks.length > 1 ? 's' : ''} selected`"></span>
            <button @click="clearSelection()" class="text-sm text-blue-600 hover:text-blue-800">Clear</button>
        </div>

        <div class="flex items-center gap-2">
            {{-- Status --}}
            <select @change="bulkUpdateStatus($event.target.value); $event.target.value = ''" class="text-sm border-blue-300 rounded-md">
                <option value="">Change Status</option>
                @foreach($taskStatuses as $status)
                    <option value="{{ $status->id }}">{{ $status->label }}</option>
                @endforeach
            </select>

            {{-- Priority --}}
            <select @change="bulkUpdatePriority($event.target.value); $event.target.value = ''" class="text-sm border-blue-300 rounded-md">
                <option value="">Change Priority</option>
                @foreach($taskPriorities as $priority)
                    <option value="{{ $priority->id }}">{{ $priority->label }}</option>
                @endforeach
            </select>

            {{-- Assignee --}}
            <select @change="bulkUpdateAssignee($event.target.value); $event.target.value = ''" class="text-sm border-blue-300 rounded-md">
                <option value="">Assign To</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>

            {{-- List --}}
            <select @change="bulkUpdateList($event.target.value); $event.target.value = ''" class="text-sm border-blue-300 rounded-md">
                <option value="">Move to List</option>
                @foreach($lists as $list)
                    <option value="{{ $list->id }}">{{ $list->name }}</option>
                @endforeach
            </select>

            <div class="w-px h-6 bg-blue-300"></div>

            {{-- Delete --}}
            <button @click="bulkDelete()" class="px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded-md transition-colors">
                Delete
            </button>
        </div>
    </div>

    {{-- Table Container with ClickUp-style scroll --}}
    <div id="task-view-container">
        <div id="task-scroll-wrapper">
            <div id="task-table">
                <table class="w-full" style="border-collapse: collapse; border-spacing: 0;">
                    <tbody>
                @foreach($taskStatuses as $status)
                    @php
                        $statusTasks = $tasksByStatus->get($status->id, collect());
                    @endphp

                    {{-- Status Group --}}
                    <tr class="border-0">
                        <td colspan="100%" class="p-0 border-0 @if(!$loop->first) pt-6 @endif">
                            <div x-data="{
                                expanded: (() => {
                                    const saved = localStorage.getItem('status_{{ $status->id }}_expanded');
                                    return saved !== null ? saved === 'true' : {{ $statusTasks->isNotEmpty() ? 'true' : 'false' }};
                                })()
                            }"
                            x-init="$watch('expanded', value => localStorage.setItem('status_{{ $status->id }}_expanded', value))"
                            class="w-full">
                                {{-- Group Header Row 1: Status Badge + Count + Actions --}}
                                <div @click="expanded = !expanded"
                                     data-status-id="{{ $status->id }}"
                                     class="flex items-center gap-2 h-9 px-3 cursor-pointer">
                                    <button class="flex-shrink-0" @click.stop="expanded = !expanded">
                                        <svg class="w-2.5 h-2.5 text-slate-400 transition-transform"
                                             :class="expanded ? 'rotate-90' : ''"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>

                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold uppercase px-2 py-1 bg-[#fafafa] rounded"
                                              style="color: {{ $status->color }}">
                                            {{ $status->label }}
                                        </span>
                                        <span class="text-sm text-slate-500">{{ $taskCountsByStatus->get($status->id, 0) }}</span>
                                    </div>

                                    <div class="flex items-center gap-1" @click.stop>
                                        <button @click="$dispatch('open-task-modal', { status_id: {{ $status->id }} })"
                                                class="flex items-center gap-1 px-2 py-0.5 text-sm text-slate-600 hover:bg-slate-200 rounded transition-colors">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            {{ __('Add Task') }}
                                        </button>
                                        <button class="p-0.5 hover:bg-slate-200 rounded transition-colors">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                {{-- Group Header Row 2: Column Headers --}}
                                <div x-show="expanded" x-collapse x-cloak>
                                    <div class="flex items-center h-9 bg-white border-b border-[#f0f0f0] text-sm font-semibold text-slate-500">
                                        {{-- Checkbox Column --}}
                                        <div class="w-8 px-2 flex-shrink-0">
                                        </div>

                                        {{-- Name Column (Fixed, Resizable) --}}
                                        <div class="px-3 flex-shrink-0 relative group" :style="`width: ${columns.name.width}px`" data-column="name">
                                            <div class="flex items-center justify-between">
                                                <span>{{ __('Name') }}</span>
                                                <button class="p-0.5 text-slate-400 hover:text-slate-600 opacity-0 group-hover:opacity-100">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            {{-- Resize Handle --}}
                                            <div class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize hover:bg-blue-500 opacity-0 group-hover:opacity-50" data-resize-column="name"></div>
                                        </div>

                                        {{-- Project Column --}}
                                        <div class="px-3 flex-shrink-0 relative group hover:border-r hover:border-[#f0f0f0]" :style="`width: ${columns.project.width}px`" data-column="project">
                                            <div class="flex items-center justify-between">
                                                <span>{{ __('Project') }}</span>
                                                <button class="p-0.5 text-slate-400 hover:text-slate-600 opacity-0 group-hover:opacity-100">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize hover:bg-blue-500 opacity-0 group-hover:opacity-50" data-resize-column="project"></div>
                                        </div>

                                        {{-- Serviciu Column --}}
                                        <div class="px-3 flex-shrink-0 relative group hover:border-r hover:border-[#f0f0f0]" :style="`width: ${columns.service.width}px`" data-column="service">
                                            <div class="flex items-center justify-between">
                                                <span>{{ __('Serviciu') }}</span>
                                                <button class="p-0.5 text-slate-400 hover:text-slate-600 opacity-0 group-hover:opacity-100">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize hover:bg-blue-500 opacity-0 group-hover:opacity-50" data-resize-column="service"></div>
                                        </div>

                                        {{-- Due Date Column --}}
                                        <div class="px-3 flex-shrink-0 relative group hover:border-r hover:border-[#f0f0f0]" :style="`width: ${columns.due_date.width}px`" data-column="due_date">
                                            <div class="flex items-center justify-between">
                                                <span>{{ __('Due date') }}</span>
                                                <button class="p-0.5 text-slate-400 hover:text-slate-600 opacity-0 group-hover:opacity-100">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize hover:bg-blue-500 opacity-0 group-hover:opacity-50" data-resize-column="due_date"></div>
                                        </div>

                                        {{-- Status Column --}}
                                        <div class="px-3 flex-shrink-0 relative group hover:border-r hover:border-[#f0f0f0]" :style="`width: ${columns.status.width}px`" data-column="status">
                                            <div class="flex items-center justify-between">
                                                <span>{{ __('Status') }}</span>
                                                <button class="p-0.5 text-slate-400 hover:text-slate-600 opacity-0 group-hover:opacity-100">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize hover:bg-blue-500 opacity-0 group-hover:opacity-50" data-resize-column="status"></div>
                                        </div>

                                        {{-- Priority Column --}}
                                        <div class="px-3 flex-shrink-0 relative group hover:border-r hover:border-[#f0f0f0]" :style="`width: ${columns.priority.width}px`" data-column="priority">
                                            <div class="flex items-center justify-between">
                                                <span>{{ __('Priority') }}</span>
                                                <button class="p-0.5 text-slate-400 hover:text-slate-600 opacity-0 group-hover:opacity-100">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize hover:bg-blue-500 opacity-0 group-hover:opacity-50" data-resize-column="priority"></div>
                                        </div>

                                        {{-- Assignee Column --}}
                                        <div class="px-3 flex-shrink-0 relative group hover:border-r hover:border-[#f0f0f0]" :style="`width: ${columns.assignee.width}px`" data-column="assignee">
                                            <div class="flex items-center justify-between">
                                                <span>{{ __('Assignee') }}</span>
                                                <button class="p-0.5 text-slate-400 hover:text-slate-600 opacity-0 group-hover:opacity-100">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize hover:bg-blue-500 opacity-0 group-hover:opacity-50" data-resize-column="assignee"></div>
                                        </div>

                                        {{-- Time Tracked Column --}}
                                        <div class="px-3 flex-shrink-0 relative group hover:border-r hover:border-[#f0f0f0]" :style="`width: ${columns.time_tracked.width}px`" data-column="time_tracked">
                                            <div class="flex items-center justify-between">
                                                <span>{{ __('Time tracked') }}</span>
                                                <button class="p-0.5 text-slate-400 hover:text-slate-600 opacity-0 group-hover:opacity-100">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize hover:bg-blue-500 opacity-0 group-hover:opacity-50" data-resize-column="time_tracked"></div>
                                        </div>

                                        {{-- Amount Column --}}
                                        <div class="px-3 flex-shrink-0 relative group hover:border-r hover:border-[#f0f0f0]" :style="`width: ${columns.amount.width}px`" data-column="amount">
                                            <div class="flex items-center justify-between">
                                                <span>{{ __('Amount') }}</span>
                                                <button class="p-0.5 text-slate-400 hover:text-slate-600 opacity-0 group-hover:opacity-100">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize hover:bg-blue-500 opacity-0 group-hover:opacity-50" data-resize-column="amount"></div>
                                        </div>

                                        {{-- Total Charge Column --}}
                                        <div class="px-3 flex-shrink-0 relative group hover:border-r hover:border-[#f0f0f0]" :style="`width: ${columns.total_charge.width}px`" data-column="total_charge">
                                            <div class="flex items-center justify-between">
                                                <span>{{ __('Total Charge') }}</span>
                                                <button class="p-0.5 text-slate-400 hover:text-slate-600 opacity-0 group-hover:opacity-100">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize hover:bg-blue-500 opacity-0 group-hover:opacity-50" data-resize-column="total_charge"></div>
                                        </div>

                                        {{-- Add Column Button --}}
                                        <div class="px-2 flex-shrink-0">
                                            <button class="p-1 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Task Rows --}}
                                    <div>
                                        @forelse($statusTasks as $task)
                                            <div class="flex items-center h-9 py-2 border-b border-[#f0f0f0] hover:bg-[#fafafa] group"
                                                 x-data="taskRow({{ $task->id }}, @js([
                                                     'name' => $task->name,
                                                     'list_id' => $task->list_id,
                                                     'due_date' => $task->due_date?->format('Y-m-d'),
                                                     'start_date' => $task->start_date?->format('Y-m-d'),
                                                     'time_tracked' => $task->time_tracked ?? 0,
                                                     'time_estimate' => $task->time_estimate ?? 0,
                                                     'amount' => $task->amount ?? 0,
                                                     'total_amount' => $task->total_amount ?? 0,
                                                     'service_id' => $task->service_id,
                                                     'status_id' => $task->status_id,
                                                     'status_label' => $task->status?->label,
                                                     'status_color' => $task->status?->color_class,
                                                     'priority_id' => $task->priority_id,
                                                     'priority_label' => $task->priority?->label,
                                                     'priority_color' => $task->priority?->color,
                                                     'assigned_to' => $task->assigned_to
                                                 ]))"
                                                 draggable="true">

                                                {{-- Checkbox --}}
                                                <div class="w-8 px-2 flex-shrink-0" @click.stop>
                                                    <input type="checkbox"
                                                           :value="{{ $task->id }}"
                                                           @change="toggleTaskSelection({{ $task->id }})"
                                                           :checked="selectedTasks.includes({{ $task->id }})"
                                                           class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600 focus:ring-1 focus:ring-blue-500 cursor-pointer transition-opacity"
                                                           :class="selectedTasks.length > 0 || selectedTasks.includes({{ $task->id }}) ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'">
                                                </div>

                                                {{-- Name (editable) --}}
                                                <div class="px-3 flex-shrink-0" :style="`width: ${columns.name.width}px`">
                                                    <div class="flex items-center gap-2">
                                                        {{-- Status Icon --}}
                                                        <div class="relative flex-shrink-0">
                                                            <button @click="openStatusMenu({{ $task->id }}, $event)"
                                                                    class="w-5 h-5 rounded-full flex items-center justify-center transition-colors hover:bg-slate-100 p-0.5"
                                                                    style="color: {{ $status->color_class }}"
                                                                    :data-task-id="{{ $task->id }}">
                                                                @php
                                                                    $statusLabel = strtoupper($status->label);
                                                                @endphp
                                                                @if($statusLabel === 'TO DO')
                                                                    {{-- Empty circle --}}
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                                        <circle cx="12" cy="12" r="9"/>
                                                                    </svg>
                                                                @elseif($statusLabel === 'IN PROGRESS')
                                                                    {{-- Circle with dot --}}
                                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                                        <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/>
                                                                        <circle cx="12" cy="12" r="4"/>
                                                                    </svg>
                                                                @elseif($statusLabel === 'FEEDBACK')
                                                                    {{-- Circle with checkmark --}}
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                                        <circle cx="12" cy="12" r="9"/>
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                                    </svg>
                                                                @elseif($statusLabel === 'DONE')
                                                                    {{-- Filled circle with checkmark --}}
                                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                                        <circle cx="12" cy="12" r="10"/>
                                                                        <path d="M9 12l2 2 4-4" stroke="white" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                                                                    </svg>
                                                                @elseif($statusLabel === 'CANCELED' || $statusLabel === 'CANCELLED')
                                                                    {{-- Circle with X --}}
                                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                                        <circle cx="12" cy="12" r="10"/>
                                                                        <path d="M8 8l8 8M16 8l-8 8" stroke="white" stroke-width="2" fill="none" stroke-linecap="round"/>
                                                                    </svg>
                                                                @elseif($statusLabel === 'COMPLETED')
                                                                    {{-- Filled circle with checkmark --}}
                                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                                        <circle cx="12" cy="12" r="10"/>
                                                                        <path d="M9 12l2 2 4-4" stroke="white" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                                                                    </svg>
                                                                @else
                                                                    {{-- Default: circle --}}
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                                        <circle cx="12" cy="12" r="9"/>
                                                                    </svg>
                                                                @endif
                                                            </button>
                                                        </div>
                                                        <div class="flex-1 min-w-0 flex items-center gap-2">
                                                            <div class="flex-1 min-w-0">
                                                                <div x-show="!editing.name"
                                                                     @click="editing.name = true; $nextTick(() => $refs.nameInput?.focus())"
                                                                     class="text-sm text-slate-900 truncate cursor-text hover:bg-white px-1 -mx-1 rounded">
                                                                    <span x-text="data.name"></span>
                                                                </div>
                                                                <input x-show="editing.name"
                                                                       x-cloak
                                                                       x-ref="nameInput"
                                                                       x-model="data.name"
                                                                       @blur="updateField('name'); editing.name = false"
                                                                       @keydown.enter="updateField('name'); editing.name = false"
                                                                       @keydown.escape="editing.name = false"
                                                                       class="w-full text-sm px-1 -mx-1 border border-blue-500 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                                                            </div>

                                                            {{-- Checklist Progress Indicator --}}
                                                            @php
                                                                $totalItems = 0;
                                                                $completedItems = 0;
                                                                if ($task->relationLoaded('checklists')) {
                                                                    foreach ($task->checklists as $checklist) {
                                                                        $totalItems += $checklist->items->count();
                                                                        $completedItems += $checklist->items->where('is_completed', true)->count();
                                                                    }
                                                                }
                                                            @endphp
                                                            @if($totalItems > 0)
                                                                <div class="flex items-center gap-1 px-1.5 py-0.5 rounded text-xs hover:bg-slate-100 transition-colors"
                                                                     :class="{ 'text-green-600': {{ $completedItems }} === {{ $totalItems }}, 'text-slate-600': {{ $completedItems }} < {{ $totalItems }} }"
                                                                     title="{{ __('Checklist items') }}">
                                                                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                                                    </svg>
                                                                    <span class="font-medium">{{ $completedItems }}/{{ $totalItems }}</span>
                                                                </div>
                                                            @endif

                                                            {{-- Dependency Indicator --}}
                                                            @if($task->relationLoaded('dependencies') && $task->dependencies->isNotEmpty())
                                                                @php
                                                                    $incompleteDeps = $task->dependencies->filter(function($dep) {
                                                                        $status = $dep->dependsOnTask->status->name ?? '';
                                                                        return !in_array(strtolower($status), ['done', 'completed', 'closed']);
                                                                    })->count();
                                                                @endphp
                                                                @if($incompleteDeps > 0)
                                                                    <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-600 border border-red-200"
                                                                         title="{{ $incompleteDeps }} incomplete {{ $incompleteDeps === 1 ? 'dependency' : 'dependencies' }}">
                                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                                        </svg>
                                                                        {{ $incompleteDeps }}
                                                                    </div>
                                                                @endif
                                                            @endif

                                                            {{-- Tags Display --}}
                                                            @if($task->relationLoaded('tags') && $task->tags->isNotEmpty())
                                                                @foreach($task->tags->take(2) as $tag)
                                                                    <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium"
                                                                         style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}"
                                                                         title="{{ $tag->name }}">
                                                                        {{ Str::limit($tag->name, 10) }}
                                                                    </div>
                                                                @endforeach
                                                                @if($task->tags->count() > 2)
                                                                    <div class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600"
                                                                         title="{{ $task->tags->count() - 2 }} more tags">
                                                                        +{{ $task->tags->count() - 2 }}
                                                                    </div>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Project/List (editable dropdown) --}}
                                                <div class="px-3 flex-shrink-0" :style="`width: ${columns.project.width}px`" x-data="{ showListDropdown: false }">
                                                    <div class="relative">
                                                        <button @click="showListDropdown = !showListDropdown"
                                                                class="w-full text-left text-sm text-slate-600 truncate hover:bg-slate-50 px-1 -mx-1 rounded">
                                                            {{ $task->list?->name ?? '–' }}
                                                        </button>

                                                        {{-- List Dropdown --}}
                                                        <div x-show="showListDropdown"
                                                             @click.away="showListDropdown = false"
                                                             class="absolute left-0 top-full mt-1 w-72 bg-white rounded-lg shadow-xl border border-slate-200 py-2 z-50 max-h-96 overflow-y-auto"
                                                             x-cloak>
                                                            {{-- Search --}}
                                                            <div class="px-3 pb-2">
                                                                <input type="text"
                                                                       placeholder="{{ __('Search lists...') }}"
                                                                       class="w-full px-2 py-1 text-sm border border-slate-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                                                            </div>

                                                            <div class="border-t border-slate-200 mb-1"></div>

                                                            {{-- Lists grouped by Space/Folder --}}
                                                            @foreach($lists as $list)
                                                                <button @click="updateListField({{ $list->id }}); showListDropdown = false"
                                                                        class="w-full px-3 py-1.5 text-left text-sm hover:bg-slate-50 flex items-center gap-2.5">
                                                                    <svg class="w-3 h-3 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                                    </svg>
                                                                    <div class="flex-1 min-w-0">
                                                                        <div class="truncate">{{ $list->name }}</div>
                                                                        @if($list->client)
                                                                            <div class="text-xs text-slate-400 truncate">{{ $list->client->name }}</div>
                                                                        @endif
                                                                    </div>
                                                                    @if($task->list_id === $list->id)
                                                                        <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                                        </svg>
                                                                    @endif
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Service/Custom Field (enhanced with colored pills) --}}
                                                <div class="px-3 flex-shrink-0 relative" :style="`width: ${columns.service.width}px`"
                                                     x-data="{
                                                         showServiceDropdown: false,
                                                         searchQuery: '',

                                                         get filteredServices() {
                                                             if (!this.searchQuery) return @js($services);
                                                             return @js($services).filter(service =>
                                                                 service.name.toLowerCase().includes(this.searchQuery.toLowerCase())
                                                             );
                                                         }
                                                     }">
                                                        <button @click="showServiceDropdown = !showServiceDropdown"
                                                                class="w-full text-left text-sm px-2 py-1 rounded hover:bg-[#fafafa] transition-colors">
                                                            @if($task->service)
                                                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium"
                                                                      style="background-color: {{ $task->service->color }}20; color: {{ $task->service->color }}">
                                                                    {{ $task->service->name }}
                                                                </span>
                                                            @else
                                                                <span class="text-slate-400">Select service...</span>
                                                            @endif
                                                        </button>

                                                        {{-- Service Dropdown --}}
                                                        <div x-show="showServiceDropdown"
                                                             @click.away="showServiceDropdown = false"
                                                             x-cloak
                                                             class="absolute left-0 top-full mt-1 w-64 bg-white rounded-lg shadow-xl border border-slate-200 py-2 z-50 max-h-80 overflow-y-auto">

                                                            {{-- Search --}}
                                                            <div class="px-3 pb-2">
                                                                <input type="text"
                                                                       x-model="searchQuery"
                                                                       placeholder="Search or add options..."
                                                                       class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                                            </div>

                                                            {{-- None Option --}}
                                                            <button @click="updateServiceField(null); showServiceDropdown = false"
                                                                    class="w-full px-3 py-2 text-left text-sm hover:bg-slate-50 text-slate-500">
                                                                None
                                                            </button>

                                                            <div class="border-t border-slate-200 my-1"></div>

                                                            {{-- Service Options as Colored Pills --}}
                                                            <template x-for="service in filteredServices" :key="service.id">
                                                                <button @click="updateServiceField(service.id); showServiceDropdown = false"
                                                                        class="w-full px-3 py-2 text-left text-sm hover:bg-slate-50 flex items-center gap-2.5">
                                                                    {{-- Drag Handle Icon (for visual consistency) --}}
                                                                    <svg class="w-4 h-4 text-slate-300" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                                                    </svg>

                                                                    {{-- Colored Pill --}}
                                                                    <span class="flex-1 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                                                          :style="`background-color: ${service.color}20; color: ${service.color}`"
                                                                          x-text="service.name"></span>

                                                                    {{-- Checkmark if selected --}}
                                                                    <span x-show="{{ $task->service_id }} === service.id"
                                                                          class="w-5 h-5 rounded-full bg-purple-600 flex items-center justify-center">
                                                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                                        </svg>
                                                                    </span>
                                                                </button>
                                                            </template>
                                                        </div>
                                                </div>

                                                {{-- Due Date (ClickUp-style date picker) --}}
                                                <div class="px-3 flex-shrink-0" :style="`width: ${columns.due_date.width}px`">
                                                    <button @click="openDatePicker({{ $task->id }}, $event, 'due', '{{ $task->start_date?->format('Y-m-d') }}', '{{ $task->due_date?->format('Y-m-d') }}')"
                                                            class="w-full text-left text-sm px-2 py-1 rounded hover:bg-[#fafafa] transition-colors"
                                                            :data-task-id="{{ $task->id }}">
                                                        @if($task->due_date)
                                                            @php
                                                                $today = \Carbon\Carbon::today();
                                                                $isPast = $task->due_date->isPast();
                                                                $isToday = $task->due_date->isToday();
                                                                $isTomorrow = $task->due_date->isTomorrow();
                                                            @endphp
                                                            <span class="inline-flex items-center gap-1.5"
                                                                  :class="{
                                                                      'text-red-600': {{ $isPast ? 'true' : 'false' }},
                                                                      'text-purple-600': {{ $isToday ? 'true' : 'false' }},
                                                                      'text-slate-600': {{ !$isPast && !$isToday ? 'true' : 'false' }}
                                                                  }">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                                </svg>
                                                                @if($task->start_date)
                                                                    <span class="text-xs">{{ $task->start_date->format('d/m') }} → </span>
                                                                @endif
                                                                @if($isToday)
                                                                    <span class="font-medium">Today</span>
                                                                @elseif($isTomorrow)
                                                                    <span>Tomorrow</span>
                                                                @else
                                                                    <span>{{ $task->due_date->format('M d') }}</span>
                                                                @endif
                                                            </span>
                                                        @else
                                                            <span class="text-slate-400">Add due date</span>
                                                        @endif
                                                    </button>
                                                </div>

                                                {{-- Status (editable dropdown) --}}
                                                <div class="px-3 flex-shrink-0" :style="`width: ${columns.status.width}px`">
                                                    <button @click="openStatusMenu({{ $task->id }}, $event)"
                                                            class="w-full inline-flex items-center gap-2 px-2 py-1 rounded text-sm hover:bg-[#fafafa] transition-colors"
                                                            :style="`color: ${data.status_color || '#64748b'}`"
                                                            :data-task-id="{{ $task->id }}">
                                                        <div class="w-3 h-3 rounded-full flex-shrink-0"
                                                             :style="`background-color: ${data.status_color || '#94a3b8'}`"></div>
                                                        <span x-text="data.status_label || '{{ $status->label }}'"></span>
                                                    </button>
                                                </div>

                                                {{-- Priority (enhanced with flag icons) --}}
                                                <div class="px-3 flex-shrink-0 relative" :style="`width: ${columns.priority.width}px`" x-data="{ showPriorityDropdown: false }">
                                                        <button @click="showPriorityDropdown = !showPriorityDropdown"
                                                                class="w-full inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-medium hover:bg-[#fafafa] transition-all">
                                                            @if($task->priority)
                                                                @php
                                                                    $priorityLabel = strtoupper($task->priority->label);
                                                                    $flagColors = [
                                                                        'URGENT' => 'text-red-600',
                                                                        'HIGH' => 'text-orange-500',
                                                                        'NORMAL' => 'text-blue-500',
                                                                        'LOW' => 'text-slate-400'
                                                                    ];
                                                                    $flagColor = $flagColors[$priorityLabel] ?? 'text-slate-400';
                                                                @endphp
                                                                <svg class="w-4 h-4 {{ $flagColor }} flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z"/>
                                                                </svg>
                                                                <span class="{{ $flagColor }}">{{ $task->priority->label }}</span>
                                                            @else
                                                                <svg class="w-4 h-4 text-slate-300 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z"/>
                                                                </svg>
                                                                <span class="text-slate-400">Set priority</span>
                                                            @endif
                                                        </button>

                                                        {{-- Priority Dropdown --}}
                                                        <div x-show="showPriorityDropdown"
                                                             @click.away="showPriorityDropdown = false"
                                                             x-cloak
                                                             class="absolute left-0 top-full mt-1 w-56 bg-white rounded-lg shadow-xl border border-slate-200 py-2 z-50">

                                                            {{-- Task Priority Section --}}
                                                            <div class="px-3 py-1 mb-1">
                                                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Task Priority</span>
                                                            </div>

                                                            {{-- None Option --}}
                                                            <button @click="updatePriorityField(null); showPriorityDropdown = false"
                                                                    class="w-full px-3 py-1.5 text-left text-sm hover:bg-slate-50 flex items-center gap-2.5 text-slate-500">
                                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z"/>
                                                                </svg>
                                                                <span>No Priority</span>
                                                            </button>

                                                            <div class="border-t border-slate-200 my-1"></div>

                                                            {{-- Priority Options with Flag Icons --}}
                                                            @foreach($taskPriorities as $priority)
                                                                @php
                                                                    $priorityLabel = strtoupper($priority->label);
                                                                    $priorityConfig = [
                                                                        'URGENT' => ['color' => 'text-red-600', 'bg' => 'hover:bg-red-50'],
                                                                        'HIGH' => ['color' => 'text-orange-500', 'bg' => 'hover:bg-orange-50'],
                                                                        'NORMAL' => ['color' => 'text-blue-500', 'bg' => 'hover:bg-blue-50'],
                                                                        'LOW' => ['color' => 'text-slate-400', 'bg' => 'hover:bg-slate-50']
                                                                    ];
                                                                    $config = $priorityConfig[$priorityLabel] ?? ['color' => 'text-slate-600', 'bg' => 'hover:bg-slate-50'];
                                                                @endphp
                                                                <button @click="updatePriorityField({{ $priority->id }}, '{{ $priority->label }}', '{{ $priority->color }}'); showPriorityDropdown = false"
                                                                        class="w-full px-3 py-1.5 text-left text-sm {{ $config['bg'] }} flex items-center gap-2.5">
                                                                    <svg class="w-4 h-4 {{ $config['color'] }}" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z"/>
                                                                    </svg>
                                                                    <span class="flex-1 {{ $config['color'] }} font-medium">{{ $priority->label }}</span>
                                                                    @if($task->priority_id === $priority->id)
                                                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                                        </svg>
                                                                    @endif
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                </div>

                                                {{-- Assignee (enhanced with search and sections) --}}
                                                <div class="px-3 flex-shrink-0 relative" :style="`width: ${columns.assignee.width}px`"
                                                     x-data="{
                                                         showAssigneeDropdown: false,
                                                         assignedUsers: @js($task->assignees->pluck('id')->toArray()),
                                                         searchQuery: '',

                                                         get currentUser() {
                                                             return {{ auth()->id() }};
                                                         },

                                                         get filteredUsers() {
                                                             if (!this.searchQuery) return @js($users);
                                                             return @js($users).filter(user =>
                                                                 user.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                                                                 user.email.toLowerCase().includes(this.searchQuery.toLowerCase())
                                                             );
                                                         },

                                                         get isCurrentUserAssigned() {
                                                             return this.assignedUsers.includes(this.currentUser);
                                                         },

                                                         async toggleAssignee(userId) {
                                                             const isAssigned = this.assignedUsers.includes(userId);
                                                             const url = `/tasks/{{ $task->id }}/assignees${isAssigned ? `/${userId}` : ''}`;
                                                             const method = isAssigned ? 'DELETE' : 'POST';

                                                             try {
                                                                 const response = await fetch(url, {
                                                                     method: method,
                                                                     headers: {
                                                                         'Content-Type': 'application/json',
                                                                         'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                                                     },
                                                                     body: method === 'POST' ? JSON.stringify({ user_id: userId }) : null
                                                                 });

                                                                 const data = await response.json();
                                                                 if (data.success) {
                                                                     if (isAssigned) {
                                                                         this.assignedUsers = this.assignedUsers.filter(id => id !== userId);
                                                                     } else {
                                                                         this.assignedUsers.push(userId);
                                                                     }
                                                                     // Close dropdown after update
                                                                     this.showAssigneeDropdown = false;
                                                                 }
                                                             } catch (error) {
                                                                 console.error('Error updating assignee:', error);
                                                             }
                                                         }
                                                     }">

                                                    {{-- Assignee Display --}}
                                                    <button @click="showAssigneeDropdown = !showAssigneeDropdown"
                                                            class="flex items-center gap-1 hover:bg-slate-50 rounded px-1 -mx-1">
                                                        @if($task->assignees->count() > 0)
                                                            <div class="flex -space-x-2">
                                                                @foreach($task->assignees->take(3) as $assignee)
                                                                    @if($assignee->avatar)
                                                                        <img src="{{ $assignee->avatar }}"
                                                                             alt="{{ $assignee->name }}"
                                                                             class="w-6 h-6 rounded-full border-2 border-white"
                                                                             title="{{ $assignee->name }}">
                                                                    @else
                                                                        <div class="w-6 h-6 rounded-full border-2 border-white bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white text-xs font-semibold"
                                                                             title="{{ $assignee->name }}">
                                                                            {{ strtoupper(substr($assignee->name, 0, 1)) }}
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                                @if($task->assignees->count() > 3)
                                                                    <div class="w-6 h-6 rounded-full border-2 border-white bg-slate-200 flex items-center justify-center text-xs font-medium text-slate-600">
                                                                        +{{ $task->assignees->count() - 3 }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center">
                                                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                                </svg>
                                                            </div>
                                                        @endif
                                                    </button>

                                                    {{-- Assignee Dropdown --}}
                                                    <div x-show="showAssigneeDropdown"
                                                         @click.away="showAssigneeDropdown = false"
                                                         x-cloak
                                                         class="absolute left-0 top-full mt-1 w-64 bg-white rounded-lg shadow-xl border border-slate-200 py-2 z-50 max-h-96 overflow-y-auto">

                                                        {{-- Search --}}
                                                        <div class="px-3 pb-2">
                                                            <input type="text"
                                                                   x-model="searchQuery"
                                                                   placeholder="Search or enter email..."
                                                                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                                        </div>

                                                        {{-- Assignees Section --}}
                                                        <div class="px-3 py-1 mb-1">
                                                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Assignees</span>
                                                        </div>

                                                        {{-- Me Option --}}
                                                        <button @click="toggleAssignee(currentUser)"
                                                                class="w-full px-3 py-2 text-left text-sm hover:bg-slate-50 flex items-center gap-2.5">
                                                            @if(auth()->user()->avatar)
                                                                <img src="{{ auth()->user()->avatar }}"
                                                                     alt="Me"
                                                                     class="w-6 h-6 rounded-full">
                                                            @else
                                                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white text-xs font-semibold">
                                                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                                                </div>
                                                            @endif
                                                            <span class="flex-1 font-medium text-slate-900">Me</span>
                                                            <span x-show="isCurrentUserAssigned"
                                                                  class="w-5 h-5 rounded-full bg-blue-600 flex items-center justify-center">
                                                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                                </svg>
                                                            </span>
                                                        </button>

                                                        <div class="border-t border-slate-200 my-1"></div>

                                                        {{-- People Section --}}
                                                        <div class="px-3 py-1 mb-1">
                                                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">People</span>
                                                        </div>

                                                        <template x-for="user in filteredUsers.filter(u => u.id !== currentUser)" :key="user.id">
                                                            <button @click="toggleAssignee(user.id)"
                                                                    class="w-full px-3 py-2 text-left text-sm hover:bg-slate-50 flex items-center gap-2.5">
                                                                <img :src="user.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}`"
                                                                     :alt="user.name"
                                                                     class="w-6 h-6 rounded-full">
                                                                <span class="flex-1 text-slate-700" x-text="user.name"></span>
                                                                <span x-show="assignedUsers.includes(user.id)"
                                                                      class="w-5 h-5 rounded-full bg-blue-600 flex items-center justify-center">
                                                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                                    </svg>
                                                                </span>
                                                            </button>
                                                        </template>

                                                        <div class="border-t border-slate-200 my-1"></div>

                                                        {{-- Invite via Email --}}
                                                        <button class="w-full px-3 py-2 text-left text-sm hover:bg-slate-50 flex items-center gap-2.5 text-purple-600">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                            </svg>
                                                            <span>Invite people via email</span>
                                                        </button>
                                                    </div>
                                                </div>

                                                {{-- Time Tracked (ClickUp-style popover) --}}
                                                <div class="px-3 flex-shrink-0 relative" :style="`width: ${columns.time_tracked.width}px`"
                                                     x-data="{
                                                         showTimePopover: false,
                                                         timeForm: {
                                                             input: '',
                                                             description: '',
                                                             billable: true
                                                         },

                                                         parseTime(input) {
                                                             // Parse '3h 20m', '3.5h', '90m', '1h30', etc.
                                                             input = input.toLowerCase().trim();
                                                             let totalMinutes = 0;

                                                             // Match hours and minutes patterns
                                                             const hMatch = input.match(/(\d+\.?\d*)h/);
                                                             const mMatch = input.match(/(\d+)m/);

                                                             if (hMatch) {
                                                                 totalMinutes += parseFloat(hMatch[1]) * 60;
                                                             }
                                                             if (mMatch) {
                                                                 totalMinutes += parseInt(mMatch[1]);
                                                             }

                                                             // If just a number, assume minutes
                                                             if (!hMatch && !mMatch && /^\d+$/.test(input)) {
                                                                 totalMinutes = parseInt(input);
                                                             }

                                                             return Math.round(totalMinutes);
                                                         },

                                                         formatTime(minutes) {
                                                             if (!minutes || minutes === 0) return '–';
                                                             const h = Math.floor(minutes / 60);
                                                             const m = minutes % 60;
                                                             return h > 0 ? `${h}h ${m}m` : `${m}m`;
                                                         },

                                                         async saveTime() {
                                                             const minutes = this.parseTime(this.timeForm.input);

                                                             if (minutes <= 0) {
                                                                 alert('Please enter a valid time (e.g., 3h 20m, 90m, or 1.5h)');
                                                                 return;
                                                             }

                                                             try {
                                                                 const response = await fetch(`/tasks/{{ $task->id }}/time-entries`, {
                                                                     method: 'POST',
                                                                     headers: {
                                                                         'Content-Type': 'application/json',
                                                                         'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                                                     },
                                                                     body: JSON.stringify({
                                                                         minutes: minutes,
                                                                         description: this.timeForm.description,
                                                                         billable: this.timeForm.billable
                                                                     })
                                                                 });

                                                                 const result = await response.json();
                                                                 if (result.success) {
                                                                     // Update displayed time reactively
                                                                     if (result.time_tracked !== undefined) {
                                                                         data.time_tracked = result.time_tracked;
                                                                     }
                                                                     if (result.total_amount !== undefined) {
                                                                         data.total_amount = result.total_amount;
                                                                     }
                                                                     this.showTimePopover = false;
                                                                     this.timeForm = { input: '', description: '', billable: true };
                                                                 } else {
                                                                     alert('Failed to save time entry');
                                                                 }
                                                             } catch (error) {
                                                                 console.error('Error saving time:', error);
                                                                 alert('Failed to save time entry');
                                                             }
                                                         }
                                                     }">

                                                    {{-- Time Display with Quick Start --}}
                                                    <div class="w-full flex items-center gap-1">
                                                        <button @click="showTimePopover = !showTimePopover"
                                                                class="flex-1 text-left text-sm px-2 py-1 rounded hover:bg-[#fafafa] transition-colors">
                                                            @php
                                                                $tracked_h = floor($task->time_tracked / 60);
                                                                $tracked_m = $task->time_tracked % 60;
                                                                $tracked_display = $tracked_h > 0 ? "{$tracked_h}h {$tracked_m}m" : ($tracked_m > 0 ? "{$tracked_m}m" : '–');
                                                            @endphp

                                                            <span class="inline-flex items-center gap-1.5">
                                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                </svg>
                                                            @if($task->time_estimate)
                                                                @php
                                                                    $estimate_h = floor($task->time_estimate / 60);
                                                                    $estimate_m = $task->time_estimate % 60;
                                                                    $estimate_display = $estimate_h > 0 ? "{$estimate_h}h {$estimate_m}m" : "{$estimate_m}m";
                                                                    $variance_pct = $task->time_tracked > 0 ? ($task->time_tracked / $task->time_estimate) * 100 : 0;
                                                                    $color_class = $variance_pct >= 100 ? 'text-red-600' : ($variance_pct >= 80 ? 'text-yellow-600' : 'text-green-600');
                                                                @endphp
                                                                <span class="{{ $color_class }} font-medium">{{ $tracked_display }}</span>
                                                                <span class="text-slate-400"> / {{ $estimate_display }}</span>
                                                            @else
                                                                <span class="text-slate-600">{{ $tracked_display }}</span>
                                                            @endif
                                                        </span>
                                                    </button>

                                                    {{-- Quick Start Button --}}
                                                    <button @click.stop="timeForm.input = '15m'; saveTime();"
                                                            title="Quick add 15 minutes"
                                                            class="flex-shrink-0 p-1 rounded hover:bg-green-50 text-green-600 hover:text-green-700 transition-colors opacity-0 group-hover:opacity-100">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </div>

                                                    {{-- Time Tracking Popover --}}
                                                    <div x-show="showTimePopover"
                                                         @click.away="showTimePopover = false"
                                                         x-cloak
                                                         class="absolute left-0 top-full mt-1 w-80 bg-white rounded-lg shadow-xl border border-slate-200 p-4 z-50">

                                                        <h3 class="text-sm font-semibold text-slate-900 mb-3">Add Time Entry</h3>

                                                        {{-- Time Input --}}
                                                        <div class="mb-3">
                                                            <label class="block text-xs font-medium text-slate-700 mb-1">Time</label>
                                                            <input type="text"
                                                                   x-model="timeForm.input"
                                                                   placeholder="e.g., 3h 20m or 90m"
                                                                   class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                                            <p class="text-xs text-slate-500 mt-1">Format: 3h 20m, 1.5h, or 90m</p>
                                                        </div>

                                                        {{-- Description --}}
                                                        <div class="mb-3">
                                                            <label class="block text-xs font-medium text-slate-700 mb-1">Description (optional)</label>
                                                            <textarea x-model="timeForm.description"
                                                                      rows="2"
                                                                      placeholder="What did you work on?"
                                                                      class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                                                        </div>

                                                        {{-- Billable Toggle --}}
                                                        <div class="mb-4 flex items-center justify-between">
                                                            <span class="text-xs font-medium text-slate-700">Billable</span>
                                                            <button @click="timeForm.billable = !timeForm.billable"
                                                                    type="button"
                                                                    class="relative inline-flex h-5 w-10 items-center rounded-full transition-colors"
                                                                    :class="timeForm.billable ? 'bg-green-600' : 'bg-slate-300'">
                                                                <span :class="timeForm.billable ? 'translate-x-6' : 'translate-x-1'"
                                                                      class="inline-block h-3 w-3 transform rounded-full bg-white transition-transform"></span>
                                                            </button>
                                                        </div>

                                                        {{-- Actions --}}
                                                        <div class="flex items-center gap-2 pt-3 border-t border-slate-200">
                                                            <button @click="showTimePopover = false"
                                                                    class="flex-1 px-3 py-2 text-sm bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50">
                                                                Cancel
                                                            </button>
                                                            <button @click="saveTime()"
                                                                    class="flex-1 px-3 py-2 text-sm bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                                                Save
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Amount (editable) --}}
                                                <div class="px-3 flex-shrink-0" :style="`width: ${columns.amount.width}px`">
                                                    <div x-show="!editing.amount"
                                                         @click="editing.amount = true; $nextTick(() => $refs.amountInput?.focus())"
                                                         class="text-sm text-slate-600 cursor-text hover:bg-white px-1 -mx-1 rounded">
                                                        <span x-text="data.amount ? '€' + data.amount : '–'"></span>
                                                    </div>
                                                    <input x-show="editing.amount"
                                                           x-cloak
                                                           x-ref="amountInput"
                                                           type="number"
                                                           step="0.01"
                                                           x-model="data.amount"
                                                           @blur="updateField('amount'); editing.amount = false"
                                                           @keydown.enter="updateField('amount'); editing.amount = false"
                                                           @keydown.escape="editing.amount = false"
                                                           class="w-20 text-sm px-1 -mx-1 border border-blue-500 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                           placeholder="0.00">
                                                </div>

                                                {{-- Total Charge (reactive) --}}
                                                <div class="px-3 flex-shrink-0" :style="`width: ${columns.total_charge.width}px`">
                                                    <div class="text-sm font-medium text-slate-900"
                                                         x-text="data.total_amount ? '€' + parseFloat(data.total_amount).toFixed(2) : '–'">
                                                    </div>
                                                </div>

                                                {{-- Actions (hidden column) --}}
                                                <div class="px-2 flex-shrink-0 opacity-0 group-hover:opacity-100" @click.stop>
                                                    <button class="p-0.5 hover:bg-slate-200 rounded">
                                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="h-8 px-3 flex items-center text-sm text-slate-400 italic border-b border-slate-100">
                                                {{ __('No tasks') }}
                                            </div>
                                        @endforelse

                                        {{-- Add Task in Group --}}
                                        <div class="group flex items-center h-9 py-2 hover:bg-[#fafafa] transition-colors" x-data="{ showAdd: false }">
                                            {{-- Checkbox Column --}}
                                            <div class="w-8 px-2 flex-shrink-0"></div>

                                            {{-- Name Column with Add Task Button --}}
                                            <div class="px-3 flex-shrink-0" :style="`width: ${columns.name.width}px`">
                                                <button @click="showAdd = true"
                                                        x-show="!showAdd"
                                                        class="flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                    {{ __('Add Task') }}
                                                </button>
                                                <input x-show="showAdd"
                                                       x-cloak
                                                       x-ref="addInput"
                                                       type="text"
                                                       placeholder="{{ __('Task name') }}"
                                                       @keydown.escape="showAdd = false"
                                                       @keydown.enter="if($event.target.value.trim()) { createTask($event.target.value, {{ $status->id }}); $event.target.value = ''; showAdd = false; }"
                                                       @blur="showAdd = false"
                                                       class="w-full text-sm px-2 py-1 border border-slate-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                            </div>

                                            {{-- Project Column --}}
                                            <div class="px-3 flex-shrink-0" :style="`width: ${columns.project.width}px`">
                                                <span class="text-xs text-slate-400 opacity-0 group-hover:opacity-100">Calculate ▼</span>
                                            </div>

                                            {{-- Serviciu Column --}}
                                            <div class="px-3 flex-shrink-0 text-center" :style="`width: ${columns.service.width}px`">
                                                <span class="text-xs text-slate-400 opacity-0 group-hover:opacity-100">Calculate ▼</span>
                                            </div>

                                            {{-- Due Date Column --}}
                                            <div class="px-3 flex-shrink-0" :style="`width: ${columns.due_date.width}px`">
                                                <span class="text-xs text-slate-400 opacity-0 group-hover:opacity-100">Calculate ▼</span>
                                            </div>

                                            {{-- Status Column --}}
                                            <div class="px-3 flex-shrink-0 text-center" :style="`width: ${columns.status.width}px`">
                                                <span class="text-xs text-slate-400 opacity-0 group-hover:opacity-100">Calculate ▼</span>
                                            </div>

                                            {{-- Time Tracked Column --}}
                                            <div class="px-3 flex-shrink-0" :style="`width: ${columns.time_tracked.width}px`">
                                                <span class="text-xs text-slate-400 opacity-0 group-hover:opacity-100">Calculate ▼</span>
                                            </div>

                                            {{-- Amount Column --}}
                                            <div class="px-3 flex-shrink-0" :style="`width: ${columns.amount.width}px`">
                                                <span class="text-xs text-slate-400 opacity-0 group-hover:opacity-100">Calculate ▼</span>
                                            </div>

                                            {{-- Total Charge Column --}}
                                            <div class="px-3 flex-shrink-0" :style="`width: ${columns.total_charge.width}px`">
                                                <span class="text-xs text-slate-400 opacity-0 group-hover:opacity-100">Calculate ▼</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
            </div>
        </div>
    </div>

    {{-- ✨ UNIFIED Status Dropdown Component (ClickUp pattern) --}}
    {{-- This single dropdown is triggered by both icon and status column --}}
    <div x-show="statusDropdown.isOpen"
         @click.away="closeStatusMenu()"
         @keydown.escape.window="closeStatusMenu()"
         x-cloak
         class="fixed w-56 bg-white rounded-lg shadow-xl border border-slate-200 py-2 z-[100] task-global-popover task-status-dropdown"
         :style="statusDropdown.anchorElement ? (() => {
             const rect = statusDropdown.anchorElement.getBoundingClientRect();
             const scrollWrapper = document.getElementById('task-scroll-wrapper');
             const scrollLeft = scrollWrapper ? scrollWrapper.scrollLeft : 0;
             return `top: ${rect.bottom + window.scrollY + 4}px; left: ${rect.left + window.scrollX - scrollLeft}px;`;
         })() : 'display: none;'">

        {{-- Search --}}
        <div class="px-3 pb-2">
            <input type="text"
                   placeholder="Search..."
                   class="w-full px-2 py-1 text-sm border border-slate-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>

        {{-- Divider --}}
        <div class="border-t border-slate-200 mb-1"></div>

        {{-- Statuses Header --}}
        <div class="px-3 py-1 flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Statuses</span>
            <button class="p-0.5 hover:bg-slate-100 rounded">
                <svg class="w-3 h-3 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/>
                </svg>
            </button>
        </div>

        {{-- Status Options --}}
        @foreach($taskStatuses as $availableStatus)
            @php
                $availableStatusLabel = strtoupper($availableStatus->label);
            @endphp
            <button @click="statusDropdown.taskId && updateTaskStatus(statusDropdown.taskId, {{ $availableStatus->id }}); closeStatusMenu();"
                    class="w-full px-3 py-1.5 text-left text-sm hover:bg-slate-50 flex items-center gap-2.5 group">
                {{-- Status Icon --}}
                <div class="w-4 h-4 flex items-center justify-center flex-shrink-0" style="color: {{ $availableStatus->color_class }}">
                    @if($availableStatusLabel === 'TO DO')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <circle cx="12" cy="12" r="9"/>
                        </svg>
                    @elseif($availableStatusLabel === 'IN PROGRESS')
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/>
                            <circle cx="12" cy="12" r="4"/>
                        </svg>
                    @elseif($availableStatusLabel === 'FEEDBACK')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <circle cx="12" cy="12" r="9"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    @elseif($availableStatusLabel === 'DONE')
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M9 12l2 2 4-4" stroke="white" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    @elseif($availableStatusLabel === 'CANCELED' || $availableStatusLabel === 'CANCELLED')
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M8 8l8 8M16 8l-8 8" stroke="white" stroke-width="2" fill="none" stroke-linecap="round"/>
                        </svg>
                    @elseif($availableStatusLabel === 'COMPLETED')
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M9 12l2 2 4-4" stroke="white" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <circle cx="12" cy="12" r="9"/>
                        </svg>
                    @endif
                </div>

                {{-- Status Label --}}
                <span class="flex-1 text-slate-700">{{ $availableStatus->label }}</span>
            </button>
        @endforeach
    </div>

    {{-- ✨ UNIFIED Date Picker Component (ClickUp pattern) --}}
    {{-- Two-column layout: quick shortcuts on left, calendar on right --}}
    <div x-show="datePicker.isOpen"
         @click.away="closeDatePicker()"
         @keydown.escape.window="closeDatePicker()"
         x-cloak
         class="fixed w-[480px] bg-white rounded-lg shadow-xl border border-slate-200 z-[100] task-global-popover task-date-picker"
         :style="datePicker.anchorElement ? (() => {
             const rect = datePicker.anchorElement.getBoundingClientRect();
             const scrollWrapper = document.getElementById('task-scroll-wrapper');
             const scrollLeft = scrollWrapper ? scrollWrapper.scrollLeft : 0;
             return `top: ${rect.bottom + window.scrollY + 4}px; left: ${rect.left + window.scrollX - scrollLeft}px;`;
         })() : 'display: none;'">

        <div class="flex">
            {{-- Left Column: Quick Shortcuts --}}
            <div class="w-40 border-r border-slate-200 py-2">
                <button @click="setQuickDate('today')"
                        class="w-full px-3 py-1.5 text-left text-sm hover:bg-slate-50 flex items-center justify-between">
                    <span class="text-slate-700">Today</span>
                    <span class="text-xs text-slate-400" x-text="new Date().toLocaleDateString('en-US', {weekday: 'short'})"></span>
                </button>

                <button @click="setQuickDate('tomorrow')"
                        class="w-full px-3 py-1.5 text-left text-sm hover:bg-slate-50 flex items-center justify-between">
                    <span class="text-slate-700">Tomorrow</span>
                    <span class="text-xs text-slate-400" x-text="(() => {
                        const tomorrow = new Date();
                        tomorrow.setDate(tomorrow.getDate() + 1);
                        return tomorrow.toLocaleDateString('en-US', {weekday: 'short'});
                    })()"></span>
                </button>

                <button @click="setQuickDate('next_week')"
                        class="w-full px-3 py-1.5 text-left text-sm hover:bg-slate-50">
                    <span class="text-slate-700">Next week</span>
                </button>

                <button @click="setQuickDate('next_weekend')"
                        class="w-full px-3 py-1.5 text-left text-sm hover:bg-slate-50">
                    <span class="text-slate-700">This weekend</span>
                </button>

                <button @click="setQuickDate('2_weeks')"
                        class="w-full px-3 py-1.5 text-left text-sm hover:bg-slate-50">
                    <span class="text-slate-700">2 weeks</span>
                </button>

                <button @click="setQuickDate('4_weeks')"
                        class="w-full px-3 py-1.5 text-left text-sm hover:bg-slate-50">
                    <span class="text-slate-700">4 weeks</span>
                </button>

                <div class="border-t border-slate-200 my-1"></div>

                <button @click="clearDate()"
                        class="w-full px-3 py-1.5 text-left text-sm hover:bg-slate-50 text-red-600">
                    <span>Clear</span>
                </button>
            </div>

            {{-- Right Column: Calendar --}}
            <div class="flex-1 p-3">
                {{-- Calendar Header --}}
                <div class="flex items-center justify-between mb-3">
                    <button @click="previousMonth()"
                            class="p-1 hover:bg-slate-100 rounded">
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>

                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-sm text-slate-900" x-text="getMonthName()"></span>
                        <span class="text-sm text-slate-600" x-text="datePicker.currentYear"></span>
                    </div>

                    <button @click="nextMonth()"
                            class="p-1 hover:bg-slate-100 rounded">
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>

                {{-- Today Button --}}
                <button @click="setQuickDate('today')"
                        class="w-full mb-2 py-1 text-xs text-center text-blue-600 hover:bg-blue-50 rounded">
                    Today
                </button>

                {{-- Calendar Grid --}}
                <div class="grid grid-cols-7 gap-1">
                    {{-- Day Headers --}}
                    <template x-for="day in ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa']">
                        <div class="text-center text-xs font-medium text-slate-500 py-1" x-text="day"></div>
                    </template>

                    {{-- Empty cells for days before month starts --}}
                    <template x-for="i in getFirstDayOfMonth()">
                        <div></div>
                    </template>

                    {{-- Day cells --}}
                    <template x-for="day in getDaysInMonth()">
                        <button @click="selectDate(day)"
                                class="h-8 text-sm rounded hover:bg-slate-100 transition-colors"
                                :class="{
                                    'bg-purple-600 text-white hover:bg-purple-700': isSelectedDate(day),
                                    'ring-2 ring-purple-300': isToday(day) && !isSelectedDate(day),
                                    'text-slate-900': !isSelectedDate(day)
                                }"
                                x-text="day">
                        </button>
                    </template>
                </div>

                {{-- Current Selection Display --}}
                <div x-show="datePicker.selectedDate" class="mt-3 pt-3 border-t border-slate-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-sm text-slate-700" x-text="(() => {
                                if (!datePicker.selectedDate) return '';
                                const date = new Date(datePicker.selectedDate + 'T00:00:00');
                                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                            })()"></span>
                        </div>
                        <button @click="clearDate()"
                                class="text-xs text-slate-500 hover:text-red-600">
                            ✕
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function clickupListView() {
    return {
        selectedTasks: [],
        columns: {
            name: { width: 561, visible: true, order: 0 },
            project: { width: 160, visible: true, order: 1 },
            service: { width: 152, visible: true, order: 2 },
            due_date: { width: 160, visible: true, order: 3 },
            status: { width: 160, visible: true, order: 4 },
            priority: { width: 140, visible: true, order: 5 },
            assignee: { width: 160, visible: true, order: 6 },
            time_tracked: { width: 160, visible: true, order: 7 },
            amount: { width: 160, visible: true, order: 8 },
            total_charge: { width: 160, visible: true, order: 9 }
        },

        // Shared status dropdown state
        statusDropdown: {
            isOpen: false,
            taskId: null,
            anchorElement: null
        },

        // Shared date picker state
        datePicker: {
            isOpen: false,
            taskId: null,
            anchorElement: null,
            mode: 'due', // 'due' or 'start'
            selectedDate: null,
            currentMonth: new Date().getMonth(),
            currentYear: new Date().getFullYear(),
            tempStartDate: null,
            tempDueDate: null
        },

        init() {
            this.initColumnManagement();
            this.initDragAndDrop();
            this.initScrollListener();
        },

        // Initialize scroll listener to close popovers on horizontal scroll
        initScrollListener() {
            const scrollWrapper = document.getElementById('task-scroll-wrapper');
            if (scrollWrapper) {
                scrollWrapper.addEventListener('scroll', () => {
                    if (this.statusDropdown.isOpen) {
                        this.closeStatusMenu();
                    }
                    if (this.datePicker.isOpen) {
                        this.closeDatePicker();
                    }
                });
            }
        },

        // Unified status menu handler
        openStatusMenu(taskId, event) {
            event.stopPropagation();

            // If clicking on the same task's trigger, toggle
            if (this.statusDropdown.isOpen && this.statusDropdown.taskId === taskId) {
                this.closeStatusMenu();
            } else {
                // Close date picker if open
                this.closeDatePicker();
                // Open for this task and anchor to clicked element
                this.statusDropdown.isOpen = true;
                this.statusDropdown.taskId = taskId;
                this.statusDropdown.anchorElement = event.currentTarget;
            }
        },

        closeStatusMenu() {
            this.statusDropdown.isOpen = false;
            this.statusDropdown.taskId = null;
            this.statusDropdown.anchorElement = null;
        },

        // Unified date picker handler
        openDatePicker(taskId, event, mode = 'due', startDate = null, dueDate = null) {
            event.stopPropagation();

            // If clicking on the same task's trigger, toggle
            if (this.datePicker.isOpen && this.datePicker.taskId === taskId) {
                this.closeDatePicker();
            } else {
                // Close status menu if open
                this.closeStatusMenu();
                // Open for this task and anchor to clicked element
                this.datePicker.isOpen = true;
                this.datePicker.taskId = taskId;
                this.datePicker.anchorElement = event.currentTarget;
                this.datePicker.mode = mode;
                this.datePicker.tempStartDate = startDate;
                this.datePicker.tempDueDate = dueDate;
                this.datePicker.selectedDate = mode === 'due' ? dueDate : startDate;

                // Set calendar to show selected date's month or current month
                if (this.datePicker.selectedDate) {
                    const date = new Date(this.datePicker.selectedDate);
                    this.datePicker.currentMonth = date.getMonth();
                    this.datePicker.currentYear = date.getFullYear();
                } else {
                    this.datePicker.currentMonth = new Date().getMonth();
                    this.datePicker.currentYear = new Date().getFullYear();
                }
            }
        },

        closeDatePicker() {
            this.datePicker.isOpen = false;
            this.datePicker.taskId = null;
            this.datePicker.anchorElement = null;
            this.datePicker.tempStartDate = null;
            this.datePicker.tempDueDate = null;
        },

        // Calendar navigation
        previousMonth() {
            if (this.datePicker.currentMonth === 0) {
                this.datePicker.currentMonth = 11;
                this.datePicker.currentYear--;
            } else {
                this.datePicker.currentMonth--;
            }
        },

        nextMonth() {
            if (this.datePicker.currentMonth === 11) {
                this.datePicker.currentMonth = 0;
                this.datePicker.currentYear++;
            } else {
                this.datePicker.currentMonth++;
            }
        },

        // Get days in current month
        getDaysInMonth() {
            return new Date(this.datePicker.currentYear, this.datePicker.currentMonth + 1, 0).getDate();
        },

        // Get first day of month (0 = Sunday, 6 = Saturday)
        getFirstDayOfMonth() {
            return new Date(this.datePicker.currentYear, this.datePicker.currentMonth, 1).getDay();
        },

        // Check if date is today
        isToday(day) {
            const today = new Date();
            return day === today.getDate() &&
                   this.datePicker.currentMonth === today.getMonth() &&
                   this.datePicker.currentYear === today.getFullYear();
        },

        // Check if date is selected
        isSelectedDate(day) {
            if (!this.datePicker.selectedDate) return false;
            const selected = new Date(this.datePicker.selectedDate);
            return day === selected.getDate() &&
                   this.datePicker.currentMonth === selected.getMonth() &&
                   this.datePicker.currentYear === selected.getFullYear();
        },

        // Select a date from calendar
        selectDate(day) {
            const dateStr = `${this.datePicker.currentYear}-${String(this.datePicker.currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

            if (this.datePicker.mode === 'due') {
                this.datePicker.tempDueDate = dateStr;
            } else {
                this.datePicker.tempStartDate = dateStr;
            }

            this.datePicker.selectedDate = dateStr;
            this.applyDateChange();
        },

        // Quick date selection helpers
        setQuickDate(option) {
            const today = new Date();
            let targetDate;

            switch(option) {
                case 'today':
                    targetDate = today;
                    break;
                case 'tomorrow':
                    targetDate = new Date(today);
                    targetDate.setDate(today.getDate() + 1);
                    break;
                case 'next_week':
                    targetDate = new Date(today);
                    targetDate.setDate(today.getDate() + 7);
                    break;
                case 'next_weekend':
                    targetDate = new Date(today);
                    const daysUntilSaturday = (6 - today.getDay() + 7) % 7;
                    targetDate.setDate(today.getDate() + (daysUntilSaturday === 0 ? 7 : daysUntilSaturday));
                    break;
                case '2_weeks':
                    targetDate = new Date(today);
                    targetDate.setDate(today.getDate() + 14);
                    break;
                case '4_weeks':
                    targetDate = new Date(today);
                    targetDate.setDate(today.getDate() + 28);
                    break;
                case 'clear':
                    this.clearDate();
                    return;
                default:
                    return;
            }

            const dateStr = `${targetDate.getFullYear()}-${String(targetDate.getMonth() + 1).padStart(2, '0')}-${String(targetDate.getDate()).padStart(2, '0')}`;

            if (this.datePicker.mode === 'due') {
                this.datePicker.tempDueDate = dateStr;
            } else {
                this.datePicker.tempStartDate = dateStr;
            }

            this.datePicker.selectedDate = dateStr;
            this.applyDateChange();
        },

        // Clear date
        clearDate() {
            if (this.datePicker.mode === 'due') {
                this.datePicker.tempDueDate = null;
            } else {
                this.datePicker.tempStartDate = null;
            }
            this.datePicker.selectedDate = null;
            this.applyDateChange();
        },

        // Apply date change to task
        applyDateChange() {
            if (!this.datePicker.taskId) return;

            const field = this.datePicker.mode === 'due' ? 'due_date' : 'start_date';
            const value = this.datePicker.mode === 'due' ? this.datePicker.tempDueDate : this.datePicker.tempStartDate;

            // Update task via API
            fetch(`/tasks/${this.datePicker.taskId}/quick-update`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ [field]: value })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close date picker after successful update
                    this.closeDatePicker();
                } else {
                    alert('Failed to update date. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error updating date:', error);
                alert('Failed to update date. Please try again.');
            });

            this.closeDatePicker();
        },

        // Get month name
        getMonthName() {
            const months = ['January', 'February', 'March', 'April', 'May', 'June',
                          'July', 'August', 'September', 'October', 'November', 'December'];
            return months[this.datePicker.currentMonth];
        },

        toggleTaskSelection(taskId) {
            const index = this.selectedTasks.indexOf(taskId);
            if (index > -1) {
                this.selectedTasks.splice(index, 1);
            } else {
                this.selectedTasks.push(taskId);
            }
        },

        clearSelection() {
            this.selectedTasks = [];
        },

        bulkOperation(action, data) {
            if (this.selectedTasks.length === 0) return;

            if (!confirm(`Are you sure you want to ${action} ${this.selectedTasks.length} task(s)?`)) {
                return;
            }

            fetch('{{ route('tasks.bulk-update') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    task_ids: this.selectedTasks,
                    action: action,
                    ...data
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Operation failed. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Operation failed. Please try again.');
            });
        },

        bulkUpdateStatus(statusId) {
            if (!statusId) return;
            this.bulkOperation('update_status', { status_id: parseInt(statusId) });
        },

        bulkUpdatePriority(priorityId) {
            if (!priorityId) return;
            this.bulkOperation('update_priority', { priority_id: parseInt(priorityId) });
        },

        bulkUpdateAssignee(userId) {
            if (!userId) return;
            this.bulkOperation('update_assigned_to', { assigned_to: parseInt(userId) });
        },

        bulkUpdateList(listId) {
            if (!listId) return;
            this.bulkOperation('update_list', { list_id: parseInt(listId) });
        },

        bulkDelete() {
            this.bulkOperation('delete', {});
        },

        initColumnManagement() {
            // Load saved column preferences from localStorage
            const saved = localStorage.getItem('clickup_columns');
            if (saved) {
                try {
                    this.columns = { ...this.columns, ...JSON.parse(saved) };
                } catch (e) {
                    console.error('Failed to load column preferences', e);
                }
            }

            // Setup column resizing
            this.setupColumnResizing();
        },

        setupColumnResizing() {
            const resizeHandles = document.querySelectorAll('[data-resize-column]');
            let isResizing = false;
            let columnName = null;
            let startX = 0;
            let startWidth = 0;

            resizeHandles.forEach(handle => {
                handle.addEventListener('mousedown', (e) => {
                    isResizing = true;
                    columnName = handle.getAttribute('data-resize-column');
                    startX = e.pageX;
                    startWidth = this.columns[columnName].width;

                    handle.classList.add('bg-blue-500', 'opacity-100');
                    document.body.style.cursor = 'col-resize';
                    e.preventDefault();
                });
            });

            document.addEventListener('mousemove', (e) => {
                if (!isResizing || !columnName) return;

                const diff = e.pageX - startX;
                // Min width 80px, Max width 500px
                const newWidth = Math.max(80, Math.min(500, startWidth + diff));

                // Update Alpine state - this will automatically update all column instances
                this.columns[columnName].width = newWidth;
            });

            document.addEventListener('mouseup', () => {
                if (isResizing) {
                    isResizing = false;
                    document.querySelectorAll('[data-resize-column]').forEach(h => {
                        h.classList.remove('bg-blue-500');
                    });
                    document.body.style.cursor = '';

                    // Save column widths
                    this.saveColumnPreferences();

                    columnName = null;
                }
            });
        },

        saveColumnPreferences() {
            localStorage.setItem('clickup_columns', JSON.stringify(this.columns));
        },

        initDragAndDrop() {
            const taskRows = document.querySelectorAll('[draggable="true"]');
            let draggedElement = null;
            let draggedTaskId = null;
            let sourceStatusId = null;

            taskRows.forEach(row => {
                // Drag start
                row.addEventListener('dragstart', (e) => {
                    draggedElement = row;
                    draggedTaskId = row.getAttribute('x-data')?.match(/taskRow\((\d+)/)?.[1];

                    // Find the status group this task belongs to
                    const statusRow = row.closest('tr');
                    sourceStatusId = statusRow?.querySelector('[data-status-id]')?.getAttribute('data-status-id');

                    row.style.opacity = '0.5';
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/html', row.innerHTML);
                });

                // Drag end
                row.addEventListener('dragend', (e) => {
                    row.style.opacity = '1';

                    // Remove all drag-over indicators
                    document.querySelectorAll('.drag-over').forEach(el => {
                        el.classList.remove('drag-over', 'border-t-2', 'border-blue-500');
                    });
                });

                // Drag over (for reordering within same status)
                row.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';

                    if (draggedElement !== row) {
                        row.classList.add('drag-over', 'border-t-2', 'border-blue-500');
                    }

                    return false;
                });

                // Drag leave
                row.addEventListener('dragleave', (e) => {
                    row.classList.remove('drag-over', 'border-t-2', 'border-blue-500');
                });

                // Drop
                row.addEventListener('drop', (e) => {
                    e.stopPropagation();
                    e.preventDefault();

                    if (draggedElement !== row) {
                        // Reorder within same status
                        const targetTaskId = row.getAttribute('x-data')?.match(/taskRow\((\d+)/)?.[1];

                        if (draggedTaskId && targetTaskId) {
                            this.reorderTasks(draggedTaskId, targetTaskId);
                        }
                    }

                    row.classList.remove('drag-over', 'border-t-2', 'border-blue-500');
                    return false;
                });
            });

            // Make status groups droppable (for status change)
            const statusGroups = document.querySelectorAll('tbody tr');
            statusGroups.forEach(statusRow => {
                const statusHeader = statusRow.querySelector('[data-status-id]');
                if (!statusHeader) return;

                const statusId = statusHeader.getAttribute('data-status-id');
                const tasksContainer = statusRow.querySelector('div > div:last-child');

                if (tasksContainer) {
                    tasksContainer.addEventListener('dragover', (e) => {
                        e.preventDefault();
                        e.dataTransfer.dropEffect = 'move';
                        tasksContainer.style.backgroundColor = 'rgba(59, 130, 246, 0.1)';
                    });

                    tasksContainer.addEventListener('dragleave', (e) => {
                        tasksContainer.style.backgroundColor = '';
                    });

                    tasksContainer.addEventListener('drop', (e) => {
                        e.preventDefault();
                        tasksContainer.style.backgroundColor = '';

                        // Move task to different status
                        if (draggedTaskId && statusId !== sourceStatusId) {
                            this.moveTaskToStatus(draggedTaskId, statusId);
                        }
                    });
                }
            });
        },

        reorderTasks(draggedTaskId, targetTaskId) {
            // TODO: Implement task reordering within same status
            console.log('Reordering task', draggedTaskId, 'near', targetTaskId);
            // This would require updating positions and saving to server
        },

        moveTaskToStatus(taskId, newStatusId) {
            // Use the global updateTaskStatus function for optimistic updates
            updateTaskStatus(taskId, newStatusId);
        },

        createTask(name, statusId) {
            if (!name || !name.trim()) return;

            const listId = {{ request('list_id') ?? 'null' }};

            if (!listId) {
                alert('Please select a list first. Use the sidebar to navigate to a specific list.');
                return;
            }

            fetch('{{ route('tasks.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    name: name.trim(),
                    status_id: statusId,
                    list_id: listId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.task) {
                    // Reload to show new task with all fields properly rendered
                    // (Optimistic creation would be complex due to the full task row template)
                    window.location.reload();
                } else {
                    alert('Failed to create task. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error creating task:', error);
                alert('Failed to create task. Please try again.');
            });
        }
    };
}

function taskRow(taskId, taskData) {
    return {
        taskId,
        data: taskData,
        originalData: { ...taskData },
        editing: {
            name: false,
            due_date: false,
            time_tracked: false,
            amount: false
        },

        updateServiceField(serviceId) {
            this.updateField('service_id', serviceId);
        },

        updatePriorityField(priorityId, label = null, color = null) {
            this.data.priority_id = priorityId;
            this.data.priority_label = label;
            this.data.priority_color = color;
            this.updateField('priority_id', priorityId);
        },

        updateStatusField(statusId, label = null, color = null) {
            this.data.status_id = statusId;
            this.data.status_label = label;
            this.data.status_color = color;

            // Update the status via API
            fetch(`/tasks/${this.taskId}/quick-update`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ status_id: statusId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Use the global updateTaskStatus for optimistic UI update
                    updateTaskStatus(taskId, statusId);
                    this.closeDatePicker();
                } else {
                    alert('Failed to update status. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error updating status:', error);
                alert('Failed to update status. Please try again.');
            });
        },

        updateAssigneeField(userId) {
            this.updateField('assigned_to', userId);
        },

        updateListField(listId) {
            this.updateField('list_id', listId);
        },

        updateField(field, customValue = null) {
            const value = customValue !== null ? customValue : this.data[field];
            const oldValue = this.originalData[field];

            // Optimistic update - UI already reflects the change
            if (customValue !== null) {
                this.data[field] = value;
            }
            this.originalData[field] = value;

            const updateData = {};
            updateData[field] = value;

            fetch(`/tasks/${this.taskId}/quick-update`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(updateData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.task) {
                    // Update with server response (includes calculated fields like total_amount)
                    if (field === 'time_tracked' && data.task.total_amount !== undefined) {
                        this.data.total_amount = data.task.total_amount;
                        this.originalData.total_amount = data.task.total_amount;
                    }
                } else {
                    // Revert on failure
                    console.error('Update failed, reverting:', data);
                    this.data[field] = oldValue;
                    this.originalData[field] = oldValue;
                    alert('Failed to update. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error updating field:', error);
                // Revert on error
                this.data[field] = oldValue;
                this.originalData[field] = oldValue;
                alert('Failed to update. Please try again.');
            });
        }
    };
}

function updateTaskStatus(taskId, statusId) {
    // Optimistic UI: Find and move the task row immediately
    const taskRow = document.querySelector(`[x-data*="taskRow(${taskId}"`);
    if (!taskRow) {
        console.error('Task row not found');
        return;
    }

    // Store the old parent for potential rollback
    const oldParent = taskRow.parentElement;
    const oldNextSibling = taskRow.nextSibling;

    // Find the new status group's task container
    const newStatusGroup = document.querySelector(`[data-status-id="${statusId}"]`)?.closest('[x-data="{ expanded: true }"]');
    if (!newStatusGroup) {
        console.error('New status group not found');
        return;
    }

    // Find the task list within the new status group
    const newTaskList = newStatusGroup.querySelector('div > div:last-child');
    if (!newTaskList) {
        console.error('New task list not found');
        return;
    }

    // Move the task row to the new status group
    newTaskList.appendChild(taskRow);

    // Update counters optimistically
    const oldStatusGroup = oldParent.closest('[x-data="{ expanded: true }"]');
    if (oldStatusGroup) {
        const oldCounter = oldStatusGroup.querySelector('.text-\\[13px\\].text-slate-500');
        if (oldCounter) {
            const oldCount = parseInt(oldCounter.textContent) || 0;
            oldCounter.textContent = Math.max(0, oldCount - 1);
        }
    }

    const newCounter = newStatusGroup.querySelector('.text-\\[13px\\].text-slate-500');
    if (newCounter) {
        const newCount = parseInt(newCounter.textContent) || 0;
        newCounter.textContent = newCount + 1;
    }

    // Send update to server
    fetch(`/tasks/${taskId}/quick-update`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            status_id: statusId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            // Rollback on failure
            console.error('Status update failed:', data);
            if (oldNextSibling) {
                oldParent.insertBefore(taskRow, oldNextSibling);
            } else {
                oldParent.appendChild(taskRow);
            }

            // Revert counters
            if (oldStatusGroup) {
                const oldCounter = oldStatusGroup.querySelector('.text-\\[13px\\].text-slate-500');
                if (oldCounter) {
                    const oldCount = parseInt(oldCounter.textContent) || 0;
                    oldCounter.textContent = oldCount + 1;
                }
            }
            if (newCounter) {
                const newCount = parseInt(newCounter.textContent) || 0;
                newCounter.textContent = Math.max(0, newCount - 1);
            }

            alert('Failed to update status. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error updating status:', error);
        // Rollback on error
        if (oldNextSibling) {
            oldParent.insertBefore(taskRow, oldNextSibling);
        } else {
            oldParent.appendChild(taskRow);
        }

        // Revert counters
        if (oldStatusGroup) {
            const oldCounter = oldStatusGroup.querySelector('.text-\\[13px\\].text-slate-500');
            if (oldCounter) {
                const oldCount = parseInt(oldCounter.textContent) || 0;
                oldCounter.textContent = oldCount + 1;
            }
        }
        if (newCounter) {
            const newCount = parseInt(newCounter.textContent) || 0;
            newCounter.textContent = Math.max(0, newCount - 1);
        }

        alert('Failed to update status. Please try again.');
    });
}

// WebSocket Real-time Updates (Laravel Echo)
// To enable real-time updates, uncomment the following code and configure Laravel Echo:
//
// if (typeof window.Echo !== 'undefined') {
//     const organizationId = {{ auth()->user()->organization_id ?? 'null' }};
//     const listId = {{ request('list_id') ?? 'null' }};
//
//     // Listen to organization-wide task events
//     if (organizationId) {
//         window.Echo.private(`organization.${organizationId}`)
//             .listen('.task.created', (e) => {
//                 console.log('Task created:', e.task);
//                 // Refresh the view to show new task
//                 window.location.reload();
//             })
//             .listen('.task.updated', (e) => {
//                 console.log('Task updated:', e.task);
//                 // Find and update the task row
//                 const taskRow = document.querySelector(`[data-task-id="${e.task.id}"]`);
//                 if (taskRow) {
//                     // Update task data via Alpine.js
//                     Alpine.$data(taskRow).data = {
//                         ...Alpine.$data(taskRow).data,
//                         ...e.task
//                     };
//                 }
//             })
//             .listen('.task.deleted', (e) => {
//                 console.log('Task deleted:', e.task_id);
//                 // Remove the task row
//                 const taskRow = document.querySelector(`[data-task-id="${e.task_id}"]`);
//                 if (taskRow) {
//                     taskRow.remove();
//                 }
//             })
//             .listen('.task.status-changed', (e) => {
//                 console.log('Task status changed:', e);
//                 // Refresh to show task in new status group
//                 window.location.reload();
//             })
//             .listen('.task.assigned', (e) => {
//                 console.log('Task assigned:', e);
//                 // Update assignee display
//                 const taskRow = document.querySelector(`[data-task-id="${e.task.id}"]`);
//                 if (taskRow) {
//                     // Refresh to update assignee avatar/name
//                     window.location.reload();
//                 }
//             });
//     }
//
//     // Listen to list-specific events
//     if (listId) {
//         window.Echo.private(`list.${listId}`)
//             .listen('.task.created', (e) => {
//                 console.log('Task created in list:', e.task);
//                 window.location.reload();
//             })
//             .listen('.task.updated', (e) => {
//                 console.log('Task updated in list:', e.task);
//             });
//     }
// }
</script>
