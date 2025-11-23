{{-- ClickUp Views Bar Controller - Pixel Perfect Replica --}}
@props(['currentView' => 'list'])

<div class="bg-white border-b border-slate-200">
    {{-- Controller Row --}}
    <div class="flex items-center justify-between h-[52px] px-4 gap-4">

        {{-- Left Side: View Tabs --}}
        <div class="flex items-center gap-1 flex-1 overflow-x-auto">
            {{-- Financiar --}}
            <a href="{{ route('tasks.index', array_merge(request()->except('view'), ['view' => 'financiar'])) }}"
               class="flex items-center gap-1.5 px-2.5 py-1.5 text-[13px] rounded hover:bg-slate-100 transition-colors whitespace-nowrap {{ $currentView === 'financiar' ? 'bg-slate-100' : '' }}">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span class="font-medium {{ $currentView === 'financiar' ? 'text-slate-900' : 'text-slate-700' }}">{{ __('Financiar') }}</span>
            </a>

            {{-- De facturat --}}
            <a href="{{ route('tasks.index', array_merge(request()->except('view'), ['view' => 'de-facturat'])) }}"
               class="flex items-center gap-1.5 px-2.5 py-1.5 text-[13px] rounded hover:bg-slate-100 transition-colors whitespace-nowrap {{ $currentView === 'de-facturat' ? 'bg-slate-100' : '' }}">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="font-medium {{ $currentView === 'de-facturat' ? 'text-slate-900' : 'text-slate-700' }}">{{ __('De facturat') }}</span>
            </a>

            {{-- Incasat --}}
            <a href="{{ route('tasks.index', array_merge(request()->except('view'), ['view' => 'incasat'])) }}"
               class="flex items-center gap-1.5 px-2.5 py-1.5 text-[13px] rounded hover:bg-slate-100 transition-colors whitespace-nowrap {{ $currentView === 'incasat' ? 'bg-slate-100' : '' }}">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span class="font-medium {{ $currentView === 'incasat' ? 'text-slate-900' : 'text-slate-700' }}">{{ __('Incasat') }}</span>
            </a>

            {{-- Boards --}}
            <a href="{{ route('tasks.index', array_merge(request()->except('view'), ['view' => 'boards'])) }}"
               class="flex items-center gap-1.5 px-2.5 py-1.5 text-[13px] rounded hover:bg-slate-100 transition-colors whitespace-nowrap {{ $currentView === 'boards' ? 'bg-slate-100' : '' }}">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                </svg>
                <span class="font-medium {{ $currentView === 'boards' ? 'text-slate-900' : 'text-slate-700' }}">{{ __('Boards') }}</span>
            </a>

            {{-- List (Active) --}}
            <a href="{{ route('tasks.index', array_merge(request()->except('view'), ['view' => 'list'])) }}"
               class="flex items-center gap-1.5 px-2.5 py-1.5 text-[13px] rounded hover:bg-slate-100 transition-colors whitespace-nowrap {{ $currentView === 'list' ? 'bg-slate-100' : '' }}">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                <span class="font-medium {{ $currentView === 'list' ? 'text-slate-900' : 'text-slate-700' }}">{{ __('List') }}</span>
            </a>

            {{-- Add View Button --}}
            <button class="flex items-center gap-1 px-2.5 py-1.5 text-[13px] text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded transition-colors whitespace-nowrap">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="font-medium">{{ __('View') }}</span>
            </button>
        </div>

        {{-- Right Side: Controls --}}
        <div class="flex items-center gap-2">
            {{-- Search Button --}}
            <button class="flex items-center gap-1.5 px-2.5 py-1.5 text-[13px] text-slate-700 hover:bg-slate-100 rounded transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span class="font-medium">{{ __('Search') }}</span>
            </button>

            {{-- Hide Button --}}
            <button class="flex items-center gap-1.5 px-2.5 py-1.5 text-[13px] text-slate-700 hover:bg-slate-100 rounded transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
                <span class="font-medium">{{ __('Hide') }}</span>
            </button>

            {{-- Customize Button --}}
            <button class="flex items-center gap-1.5 px-2.5 py-1.5 text-[13px] text-slate-700 hover:bg-slate-100 rounded transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                </svg>
                <span class="font-medium">{{ __('Customize') }}</span>
            </button>

            {{-- Divider --}}
            <div class="h-6 w-px bg-slate-200"></div>

            {{-- Add Task Button --}}
            <button @click="$dispatch('open-task-modal')"
                    class="flex items-center gap-1.5 px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-[13px] font-medium rounded transition-colors">
                {{ __('Add Task') }}
            </button>

            {{-- More Options Dropdown --}}
            <button class="p-1.5 text-slate-600 hover:bg-slate-100 rounded transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                </svg>
            </button>

            {{-- Toggle Collapse Button --}}
            <button class="p-1.5 text-slate-600 hover:bg-slate-100 rounded transition-colors">
                <svg class="w-4 h-4 transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                </svg>
            </button>
        </div>
    </div>
</div>
