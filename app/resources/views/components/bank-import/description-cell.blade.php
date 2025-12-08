@props([
    'description',
    'maxLength' => 60,
    'editable' => true,
    'rowIndex' => null,
    'inputName' => null,
])

@php
    $isTruncated = strlen($description) > $maxLength;
    $truncatedText = $isTruncated ? Str::limit($description, $maxLength) : $description;
    $inputName = $inputName ?? "transactions[{$rowIndex}][description]";
@endphp

<div 
    x-data="descriptionCell({
        description: {{ Js::from($description) }},
        editable: {{ $editable ? 'true' : 'false' }},
        rowIndex: {{ Js::from($rowIndex) }}
    })"
    class="description-cell relative min-w-0"
>
    {{-- Display mode --}}
    <div x-show="!editing" class="flex items-start gap-2">
        <div class="flex-1 min-w-0">
            {{-- Truncated text --}}
            <p 
                x-show="!expanded"
                @click="isTruncated && (expanded = true)"
                :class="{ 'cursor-pointer hover:text-slate-700': isTruncated }"
                class="text-sm text-slate-900 truncate"
                :title="description"
            >
                <span x-text="truncatedText"></span>
                <button 
                    x-show="isTruncated"
                    @click.stop="expanded = true"
                    class="ml-1 text-xs text-blue-600 hover:text-blue-800 font-medium"
                >
                    {{ __('...more') }}
                </button>
            </p>
            
            {{-- Expanded text --}}
            <div x-show="expanded" x-cloak class="space-y-1">
                <p 
                    class="text-sm text-slate-900 whitespace-pre-wrap break-words leading-relaxed"
                    x-text="description"
                ></p>
                <button 
                    @click="expanded = false" 
                    class="text-xs text-slate-400 hover:text-slate-600 font-medium"
                >
                    {{ __('Show less') }}
                </button>
            </div>
        </div>
        
        {{-- Edit button (appears on hover if editable) --}}
        @if($editable)
        <button 
            x-show="!expanded"
            @click="startEditing()"
            class="flex-shrink-0 p-1 text-slate-300 hover:text-slate-500 opacity-0 group-hover:opacity-100 transition-opacity rounded hover:bg-slate-100"
            title="{{ __('Edit description') }}"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
            </svg>
        </button>
        @endif
    </div>
    
    {{-- Edit mode --}}
    @if($editable)
    <div x-show="editing" x-cloak class="space-y-2">
        <textarea 
            x-ref="editInput"
            x-model="editedDescription"
            @keydown.escape.prevent="cancelEditing()"
            @keydown.ctrl.enter="saveEdit()"
            @keydown.meta.enter="saveEdit()"
            rows="3"
            class="w-full text-sm border border-blue-300 rounded-md px-3 py-2 
                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none
                   resize-none"
            placeholder="{{ __('Enter description...') }}"
        ></textarea>
        <div class="flex items-center justify-between">
            <span class="text-xs text-slate-400">
                {{ __('Ctrl+Enter to save, Esc to cancel') }}
            </span>
            <div class="flex items-center gap-2">
                <button 
                    type="button"
                    @click="cancelEditing()"
                    class="px-2 py-1 text-xs font-medium text-slate-600 hover:text-slate-800"
                >
                    {{ __('Cancel') }}
                </button>
                <button 
                    type="button"
                    @click="saveEdit()"
                    class="px-3 py-1 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded transition-colors"
                >
                    {{ __('Save') }}
                </button>
            </div>
        </div>
    </div>
    @endif
    
    {{-- Hidden input for form submission --}}
    <input 
        type="hidden" 
        name="{{ $inputName }}" 
        :value="description"
    >
</div>
