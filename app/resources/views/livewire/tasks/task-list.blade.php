<div class="bg-white overflow-hidden p-4"
     x-data="{
         columns: {
             name: { width: 300 },
             project: { width: 200 },
             service: { width: 200 },
             due_date: { width: 150 },
             status: { width: 150 },
             priority: { width: 120 },
             assignee: { width: 150 },
             time_tracked: { width: 120 }
         },
         init() {
             // Load column widths from localStorage
             const saved = localStorage.getItem('task_columns');
             if (saved) {
                 this.columns = JSON.parse(saved);
             }
         },
         saveColumns() {
             localStorage.setItem('task_columns', JSON.stringify(this.columns));
         }
     }"
     x-init="init()">

    {{-- Bulk Actions Toolbar --}}
    @if(count($selectedTasks) > 0)
        <div class="bulk-actions-toolbar">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-blue-900">
                        {{ count($selectedTasks) }} task{{ count($selectedTasks) > 1 ? 's' : '' }} selected
                    </span>
                    <button wire:click="clearSelection" class="text-sm text-blue-600 hover:text-blue-800">
                        Clear
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    {{-- Status --}}
                    <select wire:change="bulkUpdateStatus($event.target.value)" class="text-sm border-blue-300 rounded-md">
                        <option value="">Change Status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}">{{ $status->label }}</option>
                        @endforeach
                    </select>

                    {{-- Priority --}}
                    <select wire:change="bulkUpdatePriority($event.target.value)" class="text-sm border-blue-300 rounded-md">
                        <option value="">Change Priority</option>
                        @php
                            $priorities = App\Models\SettingOption::taskPriorities()->get();
                        @endphp
                        @foreach($priorities as $priority)
                            <option value="{{ $priority->id }}">{{ $priority->label }}</option>
                        @endforeach
                    </select>

                    <div class="w-px h-6 bg-blue-300"></div>

                    <button wire:click="bulkDelete" wire:confirm="Are you sure you want to delete selected tasks?"
                            class="px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded-md transition-colors">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Table Container with ClickUp-style scroll --}}
    <div id="task-view-container">
        <div id="task-scroll-wrapper">
            <div id="task-table">
                <table class="w-full" style="border-collapse: collapse; border-spacing: 0;">
                    <tbody>
                    @foreach($statuses as $status)
                        {{-- Status Group --}}
                        <tr class="border-0">
                            <td colspan="100%" class="p-0 border-0 @if(!$loop->first) pt-6 @endif">
                                <div x-data="{
                                    expanded: (() => {
                                        const saved = localStorage.getItem('status_{{ $status->id }}_expanded');
                                        return saved !== null ? saved === 'true' : true;
                                    })()
                                }"
                                x-init="$watch('expanded', value => localStorage.setItem('status_{{ $status->id }}_expanded', value))"
                                class="w-full">
                                    {{-- Group Header Row 1: Status Badge + Count + Actions --}}
                                    <div @click="expanded = !expanded"
                                         class="flex items-center gap-2 h-9 px-3 cursor-pointer hover:bg-slate-50">
                                        <button class="flex-shrink-0" @click.stop="expanded = !expanded">
                                            <svg class="w-2.5 h-2.5 text-slate-400 transition-transform"
                                                 :class="expanded ? 'rotate-90' : ''"
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </button>

                                        <div class="flex items-center gap-2">
                                            <span class="status-badge" style="color: {{ $status->color_class }}">
                                                {{ $status->label }}
                                            </span>
                                            <span class="text-sm text-slate-500">{{ $taskCounts[$status->id] ?? 0 }}</span>
                                        </div>

                                        <div class="flex items-center gap-1" @click.stop>
                                            <button @click="$dispatch('open-task-modal', { status_id: {{ $status->id }} })"
                                                    class="add-task-btn">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                </svg>
                                                Add Task
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Group Header Row 2: Column Headers --}}
                                    <div x-show="expanded" x-collapse x-cloak>
                                        <div class="flex items-center h-9 bg-white border-b border-gray-200 text-sm font-semibold text-slate-500">
                                            {{-- Checkbox Column --}}
                                            <div class="w-8 px-2 flex-shrink-0"></div>

                                            {{-- Name Column --}}
                                            <div class="px-3 flex-shrink-0 relative" :style="`width: ${columns.name.width}px`">
                                                Name
                                            </div>

                                            {{-- Project Column --}}
                                            <div class="px-3 flex-shrink-0" :style="`width: ${columns.project.width}px`">
                                                Project
                                            </div>

                                            {{-- Service Column --}}
                                            <div class="px-3 flex-shrink-0" :style="`width: ${columns.service.width}px`">
                                                Service
                                            </div>

                                            {{-- Due Date Column --}}
                                            <div class="px-3 flex-shrink-0" :style="`width: ${columns.due_date.width}px`">
                                                Due date
                                            </div>

                                            {{-- Status Column --}}
                                            <div class="px-3 flex-shrink-0" :style="`width: ${columns.status.width}px`">
                                                Status
                                            </div>

                                            {{-- Priority Column --}}
                                            <div class="px-3 flex-shrink-0" :style="`width: ${columns.priority.width}px`">
                                                Priority
                                            </div>

                                            {{-- Assignee Column --}}
                                            <div class="px-3 flex-shrink-0" :style="`width: ${columns.assignee.width}px`">
                                                Assignee
                                            </div>

                                            {{-- Time Tracked Column --}}
                                            <div class="px-3 flex-shrink-0" :style="`width: ${columns.time_tracked.width}px`">
                                                Time tracked
                                            </div>
                                        </div>

                                        {{-- Task Rows --}}
                                        @foreach($this->getTasksForStatus($status->id) as $task)
                                            <livewire:tasks.task-row
                                                :task="$task"
                                                :key="'task-'.$task->id"
                                                wire:key="task-{{ $task->id }}" />
                                        @endforeach

                                        @if($this->getTasksForStatus($status->id)->isEmpty())
                                            <div class="h-12 px-3 flex items-center text-sm text-gray-400 italic">
                                                No tasks
                                            </div>
                                        @endif
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

    {{-- Global Dropdowns (Alpine.js UI only) --}}
    <x-tasks.dropdowns.status :statuses="$statuses" />
    <x-tasks.dropdowns.priority />
    <x-tasks.dropdowns.assignee />
    <x-tasks.dropdowns.service />
    <x-tasks.dropdowns.list />
    <x-tasks.dropdowns.date-picker />
</div>

@push('scripts')
<script>
// Initialize Alpine collapse plugin if not already loaded
if (typeof Alpine !== 'undefined' && !Alpine.directive('collapse')) {
    Alpine.plugin(function (Alpine) {
        Alpine.directive('collapse', (el, { value }, { effect, evaluateLater }) => {
            let isOpen = evaluateLater(value);

            effect(() => {
                isOpen(show => {
                    if (show) {
                        el.style.display = 'block';
                        el.style.overflow = 'hidden';
                        el.style.height = '0px';
                        el.style.transition = 'height 0.2s ease';

                        requestAnimationFrame(() => {
                            el.style.height = el.scrollHeight + 'px';
                            setTimeout(() => {
                                el.style.height = 'auto';
                                el.style.overflow = 'visible';
                            }, 200);
                        });
                    } else {
                        el.style.overflow = 'hidden';
                        el.style.height = el.scrollHeight + 'px';
                        el.style.transition = 'height 0.2s ease';

                        requestAnimationFrame(() => {
                            el.style.height = '0px';
                            setTimeout(() => {
                                el.style.display = 'none';
                            }, 200);
                        });
                    }
                });
            });
        });
    });
}
</script>
@endpush
