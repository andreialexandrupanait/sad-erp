@props(['revenueGrowth', 'expenseGrowth', 'clientGrowth', 'newClientsThisMonth', 'newClientsLastMonth'])

<div class="bg-white border border-slate-200 rounded-xl shadow-sm hover:shadow-md transition-shadow p-5">
    <div class="flex items-start justify-between mb-4">
        <div class="flex-1">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">{{ __('app.Month-over-Month Growth') }}</p>
        </div>
        <div class="flex-shrink-0 w-10 h-10 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
            </svg>
        </div>
    </div>

    <div class="space-y-3">
        {{-- Revenue Growth --}}
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-slate-600">{{ __('app.Revenue') }}</span>
            <div class="flex items-center gap-1">
                @if($revenueGrowth > 0)
                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm font-bold text-green-600">{{ number_format(abs($revenueGrowth), 1) }}%</span>
                @elseif($revenueGrowth < 0)
                    <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M14.707 12.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm font-bold text-red-600">{{ number_format(abs($revenueGrowth), 1) }}%</span>
                @else
                    <span class="text-sm font-bold text-slate-600">0%</span>
                @endif
            </div>
        </div>

        {{-- Expense Growth --}}
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-slate-600">{{ __('app.Expenses') }}</span>
            <div class="flex items-center gap-1">
                @if($expenseGrowth > 0)
                    <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm font-bold text-red-600">{{ number_format(abs($expenseGrowth), 1) }}%</span>
                @elseif($expenseGrowth < 0)
                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M14.707 12.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm font-bold text-green-600">{{ number_format(abs($expenseGrowth), 1) }}%</span>
                @else
                    <span class="text-sm font-bold text-slate-600">0%</span>
                @endif
            </div>
        </div>

        {{-- Client Growth --}}
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-slate-600">{{ __('app.New Clients') }}</span>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400">{{ $newClientsThisMonth }}</span>
                @if($clientGrowth > 0)
                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm font-bold text-green-600">{{ number_format(abs($clientGrowth), 0) }}%</span>
                @elseif($clientGrowth < 0)
                    <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M14.707 12.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm font-bold text-red-600">{{ number_format(abs($clientGrowth), 0) }}%</span>
                @else
                    <span class="text-sm font-bold text-slate-600">0%</span>
                @endif
            </div>
        </div>
    </div>
</div>
