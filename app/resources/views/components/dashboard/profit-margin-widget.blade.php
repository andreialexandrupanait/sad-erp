{{-- Component class handles all logic - see App\View\Components\Dashboard\ProfitMarginWidget --}}
<div class="bg-white border border-slate-200 rounded-xl shadow-sm">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-100">
        <h3 class="text-base font-semibold text-slate-900">{{ __('app.Profit Margin') }}</h3>
        <a href="{{ route('financial.dashboard') }}" class="text-sm text-slate-600 hover:text-slate-900 font-medium transition-colors">{{ __('View all') }} →</a>
    </div>
    <div class="p-6">
    <div class="flex items-baseline justify-between gap-2 mb-4">
        <div class="flex items-baseline gap-2">
            <p class="text-3xl font-bold text-slate-900">{{ number_format($currentMonthProfitMargin, 1) }}%</p>
            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold {{ $colorClasses['badge'] }}">
                {{ $statusLabel }}
            </span>
        </div>
        <div class="flex-shrink-0 w-10 h-10 {{ $colorClasses['bg'] }} {{ $colorClasses['text'] }} rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
        </div>
    </div>

        <div class="pt-3 border-t border-slate-200 space-y-2">
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-500">{{ __('app.Current Month') }}</span>
                <span class="font-semibold text-slate-900">{{ number_format($currentMonthProfitMargin, 1) }}%</span>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-500">{{ __('app.Yearly Average') }}</span>
                <span class="font-semibold text-slate-900">{{ number_format($yearlyProfitMargin, 1) }}%</span>
            </div>
        </div>
    </div>
</div>
