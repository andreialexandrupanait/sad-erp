@props([
    'cols' => 1,        // Default columns
    'mdCols' => null,   // Medium breakpoint columns
    'lgCols' => null,   // Large breakpoint columns
    'gap' => 6,         // Standard gap (default: 1.5rem / 24px)
])

@php
    $gridClasses = 'grid grid-cols-' . $cols;

    if ($mdCols) {
        $gridClasses .= ' md:grid-cols-' . $mdCols;
    }

    if ($lgCols) {
        $gridClasses .= ' lg:grid-cols-' . $lgCols;
    }

    $gridClasses .= ' gap-' . $gap;
@endphp

<div {{ $attributes->merge(['class' => $gridClasses]) }}>
    {{ $slot }}
</div>
