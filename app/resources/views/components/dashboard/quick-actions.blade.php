@props(['quickActions' => []])

@php
    if (!function_exists('qa_getEventForAction')) {
        function qa_getEventForAction($actionValue) {
            $eventMap = [
                'client-create' => 'open-quick-add-client',
                'credential-create' => 'open-quick-add-credential',
                'domain-create' => 'open-quick-add-domain',
                'subscription-create' => 'open-quick-add-subscription',
                'expense-create' => 'open-quick-add-expense',
                'revenue-create' => 'open-quick-add-revenue',
            ];
            return $eventMap[$actionValue] ?? null;
        }
    }

    if (!function_exists('qa_getRouteForAction')) {
        function qa_getRouteForAction($actionValue) {
            $routeMap = [
                'client-create' => 'clients.create',
                'domain-create' => 'domains.create',
                'subscription-create' => 'subscriptions.create',
                'credential-create' => 'credentials.create',
                'expense-create' => 'financial.expenses.create',
                'revenue-create' => 'financial.revenues.create',
            ];
            return $routeMap[$actionValue] ?? '#';
        }
    }

    if (!function_exists('qa_isWhiteColor')) {
        function qa_isWhiteColor($color) {
            $c = strtolower(trim($color));
            return $c === '#ffffff' || $c === '#fff' || $c === 'white';
        }
    }

    if (!function_exists('qa_getButtonStyle')) {
        function qa_getButtonStyle($color) {
            if (qa_isWhiteColor($color)) {
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
            return "background-color: #475569;";
        }
    }

    if (!function_exists('qa_getButtonClass')) {
        function qa_getButtonClass($color) {
            if (qa_isWhiteColor($color)) {
                return 'inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 text-sm font-medium rounded-lg shadow-sm transition-colors';
            }
            return 'inline-flex items-center gap-2 px-4 py-2 text-white text-sm font-medium rounded-lg shadow-sm transition-colors';
        }
    }
@endphp

{{-- Mobile: Simple dropdown --}}
<div class="block md:hidden relative" x-data="{ open: false }">
    <button 
        @click="open = !open" 
        type="button" 
        class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg shadow-sm"
    >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Adaugă
        <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    
    <div 
        x-show="open" 
        @click.away="open = false"
        x-transition
        class="absolute right-0 mt-2 w-52 bg-white rounded-lg shadow-lg border border-slate-200 py-1 z-50"
    >
        @forelse($quickActions ?? [] as $action)
            @php $eventName = qa_getEventForAction($action->value); @endphp
            <button
                type="button"
                @if($eventName)
                    @click="$dispatch('{{ $eventName }}'); open = false"
                @else
                    onclick="window.location.href='{{ route(qa_getRouteForAction($action->value)) }}'"
                @endif
                class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-100 text-left"
            >
                <span class="w-3 h-3 rounded-full flex-shrink-0 border border-slate-200" style="background-color: {{ $action->color_class ?? '#6b7280' }};"></span>
                {{ $action->label }}
            </button>
        @empty
            <p class="px-4 py-2 text-sm text-slate-500">Nicio acțiune</p>
        @endforelse
    </div>
</div>

{{-- Desktop: Inline buttons --}}
<div class="hidden md:flex items-center gap-2">
    @forelse($quickActions ?? [] as $action)
        @php 
            $eventName = qa_getEventForAction($action->value);
            $isWhite = qa_isWhiteColor($action->color_class ?? '');
        @endphp
        <button
            type="button"
            @if($eventName)
                @click="$dispatch('{{ $eventName }}')"
            @else
                onclick="window.location.href='{{ route(qa_getRouteForAction($action->value)) }}'"
            @endif
            class="{{ qa_getButtonClass($action->color_class ?? '') }}"
            style="{{ qa_getButtonStyle($action->color_class ?? '') }}"
            @if(!$isWhite)
                onmouseover="this.style.backgroundColor=this.style.getPropertyValue('--hover-bg')"
                onmouseout="this.style.backgroundColor='{{ $action->color_class }}'"
            @endif
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ $action->label }}
        </button>
    @empty
        <p class="text-sm text-slate-500">Nicio acțiune configurată</p>
    @endforelse
</div>
