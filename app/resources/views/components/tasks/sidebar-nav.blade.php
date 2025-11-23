@props(['spaces' => []])

<!-- Task Hierarchy Sidebar -->
<div
    class="h-full bg-white border-r border-slate-200 overflow-y-auto"
    style="width: 280px;"
    x-data="{
        draggedItem: null,
        draggedType: null,
        dropTarget: null,
        contextMenu: {
            show: false,
            x: 0,
            y: 0,
            type: null,
            item: null
        },
        showContextMenu(event, type, item) {
            event.preventDefault();
            this.contextMenu = {
                show: true,
                x: event.clientX,
                y: event.clientY,
                type: type,
                item: item
            };
        },
        hideContextMenu() {
            this.contextMenu.show = false;
        },
        async moveFolder(folderId, newSpaceId) {
            try {
                const response = await fetch(`/task-folders/${folderId}/position`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({
                        space_id: newSpaceId,
                        position: 999
                    })
                });
                if (response.ok) {
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error moving folder:', error);
            }
        },
        async moveList(listId, newFolderId) {
            try {
                const response = await fetch(`/task-lists/${listId}/position`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({
                        folder_id: newFolderId,
                        position: 999
                    })
                });
                if (response.ok) {
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error moving list:', error);
            }
        },
        handleDragStart(event, id, type) {
            this.draggedItem = id;
            this.draggedType = type;
            event.target.classList.add('opacity-50');
            event.dataTransfer.effectAllowed = 'move';
        },
        handleDragEnd(event) {
            event.target.classList.remove('opacity-50');
            this.draggedItem = null;
            this.draggedType = null;
            this.dropTarget = null;
        },
        handleDragOver(event, id, type) {
            event.preventDefault();
            this.dropTarget = id;
        },
        handleDragLeave() {
            this.dropTarget = null;
        },
        async handleDrop(event, targetId, targetType) {
            event.preventDefault();
            event.stopPropagation();

            // Folder dropped on Space
            if (this.draggedType === 'folder' && targetType === 'space') {
                await this.moveFolder(this.draggedItem, targetId);
            }
            // List dropped on Folder
            else if (this.draggedType === 'list' && targetType === 'folder') {
                await this.moveList(this.draggedItem, targetId);
            }

            this.draggedItem = null;
            this.draggedType = null;
            this.dropTarget = null;
        }
    }"
    @click.away="hideContextMenu()"
>
    <!-- Header -->
    <div class="sticky top-0 z-10 bg-white border-b border-slate-200 px-4 py-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-900">{{ __('Workspaces') }}</h2>
            <button
                @click="$store.taskModals.openSpaceModal()"
                class="p-1 text-slate-400 hover:text-slate-600 rounded hover:bg-slate-100"
                title="{{ __('New Space') }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Spaces List -->
    <div class="p-2">
        @forelse($spaces ?? [] as $space)
            <div class="mb-1" x-data="{ expanded: {{ $loop->first ? 'true' : 'false' }} }">
                <!-- Space -->
                <div
                    class="group flex items-center gap-2 px-2 py-1.5 rounded hover:bg-slate-100 cursor-pointer transition-colors"
                    :class="dropTarget === {{ $space->id }} && draggedType === 'folder' ? 'bg-blue-100 ring-2 ring-blue-400' : ''"
                    @contextmenu="showContextMenu($event, 'space', {{ $space->id }})"
                    @dragover="handleDragOver($event, {{ $space->id }}, 'space')"
                    @dragleave="handleDragLeave()"
                    @drop="handleDrop($event, {{ $space->id }}, 'space')"
                >
                    <button
                        @click="expanded = !expanded"
                        class="flex items-center gap-2 flex-1 text-left"
                    >
                        <svg
                            class="w-3 h-3 text-slate-400 transition-transform"
                            :class="expanded ? 'rotate-90' : ''"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span class="text-lg">{{ $space->icon ?? '📁' }}</span>
                        <span class="text-sm font-medium text-slate-700">{{ $space->name }}</span>
                    </button>

                    <!-- Space Actions -->
                    <div class="opacity-0 group-hover:opacity-100 flex gap-1">
                        <button
                            @click.stop="$store.taskModals.openSpaceModal({{ $space->id }})"
                            class="p-1 text-slate-400 hover:text-amber-600"
                            title="{{ __('Edit Space') }}"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <button
                            @click.stop="$store.taskModals.openFolderModal({{ $space->id }})"
                            class="p-1 text-slate-400 hover:text-blue-600"
                            title="{{ __('New Folder') }}"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Folders -->
                <div x-show="expanded" class="ml-6 mt-1 space-y-1">
                    @forelse($space->folders ?? [] as $folder)
                        <div x-data="{ folderExpanded: {{ $loop->first ? 'true' : 'false' }} }">
                            <!-- Folder -->
                            <div
                                class="group flex items-center gap-2 px-2 py-1 rounded hover:bg-slate-100 cursor-move transition-colors"
                                :class="dropTarget === {{ $folder->id }} && draggedType === 'list' ? 'bg-green-100 ring-2 ring-green-400' : ''"
                                draggable="true"
                                @dragstart="handleDragStart($event, {{ $folder->id }}, 'folder')"
                                @dragend="handleDragEnd($event)"
                                @contextmenu="showContextMenu($event, 'folder', {{ $folder->id }})"
                                @dragover="handleDragOver($event, {{ $folder->id }}, 'folder')"
                                @dragleave="handleDragLeave()"
                                @drop="handleDrop($event, {{ $folder->id }}, 'folder')"
                            >
                                <button
                                    @click="folderExpanded = !folderExpanded"
                                    class="flex items-center gap-2 flex-1 text-left"
                                >
                                    <svg
                                        class="w-3 h-3 text-slate-400 transition-transform"
                                        :class="folderExpanded ? 'rotate-90' : ''"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                    <span class="text-base">{{ $folder->icon ?? '📂' }}</span>
                                    <span class="text-sm text-slate-600">{{ $folder->name }}</span>
                                </button>

                                <!-- Folder Actions -->
                                <div class="opacity-0 group-hover:opacity-100 flex gap-1">
                                    <button
                                        @click.stop="$store.taskModals.openFolderModal({{ $space->id }}, {{ $folder->id }})"
                                        class="p-1 text-slate-400 hover:text-amber-600"
                                        title="{{ __('Edit Folder') }}"
                                    >
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button
                                        @click.stop="$store.taskModals.openListModal({{ $folder->id }})"
                                        class="p-1 text-slate-400 hover:text-blue-600"
                                        title="{{ __('New List') }}"
                                    >
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Lists -->
                            <div x-show="folderExpanded" class="ml-6 mt-1 space-y-1">
                                @forelse($folder->lists ?? [] as $list)
                                    <a
                                        href="{{ route('tasks.index', ['list_id' => $list->id]) }}"
                                        class="group flex items-center gap-2 px-2 py-1 rounded hover:bg-slate-100 {{ request('list_id') == $list->id ? 'bg-blue-50 text-blue-600' : 'text-slate-600' }} cursor-move"
                                        draggable="true"
                                        @dragstart="handleDragStart($event, {{ $list->id }}, 'list')"
                                        @dragend="handleDragEnd($event)"
                                        @contextmenu="showContextMenu($event, 'list', {{ $list->id }})"
                                        @click.prevent="window.location.href = '{{ route('tasks.index', ['list_id' => $list->id]) }}'"
                                    >
                                        <div
                                            class="w-2 h-2 rounded-full"
                                            style="background-color: {{ $list->color ?? '#94a3b8' }}"
                                        ></div>
                                        <span class="text-sm flex-1">{{ $list->name }}</span>
                                        @if($list->client)
                                            <span class="text-xs text-slate-400">{{ Str::limit($list->client->name, 15) }}</span>
                                        @endif
                                        <span class="text-xs text-slate-400">{{ $list->tasks_count ?? 0 }}</span>
                                    </a>
                                @empty
                                    <div class="px-2 py-1 text-xs text-slate-400 italic">
                                        {{ __('No lists') }}
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @empty
                        <div class="px-2 py-1 text-xs text-slate-400 italic">
                            {{ __('No folders') }}
                        </div>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="p-4 text-center text-sm text-slate-400">
                <p>{{ __('No workspaces yet') }}</p>
                <button
                    @click="$store.taskModals.openSpaceModal()"
                    class="mt-2 text-blue-600 hover:text-blue-700"
                >
                    {{ __('Create your first space') }}
                </button>
            </div>
        @endforelse
    </div>

    <!-- Everything View Link -->
    <div class="border-t border-slate-200 p-2">
        <a
            href="{{ route('tasks.index') }}"
            class="flex items-center gap-2 px-2 py-1.5 rounded hover:bg-slate-100 {{ !request('list_id') ? 'bg-slate-100 text-slate-900' : 'text-slate-600' }}"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
            <span class="text-sm font-medium">{{ __('Everything') }}</span>
        </a>
    </div>

    <!-- Context Menu -->
    <div
        x-show="contextMenu.show"
        x-cloak
        :style="`position: fixed; top: ${contextMenu.y}px; left: ${contextMenu.x}px; z-index: 9999;`"
        class="bg-white rounded-lg shadow-lg border border-slate-200 py-1 min-w-[160px]"
        @click.away="hideContextMenu()"
    >
        <!-- Space Context Menu -->
        <template x-if="contextMenu.type === 'space'">
            <div>
                <button
                    @click="$store.taskModals.openSpaceModal(contextMenu.item); hideContextMenu()"
                    class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    {{ __('Edit Space') }}
                </button>
                <button
                    @click="$store.taskModals.openFolderModal(contextMenu.item); hideContextMenu()"
                    class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('New Folder') }}
                </button>
                <div class="border-t border-slate-200 my-1"></div>
                <button
                    @click="if(confirm('{{ __('Delete this space and all its folders?') }}')) {
                        fetch(`/task-spaces/${contextMenu.item}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                        }).then(() => window.location.reload());
                    }; hideContextMenu()"
                    class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    {{ __('Delete Space') }}
                </button>
            </div>
        </template>

        <!-- Folder Context Menu -->
        <template x-if="contextMenu.type === 'folder'">
            <div>
                <button
                    @click="$store.taskModals.openFolderModal(null, contextMenu.item); hideContextMenu()"
                    class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    {{ __('Edit Folder') }}
                </button>
                <button
                    @click="$store.taskModals.openListModal(contextMenu.item); hideContextMenu()"
                    class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('New List') }}
                </button>
                <div class="border-t border-slate-200 my-1"></div>
                <button
                    @click="if(confirm('{{ __('Delete this folder and all its lists?') }}')) {
                        fetch(`/task-folders/${contextMenu.item}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                        }).then(() => window.location.reload());
                    }; hideContextMenu()"
                    class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    {{ __('Delete Folder') }}
                </button>
            </div>
        </template>

        <!-- List Context Menu -->
        <template x-if="contextMenu.type === 'list'">
            <div>
                <button
                    @click="$store.taskModals.openListModal(null, contextMenu.item); hideContextMenu()"
                    class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    {{ __('Edit List') }}
                </button>
                <div class="border-t border-slate-200 my-1"></div>
                <button
                    @click="if(confirm('{{ __('Delete this list and all its tasks?') }}')) {
                        fetch(`/task-lists/${contextMenu.item}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                        }).then(() => window.location.reload());
                    }; hideContextMenu()"
                    class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    {{ __('Delete List') }}
                </button>
            </div>
        </template>
    </div>
</div>
