{{-- ClickUp List View - Pixel Perfect Replica --}}
@props(['tasksByStatus', 'taskStatuses', 'lists', 'users', 'services', 'taskPriorities'])

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

    {{-- Table Container --}}
    <div class="overflow-x-auto">
        <table class="w-full" style="border-collapse: collapse; border-spacing: 0;">
            <tbody>
                @foreach($taskStatuses as $status)
                    @php
                        $statusTasks = $tasksByStatus->get($status->id, collect());
                    @endphp

                    {{-- Status Group --}}
                    <tr class="border-0">
                        <td colspan="100%" class="p-0 border-0 @if(!$loop->first) pt-6 @endif">
                            <div x-data="{ expanded: {{ $statusTasks->isNotEmpty() ? 'true' : 'false' }} }" class="w-full">
                                {{-- Group Header Row 1: Status Badge + Count + Actions --}}
                                <div @click="expanded = !expanded"
                                     data-status-id="{{ $status->id }}"
                                     class="flex items-center gap-2 h-8 px-3 bg-slate-50 hover:bg-slate-100 cursor-pointer border-b border-slate-200">
                                    <button class="flex-shrink-0" @click.stop="expanded = !expanded">
                                        <svg class="w-2.5 h-2.5 text-slate-400 transition-transform"
                                             :class="expanded ? 'rotate-90' : ''"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>

                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-bold uppercase"
                                              style="color: {{ $status->color }}">
                                            {{ $status->label }}
                                        </span>
                                        <span class="text-[13px] text-slate-500">{{ $statusTasks->count() }}</span>
                                    </div>

                                    <div class="ml-auto flex items-center gap-1" @click.stop>
                                        <button class="p-0.5 hover:bg-slate-200 rounded transition-colors">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/>
                                            </svg>
                                        </button>
                                        <button @click="$dispatch('open-task-modal', { status_id: {{ $status->id }} })"
                                                class="flex items-center gap-1 px-2 py-0.5 text-[13px] text-slate-600 hover:bg-slate-200 rounded transition-colors">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            {{ __('Add Task') }}
                                        </button>
                                    </div>
                                </div>

                                {{-- Group Header Row 2: Column Headers --}}
                                <div x-show="expanded" x-collapse x-cloak>
                                    <div class="flex items-center h-8 bg-white border-b border-slate-200 text-[11px] font-semibold text-slate-500 uppercase tracking-wide">
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
                                        <div class="px-3 flex-shrink-0 relative group hover:border-r hover:border-slate-200" :style="`width: ${columns.project.width}px`" data-column="project">
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
                                        <div class="px-3 flex-shrink-0 relative group hover:border-r hover:border-slate-200" :style="`width: ${columns.service.width}px`" data-column="service">
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
                                        <div class="px-3 flex-shrink-0 relative group hover:border-r hover:border-slate-200" :style="`width: ${columns.due_date.width}px`" data-column="due_date">
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
                                        <div class="px-3 flex-shrink-0 relative group hover:border-r hover:border-slate-200" :style="`width: ${columns.status.width}px`" data-column="status">
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
                                        <div class="px-3 flex-shrink-0 relative group hover:border-r hover:border-slate-200" :style="`width: ${columns.priority.width}px`" data-column="priority">
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
                                        <div class="px-3 flex-shrink-0 relative group hover:border-r hover:border-slate-200" :style="`width: ${columns.assignee.width}px`" data-column="assignee">
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
                                        <div class="px-3 flex-shrink-0 relative group hover:border-r hover:border-slate-200" :style="`width: ${columns.time_tracked.width}px`" data-column="time_tracked">
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
                                        <div class="px-3 flex-shrink-0 relative group hover:border-r hover:border-slate-200" :style="`width: ${columns.amount.width}px`" data-column="amount">
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
                                        <div class="px-3 flex-shrink-0 relative group hover:border-r hover:border-slate-200" :style="`width: ${columns.total_charge.width}px`" data-column="total_charge">
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
                                            <div class="flex items-center h-8 border-b border-slate-100 hover:bg-slate-50 group"
                                                 x-data="taskRow({{ $task->id }}, @js([
                                                     'name' => $task->name,
                                                     'list_id' => $task->list_id,
                                                     'due_date' => $task->due_date?->format('Y-m-d'),
                                                     'time_tracked' => $task->time_tracked ?? 0,
                                                     'amount' => $task->amount ?? 0,
                                                     'total_amount' => $task->total_amount ?? 0,
                                                     'service_id' => $task->service_id,
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
                                                        <div x-data="{ showStatusDropdown: false }" class="relative flex-shrink-0">
                                                            <button @click="showStatusDropdown = !showStatusDropdown"
                                                                    class="w-4 h-4 rounded-full flex items-center justify-center transition-colors hover:bg-slate-100"
                                                                    style="color: {{ $status->color }}">
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
                                                            {{-- Status Dropdown --}}
                                                            <div x-show="showStatusDropdown"
                                                                 @click.away="showStatusDropdown = false"
                                                                
                                                                 class="absolute left-0 top-full mt-1 w-56 bg-white rounded-lg shadow-xl border border-slate-200 py-2 z-50"
                                                                 x-cloak>
                                                                {{-- Search --}}
                                                                <div class="px-3 pb-2">
                                                                    <input type="text"
                                                                           placeholder="Search..."
                                                                           class="w-full px-2 py-1 text-[13px] border border-slate-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                                                                </div>

                                                                {{-- Divider --}}
                                                                <div class="border-t border-slate-200 mb-1"></div>

                                                                {{-- Statuses Header --}}
                                                                <div class="px-3 py-1 flex items-center justify-between">
                                                                    <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Statuses</span>
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
                                                                    <button @click="updateTaskStatus({{ $task->id }}, {{ $availableStatus->id }}); showStatusDropdown = false"
                                                                            class="w-full px-3 py-1.5 text-left text-[13px] hover:bg-slate-50 flex items-center gap-2.5 group">
                                                                        {{-- Status Icon --}}
                                                                        <div class="w-4 h-4 flex items-center justify-center flex-shrink-0" style="color: {{ $availableStatus->color }}">
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

                                                                        {{-- Checkmark if selected --}}
                                                                        @if($availableStatus->id === $status->id)
                                                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                                            </svg>
                                                                        @endif
                                                                    </button>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <div x-show="!editing.name"
                                                                 @click="editing.name = true; $nextTick(() => $refs.nameInput?.focus())"
                                                                 class="text-[13px] text-slate-900 truncate cursor-text hover:bg-white px-1 -mx-1 rounded">
                                                                <span x-text="data.name"></span>
                                                            </div>
                                                            <input x-show="editing.name"
                                                                   x-cloak
                                                                   x-ref="nameInput"
                                                                   x-model="data.name"
                                                                   @blur="updateField('name'); editing.name = false"
                                                                   @keydown.enter="updateField('name'); editing.name = false"
                                                                   @keydown.escape="editing.name = false"
                                                                   class="w-full text-[13px] px-1 -mx-1 border border-blue-500 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Project/List (editable dropdown) --}}
                                                <div class="px-3 flex-shrink-0" :style="`width: ${columns.project.width}px`" x-data="{ showListDropdown: false }">
                                                    <div class="relative">
                                                        <button @click="showListDropdown = !showListDropdown"
                                                                class="w-full text-left text-[13px] text-slate-600 truncate hover:bg-slate-50 px-1 -mx-1 rounded">
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
                                                                       class="w-full px-2 py-1 text-[13px] border border-slate-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                                                            </div>

                                                            <div class="border-t border-slate-200 mb-1"></div>

                                                            {{-- Lists grouped by Space/Folder --}}
                                                            @foreach($lists as $list)
                                                                <button @click="updateListField({{ $list->id }}); showListDropdown = false"
                                                                        class="w-full px-3 py-1.5 text-left text-[13px] hover:bg-slate-50 flex items-center gap-2.5">
                                                                    <svg class="w-3 h-3 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                                    </svg>
                                                                    <div class="flex-1 min-w-0">
                                                                        <div class="truncate">{{ $list->name }}</div>
                                                                        @if($list->client)
                                                                            <div class="text-[11px] text-slate-400 truncate">{{ $list->client->name }}</div>
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

                                                {{-- Service (editable dropdown) --}}
                                                <div class="px-3 flex-shrink-0" :style="`width: ${columns.service.width}px`" x-data="{ showServiceDropdown: false }">
                                                    <div class="relative">
                                                        <button @click="showServiceDropdown = !showServiceDropdown"
                                                                class="w-full inline-flex items-center justify-center px-2 py-0.5 rounded text-[11px] font-medium hover:shadow-sm"
                                                                style="background-color: {{ $task->service?->color ?? '#e2e8f0' }}20; color: {{ $task->service?->color ?? '#64748b' }}">
                                                            {{ $task->service?->name ?? '–' }}
                                                        </button>

                                                        {{-- Service Dropdown --}}
                                                        <div x-show="showServiceDropdown"
                                                             @click.away="showServiceDropdown = false"
                                                             class="absolute left-0 top-full mt-1 w-56 bg-white rounded-lg shadow-xl border border-slate-200 py-2 z-50 max-h-64 overflow-y-auto"
                                                             x-cloak>
                                                            {{-- None Option --}}
                                                            <button @click="updateServiceField(null); showServiceDropdown = false"
                                                                    class="w-full px-3 py-1.5 text-left text-[13px] hover:bg-slate-50 flex items-center gap-2.5 text-slate-500">
                                                                {{ __('None') }}
                                                            </button>

                                                            <div class="border-t border-slate-200 my-1"></div>

                                                            {{-- Service Options --}}
                                                            @foreach($services as $service)
                                                                <button @click="updateServiceField({{ $service->id }}); showServiceDropdown = false"
                                                                        class="w-full px-3 py-1.5 text-left text-[13px] hover:bg-slate-50 flex items-center gap-2.5">
                                                                    <span class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $service->color }}"></span>
                                                                    <span class="flex-1">{{ $service->name }}</span>
                                                                    @if($task->service_id === $service->id)
                                                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                                        </svg>
                                                                    @endif
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Due Date (editable) --}}
                                                <div class="px-3 flex-shrink-0" :style="`width: ${columns.due_date.width}px`">
                                                    <div x-show="!editing.due_date"
                                                         @click="editing.due_date = true"
                                                         class="text-[13px] text-slate-600 cursor-pointer hover:bg-white px-1 -mx-1 rounded">
                                                        {{ $task->due_date ? $task->due_date->format('d/m/y') : '–' }}
                                                    </div>
                                                    <input x-show="editing.due_date"
                                                           x-cloak
                                                           type="date"
                                                           x-model="data.due_date"
                                                           @blur="updateField('due_date'); editing.due_date = false"
                                                           @change="updateField('due_date'); editing.due_date = false"
                                                           class="w-full text-[13px] px-1 -mx-1 border border-blue-500 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                                                </div>

                                                {{-- Status --}}
                                                <div class="px-3 flex-shrink-0 text-center" :style="`width: ${columns.status.width}px`">
                                                    <span class="text-[11px] font-medium uppercase"
                                                          style="color: {{ $status->color }}">
                                                        {{ $status->label }}
                                                    </span>
                                                </div>

                                                {{-- Priority (editable dropdown) --}}
                                                <div class="px-3 flex-shrink-0" :style="`width: ${columns.priority.width}px`" x-data="{ showPriorityDropdown: false }">
                                                    <div class="relative">
                                                        <button @click="showPriorityDropdown = !showPriorityDropdown"
                                                                class="w-full inline-flex items-center justify-center px-2 py-0.5 rounded text-[11px] font-medium hover:shadow-sm"
                                                                :style="`background-color: ${data.priority_color || '#e2e8f0'}20; color: ${data.priority_color || '#64748b'}`">
                                                            <span x-text="data.priority_label || '–'"></span>
                                                        </button>

                                                        {{-- Priority Dropdown --}}
                                                        <div x-show="showPriorityDropdown"
                                                             @click.away="showPriorityDropdown = false"
                                                             class="absolute left-0 top-full mt-1 w-56 bg-white rounded-lg shadow-xl border border-slate-200 py-2 z-50 max-h-64 overflow-y-auto"
                                                             x-cloak>
                                                            {{-- None Option --}}
                                                            <button @click="updatePriorityField(null); showPriorityDropdown = false"
                                                                    class="w-full px-3 py-1.5 text-left text-[13px] hover:bg-slate-50 flex items-center gap-2.5 text-slate-500">
                                                                {{ __('None') }}
                                                            </button>

                                                            <div class="border-t border-slate-200 my-1"></div>

                                                            {{-- Priority Options --}}
                                                            @foreach($taskPriorities as $priority)
                                                                <button @click="updatePriorityField({{ $priority->id }}, '{{ $priority->label }}', '{{ $priority->color }}'); showPriorityDropdown = false"
                                                                        class="w-full px-3 py-1.5 text-left text-[13px] hover:bg-slate-50 flex items-center gap-2.5">
                                                                    <span class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $priority->color }}"></span>
                                                                    <span class="flex-1">{{ $priority->label }}</span>
                                                                    @if($task->priority_id === $priority->id)
                                                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                                        </svg>
                                                                    @endif
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Assignees (multi-select with avatar stack) --}}
                                                <div class="px-3 flex-shrink-0" :style="`width: ${columns.assignee.width}px`"
                                                     x-data="{
                                                         showAssigneeDropdown: false,
                                                         assignedUsers: @js($task->assignees->pluck('id')->toArray()),
                                                         toggleAssignee(userId) {
                                                             const index = this.assignedUsers.indexOf(userId);
                                                             if (index > -1) {
                                                                 fetch(`/tasks/{{ $task->id }}/assignees/${userId}`, {
                                                                     method: 'DELETE',
                                                                     headers: {
                                                                         'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                                         'Accept': 'application/json'
                                                                     }
                                                                 }).then(r => r.json()).then(data => {
                                                                     if (data.success) this.assignedUsers.splice(index, 1);
                                                                 });
                                                             } else {
                                                                 fetch(`/tasks/{{ $task->id }}/assignees`, {
                                                                     method: 'POST',
                                                                     headers: {
                                                                         'Content-Type': 'application/json',
                                                                         'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                                         'Accept': 'application/json'
                                                                     },
                                                                     body: JSON.stringify({ user_id: userId })
                                                                 }).then(r => r.json()).then(data => {
                                                                     if (data.success) this.assignedUsers.push(userId);
                                                                 });
                                                             }
                                                         },
                                                         isAssigned(userId) {
                                                             return this.assignedUsers.includes(userId);
                                                         }
                                                     }">
                                                    <div class="relative">
                                                        {{-- Avatar Stack Display --}}
                                                        <button @click="showAssigneeDropdown = !showAssigneeDropdown"
                                                                class="w-full inline-flex items-center gap-1 px-2 py-0.5 rounded text-[13px] hover:bg-slate-50">
                                                            @php
                                                                $allAssignees = $task->assignees;
                                                                $displayLimit = 3;
                                                            @endphp

                                                            @if($allAssignees->isNotEmpty())
                                                                <div class="flex -space-x-2">
                                                                    @foreach($allAssignees->take($displayLimit) as $assignee)
                                                                        @if($assignee->avatar)
                                                                            <img src="{{ $assignee->avatar }}"
                                                                                 alt="{{ $assignee->name }}"
                                                                                 title="{{ $assignee->name }}"
                                                                                 class="w-6 h-6 rounded-full border-2 border-white">
                                                                        @else
                                                                            <div class="w-6 h-6 rounded-full border-2 border-white bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white text-[10px] font-semibold"
                                                                                 title="{{ $assignee->name }}">
                                                                                {{ strtoupper(substr($assignee->name, 0, 1)) }}
                                                                            </div>
                                                                        @endif
                                                                    @endforeach

                                                                    @if($allAssignees->count() > $displayLimit)
                                                                        <div class="w-6 h-6 rounded-full border-2 border-white bg-slate-200 flex items-center justify-center text-slate-600 text-[10px] font-semibold"
                                                                             title="{{ $allAssignees->skip($displayLimit)->pluck('name')->join(', ') }}">
                                                                            +{{ $allAssignees->count() - $displayLimit }}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @else
                                                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                                </svg>
                                                            @endif
                                                        </button>

                                                        {{-- Multi-Select Assignee Dropdown --}}
                                                        <div x-show="showAssigneeDropdown"
                                                             @click.away="showAssigneeDropdown = false"
                                                             class="absolute left-0 top-full mt-1 w-64 bg-white rounded-lg shadow-xl border border-slate-200 py-2 z-50 max-h-64 overflow-y-auto"
                                                             x-cloak>
                                                            <div class="px-3 pb-2">
                                                                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Assignees</div>
                                                            </div>

                                                            {{-- User Options (Multi-select) --}}
                                                            @foreach($users as $user)
                                                                <button @click="toggleAssignee({{ $user->id }})"
                                                                        class="w-full px-3 py-1.5 text-left text-[13px] hover:bg-slate-50 flex items-center gap-2.5"
                                                                        :class="isAssigned({{ $user->id }}) ? 'bg-blue-50' : ''">
                                                                    @if($user->avatar)
                                                                        <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-6 h-6 rounded-full flex-shrink-0">
                                                                    @else
                                                                        <div class="w-6 h-6 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white text-[10px] font-semibold flex-shrink-0">
                                                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                                                        </div>
                                                                    @endif
                                                                    <span class="flex-1">{{ $user->name }}</span>
                                                                    <svg x-show="isAssigned({{ $user->id }})" class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                                    </svg>
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Time Tracked (editable) --}}
                                                <div class="px-3 flex-shrink-0" :style="`width: ${columns.time_tracked.width}px`">
                                                    <div x-show="!editing.time_tracked"
                                                         @click="editing.time_tracked = true; $nextTick(() => $refs.timeInput?.focus())"
                                                         class="text-[13px] text-slate-600 cursor-text hover:bg-white px-1 -mx-1 rounded">
                                                        {{ $task->time_tracked > 0 ? floor($task->time_tracked / 60) . 'h ' . ($task->time_tracked % 60) . 'm' : '–' }}
                                                    </div>
                                                    <input x-show="editing.time_tracked"
                                                           x-cloak
                                                           x-ref="timeInput"
                                                           type="number"
                                                           x-model="data.time_tracked"
                                                           @blur="updateField('time_tracked'); editing.time_tracked = false"
                                                           @keydown.enter="updateField('time_tracked'); editing.time_tracked = false"
                                                           @keydown.escape="editing.time_tracked = false"
                                                           class="w-20 text-[13px] px-1 -mx-1 border border-blue-500 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                           placeholder="0">
                                                </div>

                                                {{-- Amount (editable) --}}
                                                <div class="px-3 flex-shrink-0" :style="`width: ${columns.amount.width}px`">
                                                    <div x-show="!editing.amount"
                                                         @click="editing.amount = true; $nextTick(() => $refs.amountInput?.focus())"
                                                         class="text-[13px] text-slate-600 cursor-text hover:bg-white px-1 -mx-1 rounded">
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
                                                           class="w-20 text-[13px] px-1 -mx-1 border border-blue-500 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                           placeholder="0.00">
                                                </div>

                                                {{-- Total Charge (reactive) --}}
                                                <div class="px-3 flex-shrink-0" :style="`width: ${columns.total_charge.width}px`">
                                                    <div class="text-[13px] font-medium text-slate-900"
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
                                            <div class="h-8 px-3 flex items-center text-[13px] text-slate-400 italic border-b border-slate-100">
                                                {{ __('No tasks') }}
                                            </div>
                                        @endforelse

                                        {{-- Add Task in Group --}}
                                        <div class="group flex items-center h-8 border-b border-slate-100 hover:bg-slate-50 transition-colors" x-data="{ showAdd: false }">
                                            {{-- Checkbox Column --}}
                                            <div class="w-8 px-2 flex-shrink-0"></div>

                                            {{-- Name Column with Add Task Button --}}
                                            <div class="px-3 flex-shrink-0" :style="`width: ${columns.name.width}px`">
                                                <button @click="showAdd = true"
                                                        x-show="!showAdd"
                                                        class="flex items-center gap-1.5 text-[13px] text-slate-500 hover:text-slate-700">
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
                                                       class="w-full text-[13px] px-2 py-1 border border-slate-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                            </div>

                                            {{-- Project Column --}}
                                            <div class="px-3 flex-shrink-0" :style="`width: ${columns.project.width}px`">
                                                <span class="text-[11px] text-slate-400 opacity-0 group-hover:opacity-100">Calculate ▼</span>
                                            </div>

                                            {{-- Serviciu Column --}}
                                            <div class="px-3 flex-shrink-0 text-center" :style="`width: ${columns.service.width}px`">
                                                <span class="text-[11px] text-slate-400 opacity-0 group-hover:opacity-100">Calculate ▼</span>
                                            </div>

                                            {{-- Due Date Column --}}
                                            <div class="px-3 flex-shrink-0" :style="`width: ${columns.due_date.width}px`">
                                                <span class="text-[11px] text-slate-400 opacity-0 group-hover:opacity-100">Calculate ▼</span>
                                            </div>

                                            {{-- Status Column --}}
                                            <div class="px-3 flex-shrink-0 text-center" :style="`width: ${columns.status.width}px`">
                                                <span class="text-[11px] text-slate-400 opacity-0 group-hover:opacity-100">Calculate ▼</span>
                                            </div>

                                            {{-- Time Tracked Column --}}
                                            <div class="px-3 flex-shrink-0" :style="`width: ${columns.time_tracked.width}px`">
                                                <span class="text-[11px] text-slate-400 opacity-0 group-hover:opacity-100">Calculate ▼</span>
                                            </div>

                                            {{-- Amount Column --}}
                                            <div class="px-3 flex-shrink-0" :style="`width: ${columns.amount.width}px`">
                                                <span class="text-[11px] text-slate-400 opacity-0 group-hover:opacity-100">Calculate ▼</span>
                                            </div>

                                            {{-- Total Charge Column --}}
                                            <div class="px-3 flex-shrink-0" :style="`width: ${columns.total_charge.width}px`">
                                                <span class="text-[11px] text-slate-400 opacity-0 group-hover:opacity-100">Calculate ▼</span>
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

        init() {
            this.initColumnManagement();
            this.initDragAndDrop();
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
                const newWidth = Math.max(80, startWidth + diff); // Min width 80px

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
                    list_id: {{ request('list_id') ?? 'null' }}
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
