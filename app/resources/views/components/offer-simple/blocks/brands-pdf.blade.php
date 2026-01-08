{{-- Brands Block - PDF View --}}
@php
    $logos = $block['data']['logos'] ?? [];
    $columns = $block['data']['columns'] ?? 4;
    $cellWidth = 100 / $columns;
@endphp

<div style="margin-bottom: 30px;">
    @if(!empty($block['data']['heading']))
        <h2 style="font-size: 14pt; font-weight: bold; color: #1e293b; margin-bottom: 20px; padding-bottom: 8px; border-bottom: 3px solid #16a34a; display: inline-block;">
            {{ $block['data']['heading'] }}
        </h2>
    @endif

    @if(count($logos) > 0)
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                @foreach($logos as $index => $logo)
                    @if(!empty($logo['src']))
                        <td style="width: {{ $cellWidth }}%; padding: 10px; text-align: center; vertical-align: middle; border: 1px solid #e2e8f0; background-color: #ffffff;">
                            <img src="{{ $logo['src'] }}" alt="{{ $logo['alt'] ?? 'Logo' }}"
                                 style="max-height: 40px; max-width: 100%; filter: grayscale(100%);">
                        </td>
                        @if(($index + 1) % $columns === 0 && $index + 1 < count($logos))
                            </tr><tr>
                        @endif
                    @endif
                @endforeach

                {{-- Fill remaining cells in last row --}}
                @php
                    $validLogos = collect($logos)->filter(fn($l) => !empty($l['src']))->count();
                    $remainder = $validLogos % $columns;
                    $emptyCells = $remainder > 0 ? $columns - $remainder : 0;
                @endphp
                @for($i = 0; $i < $emptyCells; $i++)
                    <td style="width: {{ $cellWidth }}%; padding: 10px; border: 1px solid #e2e8f0; background-color: #f8fafc;"></td>
                @endfor
            </tr>
        </table>
    @else
        <div style="padding: 30px; text-align: center; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
            <p style="color: #64748b; font-size: 10pt; margin: 0;">{{ __('No brand logos') }}</p>
        </div>
    @endif
</div>
