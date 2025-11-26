@props(['statuses'])

<div x-data="statusDropdown()"
     @open-status-dropdown.window="open($event.detail)"
     x-show="isOpen"
     @click.away="close()"
     @keydown.escape.window="close()"
     class="fixed z-50 w-56 bg-white rounded-lg shadow-xl border border-gray-200 py-2"
     :style="`top: ${position.top}px; left: ${position.left}px`"
     style="display: none;"
     x-cloak>

    <div class="px-3 py-1 mb-1">
        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</span>
    </div>

    @foreach($statuses as $status)
        <button @click="selectStatus({{ $status->id }})"
                class="w-full px-3 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-2.5 transition-colors">
            <div class="w-3 h-3 rounded-full" style="background-color: {{ $status->color_class }}"></div>
            <span>{{ $status->label }}</span>
        </button>
    @endforeach
</div>

@push('scripts')
<script>
function statusDropdown() {
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

        selectStatus(statusId) {
            // Find the Livewire component for this task
            const taskElement = document.querySelector(`[data-task-id="${this.taskId}"]`);
            if (!taskElement) return;

            const livewireElement = taskElement.closest('[wire\\:id]');
            if (!livewireElement) return;

            const wireId = livewireElement.getAttribute('wire:id');
            const component = window.Livewire?.find(wireId);

            if (component) {
                component.call('updateStatus', statusId);
            }

            this.close();
        }
    }
}
</script>
@endpush
