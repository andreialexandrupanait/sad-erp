<x-app-layout>
    <x-slot name="pageTitle">{{ __('Internal Accounts') }}</x-slot>

    <x-slot name="headerActions">
        <x-ui.button variant="default" onclick="window.location.href='{{ route('internal-accounts.create') }}'">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('New Account') }}
        </x-ui.button>
    </x-slot>

    <div class="p-6 space-y-6" x-data>
        <!-- Success/Info Messages -->
        @if (session('success'))
            <x-ui.alert variant="success">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>{{ session('success') }}</div>
            </x-ui.alert>
        @endif

        <!-- Statistics Cards -->
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            <!-- Total Accounts - Featured -->
            <div class="rounded-lg border border-slate-200 bg-gradient-to-br from-slate-900 to-slate-800 text-white shadow-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-300">{{ __('Total Accounts') }}</p>
                            <p class="mt-2 text-3xl font-bold">{{ $stats['total_accounts'] }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ __('all accounts') }}</p>
                        </div>
                        <div class="ml-4 flex h-12 w-12 items-center justify-center rounded-lg bg-white/10">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Accounts -->
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-600">{{ __('My Accounts') }}</p>
                            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['my_accounts'] }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ __('owned by me') }}</p>
                        </div>
                        <div class="ml-4 flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Shared -->
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-600">{{ __('Team Shared') }}</p>
                            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['team_accounts'] }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ __('accessible to team') }}</p>
                        </div>
                        <div class="ml-4 flex h-12 w-12 items-center justify-center rounded-lg bg-green-50">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Search and Filters -->
        <x-ui.card>
            <x-ui.card-content>
                <form method="GET" action="{{ route('internal-accounts.index') }}">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <!-- Search -->
                        <div class="flex-1">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <x-ui.input
                                    type="text"
                                    name="search"
                                    value="{{ request('search') }}"
                                    placeholder="{{ __('Search internal accounts') }}"
                                    class="pl-10"
                                />
                            </div>
                        </div>

                        <!-- Ownership Filter -->
                        <div class="w-full sm:w-44">
                            <x-ui.select name="ownership">
                                <option value="">{{ __('All Accounts') }}</option>
                                <option value="mine" {{ request('ownership') == 'mine' ? 'selected' : '' }}>{{ __('My Accounts Only') }}</option>
                                <option value="team" {{ request('ownership') == 'team' ? 'selected' : '' }}>{{ __('Team Shared Only') }}</option>
                            </x-ui.select>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex gap-2">
                            <x-ui.button type="submit" variant="default">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                </svg>
                                {{ __('Search') }}
                            </x-ui.button>
                        </div>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>

        <!-- Accounts Table -->
        <x-ui.card>
            @if ($accounts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full caption-bottom text-sm">
                        <thead class="bg-slate-100">
                            <tr class="border-b border-slate-200">
                                <x-ui.sortable-header column="account_name" label="{{ __('Account Name') }}" />
                                <x-ui.sortable-header column="url" label="{{ __('URL') }}" />
                                <x-ui.sortable-header column="username" label="{{ __('Username') }}" />
                                <th class="px-6 py-4 text-left align-middle font-medium text-slate-500">{{ __('Password') }}</th>
                                <th class="px-6 py-4 text-left align-middle font-medium text-slate-500">{{ __('Access') }}</th>
                                <th class="px-6 py-4 text-left align-middle font-medium text-slate-500">{{ __('Owner') }}</th>
                                <th class="px-6 py-4 text-right align-middle font-medium text-slate-500">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="[&_tr:last-child]:border-0">
                            @foreach ($accounts as $account)
                                <x-ui.table-row x-data="{ showPassword{{ $account->id }}: false }">
                                    <x-ui.table-cell>
                                        <div class="text-sm font-medium text-slate-900">
                                            {{ $account->account_name }}
                                        </div>
                                    </x-ui.table-cell>

                                    <!-- URL Column -->
                                    <x-ui.table-cell>
                                        @if($account->url)
                                            <a href="{{ $account->url }}" target="_blank" rel="noopener noreferrer" class="text-sm text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                </svg>
                                                <span class="truncate max-w-[150px]" title="{{ $account->url }}">{{ parse_url($account->url, PHP_URL_HOST) }}</span>
                                            </a>
                                        @else
                                            <span class="text-sm text-slate-400">-</span>
                                        @endif
                                    </x-ui.table-cell>

                                    <!-- Username with Copy -->
                                    <x-ui.table-cell>
                                        @if($account->username)
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm text-slate-700">{{ $account->username }}</span>
                                                <button
                                                    onclick="copyToClipboard('{{ $account->username }}', 'Username')"
                                                    class="text-slate-400 hover:text-slate-600 transition-colors"
                                                    title="{{ __('Copy username') }}"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-sm text-slate-400">-</span>
                                        @endif
                                    </x-ui.table-cell>

                                    <!-- Password with Toggle and Copy -->
                                    <x-ui.table-cell>
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="text-sm font-mono"
                                                :class="showPassword{{ $account->id }} ? 'text-slate-700' : 'text-slate-500'"
                                                x-text="showPassword{{ $account->id }} ? '{{ $account->password }}' : '{{ $account->masked_password }}'"
                                            ></span>
                                            <button
                                                @click="showPassword{{ $account->id }} = !showPassword{{ $account->id }}"
                                                class="text-slate-400 hover:text-slate-600 transition-colors"
                                                :title="showPassword{{ $account->id }} ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'"
                                            >
                                                <svg x-show="!showPassword{{ $account->id }}" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                <svg x-show="showPassword{{ $account->id }}" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                                </svg>
                                            </button>
                                            <button
                                                x-show="showPassword{{ $account->id }}"
                                                onclick="copyToClipboard('{{ $account->password }}', 'Password')"
                                                class="text-slate-400 hover:text-slate-600 transition-colors"
                                                title="{{ __('Copy password') }}"
                                                style="display: none;"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        @if ($account->team_accessible)
                                            <x-ui.badge variant="success">
                                                {{ __('Team') }}
                                            </x-ui.badge>
                                        @else
                                            <x-ui.badge variant="secondary">
                                                {{ __('Private') }}
                                            </x-ui.badge>
                                        @endif
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        <div class="text-sm text-slate-600">
                                            {{ $account->isOwner() ? __('You') : $account->user->name }}
                                        </div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell class="text-right">
                                        <x-table-actions
                                            :viewUrl="route('internal-accounts.show', $account)"
                                            :editUrl="$account->isOwner() ? route('internal-accounts.edit', $account) : null"
                                            :deleteAction="$account->isOwner() ? route('internal-accounts.destroy', $account) : null"
                                            :deleteConfirm="__('Are you sure you want to delete this internal account?')"
                                        />
                                    </x-ui.table-cell>
                                </x-ui.table-row>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($accounts->hasPages())
                    <div class="bg-slate-100 px-6 py-4 border-t border-slate-200">
                        {{ $accounts->links() }}
                    </div>
                @endif
            @else
                <div class="px-6 py-16 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-slate-900">{{ __('No internal accounts') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Get started by creating your first internal account') }}</p>
                    <div class="mt-6">
                        <x-ui.button variant="default" onclick="window.location.href='{{ route('internal-accounts.create') }}'">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('Add Internal Account') }}
                        </x-ui.button>
                    </div>
                </div>
            @endif
        </x-ui.card>
    </div>

    <!-- Toast Notifications -->
    <x-toast />

    @push('scripts')
    <script>
        function copyToClipboard(text, label) {
            if (!text) {
                console.warn('Nothing to copy');
                return;
            }

            navigator.clipboard.writeText(text).then(function() {
                // Show success toast
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        message: (label || '{{ __('Text') }}') + ' {{ __('copied to clipboard') }}'
                    }
                }));
            }).catch(function(err) {
                console.error('Failed to copy:', err);
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'error',
                        message: '{{ __('Failed to copy to clipboard') }}'
                    }
                }));
            });
        }
    </script>
    @endpush
</x-app-layout>
