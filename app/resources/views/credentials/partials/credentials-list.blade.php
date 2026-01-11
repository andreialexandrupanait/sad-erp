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
                <div class="bg-slate-100 py-3 px-3">
                    <div class="flex items-center gap-3">
                        <input type="checkbox"
                               x-model="groupSelections['{{ $siteName }}']"
                               @change="toggleGroup('{{ $siteName }}')"
                               class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 focus:ring-2 focus:ring-offset-2 cursor-pointer flex-shrink-0">
                        <div class="flex-1 min-w-0 flex flex-wrap items-center gap-2">
                            @if($firstCredential->url)
                                <a href="{{ $firstCredential->url }}" target="_blank"
                                   class="text-base font-semibold text-slate-800 hover:text-blue-600 transition-colors inline-flex items-center gap-1 truncate">
                                    <span class="truncate">{{ $siteName }}</span>
                                    <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            @else
                                <span class="text-base font-semibold text-slate-800 truncate">{{ $siteName }}</span>
                            @endif
                            @if($firstCredential->client)
                                <a href="{{ route('clients.show', $firstCredential->client) }}"
                                   class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium transition-colors hover:opacity-80 flex-shrink-0"
                                   style="background-color: {{ $firstCredential->client->status?->color_background ?? '#e2e8f0' }}; color: {{ $firstCredential->client->status?->color_text ?? '#475569' }};">
                                    {{ $firstCredential->client->name }}
                                </a>
                            @endif
                        </div>
                        {{-- Desktop action buttons --}}
                        <div class="hidden md:flex items-center gap-4 flex-shrink-0">
                            <button type="button"
                                    @click="$dispatch('open-email-modal', { siteName: @js($siteName), clientEmail: @js($firstCredential->client?->email ?? ''), clientName: @js($firstCredential->client?->name ?? '') })"
                                    class="text-sm text-slate-600 hover:text-blue-600 transition-colors inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span>{{ __('Email') }}</span>
                            </button>
                            <a href="{{ route('credentials.site.export', ['siteName' => $siteName]) }}"
                               class="text-sm text-slate-600 hover:text-green-600 transition-colors inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                <span>{{ __('Export') }}</span>
                            </a>
                            <a href="{{ route('credentials.create', ['site_name' => $siteName, 'client_id' => $firstCredential->client_id]) }}"
                               @mousedown="saveFilters()"
                               class="text-sm text-blue-600 hover:text-blue-800 transition-colors inline-flex items-center gap-1.5 font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('Adaugă') }}
                            </a>
                        </div>
                        {{-- Mobile dropdown --}}
                        <div class="md:hidden relative flex-shrink-0" x-data="{ open: false }">
                            <button @click="open = !open" type="button" class="p-1.5 text-slate-600 hover:text-slate-900 hover:bg-slate-200 rounded-md">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-1 w-44 bg-white rounded-lg shadow-lg border border-slate-200 py-1 z-50">
                                <button type="button" @click="$dispatch('open-email-modal', { siteName: @js($siteName), clientEmail: @js($firstCredential->client?->email ?? ''), clientName: @js($firstCredential->client?->name ?? '') }); open = false" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-100 text-left">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    {{ __('Email') }}
                                </button>
                                <a href="{{ route('credentials.site.export', ['siteName' => $siteName]) }}" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    {{ __('Export') }}
                                </a>
                                <a href="{{ route('credentials.create', ['site_name' => $siteName, 'client_id' => $firstCredential->client_id]) }}" @mousedown="saveFilters()" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-blue-600 hover:bg-slate-100 font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    {{ __('Adaugă') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Desktop: Row Layout --}}
                <div class="hidden md:block divide-y divide-slate-100">
                    @foreach($siteCredentials as $credential)
                        <div class="flex items-center py-3 px-3 hover:bg-blue-50/40 transition-colors text-sm"
                             data-selectable data-credential-id="{{ $credential->id }}" data-group="{{ $siteName }}"
                             :class="{ 'bg-blue-50': selectedIds.includes({{ $credential->id }}) }">
                            <div class="flex items-center gap-3 shrink-0 w-[140px]">
                                <input type="checkbox" :checked="selectedIds.includes({{ $credential->id }})" @change="toggleItem({{ $credential->id }})" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium @switch($credential->credential_type) @case('admin-panel') bg-blue-100 text-blue-700 @break @case('database') bg-purple-100 text-purple-700 @break @case('hosting') bg-orange-100 text-orange-700 @break @case('marketing') bg-green-100 text-green-700 @break @default bg-slate-100 text-slate-700 @endswitch">{{ $credential->platform }}</span>
                            </div>
                            <div class="pl-6 shrink-0 w-[280px]">
                                @if($credential->username)
                                    <div onclick="copyToClipboard('{{ addslashes($credential->username) }}', this)" class="font-mono text-sm text-slate-700 cursor-pointer hover:text-blue-600 inline-flex items-center gap-2 group" title="{{ __('Click to copy') }}">
                                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span class="truncate">{{ $credential->username }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </div>
                            <div class="pl-6 shrink-0 min-w-[250px] max-w-[300px]" x-data="passwordReveal({{ $credential->id }}, {{ $credential->hasPassword() ? 'true' : 'false' }})">
                                @if($credential->hasPassword())
                                    <div class="font-mono text-sm inline-flex items-center gap-2 group">
                                        <button type="button" @click="toggle()" class="flex-shrink-0 p-1 -m-1 rounded hover:bg-slate-100" :title="visible ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'">
                                            <svg x-show="loading" class="w-4 h-4 text-slate-400 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            <svg x-show="!loading && !visible" class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <svg x-show="!loading && visible" class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                        </button>
                                        <div @click="visible && copyPassword()" class="truncate" :class="visible ? 'text-slate-700 cursor-pointer hover:text-blue-600' : 'text-slate-400'"><span x-text="visible ? password : '••••••••'"></span></div>
                                        <span x-show="visible && autoHideSeconds > 0" x-cloak class="text-xs text-slate-400 tabular-nums" x-text="autoHideSeconds + 's'"></span>
                                    </div>
                                @else
                                    <div class="font-mono text-sm text-red-400 italic inline-flex items-center gap-2">
                                        <svg class="w-4 h-4 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        <span>{{ __('No password') }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="pl-6 flex-1">
                                @if($credential->url)
                                    <a href="{{ $credential->url }}" target="_blank" class="text-sm text-blue-600 hover:text-blue-800 truncate block">{{ Str::limit(preg_replace('#^https?://#', '', $credential->url), 30) }}</a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </div>
                            <div class="flex items-center justify-end gap-3 pl-4">
                                @if($credential->url)
                                    <button type="button" onclick="quickLogin('{{ addslashes($credential->username) }}', '{{ addslashes($credential->url) }}')" class="text-green-600 hover:text-green-800" title="{{ __('Quick Login') }}"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg></button>
                                @endif
                                <a href="{{ route('credentials.edit', $credential) }}" @mousedown="saveFilters()" class="text-blue-600 hover:text-blue-800" title="{{ __('Edit') }}"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                                <form method="POST" action="{{ route('credentials.destroy', $credential) }}" class="inline-flex" onsubmit="return confirm('{{ __('Are you sure you want to delete this credential?') }}')">@csrf @method('DELETE')<button type="submit" class="text-red-600 hover:text-red-800" title="{{ __('Delete') }}"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></form>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Mobile: Card Layout --}}
                <div class="md:hidden divide-y divide-slate-100">
                    @foreach($siteCredentials as $credential)
                        <div class="p-3 hover:bg-blue-50/40" data-selectable data-credential-id="{{ $credential->id }}" data-group="{{ $siteName }}" :class="{ 'bg-blue-50': selectedIds.includes({{ $credential->id }}) }" x-data="passwordReveal({{ $credential->id }}, {{ $credential->hasPassword() ? 'true' : 'false' }})">
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" :checked="selectedIds.includes({{ $credential->id }})" @change="toggleItem({{ $credential->id }})" class="h-4 w-4 rounded border-slate-300 text-blue-600 cursor-pointer">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium @switch($credential->credential_type) @case('admin-panel') bg-blue-100 text-blue-700 @break @case('database') bg-purple-100 text-purple-700 @break @case('hosting') bg-orange-100 text-orange-700 @break @case('marketing') bg-green-100 text-green-700 @break @default bg-slate-100 text-slate-700 @endswitch">{{ $credential->platform }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    @if($credential->url)<button type="button" onclick="quickLogin('{{ addslashes($credential->username) }}', '{{ addslashes($credential->url) }}')" class="p-1.5 text-green-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg></button>@endif
                                    <a href="{{ route('credentials.edit', $credential) }}" @mousedown="saveFilters()" class="p-1.5 text-blue-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                                    <form method="POST" action="{{ route('credentials.destroy', $credential) }}" class="inline-flex" onsubmit="return confirm('{{ __('Are you sure?') }}')">@csrf @method('DELETE')<button type="submit" class="p-1.5 text-red-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></form>
                                </div>
                            </div>
                            @if($credential->username)
                                <div onclick="copyToClipboard('{{ addslashes($credential->username) }}', this)" class="font-mono text-sm text-slate-700 cursor-pointer hover:text-blue-600 flex items-center gap-2 mb-2">
                                    <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span class="truncate">{{ $credential->username }}</span>
                                </div>
                            @endif
                            <div class="mb-2">
                                @if($credential->hasPassword())
                                    <div class="font-mono text-sm flex items-center gap-2">
                                        <button type="button" @click="toggle()" class="p-1 -m-1 rounded hover:bg-slate-100">
                                            <svg x-show="loading" class="w-4 h-4 text-slate-400 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            <svg x-show="!loading && !visible" class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <svg x-show="!loading && visible" class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                        </button>
                                        <div @click="visible && copyPassword()" class="truncate" :class="visible ? 'text-slate-700 cursor-pointer hover:text-blue-600' : 'text-slate-400'"><span x-text="visible ? password : '••••••••'"></span></div>
                                        <span x-show="visible && autoHideSeconds > 0" x-cloak class="text-xs text-slate-400" x-text="autoHideSeconds + 's'"></span>
                                    </div>
                                @else
                                    <div class="font-mono text-sm text-red-400 italic flex items-center gap-2">
                                        <svg class="w-4 h-4 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        <span>{{ __('No password') }}</span>
                                    </div>
                                @endif
                            </div>
                            @if($credential->url)
                                <a href="{{ $credential->url }}" target="_blank" class="text-sm text-blue-600 hover:text-blue-800 truncate block">{{ Str::limit(preg_replace('#^https?://#', '', $credential->url), 40) }}</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@else
    <x-ui.card>
        <div class="px-4 md:px-6 py-12 md:py-16 text-center">
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
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
            toast.textContent = 'Username copied! Opening login page...';
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 2000);
        });
    }
    setTimeout(() => window.open(url, '_blank'), 300);
}
</script>
@endpush
