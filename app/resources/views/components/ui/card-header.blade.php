@props(['title' => null, 'description' => null])

<div {{ $attributes->merge(['class' => 'px-4 md:px-6 py-3 md:py-4 bg-slate-100 border-b border-slate-200']) }}>
    @if($title)
        <h3 class="text-xl md:text-2xl font-semibold leading-none tracking-tight text-slate-900">{{ $title }}</h3>
    @endif

    @if($description)
        <p class="text-sm text-slate-500 mt-1">{{ $description }}</p>
    @endif

    @if(!$title && !$description)
        {{ $slot }}
    @endif
</div>
