@props([
    'status' => 'new',
    'rowIndex' => null,
    'duplicateId' => null,
    'duplicateName' => null,
])

@php
    $isNew = $status === 'new';
    $isImported = $status === 'imported';
    $isDuplicate = $status === 'duplicate';
    $isSkipped = $status === 'skipped';
@endphp

<div 
    class="row-actions flex items-center justify-end gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity"
    x-data="{ menuOpen: false }"
>
    @if($isNew)
        {{-- Edit description --}}
        <button 
            type="button"
            @click="$dispatch('edit-description', { rowIndex: {{ $rowIndex }} })"
            class="p-1.5 rounded text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"
            title="{{ __('Edit description') }}"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
            </svg>
        </button>
        
        {{-- Duplicate row --}}
        <button 
            type="button"
            @click="$dispatch('duplicate-row', { rowIndex: {{ $rowIndex }} })"
            class="p-1.5 rounded text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"
            title="{{ __('Duplicate') }}"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
        </button>
        
        {{-- Skip/Exclude --}}
        <button 
            type="button"
            @click="$dispatch('skip-row', { rowIndex: {{ $rowIndex }} })"
            class="p-1.5 rounded text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-colors"
            title="{{ __('Skip this transaction') }}"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
        </button>
        
    @elseif($isImported || $isDuplicate)
        {{-- View linked expense --}}
        @if($duplicateId)
        <a 
            href="{{ route('financial.expenses.show', $duplicateId) }}"
            target="_blank"
            class="p-1.5 rounded text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
            title="{{ __('View linked expense') }}: {{ $duplicateName ?? '' }}"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
        </a>
        @endif
        
        {{-- Re-import (override) --}}
        <button 
            type="button"
            @click="$dispatch('reimport-row', { rowIndex: {{ $rowIndex }} })"
            class="p-1.5 rounded text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-colors"
            title="{{ __('Import anyway (create duplicate)') }}"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
        </button>
        
    @elseif($isSkipped)
        {{-- Restore --}}
        <button 
            type="button"
            @click="$dispatch('restore-row', { rowIndex: {{ $rowIndex }} })"
            class="p-1.5 rounded text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors"
            title="{{ __('Restore') }}"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
            </svg>
        </button>
    @endif
    
    {{-- More actions dropdown --}}
    <div class="relative" x-data="{ open: false }">
        <button 
            type="button"
            @click="open = !open"
            class="p-1.5 rounded text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"
            title="{{ __('More actions') }}"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
            </svg>
        </button>
        
        <div 
            x-show="open" 
            x-cloak
            @click.away="open = false"
            x-transition
            class="absolute right-0 mt-1 w-48 bg-white border border-slate-200 rounded-lg shadow-lg z-50 py-1"
        >
            <button 
                type="button"
                @click="$dispatch('split-transaction', { rowIndex: {{ $rowIndex }} }); open = false"
                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"
            >
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                {{ __('Split transaction') }}
            </button>
            
            <button 
                type="button"
                @click="$dispatch('add-note', { rowIndex: {{ $rowIndex }} }); open = false"
                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"
            >
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                </svg>
                {{ __('Add note') }}
            </button>
            
            <div class="border-t border-slate-100 my-1"></div>
            
            <button 
                type="button"
                @click="$dispatch('remove-row', { rowIndex: {{ $rowIndex }} }); open = false"
                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                {{ __('Remove from list') }}
            </button>
        </div>
    </div>
</div>
