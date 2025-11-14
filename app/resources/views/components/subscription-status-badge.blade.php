@props(['status'])

@php
    $badgeConfig = [
        'active' => [
            'classes' => 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20',
            'label' => 'Activă',
            'icon' => '<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>'
        ],
        'paused' => [
            'classes' => 'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-600/20',
            'label' => 'Suspendată',
            'icon' => '<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>'
        ],
        'cancelled' => [
            'classes' => 'bg-gray-50 text-gray-700 ring-1 ring-inset ring-gray-600/20',
            'label' => 'Anulată',
            'icon' => '<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>'
        ],
    ];

    $config = $badgeConfig[$status] ?? [
        'classes' => 'bg-gray-50 text-gray-700 ring-1 ring-inset ring-gray-600/20',
        'label' => ucfirst($status ?? 'unknown'),
        'icon' => '<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>'
    ];
@endphp

<span class="inline-flex items-center gap-x-1.5 rounded-md px-2 py-1 text-xs font-medium {{ $config['classes'] }}">
    {!! $config['icon'] !!}
    {{ $config['label'] }}
</span>
