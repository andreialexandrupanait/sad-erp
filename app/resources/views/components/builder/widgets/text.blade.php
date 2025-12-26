{{-- Text Widget - Works as block (block.data) or widget (widget.data) --}}
@php $dataVar = isset($asBlock) && $asBlock ? 'block' : 'widget'; @endphp
<div class="text-widget px-4 py-3">
    {{-- Edit Mode --}}
    <div x-show="!previewMode">
        <textarea x-model="{{ $dataVar }}.data.content"
                  x-init="$nextTick(() => { if($el.scrollHeight > 40) $el.style.height = $el.scrollHeight + 'px' })"
                  @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                  placeholder="{{ __('Enter your text here...') }}"
                  rows="2"
                  class="w-full text-sm text-slate-700 bg-slate-50 border border-slate-200 rounded-lg p-3 focus:border-slate-400 focus:ring-1 focus:ring-slate-400 resize-none overflow-hidden leading-relaxed"></textarea>
    </div>

    {{-- Preview Mode --}}
    <div x-show="previewMode" class="text-sm text-slate-700 leading-relaxed whitespace-pre-wrap p-3" x-text="{{ $dataVar }}.data.content"></div>
</div>
