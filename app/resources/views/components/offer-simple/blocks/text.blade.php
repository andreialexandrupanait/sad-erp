{{-- Text Block - Builder View --}}
<div class="px-6 py-6">
    {{-- Block Heading --}}
    <div class="mb-6">
        <input x-show="!previewMode" type="text" x-model="block.data.heading"
               placeholder="{{ __('Section heading...') }}"
               class="w-full text-xl font-bold text-slate-800 bg-transparent border-none p-0 focus:ring-0">
        <h2 x-show="previewMode" class="text-xl font-bold text-slate-800">
            <span x-text="block.data.heading || '{{ __('Introduction') }}'"></span>
            <div class="h-1 w-16 bg-green-500 mt-2 rounded"></div>
        </h2>
        <div x-show="!previewMode" class="h-1 w-16 bg-green-500 mt-2 rounded"></div>
    </div>

    {{-- Body Content --}}
    <div x-show="!previewMode">
        <textarea x-model="block.data.body" rows="4"
                  placeholder="{{ __('Write your content here...') }}"
                  class="w-full text-slate-600 bg-slate-50 border border-slate-200 rounded-lg p-4 focus:border-green-400 focus:ring-1 focus:ring-green-400 resize-none leading-relaxed"></textarea>
    </div>
    <div x-show="previewMode" class="prose prose-slate max-w-none">
        <p class="text-slate-600 leading-relaxed whitespace-pre-wrap" x-text="block.data.body"></p>
    </div>
</div>
