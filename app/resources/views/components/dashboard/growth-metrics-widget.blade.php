@props(['revenueGrowth', 'expenseGrowth', 'clientGrowth', 'newClientsThisMonth', 'newClientsLastMonth'])

<div class="bg-white border border-slate-200 rounded-xl shadow-sm">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-100">
        <h3 class="text-base font-semibold text-slate-900">{{ __('app.Month-over-Month Growth') }}</h3>
        <a href="{{ route('financial.dashboard') }}" class="text-sm text-slate-600 hover:text-slate-900 font-medium transition-colors">{{ __('View all') }} →</a>
    </div>
    <div class="p-6">

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
</div>
