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
@endphp

<textarea
    {{ $disabled ? 'disabled' : '' }}
    {{ $required ? 'required' : '' }}
    @if($required) aria-required="true" @endif
    @if($hasError) aria-invalid="true" @endif
    @if($hasError && $errorId) aria-describedby="{{ $errorId }}" @endif
    {{ $attributes->class([
        'flex min-h-[80px] w-full rounded-md border bg-white px-3 py-2 text-sm placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-50 disabled:bg-slate-50 transition-colors resize-y',
        $hasError
            ? 'border-red-300 text-red-900 placeholder:text-red-300 focus:ring-red-500'
            : 'border-slate-200',
    ]) }}
>{{ $slot }}</textarea>
