{{-- Credential Cards Grouped by Site/Client --}}
<div class="space-y-8" x-data="credentialCards()">
    @forelse($credentialsBySite as $groupName => $credentials)
        @php
            $firstCredential = $credentials->first();
            $hasSiteName = $firstCredential->site_name && $firstCredential->site_name !== '';
            $displayName = $hasSiteName ? $firstCredential->site_name : ($firstCredential->client?->name ?? __('No Client'));
            $clientName = $hasSiteName ? $firstCredential->client?->name : null;
        @endphp
        {{-- Site/Client Group --}}
        <div class="space-y-4">
            {{-- Group Header --}}
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                    {{ strtoupper(substr($displayName, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">{{ $displayName }}</h2>
                    @if($clientName)
                        <p class="text-sm text-slate-500">{{ $clientName }}</p>
                    @endif
                </div>
                <span class="ml-auto text-sm text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">
                    {{ $credentials->count() }} {{ trans_choice('credential|credentials', $credentials->count()) }}
                </span>
            </div>

            {{-- Credential Cards Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($credentials as $credential)
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                        {{-- Card Header --}}
                        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                            <div class="flex items-center gap-2 min-w-0">
                                <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                </svg>
                                <span class="font-medium text-slate-900 truncate">{{ $credential->site_name ?: $credential->client?->name }}</span>
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <a href="{{ route('credentials.edit', $credential) }}"
                                   class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors"
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
                                            class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                            title="{{ __('Delete') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="p-4 space-y-3">
                            {{-- Platform Badge --}}
                            <div>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium
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
                            </div>

                            {{-- URL --}}
                            @if($credential->url || $credential->website)
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                    <a href="{{ $credential->url ?: $credential->website }}"
                                       target="_blank"
                                       class="text-sm text-blue-600 hover:text-blue-800 truncate flex-1"
                                       title="{{ $credential->url ?: $credential->website }}">
                                        {{ $credential->url ?: $credential->website }}
                                    </a>
                                    <a href="{{ $credential->url ?: $credential->website }}"
                                       target="_blank"
                                       class="p-1 text-slate-400 hover:text-blue-600 flex-shrink-0"
                                       title="{{ __('Open') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </a>
                                </div>
                            @endif

                            {{-- Username --}}
                            @if($credential->username)
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span class="text-sm text-slate-700 truncate flex-1 font-mono">{{ $credential->username }}</span>
                                    <button type="button"
                                            onclick="copyToClipboard('{{ addslashes($credential->username) }}', this)"
                                            class="p-1 text-slate-400 hover:text-slate-600 flex-shrink-0"
                                            title="{{ __('Copy username') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </button>
                                </div>
                            @endif

                            {{-- Password --}}
                            <div class="flex items-center gap-2" x-data="{ showPassword: false, password: '' }">
                                <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <span class="text-sm text-slate-400 truncate flex-1 font-mono"
                                      x-text="showPassword && password ? password : '********'"></span>
                                <button type="button"
                                        @click="if(!password) { fetchPassword({{ $credential->id }}).then(p => { password = p; showPassword = true; }); } else { showPassword = !showPassword; }"
                                        class="p-1 text-slate-400 hover:text-slate-600 flex-shrink-0"
                                        :title="showPassword ? '{{ __('Hide') }}' : '{{ __('Show') }}'">
                                    <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                                <button type="button"
                                        onclick="copyPassword({{ $credential->id }}, this)"
                                        class="p-1 text-slate-400 hover:text-slate-600 flex-shrink-0"
                                        title="{{ __('Copy password') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        {{-- Empty State --}}
        <x-ui.card>
            <div class="px-6 py-16 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                </svg>
                <h3 class="mt-4 text-sm font-medium text-slate-900">{{ __('No credentials found') }}</h3>
                <p class="mt-1 text-sm text-slate-500">{{ __('Create credentials and assign them to sites to see them here.') }}</p>
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
    Alpine.data('credentialCards', () => ({
        // Empty for now, Alpine handles password show/hide per card
    }));
});

async function fetchPassword(credentialId) {
    try {
        const response = await fetch(`/credentials/${credentialId}/password`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            },
            credentials: 'same-origin'
        });
        const data = await response.json();
        return data.password || '';
    } catch (error) {
        console.error('Error fetching password:', error);
        return '';
    }
}

function copyToClipboard(text, button) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('{{ __("Copied to clipboard") }}');
        showCopyFeedback(button);
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
                showCopyFeedback(button);
            });
        } else {
            showToast('{{ __("No password set") }}', 'error');
        }
    })
    .catch(error => {
        showToast('{{ __("Error copying password") }}', 'error');
    });
}

function showCopyFeedback(button) {
    const originalHtml = button.innerHTML;
    button.innerHTML = '<svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
    setTimeout(() => { button.innerHTML = originalHtml; }, 1500);
}

function showToast(message, type = 'success') {
    window.dispatchEvent(new CustomEvent('toast', {
        detail: { message, type }
    }));
}
</script>
