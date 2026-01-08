@props([
    'name',
    'categories',
    'selected' => null,
    'placeholder' => null,
    'showAddButton' => false,
    'addButtonId' => null,
    'width' => '200px',
    'required' => false,
    'disabled' => false,
])

@php
    $selectClasses = 'h-10 px-3 pr-10 text-sm border border-slate-200 rounded-lg bg-white
        focus:border-slate-400 focus:ring-1 focus:ring-slate-400 focus:outline-none
        transition-colors cursor-pointer appearance-none
        disabled:bg-slate-50 disabled:text-slate-500 disabled:cursor-not-allowed';

    // SVG chevron as background image
    $chevronBg = "bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236b7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[length:1.25rem_1.25rem] bg-[right_0.5rem_center] bg-no-repeat";
@endphp

<div class="flex items-center gap-2">
    <select
        name="{{ $name }}"
        style="width: {{ $width }}; min-width: {{ $width }}; max-width: {{ $width }};"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => $selectClasses . ' ' . $chevronBg]) }}
    >
        <option value="">{{ $placeholder ?? __('-- Selectează categoria --') }}</option>

        @foreach($categories as $category)
            @if($category->children && $category->children->count() > 0)
                {{-- Parent category as selectable option --}}
                <option
                    value="{{ $category->id }}"
                    {{ $selected == $category->id ? 'selected' : '' }}
                >■ {{ $category->label }}</option>

                {{-- Child categories indented --}}
                @foreach($category->children as $child)
                    <option
                        value="{{ $child->id }}"
                        {{ $selected == $child->id ? 'selected' : '' }}
                    >&nbsp;&nbsp;&nbsp;└ {{ $child->label }}</option>
                @endforeach
            @else
                {{-- Category without children --}}
                <option
                    value="{{ $category->id }}"
                    {{ $selected == $category->id ? 'selected' : '' }}
                >{{ $category->label }}</option>
            @endif
        @endforeach
    </select>

    @if($showAddButton)
        <button
            type="button"
            @if($addButtonId) id="{{ $addButtonId }}" @endif
            class="flex-shrink-0 h-10 w-10 flex items-center justify-center text-slate-400
                   hover:text-blue-600 hover:bg-blue-50 border border-slate-200 rounded-lg
                   transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
            title="{{ __('Adaugă categorie nouă') }}"
            {{ $attributes->whereStartsWith('@click') }}
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
        </button>
    @endif
</div>
