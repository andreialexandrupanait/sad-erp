@props([
    'title',
    'amount',
    'yearlyAmount' => null,
    'type' => 'revenue', // revenue, expense, profit
    'href' => null,
])

@php
    $colors = match($type) {
        'revenue' => 'bg-gradient-to-br from-green-500 to-emerald-600',
        'expense' => 'bg-gradient-to-br from-red-500 to-rose-600',
        'profit' => 'bg-gradient-to-br from-slate-700 to-slate-900',
        default => 'bg-gradient-to-br from-blue-500 to-indigo-600',
    };

    $iconColor = match($type) {
        'revenue' => 'text-green-100',
        'expense' => 'text-red-100',
        'profit' => 'text-slate-300',
        default => 'text-blue-100',
    };

    $icon = match($type) {
        'revenue' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'expense' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
        'profit' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>',
        default => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    };
@endphp

<div
    class="{{ $colors }} text-white rounded-xl shadow-sm hover:shadow-lg transition-shadow cursor-pointer p-5"
    @if($href)
        onclick="window.location.href='{{ $href }}'"
    @endif
>
    <div class="flex items-start justify-between mb-3">
        <div>
            <p class="text-xs font-medium {{ $iconColor }} uppercase tracking-wide mb-1">{{ $title }}</p>
            <p class="text-2xl font-bold">{{ $amount }}</p>
        </div>
        <div class="flex-shrink-0 w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
            {!! $icon !!}
        </div>
    </div>
    @if($yearlyAmount)
        <p class="text-xs {{ $iconColor }}">Total anual: {{ $yearlyAmount }}</p>
    @endif
</div>
