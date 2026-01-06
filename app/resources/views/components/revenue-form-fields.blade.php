@props([
    'revenue' => null,
    'clients' => [],
    'currencies' => [],
    'clientStatuses' => [],
    'prefix' => '',
    'compact' => false,
])

@php
    $p = $prefix;
    $gridClass = $compact ? 'grid-cols-1' : 'grid-cols-1 sm:grid-cols-6';
    $colSpan3 = $compact ? '' : 'sm:col-span-3';
    $colSpan6 = $compact ? '' : 'sm:col-span-6';
    $currentCurrency = old($p.'currency', $revenue->currency ?? 'RON');
@endphp

<div class="grid {{ $gridClass }} gap-x-6 gap-y-5">
    <!-- Document Name (Required) -->
    <div class="{{ $colSpan6 }}">
        <x-ui.label :for="$p.'document_name'">
            {{ __('Nume document') }} <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-1.5">
            <x-ui.input
                type="text"
                :name="$p.'document_name'"
                :id="$p.'document_name'"
                required
                :placeholder="__('Enter revenue description')"
                :value="old($p.'document_name', $revenue->document_name ?? '')"
            />
        </div>
        @error($p.'document_name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Amount -->
    <div class="{{ $colSpan3 }}">
        <x-ui.label :for="$p.'amount'">
            {{ __('Sumă') }} <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-1.5">
            <x-ui.input
                type="number"
                step="0.01"
                :name="$p.'amount'"
                :id="$p.'amount'"
                required
                :placeholder="__('0.00')"
                :value="old($p.'amount', $revenue->amount ?? '')"
            />
        </div>
        @error($p.'amount')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Currency -->
    <div class="{{ $colSpan3 }}">
        <x-ui.label :for="$p.'currency'">
            {{ __('Valută') }} <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-1.5">
            <select name="{{ $p }}currency" id="{{ $p }}currency" required class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm ring-offset-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2">
                @foreach($currencies as $currency)
                    <option value="{{ $currency->value }}" {{ $currentCurrency == $currency->value ? 'selected' : '' }}>
                        {{ $currency->label }}
                    </option>
                @endforeach
            </select>
        </div>
        @error($p.'currency')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Date -->
    <div class="{{ $colSpan3 }}">
        <x-ui.label :for="$p.'occurred_at'">
            {{ __('Dată') }} <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-1.5">
            <x-ui.input
                type="date"
                :name="$p.'occurred_at'"
                :id="$p.'occurred_at'"
                required
                :value="old($p.'occurred_at', $revenue ? $revenue->occurred_at?->format('Y-m-d') : now()->format('Y-m-d'))"
            />
        </div>
        @error($p.'occurred_at')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Client -->
    <div class="{{ $colSpan3 }}">
        <x-ui.label :for="$p.'client_id'">{{ __('Client') }}</x-ui.label>
        <div class="mt-1.5">
            <x-ui.client-select
                :name="$p.'client_id'"
                :clients="$clients"
                :selected="old($p.'client_id', $revenue->client_id ?? '')"
                :placeholder="__('Selectează client (opțional)')"
                :emptyLabel="__('Fără client')"
                :clientStatuses="$clientStatuses"
            />
        </div>
        @error($p.'client_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @if(!$compact)
    <!-- Notes -->
    <div class="{{ $colSpan6 }}">
        <x-ui.label :for="$p.'note'">{{ __('Notă') }}</x-ui.label>
        <div class="mt-1.5">
            <x-ui.textarea :name="$p.'note'" :id="$p.'note'" rows="3" :placeholder="__('Additional notes (optional)')">{{ old($p.'note', $revenue->note ?? '') }}</x-ui.textarea>
        </div>
        @error($p.'note')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
    @endif
</div>
