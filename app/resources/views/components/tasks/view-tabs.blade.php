{{-- ClickUp-style View Tabs - Pixel Perfect --}}
@props(['currentView' => 'list'])

<div class="bg-white border-b border-slate-200">
    <div class="flex items-center h-12 px-4 gap-1">
        <a href="{{ route('tasks.index', array_merge(request()->except('view'), ['view' => 'financiar'])) }}"
           class="px-4 py-2.5 text-[13px] font-medium border-b-2 transition-colors {{ $currentView === 'financiar' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
            📊 {{ __('Financiar') }}
        </a>

        <a href="{{ route('tasks.index', array_merge(request()->except('view'), ['view' => 'de-facturat'])) }}"
           class="px-4 py-2.5 text-[13px] font-medium border-b-2 transition-colors {{ $currentView === 'de-facturat' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
            📄 {{ __('De facturat') }}
        </a>

        <a href="{{ route('tasks.index', array_merge(request()->except('view'), ['view' => 'incasat'])) }}"
           class="px-4 py-2.5 text-[13px] font-medium border-b-2 transition-colors {{ $currentView === 'incasat' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
            💰 {{ __('Incasat') }}
        </a>

        <a href="{{ route('tasks.index', array_merge(request()->except('view'), ['view' => 'boards'])) }}"
           class="px-4 py-2.5 text-[13px] font-medium border-b-2 transition-colors {{ $currentView === 'boards' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
            📋 {{ __('Boards') }}
        </a>

        <a href="{{ route('tasks.index', array_merge(request()->except('view'), ['view' => 'list'])) }}"
           class="px-4 py-2.5 text-[13px] font-medium border-b-2 transition-colors {{ $currentView === 'list' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
            📝 {{ __('List') }}
        </a>

        <button class="px-4 py-2.5 text-[13px] font-medium text-slate-400 hover:text-slate-600 border-b-2 border-transparent transition-colors">
            + {{ __('View') }}
        </button>
    </div>
</div>
