<x-app-layout>
    <x-slot name="pageTitle">{{ $subscription->vendor_name }}</x-slot>

    <x-slot name="headerActions">
        <x-ui.button variant="default" onclick="window.location.href='{{ route('subscriptions.edit', $subscription) }}'">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            {{ __('Edit Subscription') }}
        </x-ui.button>
        <x-ui.button variant="outline" onclick="window.location.href='{{ route('subscriptions.index') }}'">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ __('Back') }}
        </x-ui.button>
    </x-slot>

    <div class="p-6 space-y-6">
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Subscription Overview Card -->
        <x-ui.card>
            <x-ui.card-content>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Price -->
                    <div>
                        <h3 class="text-sm font-medium text-slate-600">{{ __('Price') }}</h3>
                        <p class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format($subscription->price, 2) }} RON</p>
                        <p class="text-xs text-slate-500 mt-1">{{ ucfirst($subscription->billing_cycle) }}</p>
                    </div>

                    <!-- Status -->
                    <div>
                        <h3 class="text-sm font-medium text-slate-600">{{ __('Status') }}</h3>
                        <div class="mt-2">
                            @if($subscription->status === 'active')
                                <x-ui.badge variant="success">{{ __('Active') }}</x-ui.badge>
                            @elseif($subscription->status === 'paused')
                                <x-ui.badge variant="warning">{{ __('Paused') }}</x-ui.badge>
                            @else
                                <x-ui.badge variant="destructive">{{ __('Cancelled') }}</x-ui.badge>
                            @endif
                        </div>
                    </div>

                    <!-- Next Renewal (only for active subscriptions) -->
                    <div>
                        <h3 class="text-sm font-medium text-slate-600">{{ $subscription->status === 'active' ? __('Next Renewal') : __('Status') }}</h3>
                        @if($subscription->status === 'active')
                            <p class="mt-2 text-lg font-semibold text-slate-900">{{ $subscription->next_renewal_date->format('d M Y') }}</p>
                        @endif
                        <p class="mt-{{ $subscription->status === 'active' ? '1' : '2' }}">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $subscription->renewal_badge_color }}">
                                {{ $subscription->renewal_badge_emoji }} {{ $subscription->renewal_text }}
                            </span>
                        </p>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-slate-200 pt-6">
                    <!-- Subscription Details -->
                    <div>
                        <h3 class="text-sm font-medium text-slate-600 mb-3">{{ __('Subscription Details') }}</h3>
                        <dl class="space-y-2">
                            <div class="flex items-start">
                                <dt class="text-sm text-slate-600 w-32">{{ __('Vendor') }}:</dt>
                                <dd class="text-sm font-medium text-slate-900">{{ $subscription->vendor_name }}</dd>
                            </div>
                            <div class="flex items-start">
                                <dt class="text-sm text-slate-600 w-32">{{ __('Start Date') }}:</dt>
                                <dd class="text-sm text-slate-900">{{ $subscription->start_date->format('d M Y') }}</dd>
                            </div>
                            <div class="flex items-start">
                                <dt class="text-sm text-slate-600 w-32">{{ __('Billing Cycle') }}:</dt>
                                <dd class="text-sm text-slate-900">
                                    {{ ucfirst($subscription->billing_cycle) }}
                                    @if($subscription->billing_cycle === 'custom' && $subscription->custom_days)
                                        <span class="text-xs text-slate-500">({{ $subscription->custom_days }} {{ __('days') }})</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Cost Projections -->
                    <div>
                        <h3 class="text-sm font-medium text-slate-600 mb-3">{{ __('Cost Projections') }}</h3>
                        <dl class="space-y-2">
                            <div class="flex items-start">
                                <dt class="text-sm text-slate-600 w-40">{{ __('Monthly Cost') }}:</dt>
                                <dd class="text-sm font-semibold text-blue-600">
                                    @if($subscription->billing_cycle === 'lunar')
                                        {{ number_format($subscription->price, 2) }} RON
                                    @elseif($subscription->billing_cycle === 'anual')
                                        {{ number_format($subscription->price / 12, 2) }} RON
                                    @elseif($subscription->billing_cycle === 'custom' && $subscription->custom_days > 0)
                                        {{ number_format(($subscription->price / $subscription->custom_days) * 30, 2) }} RON
                                    @else
                                        {{ number_format($subscription->price, 2) }} RON
                                    @endif
                                </dd>
                            </div>
                            <div class="flex items-start">
                                <dt class="text-sm text-slate-600 w-40">{{ __('Annual Cost') }}:</dt>
                                <dd class="text-sm font-semibold text-purple-600">
                                    @if($subscription->billing_cycle === 'lunar')
                                        {{ number_format($subscription->price * 12, 2) }} RON
                                    @elseif($subscription->billing_cycle === 'anual')
                                        {{ number_format($subscription->price, 2) }} RON
                                    @elseif($subscription->billing_cycle === 'custom' && $subscription->custom_days > 0)
                                        {{ number_format(($subscription->price / $subscription->custom_days) * 365, 2) }} RON
                                    @else
                                        {{ number_format($subscription->price * 12, 2) }} RON
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    @if ($subscription->notes)
                        <div class="md:col-span-2">
                            <h3 class="text-sm font-medium text-slate-600 mb-2">{{ __('Notes') }}</h3>
                            <p class="text-sm text-slate-700 whitespace-pre-line bg-slate-50 p-3 rounded">{{ $subscription->notes }}</p>
                        </div>
                    @endif

                    <div class="md:col-span-2 pt-4 border-t border-slate-200">
                        <div class="flex justify-between text-xs text-slate-500">
                            <span>{{ __('Created') }}: {{ $subscription->created_at->format('d M Y H:i') }}</span>
                            <span>{{ __('Last Updated') }}: {{ $subscription->updated_at->format('d M Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </x-ui.card-content>
        </x-ui.card>

        <!-- Renewal Change History (Audit Log) -->
        @if($subscription->logs->isNotEmpty())
            <x-ui.card>
                <x-ui.card-content>
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Renewal Change History') }}</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <x-ui.table-head>{{ __('Date Changed') }}</x-ui.table-head>
                                    <x-ui.table-head>{{ __('Old Date') }}</x-ui.table-head>
                                    <x-ui.table-head>{{ __('New Date') }}</x-ui.table-head>
                                    <x-ui.table-head>{{ __('Reason') }}</x-ui.table-head>
                                    <x-ui.table-head>{{ __('Changed By') }}</x-ui.table-head>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                @foreach($subscription->logs as $log)
                                    <x-ui.table-row>
                                        <x-ui.table-cell>{{ $log->changed_at->format('d M Y H:i') }}</x-ui.table-cell>
                                        <x-ui.table-cell>{{ $log->old_renewal_date->format('d M Y') }}</x-ui.table-cell>
                                        <x-ui.table-cell>{{ $log->new_renewal_date->format('d M Y') }}</x-ui.table-cell>
                                        <x-ui.table-cell>{{ $log->change_reason }}</x-ui.table-cell>
                                        <x-ui.table-cell>
                                            @if($log->changedBy)
                                                {{ $log->changedBy->name }}
                                            @else
                                                <span class="text-slate-500 italic">{{ __('System') }}</span>
                                            @endif
                                        </x-ui.table-cell>
                                    </x-ui.table-row>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-ui.card-content>
            </x-ui.card>
        @endif
    </div>
</x-app-layout>
