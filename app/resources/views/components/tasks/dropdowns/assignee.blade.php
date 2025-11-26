<div x-data="assigneeDropdown()"
     @open-assignee-dropdown.window="open($event.detail)"
     x-show="isOpen"
     @click.away="close()"
     @keydown.escape.window="close()"
     class="fixed z-50 w-72 bg-white rounded-lg shadow-xl border border-gray-200 py-2"
     :style="`top: ${position.top}px; left: ${position.left}px`"
     style="display: none;"
     x-cloak>

    <div class="px-3 py-2 mb-1 border-b border-gray-100">
        <input type="text"
               x-model="search"
               placeholder="Search users..."
               class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
    </div>

    <div class="max-h-64 overflow-y-auto">
        @php
            $users = App\Models\User::orderBy('name')->get();
        @endphp

        @foreach($users as $user)
            <button @click="toggleAssignee({{ $user->id }})"
                    x-show="search === '' || '{{ strtolower($user->name) }}'.includes(search.toLowerCase())"
                    class="w-full px-3 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-2.5 transition-colors">

                <div class="flex-shrink-0">
                    @if($user->avatar)
                        <img src="{{ $user->avatar }}"
                             alt="{{ $user->name }}"
                             class="w-7 h-7 rounded-full">
                    @else
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white text-xs font-semibold">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                </div>

                <span class="flex-1">{{ $user->name }}</span>

                <svg x-show="assigneeIds.includes({{ $user->id }})"
                     class="w-4 h-4 text-blue-600 flex-shrink-0"
                     fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            </button>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
function assigneeDropdown() {
    return {
        isOpen: false,
        taskId: null,
        assigneeIds: [],
        search: '',
        position: { top: 0, left: 0 },

        open(detail) {
            this.taskId = detail.taskId;
            this.assigneeIds = detail.assigneeIds || [];
            this.search = '';

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
            this.assigneeIds = [];
            this.search = '';
        },

        toggleAssignee(userId) {
            const taskElement = document.querySelector(`[data-task-id="${this.taskId}"]`);
            if (!taskElement) return;

            const livewireElement = taskElement.closest('[wire\\:id]');
            if (!livewireElement) return;

            const wireId = livewireElement.getAttribute('wire:id');
            const component = window.Livewire?.find(wireId);

            if (component) {
                component.call('toggleAssignee', userId);

                // Update local state optimistically
                if (this.assigneeIds.includes(userId)) {
                    this.assigneeIds = this.assigneeIds.filter(id => id !== userId);
                } else {
                    this.assigneeIds.push(userId);
                }
            }
        }
    }
}
</script>
@endpush
