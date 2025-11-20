@props(['spaces' => []])

<!-- Task Hierarchy Sidebar -->
<div
    class="h-full bg-white border-r border-slate-200 overflow-y-auto"
    style="width: 280px;"
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
                <div class="group flex items-center gap-2 px-2 py-1.5 rounded hover:bg-slate-100 cursor-pointer">
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
                            <div class="group flex items-center gap-2 px-2 py-1 rounded hover:bg-slate-100 cursor-pointer">
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
                                        class="group flex items-center gap-2 px-2 py-1 rounded hover:bg-slate-100 {{ request('list_id') == $list->id ? 'bg-blue-50 text-blue-600' : 'text-slate-600' }}"
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
</div>

