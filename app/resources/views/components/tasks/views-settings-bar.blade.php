{{-- ClickUp Views Settings Bar - Pixel Perfect Replica --}}
@props([
    'currentGrouping' => 'status',
    'showSubtasks' => false,
    'showColumns' => true,
    'activeFilters' => 0,
    'showClosed' => false,
    'showAssignee' => true,
    'meModeActive' => false
])

<div class="bg-white border-b border-slate-200">
    <div class="flex items-center justify-between h-[44px] px-4 gap-3">

        {{-- Left Side: Filter Controls --}}
        <div class="flex items-center gap-2">

            {{-- Group Dropdown --}}
            <button class="flex items-center gap-1.5 px-2.5 py-1 text-[13px] text-slate-700 hover:bg-slate-100 rounded transition-colors">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                </svg>
                <span class="font-medium">{{ __('Group: Status') }}</span>
                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            {{-- Subtasks Toggle --}}
            <button class="flex items-center gap-1.5 px-2.5 py-1 text-[13px] {{ $showSubtasks ? 'bg-slate-100 text-slate-900' : 'text-slate-700 hover:bg-slate-100' }} rounded transition-colors">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span class="font-medium">{{ __('Subtasks') }}</span>
                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            {{-- Columns Button with Dropdown --}}
            <div class="relative" x-data="{ showColumnMenu: false }">
                <button @click="showColumnMenu = !showColumnMenu"
                        class="flex items-center gap-1.5 px-2.5 py-1 text-[13px] {{ $showColumns ? 'bg-slate-100 text-slate-900' : 'text-slate-700 hover:bg-slate-100' }} rounded transition-colors">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                    <span class="font-medium">{{ __('Columns') }}</span>
                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Column Visibility Menu --}}
                <div x-show="showColumnMenu"
                     @click.away="showColumnMenu = false"
                     class="absolute left-0 top-full mt-1 w-64 bg-white rounded-lg shadow-xl border border-slate-200 py-2 z-50"
                     x-cloak>
                    <div class="px-3 py-2 border-b border-slate-200">
                        <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide">{{ __('Show/Hide Columns') }}</div>
                    </div>

                    {{-- Column Toggle List --}}
                    <div class="py-1">
                        <label class="flex items-center gap-2 px-3 py-1.5 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" checked disabled class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600">
                            <span class="text-[13px] text-slate-400">{{ __('Name') }} ({{ __('required') }})</span>
                        </label>
                        <label class="flex items-center gap-2 px-3 py-1.5 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" checked class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600">
                            <span class="text-[13px] text-slate-700">{{ __('Project') }}</span>
                        </label>
                        <label class="flex items-center gap-2 px-3 py-1.5 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" checked class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600">
                            <span class="text-[13px] text-slate-700">{{ __('Service') }}</span>
                        </label>
                        <label class="flex items-center gap-2 px-3 py-1.5 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" checked class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600">
                            <span class="text-[13px] text-slate-700">{{ __('Due Date') }}</span>
                        </label>
                        <label class="flex items-center gap-2 px-3 py-1.5 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" checked disabled class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600">
                            <span class="text-[13px] text-slate-400">{{ __('Status') }} ({{ __('required') }})</span>
                        </label>
                        <label class="flex items-center gap-2 px-3 py-1.5 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" checked class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600">
                            <span class="text-[13px] text-slate-700">{{ __('Priority') }}</span>
                        </label>
                        <label class="flex items-center gap-2 px-3 py-1.5 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" checked class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600">
                            <span class="text-[13px] text-slate-700">{{ __('Assignee') }}</span>
                        </label>
                        <label class="flex items-center gap-2 px-3 py-1.5 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" checked class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600">
                            <span class="text-[13px] text-slate-700">{{ __('Time Tracked') }}</span>
                        </label>
                        <label class="flex items-center gap-2 px-3 py-1.5 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" checked class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600">
                            <span class="text-[13px] text-slate-700">{{ __('Amount') }}</span>
                        </label>
                        <label class="flex items-center gap-2 px-3 py-1.5 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" checked class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600">
                            <span class="text-[13px] text-slate-700">{{ __('Total Charge') }}</span>
                        </label>
                    </div>

                    <div class="border-t border-slate-200 px-3 py-2">
                        <button class="text-[11px] text-blue-600 hover:text-blue-700 font-medium">
                            {{ __('Reset to Default') }}
                        </button>
                    </div>
                </div>
            </div>

            {{-- Filter Button --}}
            <button class="flex items-center gap-1.5 px-2.5 py-1 text-[13px] {{ $activeFilters > 0 ? 'bg-purple-100 text-purple-700' : 'text-slate-700 hover:bg-slate-100' }} rounded transition-colors">
                <svg class="w-3.5 h-3.5 {{ $activeFilters > 0 ? 'text-purple-600' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <span class="font-medium">{{ __('Filter') }}</span>
                @if($activeFilters > 0)
                    <span class="flex items-center justify-center w-4 h-4 text-[10px] font-bold bg-purple-600 text-white rounded-full">{{ $activeFilters }}</span>
                @endif
            </button>

            {{-- Separator --}}
            <div class="h-5 w-px bg-slate-200"></div>

            {{-- Closed Toggle --}}
            @if($showClosed)
                <button class="flex items-center gap-1.5 px-2.5 py-1 text-[13px] bg-slate-100 text-slate-900 rounded transition-colors">
                    <span class="font-medium">{{ __('Closed') }}</span>
                    <svg class="w-3 h-3 text-slate-500 hover:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            @endif

            {{-- Assignee Toggle --}}
            <button class="flex items-center gap-1.5 px-2.5 py-1 text-[13px] {{ $showAssignee ? 'bg-slate-100 text-slate-900' : 'text-slate-700 hover:bg-slate-100' }} rounded transition-colors">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span class="font-medium">{{ __('Assignee') }}</span>
            </button>

            {{-- Me Mode Avatar Toggle --}}
            <button class="flex items-center justify-center w-7 h-7 {{ $meModeActive ? 'ring-2 ring-purple-500' : '' }} rounded-full overflow-hidden hover:ring-2 hover:ring-slate-300 transition-all">
                @auth
                    @if(auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white text-[11px] font-semibold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @endif
                @endauth
            </button>
        </div>

        {{-- Right Side: Search --}}
        <div class="flex items-center gap-2">
            {{-- Search Input --}}
            <div class="flex items-center gap-2 px-2.5 py-1 bg-slate-50 hover:bg-slate-100 rounded transition-colors">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text"
                       placeholder="{{ __('Search tasks') }}"
                       class="w-48 bg-transparent text-[13px] text-slate-700 placeholder-slate-400 focus:outline-none">
            </div>

            {{-- Search Settings Menu --}}
            <button class="p-1 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded transition-colors">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                </svg>
            </button>
        </div>
    </div>
</div>
