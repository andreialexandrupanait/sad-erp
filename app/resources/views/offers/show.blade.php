<x-app-layout>
    <x-slot name="pageTitle">{{ $offer->offer_number }}</x-slot>

    <div class="p-6">
        {{-- Top Navigation Bar --}}
        <div class="flex items-center justify-between mb-6">
            {{-- Back Button & Title --}}
            <div class="flex items-center gap-4">
                <a href="{{ route('offers.index') }}"
                   class="inline-flex items-center justify-center w-10 h-10 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 hover:text-slate-900 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">{{ $offer->offer_number }}</h1>
                    @if($offer->title)
                        <p class="text-slate-500">{{ $offer->title }}</p>
                    @endif
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2">
                @if($offer->canBeEdited())
                    <a href="{{ route('offers.edit', $offer) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        {{ __('Edit') }}
                    </a>
                @endif

                @if($offer->canBeSent())
                    <form action="{{ route('offers.send', $offer) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            {{ __('Send to Client') }}
                        </button>
                    </form>
                @endif

                @if($offer->isAccepted() && !$offer->contract_id)
                    <form action="{{ route('offers.convert-to-contract', $offer) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            {{ __('Convert to Contract') }}
                        </button>
                    </form>
                @endif

                @if($offer->contract_id && $offer->contract && $offer->contract->isDraft())
                    <form action="{{ route('offers.regenerate-contract', $offer) }}" method="POST" class="inline"
                          onsubmit="return confirm('{{ __('This will regenerate the contract draft with the current offer data. Continue?') }}')">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-amber-700 bg-amber-50 border border-amber-300 rounded-lg hover:bg-amber-100 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            {{ __('Regenerate Contract') }}
                        </button>
                    </form>
                @endif

                {{-- PDF Download --}}
                <a href="{{ route('offers.pdf', $offer) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ __('PDF') }}
                </a>

                {{-- Resend (for sent offers) --}}
                @if($offer->status !== 'draft' && $offer->status !== 'accepted')
                    <form action="{{ route('offers.resend', $offer) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            {{ __('Resend') }}
                        </button>
                    </form>
                @endif

                <form action="{{ route('offers.duplicate', $offer) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        {{ __('Duplicate') }}
                    </button>
                </form>

                {{-- More Actions Dropdown --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="inline-flex items-center justify-center w-10 h-10 text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak
                         class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-slate-200 py-1 z-10">
                        <button onclick="navigator.clipboard.writeText('{{ $offer->public_url }}'); alert('{{ __('Link copied!') }}')"
                                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            {{ __('Copy Public Link') }}
                        </button>
                        <a href="{{ $offer->public_url }}" target="_blank"
                           class="w-full flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            {{ __('View Public Page') }}
                        </a>
                        <hr class="my-1 border-slate-200">
                        <form action="{{ route('offers.destroy', $offer) }}" method="POST"
                              onsubmit="return confirm('{{ __('Are you sure you want to delete this offer?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                {{ __('Delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Messages --}}
        @if (session('success'))
            <x-ui.alert variant="success" class="mb-6">{{ session('success') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert variant="destructive" class="mb-6">{{ session('error') }}</x-ui.alert>
        @endif

        {{-- Status Banner --}}
        @if($offer->status === 'accepted')
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-green-800">{{ __('Offer Accepted') }}</p>
                    <p class="text-sm text-green-600">{{ __('Accepted on') }} {{ $offer->accepted_at->format('d.m.Y H:i') }}</p>
                </div>
                @if($offer->contract)
                    <a href="{{ route('contracts.show', $offer->contract) }}" class="ml-auto inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        {{ __('View Contract') }}
                    </a>
                @endif
            </div>
        @elseif($offer->status === 'rejected')
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-red-800">{{ __('Offer Rejected') }}</p>
                        <p class="text-sm text-red-600">{{ __('Rejected on') }} {{ $offer->rejected_at->format('d.m.Y H:i') }}</p>
                    </div>
                </div>
                @if($offer->rejection_reason)
                    <div class="mt-3 pt-3 border-t border-red-200">
                        <p class="text-sm text-red-700"><strong>{{ __('Reason:') }}</strong> {{ $offer->rejection_reason }}</p>
                    </div>
                @endif
            </div>
        @elseif($offer->isExpired())
            <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-amber-800">{{ __('Offer Expired') }}</p>
                    <p class="text-sm text-amber-600">{{ __('Expired on') }} {{ $offer->valid_until->format('d.m.Y') }}</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Quick Stats --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-white rounded-xl border border-slate-200 p-4">
                        <div class="text-sm text-slate-500 mb-1">{{ __('Status') }}</div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-semibold
                            @switch($offer->status)
                                @case('draft') bg-slate-100 text-slate-700 @break
                                @case('sent') bg-blue-100 text-blue-700 @break
                                @case('viewed') bg-purple-100 text-purple-700 @break
                                @case('accepted') bg-green-100 text-green-700 @break
                                @case('rejected') bg-red-100 text-red-700 @break
                                @case('expired') bg-amber-100 text-amber-700 @break
                            @endswitch">
                            {{ $offer->status_label }}
                        </span>
                    </div>
                    <div class="bg-white rounded-xl border border-slate-200 p-4">
                        <div class="text-sm text-slate-500 mb-1">{{ __('Total') }}</div>
                        <div class="text-xl font-bold text-slate-900">{{ number_format($offer->total, 2) }} <span class="text-sm font-normal text-slate-500">{{ $offer->currency }}</span></div>
                    </div>
                    <div class="bg-white rounded-xl border border-slate-200 p-4">
                        <div class="text-sm text-slate-500 mb-1">{{ __('Items') }}</div>
                        <div class="text-xl font-bold text-slate-900">{{ $offer->items->where('is_selected', '!=', false)->count() }}</div>
                    </div>
                    <div class="bg-white rounded-xl border border-slate-200 p-4">
                        <div class="text-sm text-slate-500 mb-1">{{ __('Valid Until') }}</div>
                        <div class="text-lg font-semibold {{ $offer->valid_until < now() ? 'text-red-600' : 'text-slate-900' }}">
                            {{ $offer->valid_until->format('d.m.Y') }}
                        </div>
                    </div>
                </div>

                {{-- Items Table --}}
                @php
                    // Only show selected items (is_selected is true or null for backwards compatibility)
                    $selectedItems = $offer->items->filter(fn($item) => $item->is_selected !== false);
                    $hasDiscounts = $selectedItems->where('discount_percent', '>', 0)->count() > 0;
                @endphp
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                        <h2 class="font-semibold text-slate-900">{{ __('Offer Items') }}</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-3 text-left font-medium text-slate-600">{{ __('Item') }}</th>
                                    <th class="px-4 py-3 text-center font-medium text-slate-600 w-24">{{ __('Qty') }}</th>
                                    <th class="px-4 py-3 text-right font-medium text-slate-600 w-32">{{ __('Unit Price') }}</th>
                                    @if($hasDiscounts)
                                        <th class="px-4 py-3 text-center font-medium text-slate-600 w-24">{{ __('Discount') }}</th>
                                    @endif
                                    <th class="px-6 py-3 text-right font-medium text-slate-600 w-32">{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($selectedItems as $item)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-4">
                                            <span class="font-medium text-slate-900">{{ $item->title }}</span>
                                            @if($item->description)
                                                <div class="text-sm text-slate-500 mt-1 line-clamp-2">{{ Str::limit($item->description, 150) }}</div>
                                            @endif
                                            @if($item->is_recurring)
                                                <span class="inline-flex items-center mt-2 px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                    </svg>
                                                    {{ $item->billing_cycle_label }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-center text-slate-600">
                                            {{ number_format($item->quantity, $item->quantity == floor($item->quantity) ? 0 : 2) }}
                                            <span class="text-slate-400">{{ $item->unit }}</span>
                                        </td>
                                        <td class="px-4 py-4 text-right text-slate-600">{{ number_format($item->unit_price, 2) }}</td>
                                        @if($hasDiscounts)
                                            <td class="px-4 py-4 text-center">
                                                @if($item->discount_percent > 0)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">
                                                        -{{ number_format($item->discount_percent, 0) }}%
                                                    </span>
                                                @else
                                                    <span class="text-slate-300">-</span>
                                                @endif
                                            </td>
                                        @endif
                                        <td class="px-6 py-4 text-right font-semibold text-slate-900">{{ number_format($item->total_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Totals --}}
                    <div class="border-t border-slate-200 bg-slate-50">
                        <div class="px-6 py-3 flex justify-end">
                            <div class="w-64 space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-600">{{ __('Subtotal') }}</span>
                                    <span class="font-medium text-slate-900">{{ number_format($offer->subtotal, 2) }} {{ $offer->currency }}</span>
                                </div>
                                @if($offer->discount_amount > 0)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-600">
                                            {{ __('Discount') }}
                                            @if($offer->discount_percent)
                                                <span class="text-green-600">({{ number_format($offer->discount_percent, 0) }}%)</span>
                                            @endif
                                        </span>
                                        <span class="font-medium text-green-600">-{{ number_format($offer->discount_amount, 2) }} {{ $offer->currency }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between pt-2 border-t border-slate-200">
                                    <span class="font-semibold text-slate-900">{{ __('Total') }}</span>
                                    <span class="text-xl font-bold text-slate-900">{{ number_format($offer->total, 2) }} {{ $offer->currency }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Introduction & Terms --}}
                @if($offer->introduction || $offer->terms)
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                            <h2 class="font-semibold text-slate-900">{{ __('Additional Information') }}</h2>
                        </div>
                        <div class="p-6 space-y-6">
                            @if($offer->introduction)
                                <div>
                                    <h3 class="text-sm font-medium text-slate-500 mb-2">{{ __('Introduction') }}</h3>
                                    <div class="prose prose-sm max-w-none text-slate-700">
                                        {!! $offer->introduction !!}
                                    </div>
                                </div>
                            @endif
                            @if($offer->terms)
                                <div>
                                    <h3 class="text-sm font-medium text-slate-500 mb-2">{{ __('Terms & Conditions') }}</h3>
                                    <div class="prose prose-sm max-w-none text-slate-700">
                                        {!! $offer->terms !!}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Activity Timeline --}}
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                        <h2 class="font-semibold text-slate-900">{{ __('Activity Timeline') }}</h2>
                    </div>
                    <div class="p-6 max-h-96 overflow-y-auto">
                        @if($offer->activities->count() > 0)
                            <div class="relative">
                                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-slate-200"></div>
                                <div class="space-y-6">
                                    @foreach($offer->activities as $activity)
                                        <div class="relative flex gap-4 pl-10">
                                            <div class="absolute left-0 w-8 h-8 rounded-full flex items-center justify-center
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
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm text-slate-900">{{ $activity->description }}</p>
                                                <p class="text-xs text-slate-500 mt-1">
                                                    {{ $activity->created_at->format('d.m.Y H:i') }}
                                                    <span class="text-slate-300 mx-1">&bull;</span>
                                                    {{ $activity->created_at->diffForHumans() }}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="mt-2 text-sm text-slate-500">{{ __('No activity yet.') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Client Card --}}
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden" x-data="{ editingClient: false }">
                    <div class="px-5 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                        <h3 class="font-semibold text-slate-900">{{ __('Client') }}</h3>
                        <div class="flex items-center gap-2">
                            @if($offer->hasTemporaryClient())
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700">
                                    {{ __('New') }}
                                </span>
                            @endif
                            @if($offer->temp_client_name)
                                <button @click="editingClient = true" type="button"
                                        class="inline-flex items-center justify-center w-7 h-7 rounded-md text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"
                                        title="{{ __('Edit client details') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="p-5">
                        @if($offer->client)
                            @php
                                // Use temp overrides if they exist, otherwise fall back to client data
                                $displayName = $offer->temp_client_name ?: $offer->client->name;
                                $displayCompany = $offer->temp_client_company ?: ($offer->client->company_name ?? null);
                                $displayContactPerson = $offer->client->contact_person;
                                $displayEmail = $offer->temp_client_email ?: $offer->client->email;
                                $displayPhone = $offer->temp_client_phone ?: $offer->client->phone;
                                $displayAddress = $offer->temp_client_address ?: $offer->client->address;
                                $displayTaxId = $offer->temp_client_tax_id ?: $offer->client->tax_id;
                                $displayRegNumber = $offer->temp_client_registration_number ?: $offer->client->registration_number;
                                $displayBankAccount = $offer->temp_client_bank_account ?: ($offer->client->bank_account ?? null);
                                $displayBankName = $offer->client->bank_name ?? null;
                            @endphp
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 font-semibold">
                                    {{ strtoupper(substr($displayName, 0, 2)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('clients.show', $offer->client) }}" class="font-semibold text-slate-900 hover:text-blue-600">
                                        {{ $displayName }}
                                    </a>
                                    @if($displayCompany && $displayCompany !== $displayName)
                                        <p class="text-sm text-slate-500">{{ $displayCompany }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-4 space-y-2">
                                @if($displayContactPerson)
                                    <div class="flex items-center gap-2 text-sm text-slate-600">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        <span>{{ $displayContactPerson }}</span>
                                    </div>
                                @endif
                                @if($displayEmail)
                                    <a href="mailto:{{ $displayEmail }}" class="flex items-center gap-2 text-sm text-slate-600 hover:text-blue-600">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $displayEmail }}
                                    </a>
                                @endif
                                @if($displayPhone)
                                    <a href="tel:{{ $displayPhone }}" class="flex items-center gap-2 text-sm text-slate-600 hover:text-blue-600">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        {{ $displayPhone }}
                                    </a>
                                @endif
                                @if($displayAddress)
                                    <div class="flex items-start gap-2 text-sm text-slate-600">
                                        <svg class="w-4 h-4 text-slate-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span>{{ $displayAddress }}</span>
                                    </div>
                                @endif
                                @if($displayTaxId)
                                    <div class="flex items-center gap-2 text-sm text-slate-600">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span>{{ __('CUI:') }} {{ $displayTaxId }}</span>
                                    </div>
                                @endif
                                @if($displayRegNumber)
                                    <div class="flex items-center gap-2 text-sm text-slate-600">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        <span>{{ __('Reg. Com.:') }} {{ $displayRegNumber }}</span>
                                    </div>
                                @endif
                                @if($displayBankAccount)
                                    <div class="flex items-center gap-2 text-sm text-slate-600">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                        <span>{{ __('IBAN:') }} {{ $displayBankAccount }}@if($displayBankName) ({{ $displayBankName }})@endif</span>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-slate-900">{{ $offer->temp_client_name }}</p>
                                    @if($offer->temp_client_company)
                                        <p class="text-sm text-slate-500">{{ $offer->temp_client_company }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-4 space-y-2">
                                @if($offer->temp_client_email)
                                    <a href="mailto:{{ $offer->temp_client_email }}" class="flex items-center gap-2 text-sm text-slate-600 hover:text-blue-600">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $offer->temp_client_email }}
                                    </a>
                                @endif
                                @if($offer->temp_client_phone)
                                    <a href="tel:{{ $offer->temp_client_phone }}" class="flex items-center gap-2 text-sm text-slate-600 hover:text-blue-600">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        {{ $offer->temp_client_phone }}
                                    </a>
                                @endif
                                @if($offer->temp_client_address)
                                    <div class="flex items-start gap-2 text-sm text-slate-600">
                                        <svg class="w-4 h-4 text-slate-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span>{{ $offer->temp_client_address }}</span>
                                    </div>
                                @endif
                                @if($offer->temp_client_tax_id)
                                    <div class="flex items-center gap-2 text-sm text-slate-600">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span>{{ __('CUI:') }} {{ $offer->temp_client_tax_id }}</span>
                                    </div>
                                @endif
                                @if($offer->temp_client_registration_number)
                                    <div class="flex items-center gap-2 text-sm text-slate-600">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        <span>{{ __('Reg. Com.:') }} {{ $offer->temp_client_registration_number }}</span>
                                    </div>
                                @endif
                                @if($offer->temp_client_bank_account)
                                    <div class="flex items-center gap-2 text-sm text-slate-600">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                        <span>{{ __('IBAN:') }} {{ $offer->temp_client_bank_account }}</span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Edit Temp Client Modal --}}
                    @if($offer->temp_client_name)
                        <div x-show="editingClient" x-cloak
                             class="fixed inset-0 z-50 overflow-y-auto"
                             x-transition:enter="ease-out duration-300"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0">
                            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                                <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" @click="editingClient = false"></div>

                                <div class="relative bg-white rounded-xl shadow-xl transform transition-all sm:max-w-lg sm:w-full mx-auto"
                                     x-transition:enter="ease-out duration-300"
                                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                     x-transition:leave="ease-in duration-200"
                                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                     @click.stop>
                                    <form action="{{ route('offers.update-temp-client', $offer) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 rounded-t-xl">
                                            <h3 class="text-lg font-semibold text-slate-900">{{ __('Edit Client Details') }}</h3>
                                        </div>
                                        <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto">
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Name') }} <span class="text-red-500">*</span></label>
                                                <input type="text" name="temp_client_name" value="{{ $offer->temp_client_name }}" required
                                                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Company') }}</label>
                                                <input type="text" name="temp_client_company" value="{{ $offer->temp_client_company }}"
                                                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Email') }}</label>
                                                    <input type="email" name="temp_client_email" value="{{ $offer->temp_client_email }}"
                                                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Phone') }}</label>
                                                    <input type="text" name="temp_client_phone" value="{{ $offer->temp_client_phone }}"
                                                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Address') }}</label>
                                                <textarea name="temp_client_address" rows="2"
                                                          class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ $offer->temp_client_address }}</textarea>
                                            </div>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('CUI') }}</label>
                                                    <input type="text" name="temp_client_tax_id" value="{{ $offer->temp_client_tax_id }}"
                                                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Reg. Com.') }}</label>
                                                    <input type="text" name="temp_client_registration_number" value="{{ $offer->temp_client_registration_number }}"
                                                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Bank Account (IBAN)') }}</label>
                                                <input type="text" name="temp_client_bank_account" value="{{ $offer->temp_client_bank_account }}"
                                                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                        </div>
                                        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 rounded-b-xl flex justify-end gap-3">
                                            <button type="button" @click="editingClient = false"
                                                    class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                                                {{ __('Cancel') }}
                                            </button>
                                            <button type="submit"
                                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                                                {{ __('Save Changes') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Details Card --}}
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200 bg-slate-50">
                        <h3 class="font-semibold text-slate-900">{{ __('Details') }}</h3>
                    </div>
                    <div class="p-5">
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-slate-500">{{ __('Created') }}</dt>
                                <dd class="text-slate-900 font-medium">{{ $offer->created_at->format('d.m.Y') }}</dd>
                            </div>
                            @if($offer->sent_at)
                                <div class="flex justify-between">
                                    <dt class="text-slate-500">{{ __('Sent') }}</dt>
                                    <dd class="text-blue-600 font-medium">{{ $offer->sent_at->format('d.m.Y H:i') }}</dd>
                                </div>
                            @endif
                            @if($offer->viewed_at)
                                <div class="flex justify-between">
                                    <dt class="text-slate-500">{{ __('Viewed') }}</dt>
                                    <dd class="text-purple-600 font-medium">{{ $offer->viewed_at->format('d.m.Y H:i') }}</dd>
                                </div>
                            @endif
                            <div class="pt-3 border-t border-slate-100">
                                <dt class="text-slate-500 mb-1">{{ __('Created By') }}</dt>
                                <dd class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-xs font-medium text-slate-600">
                                        {{ strtoupper(substr($offer->creator->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <span class="text-slate-900">{{ $offer->creator->name ?? '-' }}</span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- Public Link Card - Always visible --}}
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200 bg-slate-50">
                        <h3 class="font-semibold text-slate-900">{{ __('Public Link') }}</h3>
                    </div>
                    <div class="p-5">
                        <p class="text-sm text-slate-500 mb-3">{{ __('Share this link with your client to view the offer:') }}</p>
                        <div class="flex gap-2">
                            <input type="text" readonly value="{{ $offer->public_url }}"
                                   class="flex-1 text-sm border border-slate-200 rounded-lg px-3 py-2 bg-slate-50 text-slate-600 truncate">
                            <button onclick="navigator.clipboard.writeText('{{ $offer->public_url }}'); this.innerHTML='<svg class=\'w-4 h-4 text-green-600\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M5 13l4 4L19 7\'/></svg>'; setTimeout(() => this.innerHTML='<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z\'/></svg>', 2000)"
                                    class="px-3 py-2 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors"
                                    title="{{ __('Copy link') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </button>
                        </div>
                        <a href="{{ $offer->public_url }}" target="_blank" class="mt-3 inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            {{ __('Open in new tab') }}
                        </a>
                    </div>
                </div>

                {{-- Internal Notes --}}
                @if($offer->notes)
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-200 bg-slate-50">
                            <h3 class="font-semibold text-slate-900">{{ __('Internal Notes') }}</h3>
                        </div>
                        <div class="p-5">
                            <p class="text-sm text-slate-600 whitespace-pre-line">{{ $offer->notes }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
