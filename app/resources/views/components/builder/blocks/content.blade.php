@props(['isTemplate' => false])

<div class="px-4 py-4" x-data="{ initWidgets() { if (!block.data.widgets) block.data.widgets = []; } }" x-init="initWidgets()">
    <input type="text" x-model="block.data.title" placeholder="{{ __('Section Title (optional)') }}"
           class="w-full text-base font-semibold text-slate-900 bg-transparent border-none focus:ring-0 p-0 mb-3 placeholder:text-slate-400">
    @if($isTemplate)
        <textarea x-model="block.data.content" rows="3"
                  placeholder="{{ __('Write your content here...') }}"
                  class="w-full text-slate-700 bg-slate-50 border border-slate-200 rounded-lg p-3 focus:border-slate-400 resize-none"></textarea>
    @else
        <div contenteditable="true"
             x-html="block.data.content"
             @blur="block.data.content = $event.target.innerHTML"
             class="min-h-[80px] p-4 bg-slate-50 border border-slate-200 rounded-xl focus:border-blue-300 focus:outline-none text-slate-600 leading-relaxed"
             data-placeholder="{{ __('Write your content here...') }}"></div>
    @endif

    {{-- Widget Container --}}
    @include('components.builder.widgets.widget-container')
</div>
