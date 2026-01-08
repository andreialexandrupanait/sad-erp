@props([
    'status' => 'new',
    'size' => 'default',
    'showIcon' => true,
    'linkUrl' => null,
])

@php
    $baseClasses = 'inline-flex items-center gap-1 font-medium rounded-md border transition-colors';
    
    $sizeClasses = match($size) {
        'sm' => 'px-1.5 py-0.5 text-xs',
        'lg' => 'px-3 py-1.5 text-sm',
        default => 'px-2 py-1 text-xs',
    };
    
    $statusConfig = match($status) {
        'new' => [
            'bg' => 'bg-emerald-50',
            'text' => 'text-emerald-700',
            'border' => 'border-emerald-200',
            'dot' => 'bg-emerald-500',
            'hover' => '',
            'label' => __('New'),
            'icon' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>',
        ],
        'imported' => [
            'bg' => 'bg-blue-50',
            'text' => 'text-blue-700',
            'border' => 'border-blue-200',
            'dot' => 'bg-blue-500',
            'hover' => $linkUrl ? 'hover:bg-blue-100 cursor-pointer' : '',
            'label' => __('Imported'),
            'icon' => '<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>',
        ],
        'duplicate' => [
            'bg' => 'bg-amber-50',
            'text' => 'text-amber-700',
            'border' => 'border-amber-200',
            'dot' => 'bg-amber-500',
            'hover' => $linkUrl ? 'hover:bg-amber-100 cursor-pointer' : '',
            'label' => __('Duplicate'),
            'icon' => '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>',
        ],
        'skipped' => [
            'bg' => 'bg-slate-50',
            'text' => 'text-slate-600',
            'border' => 'border-slate-200',
            'dot' => 'bg-slate-400',
            'hover' => '',
            'label' => __('Skipped'),
            'icon' => '<path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>',
        ],
        'error' => [
            'bg' => 'bg-red-50',
            'text' => 'text-red-700',
            'border' => 'border-red-200',
            'dot' => 'bg-red-500',
            'hover' => '',
            'label' => __('Error'),
            'icon' => '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>',
        ],
        default => [
            'bg' => 'bg-slate-50',
            'text' => 'text-slate-600',
            'border' => 'border-slate-200',
            'dot' => 'bg-slate-400',
            'hover' => '',
            'label' => $status,
            'icon' => '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>',
        ],
    };
    
    $classes = implode(' ', [
        $baseClasses,
        $sizeClasses,
        $statusConfig['bg'],
        $statusConfig['text'],
        $statusConfig['border'],
        $statusConfig['hover'],
    ]);
@endphp

@if($linkUrl)
    <a 
        href="{{ $linkUrl }}" 
        target="_blank"
        class="{{ $classes }}"
        title="{{ $statusConfig['label'] }}"
        {{ $attributes }}
    >
        @if($showIcon)
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                {!! $statusConfig['icon'] !!}
            </svg>
        @else
            <span class="w-1.5 h-1.5 {{ $statusConfig['dot'] }} rounded-full"></span>
        @endif
        <span>{{ $statusConfig['label'] }}</span>
    </a>
@else
    <span 
        class="{{ $classes }}"
        title="{{ $statusConfig['label'] }}"
        {{ $attributes }}
    >
        @if($showIcon)
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                {!! $statusConfig['icon'] !!}
            </svg>
        @else
            <span class="w-1.5 h-1.5 {{ $statusConfig['dot'] }} rounded-full"></span>
        @endif
        <span>{{ $statusConfig['label'] }}</span>
    </span>
@endif
