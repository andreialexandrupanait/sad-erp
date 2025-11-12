@props(['quickActions' => []])

@php
    // Helper function to generate button classes based on color
    function getButtonClasses($color) {
        // If color is white/light (#ffffff), use outlined style
        if (strtolower($color) === '#ffffff' || strtolower($color) === '#fff') {
            return 'inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 text-sm font-medium rounded-lg transition-colors shadow-sm';
        }

        // For other colors, create solid colored buttons with hover effect
        // Extract RGB values to create hover color (darker)
        $rgb = sscanf($color, "#%02x%02x%02x");
        if ($rgb) {
            $r = max(0, $rgb[0] - 20);
            $g = max(0, $rgb[1] - 20);
            $b = max(0, $rgb[2] - 20);
            $hoverColor = sprintf("#%02x%02x%02x", $r, $g, $b);
            return "inline-flex items-center gap-2 px-4 py-2 text-white text-sm font-medium rounded-lg transition-colors shadow-sm";
        }

        // Fallback
        return 'inline-flex items-center gap-2 px-4 py-2 bg-slate-600 hover:bg-slate-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm';
    }

    function getButtonStyle($color) {
        if (strtolower($color) === '#ffffff' || strtolower($color) === '#fff') {
            return '';
        }

        $rgb = sscanf($color, "#%02x%02x%02x");
        if ($rgb) {
            $r = max(0, $rgb[0] - 20);
            $g = max(0, $rgb[1] - 20);
            $b = max(0, $rgb[2] - 20);
            $hoverColor = sprintf("#%02x%02x%02x", $r, $g, $b);
            return "background-color: {$color}; --hover-bg: {$hoverColor};";
        }

        return '';
    }
@endphp

<div class="flex items-center gap-2" x-data>
    @forelse($quickActions ?? [] as $action)
        <button
            type="button"
            @click="$dispatch('open-slide-panel', '{{ $action->value }}')"
            class="{{ getButtonClasses($action->color_class) }}"
            style="{{ getButtonStyle($action->color_class) }}"
            @if(strtolower($action->color_class) !== '#ffffff' && strtolower($action->color_class) !== '#fff')
                onmouseover="this.style.backgroundColor=this.style.getPropertyValue('--hover-bg')"
                onmouseout="this.style.backgroundColor='{{ $action->color_class }}'"
            @endif
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                @if($action->value === 'credential-create')
                    {{-- Key icon for Access/Credential --}}
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                @else
                    {{-- Plus icon for all others --}}
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                @endif
            </svg>
            {{ $action->label }}
        </button>
    @empty
        {{-- Fallback if no quick actions are configured --}}
        <p class="text-sm text-slate-500">No quick actions configured</p>
    @endforelse
</div>
