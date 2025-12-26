{{-- Section Block PDF View --}}
@if(!empty($block['data']['title']))
<div style="margin-bottom: 15px;">
    <h2 style="font-size: 18px; font-weight: bold; color: #1e293b; margin: 0;">{{ $block['data']['title'] }}</h2>
</div>
@endif

@if(!empty($block['data']['widgets']))
    @foreach($block['data']['widgets'] as $widget)
        <div style="margin-bottom: 12px;">
            @switch($widget['type'])
                {{-- Text Widget --}}
                @case('text')
                    <p style="font-size: 14px; color: #475569; line-height: 1.6; margin: 0; white-space: pre-wrap;">{{ $widget['data']['content'] ?? '' }}</p>
                    @break

                {{-- Heading Widget --}}
                @case('heading')
                    @php
                        $level = $widget['data']['level'] ?? 'h3';
                        $fontSize = $level === 'h2' ? '20px' : ($level === 'h3' ? '16px' : '14px');
                        $fontWeight = $level === 'h4' ? '500' : 'bold';
                    @endphp
                    <p style="font-size: {{ $fontSize }}; font-weight: {{ $fontWeight }}; color: #1e293b; margin: 0;">{{ $widget['data']['text'] ?? '' }}</p>
                    @break

                {{-- Image Widget --}}
                @case('image')
                    @if(!empty($widget['data']['src']))
                        <div style="text-align: center;">
                            <img src="{{ $widget['data']['src'] }}" alt="{{ $widget['data']['alt'] ?? '' }}" style="max-width: 100%; height: auto;">
                            @if(!empty($widget['data']['caption']))
                                <p style="font-size: 12px; color: #64748b; font-style: italic; margin-top: 5px;">{{ $widget['data']['caption'] }}</p>
                            @endif
                        </div>
                    @endif
                    @break

                {{-- List Widget --}}
                @case('list')
                    @php
                        $listType = $widget['data']['type'] ?? 'bullet';
                        $listStyle = $listType === 'numbered' ? 'decimal' : 'disc';
                    @endphp
                    @if(!empty($widget['data']['items']))
                        <ul style="margin: 0; padding-left: 20px; list-style-type: {{ $listStyle }};">
                            @foreach($widget['data']['items'] as $item)
                                @if(trim($item))
                                    <li style="font-size: 14px; color: #475569; margin-bottom: 4px;">{{ $item }}</li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                    @break

                {{-- Icon + Text Widget --}}
                @case('icon_text')
                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                        <span style="color: #22c55e; font-size: 16px;">✓</span>
                        <p style="font-size: 14px; color: #475569; margin: 0;">{{ $widget['data']['text'] ?? '' }}</p>
                    </div>
                    @break

                {{-- Stat Card Widget --}}
                @case('stat_card')
                    @php
                        $color = $widget['data']['color'] ?? 'green';
                        $bgColor = ['green' => '#f0fdf4', 'blue' => '#eff6ff', 'purple' => '#faf5ff', 'amber' => '#fffbeb'][$color] ?? '#f0fdf4';
                        $textColor = ['green' => '#166534', 'blue' => '#1e40af', 'purple' => '#7e22ce', 'amber' => '#b45309'][$color] ?? '#166534';
                    @endphp
                    <div style="background-color: {{ $bgColor }}; border-radius: 8px; padding: 15px;">
                        <p style="font-size: 24px; font-weight: bold; color: {{ $textColor }}; margin: 0;">{{ $widget['data']['value'] ?? '' }}</p>
                        <p style="font-size: 14px; color: {{ $textColor }}; margin: 5px 0 0 0;">{{ $widget['data']['label'] ?? '' }}</p>
                    </div>
                    @break

                {{-- Feature Box Widget --}}
                @case('feature_box')
                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px;">
                        <p style="font-size: 16px; font-weight: 600; color: #1e293b; margin: 0;">{{ $widget['data']['title'] ?? '' }}</p>
                        @if(!empty($widget['data']['description']))
                            <p style="font-size: 14px; color: #64748b; margin: 8px 0 0 0;">{{ $widget['data']['description'] }}</p>
                        @endif
                    </div>
                    @break

                {{-- Testimonial Widget --}}
                @case('testimonial')
                    <div style="background-color: #f8fafc; border-radius: 8px; padding: 15px; border-left: 4px solid #94a3b8;">
                        <p style="font-size: 14px; color: #475569; font-style: italic; margin: 0;">"{{ $widget['data']['quote'] ?? '' }}"</p>
                        @if(!empty($widget['data']['author']))
                            <p style="font-size: 13px; font-weight: 500; color: #1e293b; margin: 10px 0 0 0;">— {{ $widget['data']['author'] }}</p>
                            @if(!empty($widget['data']['role']))
                                <p style="font-size: 12px; color: #64748b; margin: 2px 0 0 0;">{{ $widget['data']['role'] }}</p>
                            @endif
                        @endif
                    </div>
                    @break

                {{-- Price Box Widget --}}
                @case('price_box')
                    @php
                        $highlighted = $widget['data']['highlighted'] ?? false;
                        $borderColor = $highlighted ? '#3b82f6' : '#e2e8f0';
                        $headerBg = $highlighted ? '#3b82f6' : '#f8fafc';
                        $headerColor = $highlighted ? '#ffffff' : '#1e293b';
                    @endphp
                    <div style="border: 2px solid {{ $borderColor }}; border-radius: 8px; overflow: hidden;">
                        <div style="background-color: {{ $headerBg }}; padding: 12px; text-align: center;">
                            <p style="font-size: 16px; font-weight: 600; color: {{ $headerColor }}; margin: 0;">{{ $widget['data']['title'] ?? '' }}</p>
                        </div>
                        <div style="padding: 15px; text-align: center; border-bottom: 1px solid #e2e8f0;">
                            <span style="font-size: 28px; font-weight: bold; color: #1e293b;">{{ $widget['data']['price'] ?? '' }}</span>
                            <span style="font-size: 14px; color: #64748b;">{{ $widget['data']['period'] ?? '' }}</span>
                        </div>
                        @if(!empty($widget['data']['features']))
                            <div style="padding: 15px;">
                                @foreach($widget['data']['features'] as $feature)
                                    @if(trim($feature))
                                        <p style="font-size: 13px; color: #475569; margin: 4px 0;">✓ {{ $feature }}</p>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                    @break

                {{-- Divider Widget --}}
                @case('divider')
                    @php
                        $style = $widget['data']['style'] ?? 'solid';
                        $borderStyle = $style === 'dashed' ? 'dashed' : ($style === 'dotted' ? 'dotted' : 'solid');
                    @endphp
                    <hr style="border: none; border-top: 1px {{ $borderStyle }} #cbd5e1; margin: 10px 0;">
                    @break

                {{-- Spacer Widget --}}
                @case('spacer')
                    <div style="height: {{ $widget['data']['height'] ?? 24 }}px;"></div>
                    @break

                {{-- Button Widget --}}
                @case('button')
                    @php
                        $btnStyle = $widget['data']['style'] ?? 'primary';
                        $align = $widget['data']['align'] ?? 'left';
                        $bgColor = $btnStyle === 'primary' ? '#2563eb' : ($btnStyle === 'secondary' ? '#475569' : 'transparent');
                        $textColor = $btnStyle === 'outline' ? '#2563eb' : '#ffffff';
                        $border = $btnStyle === 'outline' ? '2px solid #2563eb' : 'none';
                    @endphp
                    <div style="text-align: {{ $align }};">
                        <span style="display: inline-block; background-color: {{ $bgColor }}; color: {{ $textColor }}; border: {{ $border }}; padding: 10px 20px; border-radius: 6px; font-weight: 500; font-size: 14px;">
                            {{ $widget['data']['text'] ?? __('Button') }}
                        </span>
                    </div>
                    @break
            @endswitch
        </div>
    @endforeach
@endif
