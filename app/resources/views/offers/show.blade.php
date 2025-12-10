<x-app-layout>
    <x-slot name="pageTitle">{{ $offer->offer_number }}</x-slot>

    <x-slot name="headerActions">
        <div class="flex items-center gap-2">
            @if($offer->canBeEdited())
                <x-ui.button variant="outline" onclick="window.location.href='{{ route('offers.edit', $offer) }}'">
                    <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    {{ __('Edit') }}
                </x-ui.button>
            @endif

            @if($offer->canBeSent())
                <form action="{{ route('offers.send', $offer) }}" method="POST" class="inline">
                    @csrf
                    <x-ui.button variant="default" type="submit">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        {{ __('Send to Client') }}
                    </x-ui.button>
                </form>
            @endif

            @if($offer->isAccepted() && !$offer->contract_id)
                <form action="{{ route('offers.convert-to-contract', $offer) }}" method="POST" class="inline">
                    @csrf
                    <x-ui.button variant="default" type="submit">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        {{ __('Convert to Contract') }}
                    </x-ui.button>
                </form>
            @endif

            <form action="{{ route('offers.duplicate', $offer) }}" method="POST" class="inline">
                @csrf
                <x-ui.button variant="outline" type="submit">
                    <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    {{ __('Duplicate') }}
                </x-ui.button>
            </form>
        </div>
    </x-slot>

    <div class="p-6 space-y-6">
        {{-- Messages --}}
        @if (session('success'))
            <x-ui.alert variant="success">{{ session('success') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert variant="destructive">{{ session('error') }}</x-ui.alert>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Offer Details --}}
                <x-ui.card>
                    <x-ui.card-header>
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold">{{ $offer->title }}</h2>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @switch($offer->status)
                                    @case('draft') bg-slate-100 text-slate-700 @break
                                    @case('sent') bg-blue-100 text-blue-700 @break
                                    @case('viewed') bg-purple-100 text-purple-700 @break
                                    @case('accepted') bg-green-100 text-green-700 @break
                                    @case('rejected') bg-red-100 text-red-700 @break
                                    @case('expired') bg-yellow-100 text-yellow-700 @break
                                @endswitch">
                                {{ $offer->status_label }}
                            </span>
                        </div>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        @if($offer->introduction)
                            <div class="prose prose-sm max-w-none mb-6">
                                {!! $offer->introduction !!}
                            </div>
                        @endif

                        {{-- Items Table --}}
                        <div class="border rounded-lg overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-100">
                                    <tr>
                                        <th class="px-6 py-4 text-left font-medium text-slate-600">{{ __('Item') }}</th>
                                        <th class="px-6 py-4 text-center font-medium text-slate-600">{{ __('Qty') }}</th>
                                        <th class="px-6 py-4 text-right font-medium text-slate-600">{{ __('Unit Price') }}</th>
                                        <th class="px-6 py-4 text-right font-medium text-slate-600">{{ __('Total') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @foreach($offer->items as $item)
                                        <tr>
                                            <td class="px-6 py-4">
                                                <div class="font-medium text-slate-900">{{ $item->title }}</div>
                                                @if($item->description)
                                                    <div class="text-sm text-slate-500">{{ $item->description }}</div>
                                                @endif
                                                @if($item->is_recurring)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                                                        {{ $item->billing_cycle_label }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-center">{{ number_format($item->quantity, 2) }} {{ $item->unit_label }}</td>
                                            <td class="px-6 py-4 text-right">{{ number_format($item->unit_price, 2) }} {{ $offer->currency }}</td>
                                            <td class="px-6 py-4 text-right font-medium">{{ number_format($item->total_price, 2) }} {{ $offer->currency }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-slate-50">
                                    <tr>
                                        <td colspan="3" class="px-4 py-2 text-right font-medium text-slate-600">{{ __('Subtotal') }}</td>
                                        <td class="px-4 py-2 text-right font-medium">{{ number_format($offer->subtotal, 2) }} {{ $offer->currency }}</td>
                                    </tr>
                                    @if($offer->discount_amount > 0)
                                        <tr>
                                            <td colspan="3" class="px-4 py-2 text-right font-medium text-slate-600">
                                                {{ __('Discount') }}
                                                @if($offer->discount_percent)
                                                    ({{ number_format($offer->discount_percent, 1) }}%)
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-right font-medium text-red-600">-{{ number_format($offer->discount_amount, 2) }} {{ $offer->currency }}</td>
                                        </tr>
                                    @endif
                                    <tr class="text-lg">
                                        <td colspan="3" class="px-4 py-3 text-right font-bold text-slate-900">{{ __('Total') }}</td>
                                        <td class="px-6 py-4 text-right font-bold text-slate-900">{{ number_format($offer->total, 2) }} {{ $offer->currency }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        @if($offer->terms)
                            <div class="prose prose-sm max-w-none mt-6 pt-6 border-t">
                                {!! $offer->terms !!}
                            </div>
                        @endif
                    </x-ui.card-content>
                </x-ui.card>

                {{-- Activity Log --}}
                <x-ui.card>
                    <x-ui.card-header>
                        <h3 class="font-semibold">{{ __('Activity') }}</h3>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <div class="space-y-4">
                            @forelse($offer->activities as $activity)
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center
                                        @switch($activity->action)
                                            @case('created') bg-slate-100 text-slate-600 @break
                                            @case('sent') bg-blue-100 text-blue-600 @break
                                            @case('viewed') bg-purple-100 text-purple-600 @break
                                            @case('accepted') bg-green-100 text-green-600 @break
                                            @case('rejected') bg-red-100 text-red-600 @break
                                            @default bg-slate-100 text-slate-600
                                        @endswitch">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @switch($activity->action)
                                                @case('created')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                @break
                                                @case('sent')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                                @break
                                                @case('viewed')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                @break
                                                @case('accepted')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                @break
                                                @case('rejected')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                @break
                                                @default
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            @endswitch
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-sm text-slate-900">{{ $activity->description }}</div>
                                        <div class="text-xs text-slate-500">{{ $activity->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">{{ __('No activity yet.') }}</p>
                            @endforelse
                        </div>
                    </x-ui.card-content>
                </x-ui.card>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Client Info --}}
                <x-ui.card>
                    <x-ui.card-header>
                        <h3 class="font-semibold">{{ __('Client') }}</h3>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <div class="space-y-3">
                            <div>
                                <a href="{{ route('clients.show', $offer->client) }}" class="font-medium text-slate-900 hover:text-blue-600">
                                    {{ $offer->client->name }}
                                </a>
                                @if($offer->client->company_name)
                                    <div class="text-sm text-slate-500">{{ $offer->client->company_name }}</div>
                                @endif
                            </div>
                            @if($offer->client->email)
                                <div class="text-sm">
                                    <span class="text-slate-500">{{ __('Email:') }}</span>
                                    <a href="mailto:{{ $offer->client->email }}" class="text-blue-600 hover:underline">{{ $offer->client->email }}</a>
                                </div>
                            @endif
                            @if($offer->client->phone)
                                <div class="text-sm">
                                    <span class="text-slate-500">{{ __('Phone:') }}</span>
                                    <a href="tel:{{ $offer->client->phone }}" class="text-blue-600 hover:underline">{{ $offer->client->phone }}</a>
                                </div>
                            @endif
                        </div>
                    </x-ui.card-content>
                </x-ui.card>

                {{-- Offer Info --}}
                <x-ui.card>
                    <x-ui.card-header>
                        <h3 class="font-semibold">{{ __('Details') }}</h3>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-slate-500">{{ __('Valid Until') }}</dt>
                                <dd class="font-medium {{ $offer->valid_until < now() ? 'text-red-600' : 'text-slate-900' }}">
                                    {{ $offer->valid_until->format('d.m.Y') }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500">{{ __('Created') }}</dt>
                                <dd class="text-slate-900">{{ $offer->created_at->format('d.m.Y H:i') }}</dd>
                            </div>
                            @if($offer->sent_at)
                                <div class="flex justify-between">
                                    <dt class="text-slate-500">{{ __('Sent') }}</dt>
                                    <dd class="text-slate-900">{{ $offer->sent_at->format('d.m.Y H:i') }}</dd>
                                </div>
                            @endif
                            @if($offer->viewed_at)
                                <div class="flex justify-between">
                                    <dt class="text-slate-500">{{ __('Viewed') }}</dt>
                                    <dd class="text-slate-900">{{ $offer->viewed_at->format('d.m.Y H:i') }}</dd>
                                </div>
                            @endif
                            @if($offer->accepted_at)
                                <div class="flex justify-between">
                                    <dt class="text-slate-500">{{ __('Accepted') }}</dt>
                                    <dd class="text-green-600">{{ $offer->accepted_at->format('d.m.Y H:i') }}</dd>
                                </div>
                            @endif
                            @if($offer->rejected_at)
                                <div class="flex justify-between">
                                    <dt class="text-slate-500">{{ __('Rejected') }}</dt>
                                    <dd class="text-red-600">{{ $offer->rejected_at->format('d.m.Y H:i') }}</dd>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <dt class="text-slate-500">{{ __('Created By') }}</dt>
                                <dd class="text-slate-900">{{ $offer->creator->name ?? '-' }}</dd>
                            </div>
                        </dl>
                    </x-ui.card-content>
                </x-ui.card>

                {{-- Public Link --}}
                @if($offer->status !== 'draft')
                    <x-ui.card>
                        <x-ui.card-header>
                            <h3 class="font-semibold">{{ __('Public Link') }}</h3>
                        </x-ui.card-header>
                        <x-ui.card-content>
                            <div class="space-y-3">
                                <p class="text-sm text-slate-500">{{ __('Share this link with your client:') }}</p>
                                <div class="flex gap-2">
                                    <input type="text" readonly value="{{ $offer->public_url }}"
                                           class="flex-1 text-sm border rounded px-3 py-2 bg-slate-50">
                                    <button onclick="navigator.clipboard.writeText('{{ $offer->public_url }}'); alert('{{ __('Link copied!') }}')"
                                            class="px-3 py-2 border rounded hover:bg-slate-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </x-ui.card-content>
                    </x-ui.card>
                @endif

                {{-- Contract Link --}}
                @if($offer->contract)
                    <x-ui.card>
                        <x-ui.card-header>
                            <h3 class="font-semibold">{{ __('Contract') }}</h3>
                        </x-ui.card-header>
                        <x-ui.card-content>
                            <a href="{{ route('contracts.show', $offer->contract) }}" class="flex items-center gap-2 text-blue-600 hover:text-blue-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                {{ $offer->contract->contract_number }}
                            </a>
                        </x-ui.card-content>
                    </x-ui.card>
                @endif

                {{-- Notes --}}
                @if($offer->notes)
                    <x-ui.card>
                        <x-ui.card-header>
                            <h3 class="font-semibold">{{ __('Internal Notes') }}</h3>
                        </x-ui.card-header>
                        <x-ui.card-content>
                            <p class="text-sm text-slate-600">{{ $offer->notes }}</p>
                        </x-ui.card-content>
                    </x-ui.card>
                @endif

                {{-- Rejection Reason --}}
                @if($offer->rejection_reason)
                    <x-ui.card class="border-red-200 bg-red-50">
                        <x-ui.card-header>
                            <h3 class="font-semibold text-red-800">{{ __('Rejection Reason') }}</h3>
                        </x-ui.card-header>
                        <x-ui.card-content>
                            <p class="text-sm text-red-700">{{ $offer->rejection_reason }}</p>
                        </x-ui.card-content>
                    </x-ui.card>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
