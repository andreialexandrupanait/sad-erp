{{-- Price Box Widget (as block) --}}
<div class="price-box-widget m-3 rounded-lg border overflow-hidden"
     :class="block.data.highlighted ? 'border-blue-500 ring-2 ring-blue-200' : 'border-slate-200'"
     x-data="{ initFeatures() { if (!block.data.features) block.data.features = ['']; } }"
     x-init="initFeatures()">
    {{-- Header --}}
    <div class="p-4 text-center"
         :class="block.data.highlighted ? 'bg-blue-500 text-white' : 'bg-slate-50'">
        {{-- Edit Mode Title --}}
        <input x-show="!previewMode" type="text" x-model="block.data.title"
               placeholder="{{ __('Plan name') }}"
               class="w-full text-center text-lg font-semibold bg-transparent border-none p-0 focus:ring-0"
               :class="block.data.highlighted ? 'text-white placeholder:text-blue-200' : 'text-slate-900 placeholder:text-slate-400'">
        {{-- Preview Mode Title --}}
        <h4 x-show="previewMode" class="text-lg font-semibold" x-text="block.data.title"></h4>
    </div>

    {{-- Price --}}
    <div class="p-4 text-center border-b border-slate-200">
        <div class="flex items-baseline justify-center gap-1">
            {{-- Edit Mode Price --}}
            <input x-show="!previewMode" type="text" x-model="block.data.price"
                   placeholder="99"
                   class="w-24 text-center text-3xl font-bold text-slate-900 bg-transparent border-none p-0 focus:ring-0 placeholder:text-slate-400">
            {{-- Preview Mode Price --}}
            <span x-show="previewMode" class="text-3xl font-bold text-slate-900" x-text="block.data.price"></span>

            {{-- Edit Mode Period --}}
            <input x-show="!previewMode" type="text" x-model="block.data.period"
                   placeholder="/{{ __('month') }}"
                   class="w-20 text-sm text-slate-500 bg-transparent border-none p-0 focus:ring-0 placeholder:text-slate-400">
            {{-- Preview Mode Period --}}
            <span x-show="previewMode" class="text-sm text-slate-500" x-text="block.data.period"></span>
        </div>
    </div>

    {{-- Features --}}
    <div class="p-4">
        {{-- Edit Mode --}}
        <div x-show="!previewMode">
            <p class="text-xs text-slate-500 mb-2">{{ __('Features (one per line):') }}</p>
            <template x-for="(feature, idx) in (block.data.features || [])" :key="'pricef_'+block.id+'_'+idx">
                <div class="flex items-center gap-2 mb-1 group">
                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <input type="text"
                           x-model="block.data.features[idx]"
                           @keydown.enter.prevent="block.data.features.splice(idx + 1, 0, '')"
                           @keydown.backspace="if($el.value === '' && block.data.features.length > 1) { $event.preventDefault(); block.data.features.splice(idx, 1); }"
                           placeholder="{{ __('Feature...') }}"
                           class="flex-1 text-sm text-slate-700 bg-slate-50 border border-slate-200 rounded px-2 py-1 focus:border-slate-400 focus:ring-1 focus:ring-slate-400 placeholder:text-slate-400">
                    <button type="button"
                            @click="block.data.features.splice(idx, 1)"
                            x-show="block.data.features.length > 1"
                            class="opacity-0 group-hover:opacity-100 p-1 text-slate-400 hover:text-red-500">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
            <button type="button"
                    @click="if (!block.data.features) block.data.features = []; block.data.features.push('')"
                    class="mt-2 text-xs text-slate-500 hover:text-slate-700">
                + {{ __('Add feature') }}
            </button>

            {{-- Highlight toggle --}}
            <label class="flex items-center gap-2 mt-4 pt-3 border-t border-slate-200 cursor-pointer">
                <input type="checkbox" x-model="block.data.highlighted" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <span class="text-xs text-slate-600">{{ __('Highlight this plan') }}</span>
            </label>
        </div>

        {{-- Preview Mode --}}
        <ul x-show="previewMode" class="space-y-2">
            <template x-for="(feature, idx) in (block.data.features || [])" :key="'priceprev_'+block.id+'_'+idx">
                <li x-show="feature.trim()" class="flex items-center gap-2 text-sm text-slate-700">
                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span x-text="feature"></span>
                </li>
            </template>
        </ul>
    </div>
</div>
