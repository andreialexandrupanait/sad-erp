@props([
    'name' => 'content',
    'id' => null,
    'value' => '',
    'placeholder' => '',
    'disabled' => false,
])

@php
    $inputId = $id ?? 'trix-' . $name . '-' . uniqid();
@endphp

<div class="wysiwyg-wrapper">
    <input
        type="hidden"
        name="{{ $name }}"
        id="{{ $inputId }}"
        value="{{ $value }}"
        {{ $attributes->only(['x-model', 'x-ref']) }}
    >
    <trix-editor
        input="{{ $inputId }}"
        placeholder="{{ $placeholder }}"
        @if($disabled) disabled @endif
        class="trix-content prose prose-sm max-w-none bg-white"
        {{ $attributes->except(['x-model', 'x-ref', 'class']) }}
    ></trix-editor>
</div>
