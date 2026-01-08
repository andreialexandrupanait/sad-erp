{{-- Acceptance Block - Public View --}}
<div class="mb-8">
    @if(!empty($block['data']['heading']))
        <h2 class="text-xl font-semibold text-slate-800 border-b-2 border-green-500 pb-2 mb-6 inline-block">
            {{ $block['data']['heading'] }}
        </h2>
    @endif

    <div class="bg-gradient-to-br from-slate-50 to-blue-50 border border-slate-200 rounded-xl p-6">
        {{-- Paragraph/Terms --}}
        @if(!empty($block['data']['paragraph']))
            <p class="text-sm text-slate-600 leading-relaxed mb-6 whitespace-pre-wrap">
                {{ $block['data']['paragraph'] }}
            </p>
        @else
            <p class="text-sm text-slate-600 leading-relaxed mb-6">
                {{ __('By accepting this offer, you agree to the terms and conditions outlined above.') }}
            </p>
        @endif

        {{-- Action Buttons --}}
        @if($offer->status === 'sent' || $offer->status === 'viewed')
            <div class="flex flex-wrap items-center gap-3">
                {{-- Accept Button --}}
                <form action="{{ route('offers.public.accept', $offer->public_token) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-sm transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ $block['data']['acceptButtonText'] ?? __('Accept Offer') }}
                    </button>
                </form>

                {{-- Reject Button --}}
                <form action="{{ route('offers.public.reject', $offer->public_token) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium rounded-lg border border-slate-200 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        {{ $block['data']['rejectButtonText'] ?? __('Decline') }}
                    </button>
                </form>
            </div>
        @elseif($offer->status === 'accepted')
            <div class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-lg">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="font-medium text-green-800">{{ __('Offer Accepted') }}</p>
                    @if($offer->accepted_at)
                        <p class="text-sm text-green-600">{{ __('Accepted on') }} {{ $offer->accepted_at->format('d.m.Y H:i') }}</p>
                    @endif
                </div>
            </div>
        @elseif($offer->status === 'rejected')
            <div class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-lg">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="font-medium text-red-800">{{ __('Offer Declined') }}</p>
                    @if($offer->rejected_at)
                        <p class="text-sm text-red-600">{{ __('Declined on') }} {{ $offer->rejected_at->format('d.m.Y H:i') }}</p>
                    @endif
                </div>
            </div>
        @elseif($offer->status === 'expired')
            <div class="flex items-center gap-3 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="font-medium text-amber-800">{{ __('Offer Expired') }}</p>
                    <p class="text-sm text-amber-600">{{ __('This offer is no longer valid.') }}</p>
                </div>
            </div>
        @endif

        {{-- Info Note --}}
        <div class="mt-6 pt-4 border-t border-slate-200/50 flex items-start gap-3 text-xs text-slate-500">
            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p>{{ __('A verification code will be sent to your email to confirm your decision.') }}</p>
        </div>
    </div>
</div>
