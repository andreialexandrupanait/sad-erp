@props(['isTemplate' => false])

<div class="px-4 py-4">
    <div class="grid grid-cols-2 gap-8">
        <div class="space-y-2">
            <input type="text" x-model="block.data.leftTitle" placeholder="{{ __('Left Column Title') }}"
                   class="w-full text-base font-semibold text-slate-900 bg-transparent border-none focus:ring-0 p-0 mb-2">
            <textarea x-model="block.data.leftContent" rows="4" placeholder="{{ __('Left column content...') }}"
                      class="w-full text-sm text-slate-600 bg-slate-50 border border-slate-200 rounded p-2 focus:border-slate-400 resize-none"></textarea>
        </div>
        <div class="space-y-2">
            <input type="text" x-model="block.data.rightTitle" placeholder="{{ __('Right Column Title') }}"
                   class="w-full text-base font-semibold text-slate-900 bg-transparent border-none focus:ring-0 p-0 mb-2">
            <textarea x-model="block.data.rightContent" rows="4" placeholder="{{ __('Right column content...') }}"
                      class="w-full text-sm text-slate-600 bg-slate-50 border border-slate-200 rounded p-2 focus:border-slate-400 resize-none"></textarea>
        </div>
    </div>
</div>
