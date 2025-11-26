<div x-data="listDropdown()"
     @open-list-dropdown.window="open($event.detail)"
     x-show="isOpen"
     @click.away="close()"
     @keydown.escape.window="close()"
     class="fixed z-50 w-64 bg-white rounded-lg shadow-xl border border-gray-200 py-2"
     :style="`top: ${position.top}px; left: ${position.left}px`"
     style="display: none;"
     x-cloak>

    <div class="px-3 py-1 mb-1">
        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">List / Project</span>
    </div>

    <div class="max-h-64 overflow-y-auto">
        @php
            $lists = App\Models\TaskList::orderBy('name')->get();
        @endphp

        @foreach($lists as $list)
            <button @click="selectList({{ $list->id }})"
                    class="w-full px-3 py-2 text-left text-sm hover:bg-gray-50 transition-colors">
                {{ $list->name }}
            </button>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
function listDropdown() {
    return {
        isOpen: false,
        taskId: null,
        position: { top: 0, left: 0 },

        open(detail) {
            this.taskId = detail.taskId;
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
        },

        selectList(listId) {
            const taskElement = document.querySelector(`[data-task-id="${this.taskId}"]`);
            if (!taskElement) return;

            const livewireElement = taskElement.closest('[wire\\:id]');
            if (!livewireElement) return;

            const wireId = livewireElement.getAttribute('wire:id');
            const component = window.Livewire?.find(wireId);

            if (component) {
                component.call('updateList', listId);
            }

            this.close();
        }
    }
}
</script>
@endpush
