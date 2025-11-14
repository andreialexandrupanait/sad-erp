<x-app-layout>
    <x-slot name="pageTitle">{{ $client->name }}@if($client->company_name) - {{ $client->company_name }}@endif</x-slot>

    <x-slot name="headerActions">
        <x-ui.button variant="default" onclick="window.location.href='{{ route('clients.edit', $client) }}'">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            {{ __('Edit Client') }}
        </x-ui.button>
    </x-slot>

    <div class="p-6 space-y-6">

        <!-- Client Info Card -->
        <x-ui.card>
            <x-ui.card-content>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Status -->
                    <div>
                        <h3 class="text-sm font-medium text-slate-600">{{ __('Status') }}</h3>
                        <div class="mt-2">
                            <x-client-status-badge :status="$client->status" />
                        </div>
                    </div>

                    <!-- Total Revenue -->
                    <div>
                        <h3 class="text-sm font-medium text-slate-600">{{ __('Total Revenue') }}</h3>
                        <p class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format($stats['total_revenue'], 2) }} RON</p>
                    </div>

                    <!-- Active Domains -->
                    <div>
                        <h3 class="text-sm font-medium text-slate-600">{{ __('Active Domains') }}</h3>
                        <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $stats['active_domains'] }}</p>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-slate-200 pt-6">
                    <!-- Contact Info -->
                    <div>
                        <h3 class="text-sm font-medium text-slate-600 mb-3">{{ __('Contact Information') }}</h3>
                        <dl class="space-y-2">
                            @if($client->email)
                                <div class="flex items-start">
                                    <dt class="text-sm text-slate-600 w-32">{{ __('Email') }}:</dt>
                                    <dd class="text-sm text-slate-900">{{ $client->email }}</dd>
                                </div>
                            @endif
                            @if($client->phone)
                                <div class="flex items-start">
                                    <dt class="text-sm text-slate-600 w-32">{{ __('Phone') }}:</dt>
                                    <dd class="text-sm text-slate-900">{{ $client->phone }}</dd>
                                </div>
                            @endif
                            @if($client->contact_person)
                                <div class="flex items-start">
                                    <dt class="text-sm text-slate-600 w-32">{{ __('Contact Person') }}:</dt>
                                    <dd class="text-sm text-slate-900">{{ $client->contact_person }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    <!-- Company Info -->
                    <div>
                        <h3 class="text-sm font-medium text-slate-600 mb-3">{{ __('Company Information') }}</h3>
                        <dl class="space-y-2">
                            @if($client->tax_id)
                                <div class="flex items-start">
                                    <dt class="text-sm text-slate-600 w-32">{{ __('Tax ID (CUI)') }}:</dt>
                                    <dd class="text-sm text-slate-900">{{ $client->tax_id }}</dd>
                                </div>
                            @endif
                            @if($client->registration_number)
                                <div class="flex items-start">
                                    <dt class="text-sm text-slate-600 w-32">{{ __('Registration Number') }}:</dt>
                                    <dd class="text-sm text-slate-900">{{ $client->registration_number }}</dd>
                                </div>
                            @endif
                            <div class="flex items-start">
                                <dt class="text-sm text-slate-600 w-32">{{ __('VAT Payer') }}:</dt>
                                <dd class="text-sm text-slate-900">{{ $client->vat_payer ? __('Yes') : __('No') }}</dd>
                            </div>
                        </dl>
                    </div>

                    @if($client->address)
                        <div class="md:col-span-2">
                            <h3 class="text-sm font-medium text-slate-600 mb-2">{{ __('Address') }}</h3>
                            <p class="text-sm text-slate-900">{{ $client->address }}</p>
                        </div>
                    @endif

                    @if($client->notes)
                        <div class="md:col-span-2">
                            <h3 class="text-sm font-medium text-slate-600 mb-2">{{ __('Notes') }}</h3>
                            <p class="text-sm text-slate-900">{{ $client->notes }}</p>
                        </div>
                    @endif
                </div>
            </x-ui.card-content>
        </x-ui.card>

        <!-- Tabs -->
        <x-ui.card>
            <div class="border-b border-slate-200">
                <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                    <a href="{{ route('clients.show', ['client' => $client, 'tab' => 'overview']) }}"
                        class="border-transparent {{ $activeTab === 'overview' ? 'border-slate-900 text-slate-900' : 'text-slate-500 hover:text-slate-700 hover:border-slate-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        {{ __('Overview') }}
                    </a>
                    <a href="{{ route('clients.show', ['client' => $client, 'tab' => 'revenues']) }}"
                        class="border-transparent {{ $activeTab === 'revenues' ? 'border-slate-900 text-slate-900' : 'text-slate-500 hover:text-slate-700 hover:border-slate-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        {{ __('Revenues') }}
                    </a>
                    <a href="{{ route('clients.show', ['client' => $client, 'tab' => 'domains']) }}"
                        class="border-transparent {{ $activeTab === 'domains' ? 'border-slate-900 text-slate-900' : 'text-slate-500 hover:text-slate-700 hover:border-slate-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        {{ __('Domains') }}
                    </a>
                    <a href="{{ route('clients.show', ['client' => $client, 'tab' => 'credentials']) }}"
                        class="border-transparent {{ $activeTab === 'credentials' ? 'border-slate-900 text-slate-900' : 'text-slate-500 hover:text-slate-700 hover:border-slate-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        {{ __('Access Credentials') }}
                    </a>
                </nav>
            </div>

            <x-ui.card-content>
                @if($activeTab === 'overview')
                    <div class="space-y-6">
                        <!-- Quick Stats Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-slate-50 p-6 rounded-lg border border-slate-200">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-slate-500 truncate">{{ __('Total Revenue') }}</dt>
                                            <dd class="flex items-baseline">
                                                <div class="text-2xl font-semibold text-slate-900">{{ number_format($stats['total_revenue'], 2) }} RON</div>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-slate-50 p-6 rounded-lg border border-slate-200">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-10 w-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-slate-500 truncate">{{ __('Active Domains') }}</dt>
                                            <dd class="flex items-baseline">
                                                <div class="text-2xl font-semibold text-slate-900">{{ $stats['active_domains'] }}</div>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-slate-50 p-6 rounded-lg border border-slate-200">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-10 w-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-slate-500 truncate">{{ __('Credentials') }}</dt>
                                            <dd class="flex items-baseline">
                                                <div class="text-2xl font-semibold text-slate-900">{{ $stats['credentials_count'] }}</div>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div>
                            <h3 class="text-lg font-medium text-slate-900 mb-4">{{ __('Recent Activity') }}</h3>
                            <p class="text-sm text-slate-500">{{ __('Activity timeline will be displayed here') }}</p>
                        </div>
                    </div>
                @elseif($activeTab === 'revenues')
                    @if($client->revenues->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-slate-900">{{ __('No revenues yet') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('Get started by adding a revenue entry') }}</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full caption-bottom text-sm">
                                <thead class="[&_tr]:border-b">
                                    <tr class="border-b transition-colors hover:bg-slate-50/50">
                                        <x-ui.table-head>{{ __('Description') }}</x-ui.table-head>
                                        <x-ui.table-head>{{ __('Amount') }}</x-ui.table-head>
                                        <x-ui.table-head>{{ __('Date') }}</x-ui.table-head>
                                    </tr>
                                </thead>
                                <tbody class="[&_tr:last-child]:border-0">
                                    @foreach($client->revenues as $revenue)
                                        <x-ui.table-row>
                                            <x-ui.table-cell>{{ $revenue->description }}</x-ui.table-cell>
                                            <x-ui.table-cell>{{ number_format($revenue->amount, 2) }} RON</x-ui.table-cell>
                                            <x-ui.table-cell>{{ $revenue->date->format('d M Y') }}</x-ui.table-cell>
                                        </x-ui.table-row>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @elseif($activeTab === 'domains')
                    @if($client->domains->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-slate-900">{{ __('No domains yet') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('No domains are associated with this client') }}</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full caption-bottom text-sm">
                                <thead class="[&_tr]:border-b">
                                    <tr class="border-b transition-colors hover:bg-slate-50/50">
                                        <x-ui.table-head>{{ __('Domain Name') }}</x-ui.table-head>
                                        <x-ui.table-head>{{ __('Registrar') }}</x-ui.table-head>
                                        <x-ui.table-head>{{ __('Status') }}</x-ui.table-head>
                                        <x-ui.table-head>{{ __('Expiry Date') }}</x-ui.table-head>
                                        <x-ui.table-head class="text-right">{{ __('Actions') }}</x-ui.table-head>
                                    </tr>
                                </thead>
                                <tbody class="[&_tr:last-child]:border-0">
                                    @foreach($client->domains as $domain)
                                        <x-ui.table-row>
                                            <x-ui.table-cell>
                                                <a href="{{ route('domains.show', $domain) }}" class="text-slate-900 hover:text-slate-600 font-medium transition-colors">
                                                    {{ $domain->domain_name }}
                                                </a>
                                            </x-ui.table-cell>
                                            <x-ui.table-cell>{{ $domain->registrar }}</x-ui.table-cell>
                                            <x-ui.table-cell>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800">
                                                    {{ $domain->status }}
                                                </span>
                                            </x-ui.table-cell>
                                            <x-ui.table-cell>{{ $domain->expiry_date->format('d M Y') }}</x-ui.table-cell>
                                            <x-ui.table-cell class="text-right">
                                                <x-table-actions
                                                    :viewUrl="route('domains.show', $domain)"
                                                />
                                            </x-ui.table-cell>
                                        </x-ui.table-row>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @elseif($activeTab === 'credentials')
                    @if($client->accessCredentials->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-slate-900">{{ __('No credentials yet') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('No access credentials are associated with this client') }}</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <x-ui.table-head>{{ __('Platform') }}</x-ui.table-head>
                                        <x-ui.table-head>{{ __('URL') }}</x-ui.table-head>
                                        <x-ui.table-head>{{ __('Username') }}</x-ui.table-head>
                                        <x-ui.table-head>{{ __('Password') }}</x-ui.table-head>
                                        <x-ui.table-head>{{ __('Notes') }}</x-ui.table-head>
                                        <x-ui.table-head class="text-right">{{ __('Actions') }}</x-ui.table-head>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-200">
                                    @foreach($client->accessCredentials as $credential)
                                        <x-ui.table-row x-data="{ showPassword{{ $credential->id }}: false }">
                                            <!-- Platform -->
                                            <x-ui.table-cell>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                                    {{ $credential->platform }}
                                                </span>
                                            </x-ui.table-cell>

                                            <!-- URL -->
                                            <x-ui.table-cell>
                                                @if($credential->url)
                                                    <a href="{{ $credential->url }}" target="_blank" rel="noopener noreferrer"
                                                       class="text-sm text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                        </svg>
                                                        <span class="truncate max-w-[150px]" title="{{ $credential->url }}">
                                                            {{ parse_url($credential->url, PHP_URL_HOST) ?: $credential->url }}
                                                        </span>
                                                    </a>
                                                @else
                                                    <span class="text-sm text-slate-400">-</span>
                                                @endif
                                            </x-ui.table-cell>

                                            <!-- Username -->
                                            <x-ui.table-cell>
                                                @if($credential->username)
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-sm text-slate-700">{{ $credential->username }}</span>
                                                        <button onclick="copyToClipboard('{{ $credential->username }}', 'Username')"
                                                                class="text-slate-400 hover:text-slate-600 transition-colors"
                                                                title="{{ __('Copy username') }}">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                @else
                                                    <span class="text-sm text-slate-400">-</span>
                                                @endif
                                            </x-ui.table-cell>

                                            <!-- Password -->
                                            <x-ui.table-cell>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm font-mono"
                                                          :class="showPassword{{ $credential->id }} ? 'text-slate-700' : 'text-slate-500'"
                                                          x-text="showPassword{{ $credential->id }} ? '{{ $credential->password }}' : '{{ $credential->masked_password }}'">
                                                    </span>
                                                    <button @click="showPassword{{ $credential->id }} = !showPassword{{ $credential->id }}"
                                                            class="text-slate-400 hover:text-slate-600 transition-colors"
                                                            :title="showPassword{{ $credential->id }} ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'">
                                                        <svg x-show="!showPassword{{ $credential->id }}" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                        </svg>
                                                        <svg x-show="showPassword{{ $credential->id }}" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                                        </svg>
                                                    </button>
                                                    <button x-show="showPassword{{ $credential->id }}"
                                                            onclick="copyToClipboard('{{ $credential->password }}', 'Password')"
                                                            class="text-slate-400 hover:text-slate-600 transition-colors"
                                                            title="{{ __('Copy password') }}"
                                                            style="display: none;">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </x-ui.table-cell>

                                            <!-- Notes -->
                                            <x-ui.table-cell>
                                                @if($credential->notes)
                                                    <span class="text-sm text-slate-600 line-clamp-2" title="{{ $credential->notes }}">
                                                        {{ $credential->notes }}
                                                    </span>
                                                @else
                                                    <span class="text-sm text-slate-400">-</span>
                                                @endif
                                            </x-ui.table-cell>

                                            <!-- Actions -->
                                            <x-ui.table-cell class="text-right">
                                                <x-table-actions
                                                    :viewUrl="route('credentials.show', $credential)"
                                                />
                                            </x-ui.table-cell>
                                        </x-ui.table-row>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endif
            </x-ui.card-content>
        </x-ui.card>
    </div>

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
                        message: (label || 'Text') + ' copied to clipboard!'
                    }
                }));
            }).catch(function(err) {
                console.error('Failed to copy:', err);
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'error',
                        message: 'Failed to copy to clipboard'
                    }
                }));
            });
        }
    </script>
    @endpush
</x-app-layout>
