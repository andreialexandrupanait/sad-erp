{{-- Brands Block - PDF View --}}
{{-- Variables: block, offer, variables --}}

@if(isset($block['data']['logos']) && count($block['data']['logos']) > 0)
<div style="margin-bottom: 30px;">
    {{-- Section Title --}}
    <h2 style="font-size: 14pt; font-weight: bold; color: #1e293b; margin-bottom: 15px; padding-bottom: 8px; border-bottom: 3px solid #f59e0b;">
        {{ $block['data']['title'] ?? __('Trusted Partners') }}
    </h2>

    {{-- Logo Grid using Tables --}}
    @php
        $logos = $block['data']['logos'] ?? [];
        $columns = $block['data']['columns'] ?? 4;
        $logoChunks = array_chunk($logos, $columns);
        $cellWidth = (100 / $columns) . '%';
    @endphp

    <table style="width: 100%; border-collapse: separate; border-spacing: 10px;">
        @foreach($logoChunks as $row)
            <tr>
                @foreach($row as $logo)
                    <td style="width: {{ $cellWidth }}; padding: 15px; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; text-align: center; vertical-align: middle;">
                        @if(!empty($logo['src']))
                            @php
                                // Handle both URL and storage paths
                                $logoSrc = $logo['src'];
                                if (str_starts_with($logoSrc, '/storage/')) {
                                    $logoSrc = public_path(ltrim($logoSrc, '/'));
                                } elseif (str_starts_with($logoSrc, 'storage/')) {
                                    $logoSrc = public_path($logoSrc);
                                } elseif (!str_starts_with($logoSrc, 'http')) {
                                    $logoSrc = public_path('storage/' . ltrim($logoSrc, '/'));
                                }
                            @endphp
                            <img src="{{ $logoSrc }}"
                                 alt="{{ $logo['alt'] ?? '' }}"
                                 style="max-height: 50px; max-width: 100%; display: inline-block;">
                        @else
                            <span style="color: #94a3b8; font-size: 9pt;">{{ $logo['alt'] ?? __('Logo') }}</span>
                        @endif
                    </td>
                @endforeach
                {{-- Fill empty cells if row is incomplete --}}
                @for($i = count($row); $i < $columns; $i++)
                    <td style="width: {{ $cellWidth }};"></td>
                @endfor
            </tr>
        @endforeach
    </table>
</div>
@endif
