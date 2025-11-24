{{-- Task Rows Component for V2 Lazy Loading with Full ClickUp Interactive Design --}}
@props(['tasks', 'status', 'lists', 'services', 'users', 'taskPriorities'])

@forelse($tasks as $task)
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

        {{-- Project/List (using global dropdown) --}}
        <div class="px-3 flex-shrink-0" :style="`width: ${columns.project.width}px`">
            <button @click="openListMenu({{ $task->id }}, $event)"
                    class="w-full text-left text-sm text-slate-600 truncate hover:bg-slate-50 px-1 -mx-1 rounded">
                {{ $task->list?->name ?? '–' }}
            </button>
        </div>

        {{-- Service (using global dropdown) --}}
        <div class="px-3 flex-shrink-0" :style="`width: ${columns.service.width}px`">
            <button @click="openServiceMenu({{ $task->id }}, $event)" class="w-full text-left text-sm px-2 py-1 rounded hover:bg-[#fafafa] transition-colors">
                @if($task->service)
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium" style="background-color: {{ $task->service->color }}20; color: {{ $task->service->color }}">{{ $task->service->name }}</span>
                @else
                    <span class="text-slate-400">Select service...</span>
                @endif
            </button>
        </div>

        {{-- Due Date --}}
        <div class="px-3 flex-shrink-0" :style="`width: ${columns.due_date.width}px`">
            <button @click="openDatePicker({{ $task->id }}, $event, 'due', '{{ $task->start_date?->format('Y-m-d') }}', '{{ $task->due_date?->format('Y-m-d') }}')" class="w-full text-left text-sm px-2 py-1 rounded hover:bg-[#fafafa] transition-colors" :data-task-id="{{ $task->id }}">
                @if($task->due_date)
                    @php $isPast = $task->due_date->isPast(); $isToday = $task->due_date->isToday(); $isTomorrow = $task->due_date->isTomorrow(); @endphp
                    <span class="inline-flex items-center gap-1.5" :class="{ 'text-red-600': {{ $isPast ? 'true' : 'false' }}, 'text-purple-600': {{ $isToday ? 'true' : 'false' }}, 'text-slate-600': {{ !$isPast && !$isToday ? 'true' : 'false' }} }">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        @if($task->start_date)<span class="text-xs">{{ $task->start_date->format('d/m') }} → </span>@endif
                        @if($isToday)<span class="font-medium">Today</span>@elseif($isTomorrow)<span>Tomorrow</span>@else<span>{{ $task->due_date->format('M d') }}</span>@endif
                    </span>
                @else<span class="text-slate-400">Add due date</span>@endif
            </button>
        </div>

        {{-- Status --}}
        <div class="px-3 flex-shrink-0" :style="`width: ${columns.status.width}px`">
            <button @click="openStatusMenu({{ $task->id }}, $event)" class="w-full inline-flex items-center gap-2 px-2 py-1 rounded text-sm hover:bg-[#fafafa] transition-colors" :style="`color: ${data.status_color || '#64748b'}`" :data-task-id="{{ $task->id }}">
                <div class="w-3 h-3 rounded-full flex-shrink-0" :style="`background-color: ${data.status_color || '#94a3b8'}`"></div>
                <span x-text="data.status_label || '{{ $status->label }}'"></span>
            </button>
        </div>

        {{-- Priority (using global dropdown) --}}
        <div class="px-3 flex-shrink-0" :style="`width: ${columns.priority.width}px`">
            <button @click="openPriorityMenu({{ $task->id }}, $event)" class="w-full inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-medium hover:bg-[#fafafa] transition-all">
                @if($task->priority)
                    @php $priorityLabel = strtoupper($task->priority->label); $flagColors = ['URGENT' => 'text-red-600', 'HIGH' => 'text-orange-500', 'NORMAL' => 'text-blue-500', 'LOW' => 'text-slate-400']; $flagColor = $flagColors[$priorityLabel] ?? 'text-slate-400'; @endphp
                    <svg class="w-4 h-4 {{ $flagColor }} flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z"/></svg>
                    <span class="{{ $flagColor }}">{{ $task->priority->label }}</span>
                @else
                    <svg class="w-4 h-4 text-slate-300 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z"/></svg>
                    <span class="text-slate-400">Set priority</span>
                @endif
            </button>
        </div>

        {{-- Assignee (using global dropdown) --}}
        <div class="px-3 flex-shrink-0" :style="`width: ${columns.assignee.width}px`">
            <button @click="openAssigneeMenu({{ $task->id }}, $event, @js($task->assignees->pluck('id')->toArray()))" class="flex items-center gap-1 hover:bg-slate-50 rounded px-1 -mx-1">
                @if($task->assignees->count() > 0)
                    <div class="flex -space-x-2">
                        @foreach($task->assignees->take(3) as $assignee)
                            @if($assignee->avatar)<img src="{{ $assignee->avatar }}" alt="{{ $assignee->name }}" class="w-6 h-6 rounded-full border-2 border-white" title="{{ $assignee->name }}">
                            @else<div class="w-6 h-6 rounded-full border-2 border-white bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white text-xs font-semibold" title="{{ $assignee->name }}">{{ strtoupper(substr($assignee->name, 0, 1)) }}</div>@endif
                        @endforeach
                        @if($task->assignees->count() > 3)<div class="w-6 h-6 rounded-full border-2 border-white bg-slate-200 flex items-center justify-center text-xs font-medium text-slate-600">+{{ $task->assignees->count() - 3 }}</div>@endif
                    </div>
                @else<div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></div>@endif
            </button>
        </div>

        {{-- Time Tracked (simplified inline for now) --}}
        <div class="px-3 flex-shrink-0" :style="`width: ${columns.time_tracked.width}px`">
            <button class="text-left text-sm px-2 py-1 rounded hover:bg-[#fafafa] transition-colors">
                @php $tracked_h = floor($task->time_tracked / 60); $tracked_m = $task->time_tracked % 60; $tracked_display = $tracked_h > 0 ? "{$tracked_h}h {$tracked_m}m" : ($tracked_m > 0 ? "{$tracked_m}m" : '–'); @endphp
                <span class="inline-flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span class="text-slate-600">{{ $tracked_display }}</span></span>
            </button>
        </div>

        {{-- Amount (editable) --}}
        <div class="px-3 flex-shrink-0" :style="`width: ${columns.amount.width}px`">
            <div x-show="!editing.amount" @click="editing.amount = true; $nextTick(() => $refs.amountInput?.focus())" class="text-sm text-slate-600 cursor-text hover:bg-white px-1 -mx-1 rounded"><span x-text="data.amount ? '€' + data.amount : '–'"></span></div>
            <input x-show="editing.amount" x-cloak x-ref="amountInput" type="number" step="0.01" x-model="data.amount" @blur="updateField('amount'); editing.amount = false" @keydown.enter="updateField('amount'); editing.amount = false" @keydown.escape="editing.amount = false" class="w-20 text-sm px-1 -mx-1 border border-blue-500 rounded focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="0.00">
        </div>

        {{-- Total Charge --}}
        <div class="px-3 flex-shrink-0" :style="`width: ${columns.total_charge.width}px`">
            <div class="text-sm font-medium text-slate-900" x-text="data.total_amount ? '€' + parseFloat(data.total_amount).toFixed(2) : '–'"></div>
        </div>

        {{-- Actions --}}
        <div class="px-2 flex-shrink-0 opacity-0 group-hover:opacity-100" @click.stop>
            <button class="p-0.5 hover:bg-slate-200 rounded"><svg class="w-3.5 h-3.5 text-slate-500" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/></svg></button>
        </div>
    </div>
@empty
    <div class="h-8 px-3 flex items-center text-sm text-slate-400 italic border-b border-slate-100">{{ __('No tasks') }}</div>
@endforelse
