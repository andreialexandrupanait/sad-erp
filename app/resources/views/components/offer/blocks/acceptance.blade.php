{{-- Acceptance Block - Builder View --}}
{{-- Contains: Acceptance text + Accept/Reject buttons for offer approval --}}

<div class="offer-acceptance-block px-6 py-6">
    {{-- Section Title --}}
    <div class="mb-6">
        <input type="text" x-model="block.data.title"
               class="text-xl font-bold text-slate-900 bg-transparent border-none focus:ring-0 p-0 w-full"
               placeholder="{{ __('Offer Acceptance') }}">
        <div class="h-1 w-20 bg-purple-600 mt-2 rounded"></div>
    </div>

    {{-- Acceptance Panel --}}
    <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-xl p-6">
        {{-- Editable Acceptance Text --}}
        <div class="mb-6">
            <textarea x-model="block.data.acceptanceText"
                      x-init="$nextTick(() => { $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'; })"
                      @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                      placeholder="{{ __('By approving this offer, I confirm that I have read and agree to the services and conditions described above.') }}"
                      class="w-full text-slate-700 leading-relaxed bg-transparent border-none focus:ring-0 resize-none p-0 overflow-hidden min-h-[24px]"></textarea>
        </div>

        {{-- Client Info + Buttons Row --}}
        <div class="flex items-center justify-between">
            {{-- Client Info (Preview) --}}
            <div class="text-sm text-slate-500 space-y-1">
                <template x-if="block.data.showClientInfo !== false">
                    <div>
                        <p>
                            <span class="font-medium">{{ __('Client') }}:</span>
                            <span x-text="selectedClient.company || selectedClient.name || '_______________'"></span>
                        </p>
                    </div>
                </template>
                <template x-if="block.data.showDate !== false">
                    <p>
                        <span class="font-medium">{{ __('Date') }}:</span>
                        <span x-text="formatDate(new Date())"></span>
                    </p>
                </template>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-3">
                {{-- Reject Button --}}
                <button type="button" disabled
                        class="bg-white text-red-600 border border-red-200 px-6 py-3 rounded-xl font-semibold opacity-75 cursor-not-allowed flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span x-text="block.data.rejectButtonText || '{{ __('Decline') }}'"></span>
                </button>

                {{-- Accept Button --}}
                <button type="button" disabled
                        class="bg-green-600 text-white px-6 py-3 rounded-xl font-semibold opacity-75 cursor-not-allowed flex items-center gap-2 shadow-lg shadow-green-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span x-text="block.data.acceptButtonText || '{{ __('Accept Offer') }}'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Display Options --}}
    <div class="mt-4 flex flex-wrap gap-4 text-sm">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" x-model="block.data.showClientInfo"
                   class="rounded border-slate-300 text-purple-600 focus:ring-purple-500">
            <span class="text-slate-600">{{ __('Show Client Info') }}</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" x-model="block.data.showDate"
                   class="rounded border-slate-300 text-purple-600 focus:ring-purple-500">
            <span class="text-slate-600">{{ __('Show Date') }}</span>
        </label>
    </div>

    {{-- Button Text Customization --}}
    <div class="mt-4 grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Accept Button Text') }}</label>
            <input type="text" x-model="block.data.acceptButtonText"
                   placeholder="{{ __('Accept Offer') }}"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-purple-400">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Reject Button Text') }}</label>
            <input type="text" x-model="block.data.rejectButtonText"
                   placeholder="{{ __('Decline') }}"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-purple-400">
        </div>
    </div>
</div>
