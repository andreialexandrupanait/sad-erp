<div x-data="serviceDropdown()"
     @open-service-dropdown.window="open($event.detail)"
     x-show="isOpen"
     @click.away="close()"
     @keydown.escape.window="close()"
     class="fixed z-50 w-64 bg-white rounded-lg shadow-xl border border-gray-200 py-2"
     :style="`top: ${position.top}px; left: ${position.left}px`"
     style="display: none;"
     x-cloak>

    <div class="px-3 py-1 mb-1">
        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Service</span>
    </div>

    <button @click="selectService(null)"
            class="w-full px-3 py-2 text-left text-sm hover:bg-gray-50 text-gray-500">
        No Service
    </button>

    <div class="border-t border-gray-200 my-1"></div>

    <div class="max-h-64 overflow-y-auto">
        @php
            $services = App\Models\TaskService::orderBy('name')->get();
        @endphp

        @foreach($services as $service)
            <button @click="selectService({{ $service->id }})"
                    class="w-full px-3 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-2.5">
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium"
                      style="background-color: {{ $service->color }}20; color: {{ $service->color }}">
                    {{ $service->name }}
                </span>
            </button>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
function serviceDropdown() {
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

        selectService(serviceId) {
            const taskElement = document.querySelector(`[data-task-id="${this.taskId}"]`);
            if (!taskElement) return;

            const livewireElement = taskElement.closest('[wire\\:id]');
            if (!livewireElement) return;

            const wireId = livewireElement.getAttribute('wire:id');
            const component = window.Livewire?.find(wireId);

            if (component) {
                component.call('updateService', serviceId);
            }

            this.close();
        }
    }
}
</script>
@endpush
