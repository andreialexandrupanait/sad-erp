@props(['monthlySubscriptionCost', 'annualProjectedCost', 'activeSubscriptionsCount', 'pausedSubscriptionsCount', 'cancelledSubscriptionsCount'])

<div class="bg-white border border-slate-200 rounded-xl shadow-sm hover:shadow-md transition-shadow p-5">
    <div class="flex items-start justify-between mb-4">
        <div class="flex-1">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">{{ __('app.Subscription Costs') }}</p>
        </div>
        <div class="flex-shrink-0 w-10 h-10 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
        </div>
    </div>

    <div class="mb-4">
        <p class="text-3xl font-bold text-slate-900">{{ number_format($monthlySubscriptionCost, 0) }}</p>
        <p class="text-xs text-slate-500 mt-1">RON/{{ __('app.month') }}</p>
    </div>

    <div class="pt-3 border-t border-slate-200 space-y-2">
        <div class="flex items-center justify-between text-xs">
            <span class="text-slate-500">{{ __('app.Annual Projected') }}</span>
            <span class="font-semibold text-slate-900">{{ number_format($annualProjectedCost, 0) }}</span>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <div class="flex items-center gap-1.5">
                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                <span class="text-xs font-medium text-slate-600">{{ $activeSubscriptionsCount }}</span>
            </div>
            @if($pausedSubscriptionsCount > 0)
            <div class="flex items-center gap-1.5">
                <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                <span class="text-xs font-medium text-slate-600">{{ $pausedSubscriptionsCount }}</span>
            </div>
            @endif
            @if($cancelledSubscriptionsCount > 0)
            <div class="flex items-center gap-1.5">
                <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                <span class="text-xs font-medium text-slate-600">{{ $cancelledSubscriptionsCount }}</span>
            </div>
            @endif
        </div>

        <a href="{{ route('subscriptions.index') }}" class="inline-block text-xs font-medium text-indigo-600 hover:text-indigo-700 pt-1">
            {{ __('app.View all subscriptions') }} →
        </a>
    </div>
</div>
