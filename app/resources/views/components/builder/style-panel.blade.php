{{-- Block Style Customization Panel --}}
<template x-if="selectedBlockId && getSelectedBlock()">
    <div class="mt-4 pt-4 border-t border-slate-200">
        <p class="text-xs text-slate-500 font-medium mb-3 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
            {{ __('Block Styling') }}
        </p>

        {{-- Top Spacing --}}
        <div class="mb-3">
            <label class="block text-xs text-slate-600 mb-1">{{ __('Top Spacing') }}</label>
            <div class="flex gap-1">
                <template x-for="spacing in ['none', 'sm', 'md', 'lg', 'xl']" :key="'mt-'+spacing">
                    <button type="button"
                            @click="setBlockStyle('marginTop', spacing)"
                            :class="getBlockStyle('marginTop') === spacing ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="flex-1 px-2 py-1 text-xs rounded transition-colors"
                            x-text="spacing === 'none' ? '0' : spacing.toUpperCase()">
                    </button>
                </template>
            </div>
        </div>

        {{-- Bottom Spacing --}}
        <div class="mb-3">
            <label class="block text-xs text-slate-600 mb-1">{{ __('Bottom Spacing') }}</label>
            <div class="flex gap-1">
                <template x-for="spacing in ['none', 'sm', 'md', 'lg', 'xl']" :key="'mb-'+spacing">
                    <button type="button"
                            @click="setBlockStyle('marginBottom', spacing)"
                            :class="getBlockStyle('marginBottom') === spacing ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="flex-1 px-2 py-1 text-xs rounded transition-colors"
                            x-text="spacing === 'none' ? '0' : spacing.toUpperCase()">
                    </button>
                </template>
            </div>
        </div>

        {{-- Background --}}
        <div class="mb-3">
            <label class="block text-xs text-slate-600 mb-1">{{ __('Background') }}</label>
            <div class="flex gap-1">
                <template x-for="bg in [{val: 'white', label: 'W'}, {val: 'slate-50', label: 'G'}, {val: 'blue-50', label: 'B'}, {val: 'green-50', label: 'G'}, {val: 'amber-50', label: 'A'}]" :key="'bg-'+bg.val">
                    <button type="button"
                            @click="setBlockStyle('background', bg.val)"
                            :class="[
                                getBlockStyle('background') === bg.val ? 'ring-2 ring-slate-900 ring-offset-1' : '',
                                'bg-' + bg.val
                            ]"
                            class="flex-1 h-6 rounded border border-slate-200 transition-all">
                    </button>
                </template>
            </div>
        </div>

        {{-- Text Alignment --}}
        <div class="mb-3">
            <label class="block text-xs text-slate-600 mb-1">{{ __('Text Align') }}</label>
            <div class="flex gap-1">
                <template x-for="align in ['left', 'center', 'right']" :key="'align-'+align">
                    <button type="button"
                            @click="setBlockStyle('textAlign', align)"
                            :class="getBlockStyle('textAlign') === align ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="flex-1 px-2 py-1 text-xs rounded transition-colors capitalize"
                            x-text="align">
                    </button>
                </template>
            </div>
        </div>

        {{-- Border --}}
        <div class="mb-3">
            <label class="block text-xs text-slate-600 mb-1">{{ __('Border') }}</label>
            <div class="flex gap-1">
                <template x-for="border in ['none', 'subtle', 'prominent']" :key="'border-'+border">
                    <button type="button"
                            @click="setBlockStyle('border', border)"
                            :class="getBlockStyle('border') === border ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="flex-1 px-2 py-1 text-xs rounded transition-colors capitalize"
                            x-text="border">
                    </button>
                </template>
            </div>
        </div>

        {{-- Padding --}}
        <div class="mb-3">
            <label class="block text-xs text-slate-600 mb-1">{{ __('Padding') }}</label>
            <div class="flex gap-1">
                <template x-for="pad in ['none', 'sm', 'md', 'lg']" :key="'pad-'+pad">
                    <button type="button"
                            @click="setBlockStyle('padding', pad)"
                            :class="getBlockStyle('padding') === pad ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="flex-1 px-2 py-1 text-xs rounded transition-colors"
                            x-text="pad === 'none' ? '0' : pad.toUpperCase()">
                    </button>
                </template>
            </div>
        </div>

        {{-- Header Block Specific: Intro Text (for offers) --}}
        @if(isset($showHeaderOptions) && $showHeaderOptions)
        <template x-if="getSelectedBlock()?.type === 'header'">
            <div class="mt-4 pt-4 border-t border-slate-200 space-y-3">
                <p class="text-xs text-slate-500 font-medium flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                    </svg>
                    {{ __('Header Content') }}
                </p>
                <div>
                    <label class="block text-xs text-slate-600 mb-1">{{ __('Intro Title') }}</label>
                    <input type="text" x-model="getSelectedBlock().data.introTitle"
                           placeholder="{{ __('Your business partner for digital solutions.') }}"
                           class="w-full text-sm border border-slate-200 rounded px-2 py-1.5 focus:border-slate-400 focus:ring-1 focus:ring-slate-400">
                </div>
                <div>
                    <label class="block text-xs text-slate-600 mb-1">{{ __('Intro Text') }}</label>
                    <textarea x-model="getSelectedBlock().data.introText"
                              placeholder="{{ __('We deliver high-quality services tailored to your specific needs...') }}"
                              rows="3"
                              class="w-full text-sm border border-slate-200 rounded px-2 py-1.5 focus:border-slate-400 focus:ring-1 focus:ring-slate-400 resize-none"></textarea>
                </div>
            </div>
        </template>
        @endif

        {{-- Reset Button --}}
        <button type="button" @click="resetBlockStyle()"
                class="w-full mt-2 px-3 py-1.5 text-xs text-slate-600 border border-slate-200 rounded hover:bg-slate-50 transition-colors">
            {{ __('Reset Styles') }}
        </button>
    </div>
</template>
