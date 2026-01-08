@props(['isTemplate' => false])

<div class="px-4 py-3" x-data="{ initWidgets() { if (!block.data.widgets) block.data.widgets = []; } }" x-init="initWidgets()">
    @if($isTemplate)
        <textarea x-model="block.data.content" rows="2"
                  placeholder="{{ __('Write text here...') }}"
                  class="w-full text-slate-600 bg-transparent border border-slate-200 rounded-lg p-2 focus:border-slate-400 resize-none"></textarea>
    @else
        <div contenteditable="true"
             x-html="block.data.content"
             @blur="block.data.content = $event.target.innerHTML"
             class="min-h-[40px] p-3 bg-slate-50 border border-slate-200 rounded-lg focus:border-slate-400 focus:outline-none text-slate-600"
             data-placeholder="{{ __('Write text here...') }}"></div>
    @endif

    {{-- Widget Container --}}
    @include('components.builder.widgets.widget-container')
</div>
