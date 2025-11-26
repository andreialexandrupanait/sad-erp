@props(['spaces' => [], 'lists' => [], 'taskStatuses' => []])

<!-- Quick Switcher Modal (Cmd+K / Ctrl+K) -->
<div
    x-data="quickSwitcher()"
    @keydown.window.prevent.meta.k="open = true"
    @keydown.window.prevent.ctrl.k="open = true"
    x-show="open"
    x-cloak
   
    class="fixed inset-0 z-50 overflow-y-auto"
    @keydown.escape.window="close()"
>
    <!-- Backdrop -->
    <div
        x-show="open"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm"
        @click="close()"
    ></div>

    <!-- Modal Dialog -->
    <div class="flex min-h-full items-start justify-center p-4 pt-20">
        <div
            x-show="open"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden"
            @click.stop
        >
            <!-- Search Input -->
            <div class="p-4 border-b border-slate-200">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        x-ref="searchInput"
                        x-model="search"
                        @input="filterResults()"
                        @keydown.down.prevent="highlightNext()"
                        @keydown.up.prevent="highlightPrevious()"
                        @keydown.enter.prevent="selectHighlighted()"
                        type="text"
                        placeholder="{{ __('Search tasks, lists, or type a command...') }}"
                        class="w-full pl-10 pr-4 py-3 text-base border-0 focus:outline-none focus:ring-0"
                    />
                </div>
            </div>

            <!-- Results -->
            <div class="max-h-96 overflow-y-auto">
                <!-- Quick Actions -->
                <template x-if="search === '' || search.startsWith('>')">
                    <div class="p-2">
                        <div class="px-3 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            {{ __('Quick Actions') }}
                        </div>
                        @foreach([
                            ['icon' => 'M12 4v16m8-8H4', 'label' => __('Create Task'), 'action' => 'createTask', 'shortcut' => 'N'],
                            ['icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z', 'label' => __('Create Space'), 'action' => 'createSpace'],
                            ['icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z', 'label' => __('Create Folder'), 'action' => 'createFolder'],
                            ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'label' => __('Create List'), 'action' => 'createList'],
                        ] as $index => $action)
                            <button
                                @click="executeAction('{{ $action['action'] }}')"
                                :class="{ 'bg-blue-50 border-blue-500': highlightedIndex === {{ $index }} }"
                                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-50 border-2 border-transparent transition-colors"
                            >
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $action['icon'] }}"/>
                                </svg>
                                <span class="flex-1 text-left text-sm font-medium text-slate-900">{{ $action['label'] }}</span>
                                @if(isset($action['shortcut']))
                                    <kbd class="px-2 py-1 text-xs font-semibold text-slate-600 bg-slate-100 border border-slate-200 rounded">{{ $action['shortcut'] }}</kbd>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </template>

                <!-- Lists -->
                <template x-if="filteredLists.length > 0 && search !== '' && !search.startsWith('>')">
                    <div class="p-2">
                        <div class="px-3 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            {{ __('Lists') }}
                        </div>
                        <template x-for="(list, index) in filteredLists.slice(0, 5)" :key="list.id">
                            <button
                                @click="navigateToList(list.id)"
                                :class="{ 'bg-blue-50 border-blue-500': highlightedIndex === index + 4 }"
                                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-50 border-2 border-transparent transition-colors"
                            >
                                <div class="w-2 h-2 rounded-full" :style="`background-color: ${list.color || '#94a3b8'}`"></div>
                                <div class="flex-1 text-left">
                                    <div class="text-sm font-medium text-slate-900" x-text="list.name"></div>
                                    <div class="text-xs text-slate-500" x-text="list.client_name || ''"></div>
                                </div>
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </template>
                    </div>
                </template>

                <!-- Empty State -->
                <template x-if="search !== '' && !search.startsWith('>') && filteredLists.length === 0">
                    <div class="p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="mt-2 text-sm text-slate-600">{{ __('No results found') }}</p>
                    </div>
                </template>
            </div>

            <!-- Footer -->
            <div class="px-4 py-3 border-t border-slate-200 bg-slate-50 flex items-center justify-between text-xs text-slate-500">
                <div class="flex items-center gap-4">
                    <span class="flex items-center gap-1">
                        <kbd class="px-2 py-1 font-semibold bg-white border border-slate-200 rounded">↑↓</kbd>
                        {{ __('Navigate') }}
                    </span>
                    <span class="flex items-center gap-1">
                        <kbd class="px-2 py-1 font-semibold bg-white border border-slate-200 rounded">↵</kbd>
                        {{ __('Select') }}
                    </span>
                    <span class="flex items-center gap-1">
                        <kbd class="px-2 py-1 font-semibold bg-white border border-slate-200 rounded">ESC</kbd>
                        {{ __('Close') }}
                    </span>
                </div>
                <span>{{ __('Type > for commands') }}</span>
            </div>
        </div>
    </div>
</div>

<script>
function quickSwitcher() {
    return {
        open: false,
        search: '',
        highlightedIndex: 0,
        filteredLists: [],
        allLists: {!! json_encode(collect($lists)->map(function($list) {
            return [
                'id' => $list->id,
                'name' => $list->name,
                'color' => $list->color,
                'client_name' => $list->client?->name
            ];
        })->values()) !!},

        init() {
            this.$watch('open', (value) => {
                if (value) {
                    this.$nextTick(() => {
                        this.$refs.searchInput.focus();
                    });
                }
            });
        },

        close() {
            this.open = false;
            this.search = '';
            this.highlightedIndex = 0;
            this.filteredLists = [];
        },

        filterResults() {
            if (this.search === '' || this.search.startsWith('>')) {
                this.filteredLists = [];
                return;
            }

            const searchLower = this.search.toLowerCase();
            this.filteredLists = this.allLists.filter(list =>
                list.name.toLowerCase().includes(searchLower) ||
                (list.client_name && list.client_name.toLowerCase().includes(searchLower))
            );
            this.highlightedIndex = 4; // Start from first list result
        },

        highlightNext() {
            const maxIndex = this.search === '' || this.search.startsWith('>')
                ? 3 // 4 quick actions
                : 3 + Math.min(this.filteredLists.length, 5); // Quick actions + lists

            if (this.highlightedIndex < maxIndex) {
                this.highlightedIndex++;
            }
        },

        highlightPrevious() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            }
        },

        selectHighlighted() {
            if (this.search === '' || this.search.startsWith('>')) {
                // Execute quick action
                const actions = ['createTask', 'createSpace', 'createFolder', 'createList'];
                if (this.highlightedIndex < actions.length) {
                    this.executeAction(actions[this.highlightedIndex]);
                }
            } else if (this.filteredLists.length > 0) {
                // Navigate to list
                const listIndex = this.highlightedIndex - 4;
                if (listIndex >= 0 && listIndex < this.filteredLists.length) {
                    this.navigateToList(this.filteredLists[listIndex].id);
                }
            }
        },

        executeAction(action) {
            this.close();

            switch (action) {
                case 'createTask':
                    window.location.href = '{{ route('tasks.create') }}';
                    break;
                case 'createSpace':
                    Alpine.store('taskModals').openSpaceModal();
                    break;
                case 'createFolder':
                    Alpine.store('taskModals').openFolderModal();
                    break;
                case 'createList':
                    Alpine.store('taskModals').openListModal();
                    break;
            }
        },

        navigateToList(listId) {
            this.close();
            window.location.href = `/tasks?list_id=${listId}`;
        }
    };
}
</script>
