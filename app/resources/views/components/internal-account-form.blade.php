@props(['account' => null, 'action', 'method' => 'POST'])

<form method="POST" action="{{ $action }}" class="space-y-6" x-data="{ showPass: false }">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <x-ui.card-content>
            <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">

                <!-- Account/Application Name (Required) -->
                <div class="sm:col-span-6 field-wrapper">
                    <x-ui.label for="nume_cont_aplicatie">
                        Account / Application Name <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="text"
                            name="nume_cont_aplicatie"
                            id="nume_cont_aplicatie"
                            required
                            value="{{ old('nume_cont_aplicatie', $account->nume_cont_aplicatie ?? '') }}"
                            placeholder="e.g., Company Bank Account, AWS Root"
                        />
                    </div>
                    @error('nume_cont_aplicatie')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Platform (Required) -->
                <div class="sm:col-span-6 field-wrapper">
                    <x-ui.label for="platforma">
                        Platform <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="text"
                            name="platforma"
                            id="platforma"
                            required
                            value="{{ old('platforma', $account->platforma ?? '') }}"
                            placeholder="e.g., Bank Account, CRM System, Email Service"
                        />
                    </div>
                    @error('platforma')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- URL -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="url">URL</x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="url"
                            name="url"
                            id="url"
                            value="{{ old('url', $account->url ?? '') }}"
                            placeholder="https://example.com/login"
                        />
                    </div>
                    @error('url')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Username/Email -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="username">Username / Email</x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="text"
                            name="username"
                            id="username"
                            value="{{ old('username', $account->username ?? '') }}"
                            placeholder="admin or email@company.com"
                        />
                    </div>
                    @error('username')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="sm:col-span-6 field-wrapper">
                    <x-ui.label for="password">Password</x-ui.label>
                    <div class="mt-2 relative">
                        <input
                            x-bind:type="showPass ? 'text' : 'password'"
                            name="password"
                            id="password"
                            value="{{ old('password', $account && isset($account->id) ? '' : '') }}"
                            placeholder="{{ $account ? 'Leave blank to keep current password' : 'Enter password' }}"
                            class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 pr-10 text-sm ring-offset-white file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        />
                        <button
                            type="button"
                            @click="showPass = !showPass"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-500 hover:text-slate-700"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    @if($account)
                        <p class="mt-1 text-xs text-slate-500">Leave blank to keep the current password</p>
                    @endif
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Team Accessible -->
                <div class="sm:col-span-6 field-wrapper">
                    <div class="flex items-start">
                        <div class="flex h-6 items-center">
                            <input
                                type="checkbox"
                                name="accesibil_echipei"
                                id="accesibil_echipei"
                                value="1"
                                {{ old('accesibil_echipei', $account->accesibil_echipei ?? false) ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                            >
                        </div>
                        <div class="ml-3 text-sm leading-6">
                            <label for="accesibil_echipei" class="font-medium text-slate-900">Make accessible to team</label>
                            <p class="text-slate-500">Allow all team members to view this account (you remain the owner)</p>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="sm:col-span-6 field-wrapper">
                    <x-ui.label for="notes">Notes</x-ui.label>
                    <div class="mt-2">
                        <x-ui.textarea name="notes" id="notes" rows="3" placeholder="Additional information, recovery codes, etc.">{{ old('notes', $account->notes ?? '') }}</x-ui.textarea>
                    </div>
                    @error('notes')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </x-ui.card-content>

        <div class="flex items-center justify-end gap-x-6 border-t border-slate-200 px-4 py-4 sm:px-8 bg-slate-50">
            <x-ui.button type="button" variant="ghost" onclick="window.location.href='{{ route('internal-accounts.index') }}'">
                Cancel
            </x-ui.button>
            <x-ui.button type="submit" variant="default">
                {{ $account ? 'Update Account' : 'Create Account' }}
            </x-ui.button>
        </div>
    </x-ui.card>
</form>
