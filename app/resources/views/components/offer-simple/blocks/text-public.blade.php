{{-- Text Block - Public View --}}
<div class="mb-8">
    @if(!empty($block['data']['heading']))
        <h2 class="text-xl font-semibold text-slate-800 border-b-2 border-green-500 pb-2 mb-4 inline-block">
            {{ $block['data']['heading'] }}
        </h2>
    @endif

    @if(!empty($block['data']['body']))
        <div class="prose prose-slate max-w-none">
            <p class="text-slate-600 leading-relaxed whitespace-pre-wrap">{{ $block['data']['body'] }}</p>
        </div>
    @endif
</div>
