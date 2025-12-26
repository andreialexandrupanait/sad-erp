{{-- Brands Block - Public View --}}
<div class="mb-8">
    @if(!empty($block['data']['heading']))
        <h2 class="text-xl font-semibold text-slate-800 border-b-2 border-green-500 pb-2 mb-6 inline-block">
            {{ $block['data']['heading'] }}
        </h2>
    @endif

    @php
        $logos = $block['data']['logos'] ?? [];
        $columns = $block['data']['columns'] ?? 4;
        $gridCols = match($columns) {
            2 => 'grid-cols-2',
            3 => 'grid-cols-3',
            5 => 'grid-cols-5',
            6 => 'grid-cols-6',
            default => 'grid-cols-4'
        };
    @endphp

    @if(count($logos) > 0)
        <div class="grid {{ $gridCols }} gap-4">
            @foreach($logos as $logo)
                @if(!empty($logo['src']))
                    <div class="bg-white border border-slate-200 rounded-lg p-4 flex items-center justify-center min-h-[80px] hover:shadow-md transition-shadow">
                        <img src="{{ $logo['src'] }}" alt="{{ $logo['alt'] ?? 'Logo' }}"
                             class="max-h-12 max-w-full object-contain grayscale hover:grayscale-0 transition-all">
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <div class="text-center py-8 text-slate-400 bg-slate-50 rounded-lg">
            <p class="text-sm">{{ __('No brand logos') }}</p>
        </div>
    @endif
</div>
