@props([
    'domain' => null,
    'clients' => [],
    'registrars' => [],
    'statuses' => [],
    'clientStatuses' => [],
    'prefix' => '',
    'compact' => false,
])

@php
    $p = $prefix;
    $gridClass = $compact ? 'grid-cols-1' : 'grid-cols-1 sm:grid-cols-6';
    $colSpan3 = $compact ? '' : 'sm:col-span-3';
    $colSpan6 = $compact ? '' : 'sm:col-span-6';
    $defaultStatus = $statuses->first()?->value ?? 'Active';
@endphp

<div class="grid {{ $gridClass }} gap-x-6 gap-y-5">
    <!-- Domain Name (Required) -->
    <div class="{{ $colSpan6 }}">
        <x-ui.label :for="$p.'domain_name'">
            {{ __('Domain Name') }} <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-1.5">
            <x-ui.input
                type="text"
                :name="$p.'domain_name'"
                :id="$p.'domain_name'"
                required
                :value="old($p.'domain_name', $domain->domain_name ?? '')"
                :placeholder="__('example.com')"
            />
        </div>
        @error($p.'domain_name')
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
                :selected="old($p.'client_id', $domain->client_id ?? '')"
                :placeholder="__('Select client (optional)')"
                :emptyLabel="__('No Client')"
                :clientStatuses="$clientStatuses"
            />
        </div>
        @error($p.'client_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Registrar -->
    <div class="{{ $colSpan3 }}">
        <x-ui.label :for="$p.'registrar'">{{ __('Registrar') }}</x-ui.label>
        <div class="mt-1.5">
            <x-ui.nomenclature-select
                :name="$p.'registrar'"
                category="domain_registrars"
                :options="$registrars"
                :selected="old($p.'registrar', $domain->registrar ?? '')"
                :placeholder="__('Select registrar')"
                :hasColors="true"
                :allowEmpty="true"
            />
        </div>
        @error($p.'registrar')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Registration Date -->
    <div class="{{ $colSpan3 }}">
        <x-ui.label :for="$p.'registration_date'">{{ __('Registration Date') }}</x-ui.label>
        <div class="mt-1.5">
            <x-ui.input
                type="date"
                :name="$p.'registration_date'"
                :id="$p.'registration_date'"
                :value="old($p.'registration_date', $domain ? $domain->registration_date?->format('Y-m-d') : '')"
            />
        </div>
        @error($p.'registration_date')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Expiry Date -->
    <div class="{{ $colSpan3 }}">
        <x-ui.label :for="$p.'expiry_date'">
            {{ __('Expiry Date') }} <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-1.5">
            <x-ui.input
                type="date"
                :name="$p.'expiry_date'"
                :id="$p.'expiry_date'"
                required
                :value="old($p.'expiry_date', $domain ? $domain->expiry_date?->format('Y-m-d') : '')"
            />
        </div>
        @error($p.'expiry_date')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Annual Cost -->
    <div class="{{ $colSpan3 }}">
        <x-ui.label :for="$p.'annual_cost'">{{ __('Annual Cost ($)') }}</x-ui.label>
        <div class="mt-1.5">
            <x-ui.input
                type="number"
                step="0.01"
                :name="$p.'annual_cost'"
                :id="$p.'annual_cost'"
                :value="old($p.'annual_cost', $domain->annual_cost ?? '')"
                :placeholder="__('15.00')"
            />
        </div>
        @error($p.'annual_cost')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Status -->
    <div class="{{ $colSpan3 }}">
        <x-ui.label :for="$p.'status'">
            {{ __('Status') }} <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-1.5">
            <x-ui.nomenclature-select
                :name="$p.'status'"
                category="domain_statuses"
                :options="$statuses"
                :selected="old($p.'status', $domain->status ?? $defaultStatus)"
                :placeholder="__('Select status')"
                :hasColors="true"
                :required="true"
            />
        </div>
        @error($p.'status')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Auto Renew -->
    <div class="{{ $colSpan6 }}">
        <div class="flex items-start">
            <div class="flex h-6 items-center">
                <input
                    type="checkbox"
                    :name="$p.'auto_renew'"
                    :id="$p.'auto_renew'"
                    value="1"
                    {{ old($p.'auto_renew', $domain->auto_renew ?? false) ? 'checked' : '' }}
                    class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                >
            </div>
            <div class="ml-3 text-sm leading-6">
                <label :for="$p.'auto_renew'" class="font-medium text-slate-900">{{ __('Auto-renew enabled') }}</label>
                @if(!$compact)
                    <p class="text-slate-500">{{ __('Domain will automatically renew before expiry') }}</p>
                @endif
            </div>
        </div>
    </div>

    @if(!$compact)
    <!-- Notes -->
    <div class="{{ $colSpan6 }}">
        <x-ui.label :for="$p.'notes'">{{ __('Notes') }}</x-ui.label>
        <div class="mt-1.5">
            <x-ui.textarea :name="$p.'notes'" :id="$p.'notes'" rows="3" :placeholder="__('Additional information...')">{{ old($p.'notes', $domain->notes ?? '') }}</x-ui.textarea>
        </div>
        @error($p.'notes')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
    @endif
</div>
