<div x-data="datePickerDropdown()"
     @open-date-picker.window="open($event.detail)"
     x-show="isOpen"
     @click.away="close()"
     @keydown.escape.window="close()"
     class="fixed z-50 w-80 bg-white rounded-lg shadow-xl border border-gray-200 p-4"
     :style="`top: ${position.top}px; left: ${position.left}px`"
     style="display: none;"
     x-cloak>

    <div class="mb-3">
        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Due Date</span>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-2 gap-2 mb-3">
        <button @click="setDate('today')"
                class="px-3 py-2 text-sm bg-gray-50 hover:bg-gray-100 rounded transition-colors">
            Today
        </button>
        <button @click="setDate('tomorrow')"
                class="px-3 py-2 text-sm bg-gray-50 hover:bg-gray-100 rounded transition-colors">
            Tomorrow
        </button>
        <button @click="setDate('next-week')"
                class="px-3 py-2 text-sm bg-gray-50 hover:bg-gray-100 rounded transition-colors">
            Next Week
        </button>
        <button @click="setDate('next-month')"
                class="px-3 py-2 text-sm bg-gray-50 hover:bg-gray-100 rounded transition-colors">
            Next Month
        </button>
    </div>

    <div class="border-t border-gray-200 my-3"></div>

    {{-- Date Inputs --}}
    <div class="space-y-3">
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Start Date</label>
            <input type="date"
                   x-model="startDate"
                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Due Date</label>
            <input type="date"
                   x-model="dueDate"
                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>
    </div>

    <div class="border-t border-gray-200 my-3"></div>

    {{-- Actions --}}
    <div class="flex items-center justify-between gap-2">
        <button @click="clearDates()"
                class="px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded transition-colors">
            Clear
        </button>
        <button @click="saveDates()"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
            Save
        </button>
    </div>
</div>

@push('scripts')
<script>
function datePickerDropdown() {
    return {
        isOpen: false,
        taskId: null,
        startDate: '',
        dueDate: '',
        position: { top: 0, left: 0 },

        open(detail) {
            this.taskId = detail.taskId;
            this.startDate = detail.startDate || '';
            this.dueDate = detail.dueDate || '';

            const rect = detail.anchor.getBoundingClientRect();
            this.position = {
                top: rect.bottom + window.scrollY + 4,
                left: rect.left + window.scrollX
            };
            this.isOpen = true;
        },

        close() {
            this.isOpen = false;
            this.taskId = null;
            this.startDate = '';
            this.dueDate = '';
        },

        setDate(type) {
            const today = new Date();
            let date;

            switch(type) {
                case 'today':
                    date = today;
                    break;
                case 'tomorrow':
                    date = new Date(today);
                    date.setDate(date.getDate() + 1);
                    break;
                case 'next-week':
                    date = new Date(today);
                    date.setDate(date.getDate() + 7);
                    break;
                case 'next-month':
                    date = new Date(today);
                    date.setMonth(date.getMonth() + 1);
                    break;
            }

            if (date) {
                this.dueDate = date.toISOString().split('T')[0];
            }
        },

        clearDates() {
            this.startDate = '';
            this.dueDate = '';
            this.saveDates();
        },

        saveDates() {
            const taskElement = document.querySelector(`[data-task-id="${this.taskId}"]`);
            if (!taskElement) return;

            const livewireElement = taskElement.closest('[wire\\:id]');
            if (!livewireElement) return;

            const wireId = livewireElement.getAttribute('wire:id');
            const component = window.Livewire?.find(wireId);

            if (component) {
                component.call('updateDates', {
                    start_date: this.startDate || null,
                    due_date: this.dueDate || null
                });
            }

            this.close();
        }
    }
}
</script>
@endpush
