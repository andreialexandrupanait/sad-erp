<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $offer->offer_number }} - {{ $offer->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen">
    <div class="max-w-4xl mx-auto py-8 px-4" x-data="publicOffer()">
        {{-- Header --}}
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">{{ $offer->title }}</h1>
                    <p class="text-slate-500 mt-1">{{ __('Offer') }} #{{ $offer->offer_number }}</p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    @switch($offer->status)
                        @case('sent') @case('viewed') bg-blue-100 text-blue-700 @break
                        @case('accepted') bg-green-100 text-green-700 @break
                        @case('rejected') bg-red-100 text-red-700 @break
                        @case('expired') bg-yellow-100 text-yellow-700 @break
                        @default bg-slate-100 text-slate-700
                    @endswitch">
                    {{ $offer->status_label }}
                </span>
            </div>
        </div>

        {{-- Messages --}}
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4 mb-6">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 mb-6">
                {{ session('error') }}
            </div>
        @endif

        {{-- Offer Content --}}
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            {{-- Client Info --}}
            <div class="border-b pb-4 mb-6">
                <p class="text-sm text-slate-500">{{ __('Prepared for') }}</p>
                <p class="font-medium text-slate-900">{{ $offer->client->name }}</p>
                @if($offer->client->company_name)
                    <p class="text-slate-600">{{ $offer->client->company_name }}</p>
                @endif
            </div>

            {{-- Validity --}}
            <div class="flex items-center gap-2 mb-6 {{ $offer->valid_until < now() ? 'text-red-600' : 'text-slate-600' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>
                    {{ __('Valid until') }}: <strong>{{ $offer->valid_until->format('d.m.Y') }}</strong>
                    @if($offer->valid_until < now())
                        <span class="text-red-600">({{ __('Expired') }})</span>
                    @endif
                </span>
            </div>

            {{-- Introduction --}}
            @if($offer->introduction)
                <div class="prose prose-sm max-w-none mb-6">
                    {!! $offer->introduction !!}
                </div>
            @endif

            {{-- Items --}}
            <div class="border rounded-lg overflow-hidden mb-6">
                <table class="w-full text-sm">
                    <thead class="bg-slate-100">
                        <tr>
                            <th class="px-6 py-4 text-left font-medium text-slate-600">{{ __('Description') }}</th>
                            <th class="px-6 py-4 text-center font-medium text-slate-600">{{ __('Qty') }}</th>
                            <th class="px-6 py-4 text-right font-medium text-slate-600">{{ __('Price') }}</th>
                            <th class="px-6 py-4 text-right font-medium text-slate-600">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($offer->items as $item)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-900">{{ $item->title }}</div>
                                    @if($item->description)
                                        <div class="text-sm text-slate-500 mt-1">{{ $item->description }}</div>
                                    @endif
                                    @if($item->is_recurring)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700 mt-1">
                                            {{ $item->billing_cycle_label }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">{{ number_format($item->quantity, 2) }}</td>
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

            {{-- Terms --}}
            @if($offer->terms)
                <div class="prose prose-sm max-w-none pt-6 border-t">
                    <h3>{{ __('Terms & Conditions') }}</h3>
                    {!! $offer->terms !!}
                </div>
            @endif
        </div>

        {{-- Actions --}}
        @if($offer->canBeAccepted())
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="font-semibold text-slate-900 mb-4">{{ __('Your Response') }}</h3>

                <div class="flex flex-col sm:flex-row gap-4">
                    {{-- Accept --}}
                    <form action="{{ route('offers.public.accept', $offer->public_token) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ __('Accept Offer') }}
                        </button>
                    </form>

                    {{-- Reject --}}
                    <button type="button" @click="showRejectModal = true"
                            class="flex-1 inline-flex items-center justify-center px-6 py-3 border border-red-300 rounded-md text-base font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        {{ __('Decline Offer') }}
                    </button>
                </div>
            </div>

            {{-- Reject Modal --}}
            <div x-show="showRejectModal" x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto"
                 @keydown.escape.window="showRejectModal = false">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-black/50" @click="showRejectModal = false"></div>
                    <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                        <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Decline Offer') }}</h3>
                        <form action="{{ route('offers.public.reject', $offer->public_token) }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Reason (optional)') }}</label>
                                <textarea name="reason" rows="3"
                                          class="w-full border-slate-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                          placeholder="{{ __('Please let us know why you are declining...') }}"></textarea>
                            </div>
                            <div class="flex gap-3">
                                <button type="button" @click="showRejectModal = false"
                                        class="flex-1 px-4 py-2 border border-slate-300 rounded-md text-slate-700 hover:bg-slate-50">
                                    {{ __('Cancel') }}
                                </button>
                                <button type="submit"
                                        class="flex-1 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                    {{ __('Decline') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @elseif($offer->isAccepted())
            <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
                <svg class="w-12 h-12 text-green-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-lg font-semibold text-green-800">{{ __('Offer Accepted') }}</h3>
                <p class="text-green-700 mt-1">{{ __('Thank you! This offer was accepted on') }} {{ $offer->accepted_at->format('d.m.Y') }}.</p>
            </div>
        @elseif($offer->isRejected())
            <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                <svg class="w-12 h-12 text-red-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-lg font-semibold text-red-800">{{ __('Offer Declined') }}</h3>
                <p class="text-red-700 mt-1">{{ __('This offer was declined on') }} {{ $offer->rejected_at->format('d.m.Y') }}.</p>
            </div>
        @elseif($offer->isExpired())
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                <svg class="w-12 h-12 text-yellow-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-lg font-semibold text-yellow-800">{{ __('Offer Expired') }}</h3>
                <p class="text-yellow-700 mt-1">{{ __('This offer has expired. Please contact us for a new offer.') }}</p>
            </div>
        @endif

        {{-- Footer --}}
        <div class="text-center text-sm text-slate-500 mt-8">
            <p>{{ __('Questions? Contact us at') }} <a href="mailto:contact@example.com" class="text-blue-600 hover:underline">contact@example.com</a></p>
        </div>
    </div>

    <script>
    function publicOffer() {
        return {
            showRejectModal: false
        };
    }
    </script>
</body>
</html>
