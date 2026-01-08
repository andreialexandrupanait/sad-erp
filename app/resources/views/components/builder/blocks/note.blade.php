@props(['isTemplate' => false])

<div class="px-4 py-4" x-data="{ initWidgets() { if (!block.data.widgets) block.data.widgets = []; } }" x-init="initWidgets()">
    <div class="bg-sky-50 border border-sky-200 p-4 rounded-lg">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-sky-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <textarea x-model="block.data.content" rows="2"
                      placeholder="{{ __('Add a note...') }}"
                      class="flex-1 text-slate-600 text-sm bg-transparent border-none focus:ring-0 p-0 resize-none"></textarea>
        </div>
    </div>

    {{-- Widget Container --}}
    @include('components.builder.widgets.widget-container')
</div>
