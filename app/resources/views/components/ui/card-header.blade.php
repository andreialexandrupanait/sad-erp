@props(['title' => null, 'description' => null])

<div {{ $attributes->merge(['class' => 'px-6 py-4 bg-slate-100 border-b border-slate-200']) }}>
    @if($title)
        <h3 class="text-2xl font-semibold leading-none tracking-tight">{{ $title }}</h3>
    @endif

    @if($description)
        <p class="text-sm text-slate-500">{{ $description }}</p>
    @endif

    @if(!$title && !$description)
        {{ $slot }}
    @endif
</div>
