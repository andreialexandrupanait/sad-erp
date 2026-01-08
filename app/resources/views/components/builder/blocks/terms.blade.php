@props(['isTemplate' => false])

<div class="px-4 py-6">
    <h3 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-3">
        <span class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </span>
        {{ __('Terms and Conditions') }}
    </h3>
    <textarea x-model="block.data.content" rows="5"
              placeholder="{{ __('Add terms and conditions...') }}"
              class="w-full text-sm text-slate-600 bg-slate-50 border border-slate-200 rounded-xl p-4 focus:border-blue-300 focus:ring-1 focus:ring-blue-300 resize-none leading-relaxed"></textarea>
</div>
