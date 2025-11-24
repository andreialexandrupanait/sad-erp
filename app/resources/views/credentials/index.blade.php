<x-app-layout>
    <x-slot name="pageTitle">Acces & parole</x-slot>

    <x-slot name="headerActions">
        <x-ui.button variant="default" onclick="window.location.href='{{ route('credentials.create') }}'">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Acces nou
        </x-ui.button>
    </x-slot>

    <div class="p-6 space-y-6" x-data>
        <!-- Success Messages -->
        @if (session('success'))
            <x-ui.alert variant="success">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>{{ session('success') }}</div>
            </x-ui.alert>
        @endif

        <!-- Search and Filter Form -->
        <x-ui.card>
            <x-ui.card-content>
                <form method="GET" action="{{ route('credentials.index') }}">
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
                                    placeholder="{{ __('Search credentials') }}"
                                    class="pl-10"
                                />
                            </div>
                        </div>

                        <!-- Client Filter -->
                        <div class="w-full sm:w-52">
                            <x-ui.select name="client_id">
                                <option value="">{{ __('All Clients') }}</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->display_name }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </div>

                        <!-- Platform Filter -->
                        <div class="w-full sm:w-48">
                            <x-ui.select name="platform">
                                <option value="">{{ __('All Platforms') }}</option>
                                @foreach ($platforms as $platform)
                                    <option value="{{ $platform->value }}" {{ request('platform') == $platform->value ? 'selected' : '' }}>
                                        {{ $platform->label }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-2">
                            <x-ui.button type="submit" variant="default">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                </svg>
                                {{ __('Search') }}
                            </x-ui.button>
                            @if(request()->has('search') || request()->has('client_id') || request()->has('platform'))
                                <x-ui.button variant="outline" onclick="window.location.href='{{ route('credentials.index') }}'">
                                    {{ __('Clear') }}
                                </x-ui.button>
                            @endif
                        </div>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>

        <!-- Credentials Table -->
        <x-ui.card>
            @if($credentials->isEmpty())
                <div class="px-6 py-16 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-slate-900">{{ __('No credentials') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Get started by creating your first credential') }}</p>
                    <div class="mt-6">
                        <x-ui.button variant="default" onclick="window.location.href='{{ route('credentials.create') }}'">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('Add Credential') }}
                        </x-ui.button>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full caption-bottom text-sm">
                        <thead class="[&_tr]:border-b">
                            <tr class="border-b transition-colors hover:bg-slate-50/50">
                                <x-ui.sortable-header column="client_id" label="{{ __('Client') }}" />
                                <x-ui.sortable-header column="platform" label="{{ __('Platform') }}" />
                                <x-ui.sortable-header column="username" label="{{ __('Username') }}" />
                                <th class="h-12 px-4 text-left align-middle font-medium text-slate-500">{{ __('Password') }}</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-slate-500">{{ __('URL') }}</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-slate-500">{{ __('Last Accessed') }}</th>
                                <th class="h-12 px-4 text-right align-middle font-medium text-slate-500">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="[&_tr:last-child]:border-0">
                            @foreach ($credentials as $credential)
                                <x-ui.table-row>
                                    <x-ui.table-cell>
                                        <div class="font-medium text-slate-900">
                                            {{ $credential->client->display_name }}
                                        </div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        <x-ui.badge variant="secondary">
                                            {{ $credential->platform }}
                                        </x-ui.badge>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        @if($credential->username)
                                            <div class="flex items-center gap-2"
                                                 x-data="{
                                                     copied: false,
                                                     async copyUsername() {
                                                         try {
                                                             await navigator.clipboard.writeText('{{ addslashes($credential->username) }}');
                                                             this.copied = true;
                                                             setTimeout(() => this.copied = false, 2000);
                                                         } catch (err) {
                                                             console.error('Failed to copy:', err);
                                                         }
                                                     }
                                                 }">
                                                <span class="text-sm text-slate-700">
                                                    {{ $credential->username }}
                                                </span>
                                                <button @click="copyUsername()"
                                                        type="button"
                                                        class="inline-flex items-center justify-center h-7 w-7 rounded-md text-slate-400 hover:text-slate-900 hover:bg-slate-100 transition-colors"
                                                        :title="copied ? 'Copied!' : 'Copy username'">
                                                    <svg x-show="!copied"
                                                         class="h-3.5 w-3.5"
                                                         fill="none"
                                                         stroke="currentColor"
                                                         viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                    </svg>
                                                    <svg x-show="copied"
                                                         x-cloak
                                                         class="h-3.5 w-3.5 text-green-600"
                                                         fill="none"
                                                         stroke="currentColor"
                                                         viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        @else
                                            <div class="text-sm text-slate-700">-</div>
                                        @endif
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        <div class="flex items-center gap-2"
                                             x-data="{
                                                 copied: false,
                                                 async copyPassword() {
                                                     try {
                                                         await navigator.clipboard.writeText('{{ addslashes($credential->password) }}');
                                                         this.copied = true;
                                                         setTimeout(() => this.copied = false, 2000);
                                                     } catch (err) {
                                                         console.error('Failed to copy:', err);
                                                     }
                                                 }
                                             }">
                                            <span class="text-sm font-mono text-slate-500">
                                                {{ $credential->masked_password }}
                                            </span>
                                            <button @click="copyPassword()"
                                                    type="button"
                                                    class="inline-flex items-center justify-center h-7 w-7 rounded-md text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-colors"
                                                    :title="copied ? 'Copied!' : 'Copy password'">
                                                <svg x-show="!copied"
                                                     class="h-4 w-4"
                                                     fill="none"
                                                     stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                                <svg x-show="copied"
                                                     x-cloak
                                                     class="h-4 w-4 text-green-600"
                                                     fill="none"
                                                     stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        @if ($credential->url)
                                            <a href="{{ $credential->url }}" target="_blank" class="text-sm text-slate-600 hover:text-slate-900 underline truncate block max-w-xs">
                                                {{ $credential->url }}
                                            </a>
                                        @else
                                            <span class="text-sm text-slate-500">-</span>
                                        @endif
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        <div class="text-sm text-slate-700">
                                            {{ $credential->last_accessed_at ? $credential->last_accessed_at->diffForHumans() : __('Never') }}
                                        </div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell class="text-right">
                                        <x-table-actions
                                            :viewUrl="route('credentials.show', $credential)"
                                            :editUrl="route('credentials.edit', $credential)"
                                            :deleteAction="route('credentials.destroy', $credential)"
                                            :deleteConfirm="__('Are you sure you want to delete this credential?')"
                                        />
                                    </x-ui.table-cell>
                                </x-ui.table-row>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($credentials->hasPages())
                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
                        {{ $credentials->links() }}
                    </div>
                @endif
            @endif
        </x-ui.card>
    </div>

    <!-- Toast Notifications -->
    <x-toast />
</x-app-layout>
