@props(['service' => null, 'action', 'method' => 'POST'])

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <x-unsaved-form-warning />

    <x-ui.card>
        <x-ui.card-content>
            <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                <!-- Name (Required) -->
                <div class="sm:col-span-6">
                    <x-ui.label for="name">
                        {{ __('Nume serviciu') }} <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="text"
                            name="name"
                            id="name"
                            required
                            placeholder="{{ __('ex: Dezvoltare web, Consultanță, Design grafic') }}"
                            value="{{ old('name', $service->name ?? '') }}"
                        />
                    </div>
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="sm:col-span-6">
                    <x-ui.label for="description">{{ __('Descriere') }}</x-ui.label>
                    <div class="mt-2">
                        <x-ui.textarea
                            name="description"
                            id="description"
                            rows="3"
                            placeholder="{{ __('Descrieți pe scurt serviciul oferit...') }}"
                        >{{ old('description', $service->description ?? '') }}</x-ui.textarea>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">{{ __('O descriere clară ajută la identificarea rapidă a serviciului') }}</p>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Pricing Section Header -->
                <div class="sm:col-span-6 pt-4">
                    <h3 class="text-sm font-semibold text-slate-900 border-b border-slate-200 pb-2">{{ __('Tarifare') }}</h3>
                </div>

                <!-- Default Rate -->
                <div class="sm:col-span-2">
                    <x-ui.label for="default_rate">{{ __('Tarif implicit') }}</x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="number"
                            name="default_rate"
                            id="default_rate"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            value="{{ old('default_rate', $service->default_rate ?? '') }}"
                        />
                    </div>
                    @error('default_rate')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Currency -->
                <div class="sm:col-span-2">
                    <x-ui.label for="currency">{{ __('Moneda') }}</x-ui.label>
                    <div class="mt-2">
                        <x-ui.select name="currency" id="currency">
                            @php $currentCurrency = old('currency', $service->currency ?? 'RON'); @endphp
                            <option value="RON" {{ $currentCurrency === 'RON' ? 'selected' : '' }}>RON - Leu românesc</option>
                            <option value="EUR" {{ $currentCurrency === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                            <option value="USD" {{ $currentCurrency === 'USD' ? 'selected' : '' }}>USD - Dolar american</option>
                        </x-ui.select>
                    </div>
                    @error('currency')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Unit -->
                <div class="sm:col-span-2">
                    <x-ui.label for="unit">{{ __('Unitate de măsură') }}</x-ui.label>
                    <div class="mt-2">
                        <x-ui.select name="unit" id="unit">
                            @php $currentUnit = old('unit', $service->unit ?? 'ora'); @endphp
                            @foreach(\App\Models\Service::UNITS as $value => $label)
                                <option value="{{ $value }}" {{ $currentUnit === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Unitatea folosită pentru calculul tarifului') }}</p>
                    @error('unit')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="sm:col-span-6 pt-4">
                    <div class="flex items-start">
                        <div class="flex h-6 items-center">
                            <input
                                type="checkbox"
                                name="is_active"
                                id="is_active"
                                value="1"
                                {{ old('is_active', $service->is_active ?? true) ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                            >
                        </div>
                        <div class="ml-3 text-sm leading-6">
                            <label for="is_active" class="font-medium text-slate-900">{{ __('Serviciu activ') }}</label>
                            <p class="text-slate-500">{{ __('Serviciile inactive nu vor apărea în listele de selecție') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.card-content>

        <div class="flex items-center justify-end gap-x-4 border-t border-slate-200 px-4 py-4 sm:px-8 bg-slate-50">
            <x-ui.button type="button" variant="ghost" onclick="window.location.href='{{ route('settings.services') }}'">
                {{ __('Anulează') }}
            </x-ui.button>
            <x-ui.button type="submit" variant="default">
                {{ $service ? __('Actualizează serviciu') : __('Creează serviciu') }}
            </x-ui.button>
        </div>
    </x-ui.card>
</form>
