@props(['header' => null, 'footer' => null])

<div {{ $attributes->merge(['class' => 'rounded-[10px] border border-slate-200 bg-white text-slate-950 shadow-sm overflow-hidden']) }}>
    @if($header)
        <div class="flex flex-col space-y-1.5 p-6">
            {{ $header }}
        </div>
    @endif

    {{ $slot }}

    @if($footer)
        <div class="flex items-center p-6 pt-0">
            {{ $footer }}
        </div>
    @endif
</div>
