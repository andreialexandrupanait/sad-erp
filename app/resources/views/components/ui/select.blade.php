@props([
    'disabled' => false,
    'error' => false,
    'required' => false,
])

@php
    // Auto-detect error from validation errors if name attribute exists
    $name = $attributes->get('name');
    $hasError = $error || ($name && $errors->has($name));
    $errorId = $name ? $name . '-error' : null;

    $baseClasses = 'appearance-none flex h-10 w-full items-center justify-between rounded-md border bg-white pl-3 pr-10 py-2 text-sm placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-50 disabled:bg-slate-50 transition-colors';

    $chevronSvg = "bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236b7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[length:1.25rem_1.25rem] bg-[right_0.75rem_center] bg-no-repeat";

    $stateClasses = $hasError
        ? 'border-red-300 focus:ring-red-500'
        : 'border-slate-200';
@endphp

<select
    {{ $disabled ? 'disabled' : '' }}
    {{ $required ? 'required' : '' }}
    @if($required) aria-required="true" @endif
    @if($hasError) aria-invalid="true" @endif
    @if($hasError && $errorId) aria-describedby="{{ $errorId }}" @endif
    {{ $attributes->merge(['class' => "$baseClasses $chevronSvg $stateClasses"]) }}
>
    {{ $slot }}
</select>
