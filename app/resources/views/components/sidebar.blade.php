<aside {{ $attributes->merge(['class' => 'w-64 h-screen bg-white border-r border-slate-200 flex flex-col shadow-sm fixed md:static inset-y-0 left-0 z-50 transform transition-transform duration-300 ease-in-out']) }}
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
    <div class="h-16 flex items-center px-4 border-b border-slate-200 flex-shrink-0">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <span class="font-semibold text-slate-900">CRM Simplead</span>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-4 px-3">

        <!-- Dashboard -->
        <div class="mb-4">
            <a href="{{ route('dashboard') }}"
               class="flex items-center px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('dashboard') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                {{ __('Dashboard') }}
            </a>
        </div>

        <!-- Divider -->
        <div class="border-t border-slate-200 my-4"></div>

        <!-- Clients Section -->
        <div class="mb-4">
            <div class="px-3 mb-2">
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('Clients') }}</h3>
            </div>
            <div class="space-y-0.5">
                <a href="{{ route('clients.index') }}"
                   class="flex items-center px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('clients.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    {{ __('Clients') }}
                </a>
                <a href="{{ route('credentials.index') }}"
                   class="flex items-center px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('credentials.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    {{ __('Access') }}
                </a>
            </div>
        </div>

        <!-- Divider -->
        <div class="border-t border-slate-200 my-4"></div>

        <!-- Task Management Section -->
        <div class="mb-4">
            <div class="px-3 mb-2">
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('Task Management') }}</h3>
            </div>
            <div class="space-y-0.5">
                <!-- Everything View -->
                <a href="{{ route('tasks.index') }}"
                   class="flex items-center px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('tasks.index') && !request('list_id') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    {{ __('Everything') }}
                </a>

                <!-- Workspaces -->
                @if(isset($taskSpaces) && $taskSpaces->count() > 0)
                    @foreach($taskSpaces as $space)
                        <div x-data="{ spaceExpanded: {{ request()->routeIs('tasks.*') || request('list_id') ? 'true' : 'false' }} }">
                            <!-- Space -->
                            <div class="group flex items-center gap-1.5 px-3 py-1.5 rounded-md hover:bg-slate-50 transition-colors"
                                 :class="dropTarget === {{ $space->id }} && draggedType === 'folder' ? 'bg-blue-100 ring-1 ring-blue-400' : ''"
                                 @contextmenu="showContextMenu($event, 'space', {{ $space->id }})"
                                 @dragover="handleDragOver($event, {{ $space->id }}, 'space')"
                                 @dragleave="handleDragLeave()"
                                 @drop="handleDrop($event, {{ $space->id }}, 'space')">
                                <button @click="spaceExpanded = !spaceExpanded" class="flex items-center gap-1.5 flex-1 text-left text-sm text-slate-600 hover:text-slate-900">
                                    <svg class="w-2.5 h-2.5 text-slate-400 transition-transform" :class="spaceExpanded ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                    <span>{{ $space->icon ?? '📁' }}</span>
                                    <span>{{ $space->name }}</span>
                                </button>
                                <div class="opacity-0 group-hover:opacity-100 flex gap-0.5">
                                    <button @click.stop="$store.taskModals.openSpaceModal({{ $space->id }})" class="p-0.5 text-slate-400 hover:text-amber-600" title="{{ __('Edit') }}">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button @click.stop="$store.taskModals.openFolderModal({{ $space->id }})" class="p-0.5 text-slate-400 hover:text-blue-600" title="{{ __('New Folder') }}">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Folders -->
                            <div x-show="spaceExpanded" class="ml-4 space-y-0.5">
                                @forelse($space->folders ?? [] as $folder)
                                    <div x-data="{ folderExpanded: {{ request()->routeIs('tasks.*') || request('list_id') ? 'true' : 'false' }} }">
                                        <!-- Folder -->
                                        <div class="group flex items-center gap-1.5 px-2 py-1 rounded-md hover:bg-slate-50 cursor-move transition-colors"
                                             :class="dropTarget === {{ $folder->id }} && draggedType === 'list' ? 'bg-green-100 ring-1 ring-green-400' : ''"
                                             draggable="true"
                                             @dragstart="handleDragStart($event, {{ $folder->id }}, 'folder')"
                                             @dragend="handleDragEnd($event)"
                                             @contextmenu="showContextMenu($event, 'folder', {{ $folder->id }})"
                                             @dragover="handleDragOver($event, {{ $folder->id }}, 'folder')"
                                             @dragleave="handleDragLeave()"
                                             @drop="handleDrop($event, {{ $folder->id }}, 'folder')">
                                            <button @click="folderExpanded = !folderExpanded" class="flex items-center gap-1.5 flex-1 text-left text-sm text-slate-600">
                                                <svg class="w-2.5 h-2.5 text-slate-400 transition-transform" :class="folderExpanded ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                                <span>{{ $folder->icon ?? '📂' }}</span>
                                                <span>{{ $folder->name }}</span>
                                            </button>
                                            <div class="opacity-0 group-hover:opacity-100 flex gap-0.5">
                                                <button @click.stop="$store.taskModals.openFolderModal({{ $space->id }}, {{ $folder->id }})" class="p-0.5 text-slate-400 hover:text-amber-600" title="{{ __('Edit') }}">
                                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                                <button @click.stop="$store.taskModals.openListModal({{ $folder->id }})" class="p-0.5 text-slate-400 hover:text-blue-600" title="{{ __('New List') }}">
                                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Lists -->
                                        <div x-show="folderExpanded" class="ml-4 space-y-0.5">
                                            @forelse($folder->lists ?? [] as $list)
                                                <a href="{{ route('tasks.index', ['list_id' => $list->id]) }}"
                                                   class="group flex items-center gap-1.5 px-2 py-1 rounded-md hover:bg-slate-50 cursor-move text-xs {{ request('list_id') == $list->id ? 'bg-blue-50 text-blue-600' : 'text-slate-600' }}"
                                                   draggable="true"
                                                   @dragstart="handleDragStart($event, {{ $list->id }}, 'list')"
                                                   @dragend="handleDragEnd($event)"
                                                   @contextmenu="showContextMenu($event, 'list', {{ $list->id }})"
                                                   @click.prevent="window.location.href = '{{ route('tasks.index', ['list_id' => $list->id]) }}'">
                                                    <div class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background-color: {{ $list->color ?? '#94a3b8' }}"></div>
                                                    <span class="flex-1 truncate">{{ $list->name }}</span>
                                                    @if($list->tasks_count ?? 0 > 0)
                                                        <span class="text-xs text-slate-400">{{ $list->tasks_count }}</span>
                                                    @endif
                                                </a>
                                            @empty
                                                <div class="px-2 py-1 text-xs text-slate-400 italic">{{ __('No lists') }}</div>
                                            @endforelse
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-2 py-1 text-xs text-slate-400 italic">{{ __('No folders') }}</div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                @endif

                <!-- Services -->
                <a href="{{ route('task-services.index') }}"
                   class="flex items-center px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('task-services.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    {{ __('Services') }}
                </a>
            </div>
        </div>

        <!-- Divider -->
        <div class="border-t border-slate-200 my-4"></div>

        <!-- Accounting Section -->
        <div class="mb-4">
            <div class="px-3 mb-2">
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('Accounting') }}</h3>
            </div>
            <div class="space-y-0.5">
                <a href="{{ route('financial.dashboard') }}" class="flex items-center px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('financial.dashboard') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ __('Financial') }}
                </a>
                <a href="{{ route('financial.revenues.index') }}" class="flex items-center px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('financial.revenues.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    {{ __('Revenues') }}
                </a>
                <a href="{{ route('financial.expenses.index') }}" class="flex items-center px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('financial.expenses.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                    </svg>
                    {{ __('Expenses') }}
                </a>
                <a href="{{ route('financial.files.index') }}" class="flex items-center px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('financial.files.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    {{ __('Files') }}
                </a>
                <a href="{{ route('financial.yearly-report') }}" class="flex items-center px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('financial.yearly-report') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    {{ __('Financial History') }}
                </a>
            </div>
        </div>

        <!-- Divider -->
        <div class="border-t border-slate-200 my-4"></div>

        <!-- Internal Resources Section -->
        <div class="mb-4">
            <div class="px-3 mb-2">
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('Internal Resources') }}</h3>
            </div>
            <div class="space-y-0.5">
                <a href="{{ route('internal-accounts.index') }}"
                   class="flex items-center px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('internal-accounts.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    {{ __('Accounts') }}
                </a>
                <a href="{{ route('subscriptions.index') }}"
                   class="flex items-center px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('subscriptions.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    {{ __('Subscriptions') }}
                </a>
                <a href="{{ route('domains.index') }}"
                   class="flex items-center px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('domains.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                    {{ __('Domains') }}
                </a>
            </div>
        </div>
    </nav>

    <!-- User Section (Bottom) -->
    <div class="border-t border-slate-100 p-3 space-y-0.5 flex-shrink-0">
        <a href="{{ route('profile.edit') }}"
           class="flex items-center px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('profile.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
            <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            {{ __('Profile') }}
        </a>
        <a href="{{ route('settings.index') }}"
           class="flex items-center px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('settings.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
            <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            {{ __('Settings') }}
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center px-3 py-1.5 text-sm rounded-md transition-colors text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                {{ __('Log Out') }}
            </button>
        </form>
    </div>

    <!-- Context Menu for Task Management -->
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
</aside>
