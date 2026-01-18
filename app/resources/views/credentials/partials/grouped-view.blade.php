{{-- Grouped Accordion View for Credentials --}}
<div class="space-y-4" x-data="credentialAccordion()">
    @forelse($groupedCredentials as $clientId => $siteGroups)
        @php
            $firstSiteGroup = $siteGroups->first();
            $firstCredential = $firstSiteGroup ? $firstSiteGroup->first() : null;
            $client = $firstCredential?->client;
            $totalCredentials = $siteGroups->flatten()->count();
        @endphp
        @continue(!$client)

        <!-- Client Accordion -->
        <x-ui.card class="overflow-hidden">
            <div class="border-b border-slate-200">
                <button type="button"
                        class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-slate-50 transition-colors"
                        @click="toggleClient({{ $clientId }})">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-slate-500 transition-transform duration-200"
                             :class="{ 'rotate-90': isClientOpen({{ $clientId }}) }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">{{ $client->display_name }}</h3>
                            <p class="text-sm text-slate-500">
                                {{ $siteGroups->count() }} {{ trans_choice('site|sites', $siteGroups->count()) }} &middot;
                                {{ $totalCredentials }} {{ trans_choice('credential|credentials', $totalCredentials) }}
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('clients.show', $client) }}"
                       class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                       @click.stop>
                        {{ __('View Client') }}
                    </a>
                </button>
            </div>

            <!-- Sites within Client -->
            <div x-show="isClientOpen({{ $clientId }})" x-collapse class="divide-y divide-slate-100">
                @foreach($siteGroups as $siteName => $credentials)
                    @php
                        $siteKey = $clientId . '_' . md5($siteName);
                        $displaySiteName = $siteName === '__no_site__' ? __('(No Site)') : $siteName;
                    @endphp

                    <div class="bg-slate-50/50">
                        <!-- Site Header -->
                        <button type="button"
                                class="w-full px-6 py-3 flex items-center justify-between text-left hover:bg-slate-100 transition-colors"
                                @click="toggleSite('{{ $siteKey }}')">
                            <div class="flex items-center gap-3 ml-4">
                                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200"
                                     :class="{ 'rotate-90': isSiteOpen('{{ $siteKey }}') }"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                    </svg>
                                    <span class="font-medium text-slate-700">{{ $displaySiteName }}</span>
                                    <span class="text-sm text-slate-400">({{ $credentials->count() }})</span>
                                </div>
                            </div>
                        </button>

                        <!-- Credentials within Site -->
                        <div x-show="isSiteOpen('{{ $siteKey }}')" x-collapse>
                            <div class="px-6 pb-4 ml-4">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead class="text-xs text-slate-500 uppercase bg-slate-100 rounded-t-lg">
                                            <tr>
                                                <th class="px-3 py-2 text-left rounded-tl-lg">{{ __('Type') }}</th>
                                                <th class="px-3 py-2 text-left">{{ __('Platform') }}</th>
                                                <th class="px-3 py-2 text-left">{{ __('Username') }}</th>
                                                <th class="px-3 py-2 text-left">{{ __('Password') }}</th>
                                                <th class="px-3 py-2 text-left">{{ __('Website') }}</th>
                                                <th class="px-3 py-2 text-right rounded-tr-lg">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 bg-white rounded-b-lg">
                                            @foreach($credentials as $credential)
                                                <tr class="hover:bg-slate-50">
                                                    <td class="px-3 py-2.5">
                                                        <x-ui.badge variant="{{ $credential->type_badge_color }}">
                                                            {{ __($credential->type_label) }}
                                                        </x-ui.badge>
                                                    </td>
                                                    <td class="px-3 py-2.5">
                                                        <x-ui.badge variant="secondary">{{ $credential->platform }}</x-ui.badge>
                                                    </td>
                                                    <td class="px-3 py-2.5">
                                                        @if($credential->username)
                                                            <div class="flex items-center gap-2">
                                                                <span class="text-slate-700 font-mono text-xs">{{ $credential->username }}</span>
                                                                <button type="button"
                                                                        onclick="copyToClipboard('{{ $credential->username }}', this)"
                                                                        class="text-slate-400 hover:text-slate-600"
                                                                        title="{{ __('Copy username') }}">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        @else
                                                            <span class="text-slate-400">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-2.5">
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-slate-400 font-mono text-xs">********</span>
                                                            <button type="button"
                                                                    onclick="copyPassword({{ $credential->id }}, this)"
                                                                    class="text-slate-400 hover:text-slate-600"
                                                                    title="{{ __('Copy password') }}">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-2.5">
                                                        @if($credential->website ?: $credential->url)
                                                            <a href="{{ $credential->website ?: $credential->url }}"
                                                               target="_blank"
                                                               class="text-blue-600 hover:text-blue-800 truncate block max-w-[150px]"
                                                               title="{{ $credential->website ?: $credential->url }}">
                                                                {{ parse_url($credential->website ?: $credential->url, PHP_URL_HOST) }}
                                                            </a>
                                                        @else
                                                            <span class="text-slate-400">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-2.5 text-right">
                                                        <div class="flex items-center justify-end gap-1">
                                                            @if($credential->quick_login_url)
                                                                <button type="button"
                                                                        onclick="quickLogin({{ $credential->id }}, '{{ $credential->username }}', '{{ $credential->quick_login_url }}')"
                                                                        class="p-1.5 text-slate-400 hover:text-green-600 hover:bg-green-50 rounded"
                                                                        title="{{ __('Quick Login') }}">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                                                    </svg>
                                                                </button>
                                                            @endif
                                                            <a href="{{ route('credentials.show', $credential) }}"
                                                               class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded"
                                                               title="{{ __('View') }}">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                                </svg>
                                                            </a>
                                                            <a href="{{ route('credentials.edit', $credential) }}"
                                                               class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded"
                                                               title="{{ __('Edit') }}">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                </svg>
                                                            </a>
                                                            <form action="{{ route('credentials.destroy', $credential) }}" method="POST" class="inline"
                                                                  onsubmit="return confirm('{{ __('Delete this credential?') }}')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                        class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded"
                                                                        title="{{ __('Delete') }}">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                    </svg>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    @empty
        <!-- Empty State -->
        <x-ui.card>
            <div class="px-6 py-16 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                <h3 class="mt-4 text-sm font-medium text-slate-900">{{ __('No credentials') }}</h3>
                <p class="mt-1 text-sm text-slate-500">{{ __('Get started by creating your first credential') }}</p>
                <div class="mt-6">
                    <x-ui.button variant="default" onclick="window.location.href='{{ route('credentials.create') }}'">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('Add Credential') }}
                    </x-ui.button>
                </div>
            </div>
        </x-ui.card>
    @endforelse
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('credentialAccordion', () => ({
        openClients: {},
        openSites: {},

        init() {
            // Auto-expand first client if exists
            @if($groupedCredentials->isNotEmpty())
                @php
                    $firstClientKey = $groupedCredentials->keys()->first();
                    $firstClient = $groupedCredentials->first();
                    $firstSiteKeyPart = $firstClient && $firstClient->keys()->isNotEmpty() ? $firstClient->keys()->first() : '';
                    $firstSiteKey = $firstClientKey . '_' . md5($firstSiteKeyPart);
                @endphp
                @if($firstClientKey !== null)
                    this.openClients[{{ $firstClientKey }}] = true;
                    this.openSites['{{ $firstSiteKey }}'] = true;
                @endif
            @endif
        },

        isClientOpen(clientId) {
            return this.openClients[clientId] === true;
        },

        toggleClient(clientId) {
            this.openClients[clientId] = !this.openClients[clientId];
        },

        isSiteOpen(siteKey) {
            return this.openSites[siteKey] === true;
        },

        toggleSite(siteKey) {
            this.openSites[siteKey] = !this.openSites[siteKey];
        }
    }));
});

function copyToClipboard(text, button) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('{{ __("Copied to clipboard") }}');
        // Visual feedback
        const originalHtml = button.innerHTML;
        button.innerHTML = '<svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
        setTimeout(() => { button.innerHTML = originalHtml; }, 1500);
    });
}

function copyPassword(credentialId, button) {
    fetch(`/credentials/${credentialId}/password`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.password) {
            navigator.clipboard.writeText(data.password).then(() => {
                showToast('{{ __("Password copied to clipboard") }}');
                const originalHtml = button.innerHTML;
                button.innerHTML = '<svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                setTimeout(() => { button.innerHTML = originalHtml; }, 1500);
            });
        } else {
            showToast('{{ __("No password set") }}', 'error');
        }
    })
    .catch(error => {
        showToast('{{ __("Error copying password") }}', 'error');
    });
}

function quickLogin(credentialId, username, url) {
    if (username) {
        navigator.clipboard.writeText(username).then(() => {
            showToast('{{ __("Username copied, opening login page...") }}');
        });
    }
    setTimeout(() => {
        window.open(url, '_blank');
    }, 300);
}

function showToast(message, type = 'success') {
    window.dispatchEvent(new CustomEvent('toast', {
        detail: { message: message, type: type }
    }));
}
</script>
