{{-- Acceptance Block - Builder View --}}
<div class="px-6 py-6">
    {{-- Block Heading --}}
    <div class="mb-6">
        <input x-show="!previewMode" type="text" x-model="block.data.heading"
               placeholder="{{ __('Acceptance heading...') }}"
               class="w-full text-xl font-bold text-slate-800 bg-transparent border-none p-0 focus:ring-0">
        <h2 x-show="previewMode" class="text-xl font-bold text-slate-800">
            <span x-text="block.data.heading || '{{ __('Care este următorul pas?') }}'"></span>
            <div class="h-1 w-16 bg-green-500 mt-2 rounded"></div>
        </h2>
        <div x-show="!previewMode" class="h-1 w-16 bg-green-500 mt-2 rounded"></div>
    </div>

    {{-- Acceptance Content Card --}}
    <div class="bg-gradient-to-br from-slate-50 to-blue-50 border border-slate-200 rounded-xl p-6">
        {{-- Paragraph/Terms --}}
        <div class="mb-6">
            <textarea x-show="!previewMode" x-model="block.data.paragraph"
                      placeholder="{{ __('Enter acceptance terms, conditions, or any message for the client...') }}"
                      x-ref="acceptanceParagraph"
                      x-effect="$nextTick(() => { if($refs.acceptanceParagraph) { $refs.acceptanceParagraph.style.height = 'auto'; $refs.acceptanceParagraph.style.height = Math.max($refs.acceptanceParagraph.scrollHeight, 100) + 'px'; } })"
                      x-on:input="$el.style.height = 'auto'; $el.style.height = Math.max($el.scrollHeight, 100) + 'px'"
                      class="w-full text-sm text-slate-600 bg-white/50 border border-slate-200 rounded-lg p-3 focus:border-blue-400 resize-none overflow-hidden min-h-[100px]"></textarea>
            <p x-show="previewMode" class="text-sm text-slate-600 leading-relaxed whitespace-pre-wrap"
               x-text="block.data.paragraph || '{{ __('By accepting this offer, you agree to the terms and conditions outlined above.') }}'"></p>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-wrap items-center justify-end gap-3">
            {{-- Reject Button --}}
            <div class="flex items-center gap-2">
                <span x-show="previewMode"
                      class="inline-flex items-center gap-2 px-6 py-3 bg-slate-100 text-slate-600 font-medium rounded-lg border border-slate-200 hover:bg-slate-200 transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span x-text="block.data.rejectButtonText || '{{ __('Decline') }}'"></span>
                </span>
                <input x-show="!previewMode" type="text" x-model="block.data.rejectButtonText"
                       placeholder="{{ __('Decline') }}"
                       class="px-4 py-2 text-sm text-slate-600 bg-slate-50 border border-slate-200 rounded-lg focus:border-slate-400">
            </div>

            {{-- Accept Button --}}
            <div class="flex items-center gap-2">
                <span x-show="previewMode"
                      class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white font-medium rounded-lg shadow-sm hover:bg-green-700 transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span x-text="block.data.acceptButtonText || '{{ __('Accept Offer') }}'"></span>
                </span>
                <input x-show="!previewMode" type="text" x-model="block.data.acceptButtonText"
                       placeholder="{{ __('Accept Offer') }}"
                       class="px-4 py-2 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg focus:border-green-400">
            </div>
        </div>

        {{-- Info Note --}}
        <div class="mt-6 pt-4 border-t border-slate-200/50 flex items-start gap-3 text-xs text-slate-500">
            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p>{{ __('The client will receive a verification code via email to confirm their decision. All actions are logged for compliance.') }}</p>
        </div>
    </div>

    {{-- Block Options (Edit Mode) --}}
    <div x-show="!previewMode" class="mt-4 pt-4 border-t border-dashed border-slate-200">
        <div class="space-y-3">
            <div class="flex items-center gap-4 text-xs text-slate-500">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" x-model="block.data.requireSignature" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span>{{ __('Require digital signature') }}</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" x-model="block.data.sendConfirmation" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span>{{ __('Send email confirmation') }}</span>
                </label>
            </div>
        </div>
    </div>
</div>
