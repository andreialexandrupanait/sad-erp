{{-- Text Block - PDF View --}}
<div style="margin-bottom: 20px; padding: 15px 0;">
    @if(!empty($block['data']['heading']))
        <h2 style="font-size: 14pt; font-weight: bold; color: #1e293b; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 3px solid #16a34a; display: inline-block;">
            {{ $block['data']['heading'] }}
        </h2>
    @endif

    @if(!empty($block['data']['body']))
        <p style="font-size: 10pt; color: #475569; line-height: 1.6; margin-top: 10px; white-space: pre-wrap;">{{ $block['data']['body'] }}</p>
    @endif
</div>
