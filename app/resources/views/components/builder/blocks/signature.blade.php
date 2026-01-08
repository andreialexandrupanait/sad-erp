{{-- Signature/Acceptance Block Component --}}
{{--
    Props:
    - $isTemplate: bool - Whether this is in template builder (buttons disabled)
    - $isPreview: bool - Whether in preview mode (Alpine x-bind for offers)
--}}
@props(['isTemplate' => false])

<div class="px-4 py-6">
    {{-- Section Header --}}
    <h3 class="text-lg font-semibold text-slate-900 mb-6 flex items-center gap-3">
        <span class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </span>
        {{ __('Offer Acceptance') }}
    </h3>

    {{-- Acceptance Panel --}}
    <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-xl p-6">
        @if($isTemplate)
            {{-- Template mode: editable text with auto-expand --}}
            <div class="mb-6">
                <textarea x-model="block.data.signatureText"
                          x-init="$nextTick(() => { $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'; })"
                          @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                          placeholder="{{ __('By approving this offer, I confirm that I have read and agree to the services and conditions described above.') }}"
                          class="w-full text-slate-700 leading-relaxed bg-transparent border-none focus:ring-0 resize-none p-0 overflow-hidden min-h-[24px]"></textarea>
            </div>
        @else
            {{-- Offer mode: editable in edit, static in preview --}}
            <template x-if="!previewMode">
                <div class="mb-6">
                    <textarea x-model="block.data.signatureText"
                              x-init="$nextTick(() => { $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'; })"
                              @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                              placeholder="{{ __('By approving this offer, I confirm that I have read and agree to the services and conditions described above.') }}"
                              class="w-full text-slate-700 leading-relaxed bg-transparent border-none focus:ring-0 resize-none p-0 overflow-hidden min-h-[24px]"></textarea>
                </div>
            </template>
            <template x-if="previewMode">
                <p class="text-slate-700 leading-relaxed mb-6" x-text="block.data.signatureText || '{{ __('By approving this offer, I confirm that I have read and agree to the services and conditions described above.') }}'"></p>
            </template>
        @endif

        <div class="flex items-center justify-between">
            <div class="text-sm text-slate-500 space-y-1">
                @if($isTemplate)
                    <p><span class="font-medium">{{ __('Client') }}:</span> {{ __('Client Name') }}</p>
                    <p><span class="font-medium">{{ __('Date') }}:</span> DD.MM.YYYY</p>
                @else
                    <p><span class="font-medium">{{ __('Client') }}:</span> <span x-text="selectedClient.company || selectedClient.name || '_______________'"></span></p>
                    <p><span class="font-medium">{{ __('Date') }}:</span> <span x-text="formatDate(new Date())"></span></p>
                @endif
            </div>
            <div class="flex items-center gap-3">
                @if($isTemplate)
                    {{-- Template mode: disabled preview buttons --}}
                    <button type="button" disabled
                            class="bg-white text-red-600 border border-red-200 px-6 py-3 rounded-xl font-semibold opacity-75 cursor-not-allowed flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span>{{ __('Reject') }}</span>
                    </button>
                    <button type="button" disabled
                            class="bg-green-600 text-white px-6 py-3 rounded-xl font-semibold opacity-75 cursor-not-allowed flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>{{ __('Accept') }}</span>
                    </button>
                @else
                    {{-- Offer mode: functional buttons --}}
                    <button type="button" @click="rejectOffer()"
                            class="bg-white text-red-600 border border-red-200 px-6 py-3 rounded-xl font-semibold hover:bg-red-50 hover:border-red-300 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span>{{ __('Reject') }}</span>
                    </button>
                    <button type="button" @click="approveOffer()"
                            class="bg-green-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-green-700 shadow-lg shadow-green-200 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>{{ __('Accept') }}</span>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
