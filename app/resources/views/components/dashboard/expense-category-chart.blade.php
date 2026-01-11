{{-- Component class handles all logic - see App\View\Components\Dashboard\ExpenseCategoryChart --}}
<div class="bg-white border border-slate-200 rounded-xl shadow-sm">
    <div class="flex items-center justify-between px-4 md:px-6 py-3 md:py-4 border-b border-slate-200 bg-slate-100">
        <h3 class="text-base font-semibold text-slate-900">{{ __('app.Expenses by Category') }}</h3>
        <a href="{{ route('financial.expenses.index') }}" class="text-sm text-slate-600 hover:text-slate-900 font-medium transition-colors">{{ __('app.View all') }} →</a>
    </div>
    <div class="p-4 md:p-6">

    @if($hasData)
        <!-- Top Categories with Progress Bars (Scrollable) -->
        <div class="space-y-3 max-h-64 overflow-y-auto pr-1 scrollbar-thin scrollbar-thumb-slate-300 scrollbar-track-slate-100">
            @foreach($processedCategories as $data)
                <div class="space-y-1">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-700 font-medium truncate flex-1 min-w-0 pr-2">
                            {{ $data['item']->category->label ?? __('app.Uncategorized') }}
                        </span>
                        <span class="text-slate-900 font-semibold flex-shrink-0">
                            {{ number_format($data['item']->total, 0) }} <span class="text-slate-500 font-normal">RON</span>
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500"
                                 style="width: {{ number_format($data['percentage'], 1) }}%; background-color: {{ $data['color'] }}"></div>
                        </div>
                        <span class="text-xs text-slate-500 font-medium w-10 text-right flex-shrink-0">{{ number_format($data['percentage'], 0) }}%</span>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="py-8 text-center">
            <p class="text-xs text-slate-400">{{ __('app.No data available') }}</p>
        </div>
    @endif
    </div>
</div>
