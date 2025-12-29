@if(count($credentialsBySite) > 0)
    <div class="space-y-4">
        @foreach($credentialsBySite as $siteName => $siteCredentials)
            @php
                $firstCredential = $siteCredentials->first();
                $borderColor = $firstCredential->client?->status?->color_class ?? '#64748b';
            @endphp

            <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden border-l-4"
                 style="border-left-color: {{ $borderColor }};">
                {{-- Site Group Header --}}
                <div class="px-6 py-3 bg-slate-100">
                    <div class="flex items-center justify-between">
                        {{-- LEFT: Checkbox + Site name --}}
                        <div class="flex items-center gap-3">
                            {{-- Group select checkbox --}}
                            <input type="checkbox"
                                   x-model="groupSelections['{{ $siteName }}']"
                                   @change="toggleGroup('{{ $siteName }}')"
                                   class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 focus:ring-2 focus:ring-offset-2 cursor-pointer">

                            {{-- Site name with external link --}}
                            @if($firstCredential->url)
                                <a href="{{ $firstCredential->url }}" target="_blank"
                                   class="text-base font-semibold text-slate-800 hover:text-blue-600 transition-colors inline-flex items-center gap-2">
                                    {{ $siteName }}
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            @else
                                <span class="text-base font-semibold text-slate-800">{{ $siteName }}</span>
                            @endif
                        </div>

                        {{-- RIGHT: Client badge + Add button --}}
                        <div class="flex items-center gap-4">
                            {{-- Client badge with status color --}}
                            @if($firstCredential->client)
                                <a href="{{ route('clients.show', $firstCredential->client) }}"
                                   class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium transition-colors hover:opacity-80"
                                   style="background-color: {{ $firstCredential->client->status?->color_background ?? '#e2e8f0' }}; color: {{ $firstCredential->client->status?->color_text ?? '#475569' }};">
                                    {{ $firstCredential->client->name }}
                                </a>
                            @endif

                            {{-- Add credential button --}}
                            <a href="{{ route('credentials.create', ['site_name' => $siteName, 'client_id' => $firstCredential->client_id]) }}"
                               class="text-sm text-blue-600 hover:text-blue-800 transition-colors flex items-center gap-1.5 font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('Adaugă') }}
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Credentials Table --}}
                <table class="w-full text-sm table-fixed">
                    <colgroup>
                        <col class="w-12">
                        <col class="w-[16%]">
                        <col class="w-[20%]">
                        <col class="w-[18%]">
                        <col class="w-[22%]">
                        <col class="w-[14%]">
                    </colgroup>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($siteCredentials as $credential)
                            <tr class="hover:bg-blue-50/40 transition-colors" data-selectable data-credential-id="{{ $credential->id }}" data-group="{{ $siteName }}"
                                :class="{ 'bg-blue-50': selectedIds.includes({{ $credential->id }}) }">
                                {{-- Checkbox --}}
                                <td class="px-6 py-4 align-middle">
                                    <input type="checkbox"
                                           :checked="selectedIds.includes({{ $credential->id }})"
                                           @change="toggleItem({{ $credential->id }})"
                                           class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 focus:ring-2 focus:ring-offset-2 cursor-pointer transition-colors">
                                </td>
                                {{-- Platform --}}
                                <td class="px-6 py-3 align-middle">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                        @switch($credential->credential_type)
                                            @case('admin-panel') bg-blue-100 text-blue-700 @break
                                            @case('database') bg-purple-100 text-purple-700 @break
                                            @case('hosting') bg-orange-100 text-orange-700 @break
                                            @case('marketing') bg-green-100 text-green-700 @break
                                            @default bg-slate-100 text-slate-700
                                        @endswitch
                                    ">
                                        {{ $credential->platform }}
                                    </span>
                                </td>

                                {{-- Username with icon --}}
                                <td class="px-6 py-3 align-middle">
                                    @if($credential->username)
                                        <div onclick="copyToClipboard('{{ addslashes($credential->username) }}', this)"
                                             class="font-mono text-sm text-slate-700 cursor-pointer hover:text-blue-600 transition-colors inline-flex items-center gap-2 group"
                                             title="{{ __('Click to copy') }}">
                                            <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            <span class="truncate">{{ $credential->username }}</span>
                                            <svg class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>

                                {{-- Password with icon --}}
                                <td class="px-6 py-3 align-middle" x-data="{ password: '', loaded: false }">
                                    <div x-init="fetchPassword({{ $credential->id }}).then(p => { password = p; loaded = true; })"
                                         @click="if(password) { copyToClipboard(password, $el); }"
                                         class="font-mono text-sm text-slate-700 cursor-pointer hover:text-blue-600 transition-colors inline-flex items-center gap-2 group rounded-md px-2 py-1 -mx-2 -my-1 hover:bg-slate-100"
                                         title="{{ __('Click to copy') }}">
                                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        <span x-text="loaded ? password : '••••••••'"></span>
                                        <svg x-show="loaded" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                </td>

                                {{-- Login URL --}}
                                <td class="px-6 py-3 align-middle">
                                    @if($credential->url)
                                        <a href="{{ $credential->url }}" target="_blank"
                                           class="text-sm text-blue-600 hover:text-blue-800 transition-colors truncate block"
                                           title="{{ $credential->url }}">
                                            {{ Str::limit(preg_replace('#^https?://#', '', $credential->url), 30) }}
                                        </a>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="px-6 py-3 align-middle text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        {{-- Quick Access button --}}
                                        @if($credential->url)
                                            <button type="button"
                                                    onclick="quickLogin('{{ addslashes($credential->username) }}', '{{ addslashes($credential->url) }}')"
                                                    class="inline-flex items-center text-green-600 hover:text-green-800 transition-colors"
                                                    title="{{ __('Quick Login') }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                                </svg>
                                            </button>
                                        @endif

                                        {{-- Edit button --}}
                                        <a href="{{ route('credentials.edit', $credential) }}"
                                           class="inline-flex items-center text-blue-600 hover:text-blue-800 transition-colors"
                                           title="{{ __('Edit') }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>

                                        {{-- Delete button --}}
                                        <form method="POST" action="{{ route('credentials.destroy', $credential) }}" class="inline-flex"
                                              onsubmit="return confirm('{{ __('Are you sure you want to delete this credential?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center text-red-600 hover:text-red-800 transition-colors"
                                                    title="{{ __('Delete') }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        @endforeach
    </div>
@else
    <x-ui.card>
        <div class="px-6 py-16 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-slate-900">{{ __('No credentials') }}</h3>
            <p class="mt-1 text-sm text-slate-500">
                @if(request('search') || request('client_id'))
                    {{ __('No credentials match your search criteria') }}
                @else
                    {{ __('Get started by creating your first credential') }}
                @endif
            </p>
            @if(!request('search') && !request('client_id'))
                <div class="mt-6">
                    <x-ui.button variant="default" onclick="window.location.href='{{ route('credentials.create') }}'">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('Add Credential') }}
                    </x-ui.button>
                </div>
            @endif
        </div>
    </x-ui.card>
@endif

@push('scripts')
<script>
function quickLogin(username, url) {
    if (username) {
        navigator.clipboard.writeText(username).then(() => {
            // Create a temporary toast notification
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
            toast.textContent = 'Username copied! Opening login page...';
            document.body.appendChild(toast);

            // Remove toast after 2 seconds
            setTimeout(() => {
                toast.remove();
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy username:', err);
        });
    }

    setTimeout(() => {
        window.open(url, '_blank');
    }, 300);
}
</script>
@endpush
