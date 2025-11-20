<!-- Bulk Actions Bar -->
<div
    x-show="$store.taskBulk.selectedTasks.length > 0"
    x-transition:enter="transform ease-out duration-300 transition"
    x-transition:enter-start="translate-y-2 opacity-0"
    x-transition:enter-end="translate-y-0 opacity-100"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-cloak
    class="fixed bottom-6 left-1/2 transform -translate-x-1/2 z-40"
>
    <div class="bg-slate-900 text-white rounded-lg shadow-2xl px-6 py-4 flex items-center gap-6">
        <!-- Selected Count -->
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="font-medium">
                <span x-text="$store.taskBulk.selectedTasks.length"></span>
                <span x-text="$store.taskBulk.selectedTasks.length === 1 ? '{{ __('task selected') }}' : '{{ __('tasks selected') }}'"></span>
            </span>
        </div>

        <!-- Divider -->
        <div class="h-6 w-px bg-slate-700"></div>

        <!-- Actions -->
        <div class="flex items-center gap-2">
            <!-- Change Status -->
            <button
                @click="$store.taskBulk.showStatusPicker = !$store.taskBulk.showStatusPicker"
                class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 transition-colors text-sm font-medium flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                {{ __('Status') }}
            </button>

            <!-- Move to List -->
            <button
                @click="$store.taskBulk.showListPicker = !$store.taskBulk.showListPicker"
                class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 transition-colors text-sm font-medium flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                {{ __('Move') }}
            </button>

            <!-- Delete -->
            <button
                @click="$store.taskBulk.bulkDelete()"
                class="px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 transition-colors text-sm font-medium flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                {{ __('Delete') }}
            </button>
        </div>

        <!-- Divider -->
        <div class="h-6 w-px bg-slate-700"></div>

        <!-- Clear Selection -->
        <button
            @click="$store.taskBulk.clearSelection()"
            class="px-3 py-1.5 rounded-lg hover:bg-slate-800 transition-colors text-sm font-medium"
        >
            {{ __('Clear') }}
        </button>
    </div>

    <!-- Status Picker Dropdown -->
    <div
        x-show="$store.taskBulk.showStatusPicker"
        @click.away="$store.taskBulk.showStatusPicker = false"
        x-transition
        class="absolute bottom-full mb-2 left-0 bg-white rounded-lg shadow-xl border border-slate-200 py-2 min-w-[200px]"
    >
        @foreach($taskStatuses ?? [] as $status)
            <button
                @click="$store.taskBulk.bulkUpdateStatus({{ $status->id }})"
                class="w-full px-4 py-2 text-left hover:bg-slate-50 flex items-center gap-3 text-slate-900"
            >
                <div class="w-3 h-3 rounded-full" style="background-color: {{ $status->color }}"></div>
                <span class="text-sm">{{ $status->label }}</span>
            </button>
        @endforeach
    </div>

    <!-- List Picker Dropdown -->
    <div
        x-show="$store.taskBulk.showListPicker"
        @click.away="$store.taskBulk.showListPicker = false"
        x-transition
        class="absolute bottom-full mb-2 left-0 bg-white rounded-lg shadow-xl border border-slate-200 py-2 min-w-[250px] max-h-64 overflow-y-auto"
    >
        @foreach($lists ?? [] as $list)
            <button
                @click="$store.taskBulk.bulkMoveToList({{ $list->id }})"
                class="w-full px-4 py-2 text-left hover:bg-slate-50 flex items-center gap-3 text-slate-900"
            >
                <div class="w-2 h-2 rounded-full" style="background-color: {{ $list->color ?? '#94a3b8' }}"></div>
                <div class="flex-1">
                    <div class="text-sm font-medium">{{ $list->name }}</div>
                    @if($list->client)
                        <div class="text-xs text-slate-500">{{ $list->client->name }}</div>
                    @endif
                </div>
            </button>
        @endforeach
    </div>
</div>
