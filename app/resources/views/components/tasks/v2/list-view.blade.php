{{-- ClickUp-Style Task List View with Lazy Loading Performance --}}
@props(['statuses', 'organizationId', 'taskStatuses', 'taskPriorities', 'services', 'lists', 'users', 'filters' => []])

<div class="flex-1 overflow-hidden bg-white" x-data="taskListManager()">

    {{-- ClickUp-style Column Headers (sticky) --}}
    <div class="bg-[#f8f9fa] border-b border-[#e6e9ef] px-4 h-11 flex items-center sticky top-0 z-10">
        <div class="flex items-center text-[11px] font-semibold text-[#8b9bac] uppercase tracking-wide">
            <div class="w-8 px-2 flex-shrink-0"></div>
            <div class="px-3 flex-1 min-w-0">Name</div>
            <div class="px-3 w-48 flex-shrink-0">Lists</div>
            <div class="px-3 w-40 flex-shrink-0">Service</div>
            <div class="px-3 w-32 flex-shrink-0">Due date</div>
            <div class="px-3 w-40 flex-shrink-0">Assignee</div>
            <div class="px-3 w-32 flex-shrink-0">Priority</div>
            <div class="px-3 w-32 flex-shrink-0 text-right">Time</div>
            <div class="px-3 w-32 flex-shrink-0 text-right">Amount</div>
            <div class="px-2 w-20 flex-shrink-0"></div>
        </div>
    </div>

    {{-- Status Groups --}}
    <div class="overflow-y-auto" style="height: calc(100vh - 180px);">
        @forelse($statuses as $statusData)
            <x-tasks.v2.status-group
                :status="$statusData['status']"
                :taskCount="$statusData['count']"
                :organizationId="$organizationId"
            />
        @empty
            <div class="py-16 text-center">
                <svg class="mx-auto h-16 w-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-slate-900">No tasks found</h3>
                <p class="mt-2 text-sm text-slate-500">Get started by creating a new task.</p>
                <div class="mt-6">
                    <a href="{{ route('tasks.create') }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Create Task
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
function taskListManager() {
    return {
        searchQuery: '',
        listFilter: '',
        assigneeFilter: '',

        applyFilters() {
            // Build query string
            const params = new URLSearchParams();
            if (this.searchQuery) params.append('search', this.searchQuery);
            if (this.listFilter) params.append('list_id', this.listFilter);
            if (this.assigneeFilter) params.append('assignee', this.assigneeFilter);

            // Reload page with filters
            window.location.href = `{{ route('tasks.index') }}?${params.toString()}`;
        }
    }
}
</script>
@endpush

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
