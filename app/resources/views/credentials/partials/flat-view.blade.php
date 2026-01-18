{{-- Flat Table View for Credentials --}}
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
                <thead class="bg-slate-100">
                    <tr class="border-b border-slate-200">
                        <th class="px-6 py-4 text-left align-middle font-medium text-slate-500 w-12">
                            <x-bulk-checkbox x-model="selectAll" @change="toggleAll" />
                        </th>
                        <x-ui.sortable-header column="client_id" label="{{ __('Client') }}" />
                        <x-ui.sortable-header column="site_name" label="{{ __('Site') }}" />
                        <th class="px-6 py-4 text-left align-middle font-medium text-slate-500">{{ __('Type') }}</th>
                        <x-ui.sortable-header column="platform" label="{{ __('Platform') }}" />
                        <x-ui.sortable-header column="username" label="{{ __('Username') }}" />
                        <th class="px-6 py-4 text-left align-middle font-medium text-slate-500">{{ __('Password') }}</th>
                        <th class="px-6 py-4 text-left align-middle font-medium text-slate-500">{{ __('Website') }}</th>
                        <th class="px-6 py-4 text-right align-middle font-medium text-slate-500">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="[&_tr:last-child]:border-0">
                    @foreach ($credentials as $credential)
                        <x-ui.table-row data-selectable data-credential-id="{{ $credential->id }}">
                            <x-ui.table-cell>
                                <x-bulk-checkbox
                                    @change="toggleItem({{ $credential->id }})"
                                    x-bind:checked="selectedIds.includes({{ $credential->id }})"
                                />
                            </x-ui.table-cell>
                            <x-ui.table-cell>
                                <a href="{{ route('clients.show', $credential->client) }}" class="font-medium text-slate-900 hover:text-blue-600">
                                    {{ $credential->client->display_name }}
                                </a>
                            </x-ui.table-cell>
                            <x-ui.table-cell>
                                @if($credential->site_name)
                                    <span class="font-medium text-slate-700">{{ $credential->site_name }}</span>
                                @else
                                    <span class="text-slate-400 text-sm">-</span>
                                @endif
                            </x-ui.table-cell>
                            <x-ui.table-cell>
                                <x-ui.badge variant="{{ $credential->type_badge_color }}">
                                    {{ __($credential->type_label) }}
                                </x-ui.badge>
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
                                                     await navigator.clipboard.writeText(@js($credential->username));
                                                     this.copied = true;
                                                     setTimeout(() => this.copied = false, 2000);
                                                 } catch (err) {
                                                     console.error('Failed to copy:', err);
                                                 }
                                             }
                                         }">
                                        <span class="text-sm text-slate-700 truncate max-w-[150px]" title="{{ $credential->username }}">
                                            {{ $credential->username }}
                                        </span>
                                        <button @click="copyUsername()"
                                                type="button"
                                                class="inline-flex items-center justify-center h-7 w-7 rounded-md text-slate-400 hover:text-slate-900 hover:bg-slate-100 transition-colors flex-shrink-0"
                                                :title="copied ? '{{ __('Copied!') }}' : '{{ __('Copy username') }}'">
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
                                    <span class="text-sm text-slate-400">-</span>
                                @endif
                            </x-ui.table-cell>
                            <x-ui.table-cell>
                                <div class="flex items-center gap-2"
                                     x-data="{
                                         copied: false,
                                         async copyPassword() {
                                             try {
                                                 await navigator.clipboard.writeText(@js($credential->password));
                                                 this.copied = true;
                                                 setTimeout(() => this.copied = false, 2000);
                                             } catch (err) {
                                                 console.error('Failed to copy:', err);
                                             }
                                         }
                                     }">
                                    <span class="text-sm font-mono text-slate-500">********</span>
                                    <button @click="copyPassword()"
                                            type="button"
                                            class="inline-flex items-center justify-center h-7 w-7 rounded-md text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-colors flex-shrink-0"
                                            :title="copied ? '{{ __('Copied!') }}' : '{{ __('Copy password') }}'">
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
                                @php
                                    $websiteUrl = $credential->website ?: $credential->url;
                                @endphp
                                @if ($websiteUrl)
                                    <a href="{{ $websiteUrl }}" target="_blank" class="text-sm text-blue-600 hover:text-blue-800 hover:underline truncate block max-w-[120px]" title="{{ $websiteUrl }}">
                                        {{ parse_url($websiteUrl, PHP_URL_HOST) ?: $websiteUrl }}
                                    </a>
                                @else
                                    <span class="text-sm text-slate-400">-</span>
                                @endif
                            </x-ui.table-cell>
                            <x-ui.table-cell class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <!-- Quick Login Button -->
                                    @if($credential->url)
                                        <a href="{{ $credential->url }}"
                                           target="_blank"
                                           class="inline-flex items-center justify-center h-8 w-8 rounded-md text-green-600 hover:text-green-800 hover:bg-green-50 transition-colors"
                                           title="{{ __('Open Login Page') }}"
                                           x-data
                                           @click="
                                               navigator.clipboard.writeText(@js($credential->username));
                                               $dispatch('toast', {message: '{{ __('Username copied!') }}', type: 'success'});
                                           ">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                            </svg>
                                        </a>
                                    @endif
                                    <x-table-actions
                                        :viewUrl="route('credentials.show', $credential)"
                                        :editUrl="route('credentials.edit', $credential)"
                                        :deleteAction="route('credentials.destroy', $credential)"
                                        :deleteConfirm="__('Are you sure you want to delete this credential?')"
                                    />
                                </div>
                            </x-ui.table-cell>
                        </x-ui.table-row>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($credentials->hasPages())
            <div class="bg-slate-100 px-6 py-4 border-t border-slate-200">
                {{ $credentials->links() }}
            </div>
        @endif
    @endif
</x-ui.card>
