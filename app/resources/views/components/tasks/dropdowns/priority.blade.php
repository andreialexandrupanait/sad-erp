<div x-data="priorityDropdown()"
     @open-priority-dropdown.window="open($event.detail)"
     x-show="isOpen"
     @click.away="close()"
     @keydown.escape.window="close()"
     class="fixed z-50 w-56 bg-white rounded-lg shadow-xl border border-gray-200 py-2"
     :style="`top: ${position.top}px; left: ${position.left}px`"
     style="display: none;"
     x-cloak>

    <div class="px-3 py-1 mb-1">
        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Priority</span>
    </div>

    <button @click="selectPriority(null)"
            class="w-full px-3 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-2.5 text-gray-500">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z"/>
        </svg>
        <span>No Priority</span>
    </button>

    <div class="border-t border-gray-200 my-1"></div>

    @php
        $priorities = App\Models\SettingOption::taskPriorities()->get();
        $priorityConfig = [
            'URGENT' => ['color' => 'text-red-600', 'bg' => 'hover:bg-red-50'],
            'HIGH' => ['color' => 'text-orange-500', 'bg' => 'hover:bg-orange-50'],
            'NORMAL' => ['color' => 'text-blue-500', 'bg' => 'hover:bg-blue-50'],
            'LOW' => ['color' => 'text-gray-400', 'bg' => 'hover:bg-gray-50']
        ];
    @endphp

    @foreach($priorities as $priority)
        @php
            $label = strtoupper($priority->label);
            $config = $priorityConfig[$label] ?? ['color' => 'text-gray-600', 'bg' => 'hover:bg-gray-50'];
        @endphp
        <button @click="selectPriority({{ $priority->id }})"
                class="w-full px-3 py-2 text-left text-sm {{ $config['bg'] }} flex items-center gap-2.5">
            <svg class="w-4 h-4 {{ $config['color'] }}" fill="currentColor" viewBox="0 0 20 20">
                <path d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z"/>
            </svg>
            <span class="{{ $config['color'] }} font-medium">{{ $priority->label }}</span>
        </button>
    @endforeach
</div>

@push('scripts')
<script>
function priorityDropdown() {
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

        selectPriority(priorityId) {
            const taskElement = document.querySelector(`[data-task-id="${this.taskId}"]`);
            if (!taskElement) return;

            const livewireElement = taskElement.closest('[wire\\:id]');
            if (!livewireElement) return;

            const wireId = livewireElement.getAttribute('wire:id');
            const component = window.Livewire?.find(wireId);

            if (component) {
                component.call('updatePriority', priorityId);
            }

            this.close();
        }
    }
}
</script>
@endpush
