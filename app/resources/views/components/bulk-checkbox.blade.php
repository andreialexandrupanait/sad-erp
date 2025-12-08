@props([
    "id" => null,
    "checked" => false,
    "xModel" => null,
])

<div class="flex items-center h-full">
    <input
        type="checkbox"
        {{ $attributes->merge([
            "class" => "h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 focus:ring-2 focus:ring-offset-2 cursor-pointer transition-colors",
        ]) }}
        @if($id) id="{{ $id }}" @endif
        @if($xModel) x-model="{{ $xModel }}" @endif
        @if($checked) checked @endif
    >
</div>
