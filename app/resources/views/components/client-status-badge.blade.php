@props(['status'])

@if($status)
    <span
        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
        style="background-color: {{ $status->color_background }}; color: {{ $status->color_text }};"
    >
        {{ $status->name }}
    </span>
@else
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
        No Status
    </span>
@endif
