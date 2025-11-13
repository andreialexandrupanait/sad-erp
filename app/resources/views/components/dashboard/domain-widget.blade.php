@props(['expiringDomains', 'domainRenewals30Days', 'domainRenewals60Days', 'domainRenewals90Days'])

<div class="bg-white border border-slate-200 rounded-xl shadow-sm hover:shadow-md transition-shadow p-5">
    <div class="flex items-start justify-between mb-4">
        <div class="flex-1">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">{{ __('app.Domain Management') }}</p>
        </div>
        <div class="flex-shrink-0 w-10 h-10 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
            </svg>
        </div>
    </div>

    @if($expiringDomains->count() > 0)
    <div class="flex items-center justify-between bg-orange-50 border border-orange-200 rounded-lg px-3 py-2 mb-4">
        <span class="text-sm font-medium text-orange-900">{{ __('app.Expiring Soon') }}</span>
        <span class="inline-flex items-center justify-center min-w-[24px] h-6 px-2 rounded-md text-xs font-bold bg-orange-600 text-white">
            {{ $expiringDomains->count() }}
        </span>
    </div>
    @endif

    <div class="space-y-2.5">
        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ __('app.Renewal Costs') }}</p>

        <div class="flex items-center justify-between">
            <span class="text-sm text-slate-600">30{{ __('app.days') }}</span>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400">{{ $domainRenewals30Days['count'] }}</span>
                <span class="text-sm font-semibold text-slate-900">{{ number_format($domainRenewals30Days['cost'], 0) }}</span>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <span class="text-sm text-slate-600">60{{ __('app.days') }}</span>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400">{{ $domainRenewals60Days['count'] }}</span>
                <span class="text-sm font-semibold text-slate-900">{{ number_format($domainRenewals60Days['cost'], 0) }}</span>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <span class="text-sm text-slate-600">90{{ __('app.days') }}</span>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400">{{ $domainRenewals90Days['count'] }}</span>
                <span class="text-sm font-semibold text-slate-900">{{ number_format($domainRenewals90Days['cost'], 0) }}</span>
            </div>
        </div>

        <a href="{{ route('domains.index') }}" class="inline-block text-xs font-medium text-purple-600 hover:text-purple-700 pt-1">
            {{ __('app.View all domains') }} →
        </a>
    </div>
</div>
