{{-- Component class handles all logic - see App\View\Components\Dashboard\DomainWidget --}}
<div class="bg-white border border-slate-200 rounded-xl shadow-sm">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
        <h3 class="text-base font-semibold text-slate-900">{{ __('app.Domain Management') }}</h3>
        <a href="{{ route('domains.index') }}" class="text-sm text-slate-600 hover:text-slate-900 font-medium transition-colors">{{ __('View all') }} →</a>
    </div>
    <div class="p-6">

    @if($expiringDomains->count() > 0)
        <!-- Expiring Domains List -->
        <div class="space-y-2 mb-4">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-orange-700 uppercase tracking-wide">{{ __('app.Expiring Soon') }}</p>
                <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-md text-xs font-bold bg-orange-100 text-orange-700">
                    {{ $expiringDomains->count() }}
                </span>
            </div>

            @foreach($processedDomains as $item)
                <div class="flex items-center justify-between p-2 bg-orange-50 border border-orange-100 rounded-lg hover:bg-orange-100 transition-colors cursor-pointer"
                     onclick="window.location.href='{{ route('domains.edit', $item['domain']) }}'">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-slate-900 truncate">{{ $item['domain']->domain_name }}</p>
                        <p class="text-xs text-slate-500">{{ $item['domain']->expiry_date->format('d.m.Y') }}</p>
                    </div>
                    <div class="text-right flex-shrink-0 ml-2">
                        @if($item['isPast'])
                            <p class="text-xs font-semibold text-red-700">
                                {{ $item['daysText'] }} {{ $item['dayLabel'] }}
                            </p>
                        @else
                            <p class="text-xs font-semibold text-orange-700">
                                {{ $item['daysText'] }} {{ $item['dayLabel'] }}
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach

            @if($expiringDomains->count() > 3)
                <p class="text-xs text-slate-500 text-center pt-1">
                    +{{ $expiringDomains->count() - 3 }} {{ __('more') }}
                </p>
            @endif
        </div>

        <div class="border-t border-slate-200 pt-3 mb-3"></div>
    @endif

    <div class="space-y-2.5">
        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ __('app.Renewal Costs') }}</p>

        <div class="flex items-center justify-between">
            <span class="text-sm text-slate-600">30 {{ __('app.days') }}</span>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400">{{ $domainRenewals30Days['count'] }}</span>
                <span class="text-sm font-semibold text-slate-900">{{ number_format($domainRenewals30Days['cost'], 0) }} RON</span>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <span class="text-sm text-slate-600">60 {{ __('app.days') }}</span>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400">{{ $domainRenewals60Days['count'] }}</span>
                <span class="text-sm font-semibold text-slate-900">{{ number_format($domainRenewals60Days['cost'], 0) }} RON</span>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <span class="text-sm text-slate-600">90 {{ __('app.days') }}</span>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400">{{ $domainRenewals90Days['count'] }}</span>
                <span class="text-sm font-semibold text-slate-900">{{ number_format($domainRenewals90Days['cost'], 0) }} RON</span>
            </div>
        </div>
    </div>
    </div>
</div>
