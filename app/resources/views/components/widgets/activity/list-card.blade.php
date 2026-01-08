@props([
    'title',
    'items',
    'emptyMessage' => __('No items'),
    'viewAllHref' => null,
])

<div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-100">
        <h3 class="text-base font-semibold text-slate-900">{{ $title }}</h3>
        @if($viewAllHref)
            <a href="{{ $viewAllHref }}" class="text-sm text-slate-600 hover:text-slate-900 font-medium transition-colors">{{ __('View all') }} →</a>
        @endif
    </div>
    <div class="p-6">
        @if($items && $items->count() > 0)
            <div class="space-y-3">
                {{ $slot }}
            </div>
        @else
            <p class="text-sm text-slate-500 text-center py-8">{{ $emptyMessage }}</p>
        @endif
    </div>
</div>
