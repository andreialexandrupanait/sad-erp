@props(['for' => null, 'required' => false])

<label
    @if($for) for="{{ $for }}" @endif
    {{ $attributes->merge(['class' => 'text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70']) }}
>
    {{ $slot }}
    @if($required)
        <span class="text-red-500 ml-0.5">*</span>
    @endif
</label>
