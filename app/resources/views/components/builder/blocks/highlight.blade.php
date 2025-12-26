@props(['isTemplate' => false])

<div class="px-4 py-4" x-data="{ initWidgets() { if (!block.data.widgets) block.data.widgets = []; } }" x-init="initWidgets()">
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
        <textarea x-model="block.data.content" rows="2"
                  placeholder="{{ __('Important information...') }}"
                  class="w-full text-slate-700 font-medium bg-transparent border-none focus:ring-0 p-0 resize-none"></textarea>
    </div>

    {{-- Widget Container --}}
    @include('components.builder.widgets.widget-container')
</div>
