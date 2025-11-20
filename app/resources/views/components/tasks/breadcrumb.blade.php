@props(['currentList' => null])

@if($currentList)
    <nav class="flex items-center gap-2 text-sm text-slate-600 mb-4">
        <!-- Home -->
        <a href="{{ route('tasks.index') }}" class="hover:text-slate-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
        </a>

        @if($currentList->folder ?? null)
            <!-- Separator -->
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>

            <!-- Space -->
            @if(($currentList->folder->space ?? null))
                <span class="flex items-center gap-1">
                    <span>{{ $currentList->folder->space->icon ?? '📁' }}</span>
                    <span class="hover:text-slate-900">{{ $currentList->folder->space->name }}</span>
                </span>

                <!-- Separator -->
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            @endif

            <!-- Folder -->
            <span class="flex items-center gap-1">
                <span>{{ $currentList->folder->icon ?? '📂' }}</span>
                <span class="hover:text-slate-900">{{ $currentList->folder->name }}</span>
            </span>

            <!-- Separator -->
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        @endif

        <!-- Current List -->
        <span class="flex items-center gap-1 font-medium text-slate-900">
            <div
                class="w-2 h-2 rounded-full"
                style="background-color: {{ $currentList->color ?? '#94a3b8' }}"
            ></div>
            {{ $currentList->name }}
        </span>

        <!-- Client Badge -->
        @if($currentList->client)
            <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">
                {{ $currentList->client->name }}
            </span>
        @endif
    </nav>
@else
    <nav class="flex items-center gap-2 text-sm mb-4">
        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
        </svg>
        <span class="font-medium text-slate-900">{{ __('All Tasks') }}</span>
    </nav>
@endif
