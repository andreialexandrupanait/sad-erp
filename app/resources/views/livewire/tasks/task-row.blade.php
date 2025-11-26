<div class="task-row flex items-center h-9 border-b border-gray-100 hover:bg-slate-50 group"
     data-task-id="{{ $task->id }}"
     wire:loading.class="opacity-50"
     x-data="{
         editing: { name: false },
         saving: false,
         taskName: '{{ addslashes($task->name) }}',
         saveField(field, value) {
             console.log('Saving field:', field, 'Value:', value);
             @this.call('updateField', field, value);
         },
         openStatus($event) {
             $dispatch('open-status-dropdown', {
                 taskId: {{ $task->id }},
                 anchor: $event.target
             })
         },
         openPriority($event) {
             $dispatch('open-priority-dropdown', {
                 taskId: {{ $task->id }},
                 anchor: $event.target
             })
         },
         openAssignee($event) {
             $dispatch('open-assignee-dropdown', {
                 taskId: {{ $task->id }},
                 anchor: $event.target,
                 assigneeIds: @js($task->assignees->pluck('id')->toArray())
             })
         },
         openService($event) {
             $dispatch('open-service-dropdown', {
                 taskId: {{ $task->id }},
                 anchor: $event.target
             })
         },
         openList($event) {
             $dispatch('open-list-dropdown', {
                 taskId: {{ $task->id }},
                 anchor: $event.target
             })
         },
         openDatePicker($event) {
             $dispatch('open-date-picker', {
                 taskId: {{ $task->id }},
                 anchor: $event.target,
                 startDate: '{{ $task->start_date?->format("Y-m-d") }}',
                 dueDate: '{{ $task->due_date?->format("Y-m-d") }}'
             })
         }
     }">

    {{-- Checkbox --}}
    <div class="w-8 px-2 flex-shrink-0" @click.stop>
        <input type="checkbox"
               value="{{ $task->id }}"
               wire:change="$parent.toggleTask({{ $task->id }})"
               class="task-checkbox">
    </div>

    {{-- Name (Editable) --}}
    <div class="px-3 flex-shrink-0" x-bind:style="$root.columns ? `width: ${$root.columns.name.width}px` : 'width: 300px'">
        <template x-if="!editing.name">
            <span @click="editing.name = true"
                  class="text-sm text-gray-900 cursor-text hover:bg-gray-100 px-1 -mx-1 rounded truncate block">
                {{ $task->name }}
            </span>
        </template>
        <template x-if="editing.name">
            <input type="text"
                   x-model="taskName"
                   x-ref="nameInput"
                   @blur="
                       if (taskName !== '{{ addslashes($task->name) }}') {
                           saveField('name', taskName);
                       };
                       editing.name = false
                   "
                   @keydown.enter="
                       if (taskName !== '{{ addslashes($task->name) }}') {
                           saveField('name', taskName);
                       };
                       editing.name = false;
                       $event.preventDefault()
                   "
                   @keydown.escape="
                       taskName = '{{ addslashes($task->name) }}';
                       editing.name = false
                   "
                   x-init="$nextTick(() => { $el.focus(); $el.select(); })"
                   class="w-full text-sm border border-blue-300 rounded px-2 py-1">
        </template>
    </div>

    {{-- Project --}}
    <div class="px-3 flex-shrink-0" x-bind:style="$root.columns ? `width: ${$root.columns.project.width}px` : 'width: 200px'">
        <button @click="openList($event)"
                class="w-full text-left text-sm text-gray-600 truncate hover:bg-gray-100 px-1 -mx-1 rounded">
            {{ $task->list?->name ?? '–' }}
        </button>
    </div>

    {{-- Service --}}
    <div class="px-3 flex-shrink-0" x-bind:style="$root.columns ? `width: ${$root.columns.service.width}px` : 'width: 200px'">
        <button @click="openService($event)" class="w-full text-left text-sm hover:bg-gray-100 px-1 -mx-1 rounded">
            @if($task->service)
                <span class="service-tag"
                      style="background-color: {{ $task->service->color }}20; color: {{ $task->service->color }}">
                    {{ $task->service->name }}
                </span>
            @else
                <span class="text-gray-400 text-xs">–</span>
            @endif
        </button>
    </div>

    {{-- Due Date --}}
    <div class="px-3 flex-shrink-0" x-bind:style="$root.columns ? `width: ${$root.columns.due_date.width}px` : 'width: 150px'">
        <button @click="openDatePicker($event)" class="w-full text-left text-sm hover:bg-gray-100 px-1 -mx-1 rounded">
            @if($task->due_date)
                @php
                    $isPast = $task->due_date->isPast();
                    $isToday = $task->due_date->isToday();
                    $dateClass = $isPast ? 'date-overdue' : ($isToday ? 'date-today' : 'date-upcoming');
                @endphp
                <span class="{{ $dateClass }}">{{ $task->due_date->format('M d, Y') }}</span>
            @else
                <span class="text-gray-400 text-xs">–</span>
            @endif
        </button>
    </div>

    {{-- Status --}}
    <div class="px-3 flex-shrink-0" x-bind:style="$root.columns ? `width: ${$root.columns.status.width}px` : 'width: 150px'">
        <button @click="openStatus($event)"
                class="w-full inline-flex items-center gap-2 text-sm hover:bg-gray-100 px-1 -mx-1 rounded"
                style="color: {{ $task->status->color_class }}">
            <div class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $task->status->color_class }}"></div>
            <span class="truncate">{{ $task->status->label }}</span>
        </button>
    </div>

    {{-- Priority --}}
    <div class="px-3 flex-shrink-0" x-bind:style="$root.columns ? `width: ${$root.columns.priority.width}px` : 'width: 120px'">
        <button @click="openPriority($event)"
                class="w-full inline-flex items-center gap-1.5 text-xs hover:bg-gray-100 px-1 -mx-1 rounded">
            @if($task->priority)
                @php
                    $priorityLabel = strtoupper($task->priority->label);
                    $flagColors = ['URGENT' => 'priority-urgent', 'HIGH' => 'priority-high', 'NORMAL' => 'priority-normal', 'LOW' => 'priority-low'];
                    $flagClass = $flagColors[$priorityLabel] ?? 'priority-normal';
                @endphp
                <svg class="w-3.5 h-3.5 {{ $flagClass }}" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z"/>
                </svg>
                <span class="{{ $flagClass }} truncate">{{ $task->priority->label }}</span>
            @else
                <span class="text-gray-400 text-xs">–</span>
            @endif
        </button>
    </div>

    {{-- Assignee --}}
    <div class="px-3 flex-shrink-0" x-bind:style="$root.columns ? `width: ${$root.columns.assignee.width}px` : 'width: 150px'">
        <button @click="openAssignee($event)" class="flex items-center hover:bg-gray-100 rounded px-1 -mx-1">
            @if($task->assignees->count() > 0)
                <div class="flex -space-x-2">
                    @foreach($task->assignees->take(3) as $assignee)
                        @if($assignee->avatar)
                            <img src="{{ $assignee->avatar }}" alt="{{ $assignee->name }}"
                                 class="assignee-avatar" title="{{ $assignee->name }}">
                        @else
                            <div class="assignee-avatar bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white text-xs font-semibold"
                                 title="{{ $assignee->name }}">
                                {{ strtoupper(substr($assignee->name, 0, 1)) }}
                            </div>
                        @endif
                    @endforeach
                    @if($task->assignees->count() > 3)
                        <div class="assignee-avatar bg-gray-200 flex items-center justify-center text-xs font-medium text-gray-600">
                            +{{ $task->assignees->count() - 3 }}
                        </div>
                    @endif
                </div>
            @else
                <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
            @endif
        </button>
    </div>

    {{-- Time Tracked --}}
    <div class="px-3 flex-shrink-0" x-bind:style="$root.columns ? `width: ${$root.columns.time_tracked.width}px` : 'width: 120px'">
        @php
            $tracked_h = floor($task->time_tracked / 60);
            $tracked_m = $task->time_tracked % 60;
            $tracked_display = $tracked_h > 0 ? "{$tracked_h}h {$tracked_m}m" : ($tracked_m > 0 ? "{$tracked_m}m" : '–');
        @endphp
        <span class="time-tracked">
            @if($task->time_tracked > 0)
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            @endif
            <span>{{ $tracked_display }}</span>
        </span>
    </div>
</div>
