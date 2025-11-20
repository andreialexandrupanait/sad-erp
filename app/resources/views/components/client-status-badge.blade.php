@props(['status', 'editable' => false])

@if($status)
    <span
        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium transition-all duration-150 {{ $editable ? 'hover:shadow-sm hover:scale-105 cursor-pointer' : '' }}"
        style="background-color: {{ $status->color_background }}; color: {{ $status->color_text }};"
    >
        {{ $status->name }}
        @if($editable)
            <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
            </svg>
        @endif
    </span>
@else
    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-50 text-yellow-700 border border-dashed border-yellow-300 transition-all duration-150 {{ $editable ? 'hover:bg-yellow-100 hover:border-yellow-400 hover:shadow-sm cursor-pointer' : '' }}">
        {{ __('No Status') }}
        @if($editable)
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
        @endif
    </span>
@endif
