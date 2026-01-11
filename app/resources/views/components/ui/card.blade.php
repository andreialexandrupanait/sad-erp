@props(['header' => null, 'footer' => null])

<div {{ $attributes->merge(['class' => 'rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-950 dark:text-slate-100 shadow-sm overflow-hidden transition-colors duration-200']) }}>
    @if($header)
        <div class="flex flex-col space-y-1.5 p-4 md:p-6">
            {{ $header }}
        </div>
    @endif

    {{ $slot }}

    @if($footer)
        <div class="flex items-center p-4 md:p-6 pt-0">
            {{ $footer }}
        </div>
    @endif
</div>
