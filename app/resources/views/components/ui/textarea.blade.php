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
        'flex min-h-[80px] w-full rounded-md border bg-white px-3 py-2 text-sm ring-offset-white placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 disabled:bg-slate-50 transition-colors resize-y',
        $hasError
            ? 'border-red-300 text-red-900 placeholder:text-red-300 focus-visible:ring-red-500'
            : 'border-slate-200 focus-visible:ring-blue-500',
    ]) }}
>{{ $slot }}</textarea>
