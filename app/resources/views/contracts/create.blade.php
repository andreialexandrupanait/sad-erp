<x-app-layout>
    <x-slot name="pageTitle">{{ __('Create Contract') }}</x-slot>

    <div class="p-6 max-w-4xl">
        @if (session('error'))
            <x-ui.alert variant="destructive" class="mb-6">{{ session('error') }}</x-ui.alert>
        @endif

        <x-ui.card>
            <x-ui.card-header>
                <h2 class="text-lg font-semibold">{{ __('New Contract') }}</h2>
                <p class="text-sm text-slate-500">{{ __('Create a new contract from scratch or use a template.') }}</p>
            </x-ui.card-header>
            <x-ui.card-content>
                <form action="{{ route('contracts.store') }}" method="POST" x-data="createContract()">
                    @csrf

                    <div class="space-y-6">
                        {{-- Title --}}
                        <div>
                            <label for="title" class="block text-sm font-medium text-slate-700 mb-1">
                                {{ __('Contract Title') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="title"
                                   name="title"
                                   value="{{ old('title') }}"
                                   required
                                   class="w-full border-slate-200 rounded-md focus:ring-slate-950 focus:border-slate-950 text-sm"
                                   placeholder="{{ __('e.g., Service Agreement, Maintenance Contract') }}">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Template Selection --}}
                        <div>
                            <label for="template_id" class="block text-sm font-medium text-slate-700 mb-1">
                                {{ __('Template') }}
                            </label>
                            <select id="template_id"
                                    name="template_id"
                                    class="w-full border-slate-200 rounded-md focus:ring-slate-950 focus:border-slate-950 text-sm">
                                <option value="">{{ __('No template (start from scratch)') }}</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                        {{ $template->name }}
                                        @if($template->is_default) ({{ __('Default') }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-slate-500">{{ __('Select a template to pre-fill the contract content.') }}</p>
                        </div>

                        {{-- Client Selection --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ __('Client') }}
                            </label>
                            <div @client-selected.window="clientId = $event.detail.id">
                                <x-ui.client-select
                                    name="client_id"
                                    :clients="$clients"
                                    :selected="old('client_id')"
                                    :placeholder="__('Select client or enter manually below')"
                                    :searchPlaceholder="__('Search clients...')"
                                    :allowEmpty="true"
                                    :emptyLabel="__('-- Enter manually --')"
                                    :clientStatuses="$clientStatuses ?? []"
                                />
                            </div>
                        </div>

                        {{-- Manual Client Info (shown when no client selected) --}}
                        <div x-show="!clientId" x-collapse class="space-y-4 p-4 bg-slate-50 rounded-lg">
                            <p class="text-sm font-medium text-slate-600">{{ __('Or enter client details manually:') }}</p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="temp_client_name" class="block text-sm font-medium text-slate-700 mb-1">
                                        {{ __('Client Name') }} <span class="text-red-500" x-show="!clientId">*</span>
                                    </label>
                                    <input type="text"
                                           id="temp_client_name"
                                           name="temp_client_name"
                                           value="{{ old('temp_client_name') }}"
                                           :required="!clientId"
                                           class="w-full border-slate-200 rounded-md focus:ring-slate-950 focus:border-slate-950 text-sm">
                                </div>
                                <div>
                                    <label for="temp_client_company" class="block text-sm font-medium text-slate-700 mb-1">
                                        {{ __('Company Name') }}
                                    </label>
                                    <input type="text"
                                           id="temp_client_company"
                                           name="temp_client_company"
                                           value="{{ old('temp_client_company') }}"
                                           class="w-full border-slate-200 rounded-md focus:ring-slate-950 focus:border-slate-950 text-sm">
                                </div>
                            </div>
                            <div>
                                <label for="temp_client_email" class="block text-sm font-medium text-slate-700 mb-1">
                                    {{ __('Email') }}
                                </label>
                                <input type="email"
                                       id="temp_client_email"
                                       name="temp_client_email"
                                       value="{{ old('temp_client_email') }}"
                                       class="w-full border-slate-200 rounded-md focus:ring-slate-950 focus:border-slate-950 text-sm">
                            </div>
                        </div>

                        @error('temp_client_name')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        {{-- Contract Period --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-slate-700 mb-1">
                                    {{ __('Start Date') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="date"
                                       id="start_date"
                                       name="start_date"
                                       value="{{ old('start_date', date('Y-m-d')) }}"
                                       required
                                       class="w-full border-slate-200 rounded-md focus:ring-slate-950 focus:border-slate-950 text-sm">
                                @error('start_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-slate-700 mb-1">
                                    {{ __('End Date') }}
                                </label>
                                <input type="date"
                                       id="end_date"
                                       name="end_date"
                                       value="{{ old('end_date') }}"
                                       class="w-full border-slate-200 rounded-md focus:ring-slate-950 focus:border-slate-950 text-sm">
                                <p class="mt-1 text-xs text-slate-500">{{ __('Leave empty for indefinite contracts.') }}</p>
                            </div>
                        </div>

                        {{-- Value and Currency --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <label for="total_value" class="block text-sm font-medium text-slate-700 mb-1">
                                    {{ __('Contract Value') }}
                                </label>
                                <input type="number"
                                       id="total_value"
                                       name="total_value"
                                       value="{{ old('total_value') }}"
                                       step="0.01"
                                       min="0"
                                       class="w-full border-slate-200 rounded-md focus:ring-slate-950 focus:border-slate-950 text-sm"
                                       placeholder="0.00">
                            </div>
                            <div>
                                <label for="currency" class="block text-sm font-medium text-slate-700 mb-1">
                                    {{ __('Currency') }} <span class="text-red-500">*</span>
                                </label>
                                <select id="currency"
                                        name="currency"
                                        required
                                        class="w-full border-slate-200 rounded-md focus:ring-slate-950 focus:border-slate-950 text-sm">
                                    <option value="RON" {{ old('currency', 'RON') == 'RON' ? 'selected' : '' }}>RON</option>
                                    <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                                    <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                                </select>
                            </div>
                        </div>

                        {{-- Auto Renew --}}
                        <div class="flex items-center">
                            <input type="checkbox"
                                   id="auto_renew"
                                   name="auto_renew"
                                   value="1"
                                   {{ old('auto_renew') ? 'checked' : '' }}
                                   class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-950">
                            <label for="auto_renew" class="ml-2 text-sm text-slate-700">
                                {{ __('Auto-renew contract when it expires') }}
                            </label>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-8 flex items-center gap-3 pt-6 border-t border-slate-200">
                        <x-ui.button variant="default" type="submit">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('Create Contract') }}
                        </x-ui.button>
                        <x-ui.button variant="outline" type="button" onclick="window.location.href='{{ route('contracts.index') }}'">
                            {{ __('Cancel') }}
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>
    </div>

    @push('scripts')
    <script>
    function createContract() {
        return {
            clientId: '{{ old('client_id', '') }}',
        };
    }
    </script>
    @endpush
</x-app-layout>
