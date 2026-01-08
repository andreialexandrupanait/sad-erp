@props([
    'name' => '',
    'label' => '',
    'required' => false,
    'hint' => null,
    'error' => null,
])

@php
    // Try to get error from session if not explicitly provided
    $errorMessage = $error ?? $errors->first($name);
    $hasError = !empty($errorMessage);
    $errorId = $name ? $name . '-error' : null;
    $hintId = $name ? $name . '-hint' : null;
@endphp

<div {{ $attributes->merge(['class' => 'field-wrapper']) }}>
    @if($label)
        <x-ui.label :for="$name" class="{{ $hasError ? 'text-red-600' : '' }}">
            {{ $label }}
            @if($required)
                <span class="text-red-500 ml-0.5" aria-hidden="true">*</span>
                <span class="sr-only">{{ __('required') }}</span>
            @endif
        </x-ui.label>
    @endif

    <div class="mt-2 relative">
        {{ $slot }}

        @if($hasError)
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" aria-hidden="true">
                <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
        @endif
    </div>

    @if($hasError)
        <p id="{{ $errorId }}" class="mt-2 text-sm text-red-600 flex items-center gap-1" role="alert" aria-live="assertive">
            <svg class="h-4 w-4 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            {{ $errorMessage }}
        </p>
    @elseif($hint)
        <p id="{{ $hintId }}" class="mt-2 text-sm text-slate-500">{{ $hint }}</p>
    @endif
</div>
