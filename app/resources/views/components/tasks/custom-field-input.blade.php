@props(['field', 'value' => null, 'task' => null])

@php
    $inputName = 'custom_fields[' . $field->id . ']';
    $inputId = 'custom_field_' . $field->id;
    $currentValue = old('custom_fields.' . $field->id, $value ?? ($task ? $task->customFieldValues->where('custom_field_id', $field->id)->first()?->value : null));
@endphp

<div class="mb-4">
    <label for="{{ $inputId }}" class="block text-sm font-medium text-slate-700 mb-2">
        {{ $field->name }}
        @if($field->is_required)
            <span class="text-red-500">*</span>
        @endif
    </label>

    @if($field->description)
        <p class="text-xs text-slate-500 mb-2">{{ $field->description }}</p>
    @endif

    @switch($field->type)
        @case('text')
            <input
                type="text"
                name="{{ $inputName }}"
                id="{{ $inputId }}"
                value="{{ $currentValue }}"
                {{ $field->is_required ? 'required' : '' }}
                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                placeholder="{{ $field->name }}"
            >
            @break

        @case('number')
            <input
                type="number"
                name="{{ $inputName }}"
                id="{{ $inputId }}"
                value="{{ $currentValue }}"
                {{ $field->is_required ? 'required' : '' }}
                step="any"
                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                placeholder="{{ $field->name }}"
            >
            @break

        @case('date')
            <input
                type="date"
                name="{{ $inputName }}"
                id="{{ $inputId }}"
                value="{{ $currentValue }}"
                {{ $field->is_required ? 'required' : '' }}
                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            >
            @break

        @case('email')
            <input
                type="email"
                name="{{ $inputName }}"
                id="{{ $inputId }}"
                value="{{ $currentValue }}"
                {{ $field->is_required ? 'required' : '' }}
                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                placeholder="email@example.com"
            >
            @break

        @case('url')
            <input
                type="url"
                name="{{ $inputName }}"
                id="{{ $inputId }}"
                value="{{ $currentValue }}"
                {{ $field->is_required ? 'required' : '' }}
                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                placeholder="https://example.com"
            >
            @break

        @case('phone')
            <input
                type="tel"
                name="{{ $inputName }}"
                id="{{ $inputId }}"
                value="{{ $currentValue }}"
                {{ $field->is_required ? 'required' : '' }}
                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                placeholder="+1234567890"
            >
            @break

        @case('dropdown')
            <select
                name="{{ $inputName }}"
                id="{{ $inputId }}"
                {{ $field->is_required ? 'required' : '' }}
                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            >
                <option value="">{{ __('Select an option') }}</option>
                @foreach($field->options ?? [] as $option)
                    <option value="{{ $option }}" {{ $currentValue == $option ? 'selected' : '' }}>
                        {{ $option }}
                    </option>
                @endforeach
            </select>
            @break

        @case('checkbox')
            <div class="flex items-center">
                <input
                    type="checkbox"
                    name="{{ $inputName }}"
                    id="{{ $inputId }}"
                    value="1"
                    {{ $currentValue ? 'checked' : '' }}
                    class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-slate-300 rounded"
                >
                <label for="{{ $inputId }}" class="ml-2 text-sm text-slate-600">
                    {{ $field->description ?? __('Enable this option') }}
                </label>
            </div>
            @break

        @default
            <input
                type="text"
                name="{{ $inputName }}"
                id="{{ $inputId }}"
                value="{{ $currentValue }}"
                {{ $field->is_required ? 'required' : '' }}
                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            >
    @endswitch

    @error('custom_fields.' . $field->id)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
