<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Integrare Bancară') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6 px-6 space-y-6">
            {{-- Success/Error Messages --}}
            @if (session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            {{-- Configuration Check --}}
            @if(!config('banking.banca_transilvania.client_id') || !config('banking.banca_transilvania.client_secret'))
                <div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded-lg">
                    <div class="flex items-start">
                        <svg class="h-5 w-5 text-orange-500 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div class="text-sm text-orange-700">
                            <p class="font-semibold mb-2">Configurare Necesară</p>
                            <p class="mb-3">Pentru a folosi integrarea bancară, trebuie să configurați credențialele API de la Banca Transilvania.</p>
                            <div class="bg-white border border-orange-200 rounded p-3 text-xs font-mono mb-3">
                                <p class="mb-1"><span class="text-orange-600">BT_CLIENT_ID</span>=your_client_id</p>
                                <p><span class="text-orange-600">BT_CLIENT_SECRET</span>=your_client_secret</p>
                            </div>
                            <p class="text-xs mb-2"><strong>Pași pentru configurare:</strong></p>
                            <ol class="list-decimal list-inside text-xs space-y-1 mb-3">
                                <li>Înregistrați-vă la <a href="https://developers.bancatransilvania.ro" target="_blank" class="underline hover:text-orange-900">Banca Transilvania Developer Portal</a></li>
                                <li>Creați o aplicație nouă pentru PSD2/Open Banking</li>
                                <li>Copiați Client ID și Client Secret</li>
                                <li>Editați fișierul <code class="bg-orange-100 px-1 rounded">/var/www/erp/app/.env</code> și completați:
                                    <div class="mt-2 mb-2 ml-4 p-2 bg-slate-800 text-slate-100 rounded text-xs font-mono">
                                        <div class="mb-1">BT_CLIENT_ID=<span class="text-green-400">your_client_id_here</span></div>
                                        <div>BT_CLIENT_SECRET=<span class="text-green-400">your_secret_here</span></div>
                                    </div>
                                </li>
                                <li>Rulați comanda:
                                    <div class="mt-1 mb-2 ml-4 p-2 bg-slate-800 text-slate-100 rounded text-xs font-mono cursor-pointer hover:bg-slate-700" onclick="copyToClipboard('docker exec erp_app php artisan config:clear')" title="Click to copy">
                                        docker exec erp_app php artisan config:clear
                                        <span class="text-green-400 ml-2">📋</span>
                                    </div>
                                </li>
                                <li>Setați Redirect URI în portalul BT:
                                    <div class="mt-1 ml-4 p-2 bg-white border border-orange-300 rounded text-xs font-mono cursor-pointer hover:bg-orange-50" onclick="copyToClipboard('{{ config('banking.banca_transilvania.redirect_uri') }}')" title="Click to copy">
                                        {{ config('banking.banca_transilvania.redirect_uri') }}
                                        <span class="ml-2">📋</span>
                                    </div>
                                </li>
                                <li>Reîncărcați această pagină</li>
                            </ol>

                            <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded">
                                <p class="text-xs text-blue-800"><strong>💡 Ajutor rapid:</strong> Rulați acest singur command pentru a edita fișierul:</p>
                                <div class="mt-2 p-2 bg-slate-800 text-slate-100 rounded text-xs font-mono cursor-pointer hover:bg-slate-700" onclick="copyToClipboard('docker exec -it erp_app nano /var/www/html/.env')" title="Click to copy">
                                    docker exec -it erp_app nano /var/www/html/.env
                                    <span class="text-green-400 ml-2">📋 Click to copy</span>
                                </div>
                                <p class="text-xs text-blue-700 mt-2">După editare: CTRL+X → Y → Enter pentru a salva</p>
                            </div>

                            <p class="text-xs mt-3"><strong>Notă:</strong> Pentru testare, lăsați <code class="bg-orange-100 px-1 rounded">BT_SANDBOX_MODE=true</code></p>
                        </div>
                    </div>
                </div>
            @else
                {{-- Info Card --}}
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                    <div class="flex items-start">
                        <svg class="h-5 w-5 text-blue-500 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div class="text-sm text-blue-700">
                            <p class="font-semibold mb-1">Despre Integrarea Bancară</p>
                            <p>Conectați-vă contul Banca Transilvania pentru a sincroniza automat tranzacțiile și a descărca extrasele de cont lunare. Sistemul va sincroniza tranzacțiile la fiecare {{ config('banking.sync.frequency_hours') }} ore și va încerca să le potrivească automat cu facturile și cheltuielile dvs.</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Connected Accounts --}}
            @if ($credentials->count() > 0)
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 bg-slate-100">
                        <h3 class="text-lg font-semibold text-slate-800">Conturi Conectate</h3>
                    </div>

                    <div class="divide-y divide-slate-200">
                        @foreach ($credentials as $credential)
                            <div class="px-6 py-5 hover:bg-slate-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="text-base font-semibold text-slate-900">{{ $credential->bank_name }}</h4>
                                                <p class="text-sm text-slate-600">{{ $credential->account_iban }}</p>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                                            {{-- Status --}}
                                            <div class="flex items-center gap-2">
                                                @if ($credential->status === 'active')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <circle cx="10" cy="10" r="5"/>
                                                        </svg>
                                                        Activ
                                                    </span>
                                                @elseif ($credential->status === 'error')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <circle cx="10" cy="10" r="5"/>
                                                        </svg>
                                                        Eroare
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <circle cx="10" cy="10" r="5"/>
                                                        </svg>
                                                        Inactiv
                                                    </span>
                                                @endif
                                            </div>

                                            {{-- Last Sync --}}
                                            <div class="text-sm text-slate-600">
                                                <span class="font-medium">Ultima sincronizare:</span>
                                                {{ $credential->last_successful_sync_at?->diffForHumans() ?? 'Niciodată' }}
                                            </div>

                                            {{-- Consent Expiry --}}
                                            <div class="text-sm">
                                                @if ($credential->consent_expires_at && $credential->consent_expires_at->isPast())
                                                    <span class="text-red-600 font-medium">Consimțământ expirat</span>
                                                @elseif ($credential->consent_expires_at && $credential->consent_expires_at->diffInDays() <= 7)
                                                    <span class="text-orange-600 font-medium">Expiră în {{ $credential->consent_expires_at->diffForHumans() }}</span>
                                                @else
                                                    <span class="text-slate-600">Valabil până {{ $credential->consent_expires_at?->format('d.m.Y') }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        @if ($credential->error_message)
                                            <div class="mt-3 text-sm text-red-600">
                                                <span class="font-medium">Eroare:</span> {{ $credential->error_message }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2 ml-4">
                                        <a href="{{ route('settings.banking.show', $credential) }}"
                                           class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                            Detalii
                                        </a>

                                        @if ($credential->canSync())
                                            <form action="{{ route('settings.banking.sync', $credential) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                    </svg>
                                                    Sincronizează
                                                </button>
                                            </form>
                                        @endif

                                        <form action="{{ route('settings.banking.disconnect', $credential) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    onclick="return confirm('Sigur doriți să deconectați acest cont bancar? Toate datele asociate vor rămâne salvate.')"
                                                    class="inline-flex items-center px-4 py-2 bg-white border border-red-300 rounded-lg text-sm font-medium text-red-700 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                                Deconectează
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                {{-- No Connected Accounts --}}
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-slate-900">Niciun cont conectat</h3>
                        <p class="mt-1 text-sm text-slate-500">Începeți prin a conecta contul dvs. Banca Transilvania.</p>
                        <div class="mt-6">
                            @if(config('banking.banca_transilvania.client_id') && config('banking.banca_transilvania.client_secret'))
                                <a href="{{ route('settings.banking.connect') }}"
                                   class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-lg text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Conectează Cont Bancar
                                </a>
                            @else
                                <button disabled
                                        class="inline-flex items-center px-6 py-3 bg-slate-300 border border-transparent rounded-lg text-base font-medium text-slate-500 cursor-not-allowed">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    Configurare Necesară
                                </button>
                                <p class="mt-2 text-xs text-slate-500">Configurați credențialele API mai sus</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- System Status --}}
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-100">
                    <h3 class="text-lg font-semibold text-slate-800">Status Sistem</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-sm text-slate-600">Mod:</span>
                            <span class="ml-2 text-sm font-medium">
                                @if (config('banking.banca_transilvania.sandbox_mode'))
                                    <span class="text-orange-600">Sandbox (Test)</span>
                                @else
                                    <span class="text-green-600">Production</span>
                                @endif
                            </span>
                        </div>
                        <div>
                            <span class="text-sm text-slate-600">Frecvență sincronizare:</span>
                            <span class="ml-2 text-sm font-medium text-slate-900">La fiecare {{ config('banking.sync.frequency_hours') }} ore</span>
                        </div>
                        <div>
                            <span class="text-sm text-slate-600">Potrivire automată:</span>
                            <span class="ml-2 text-sm font-medium">
                                @if (config('banking.matching.auto_match_enabled'))
                                    <span class="text-green-600">Activă</span>
                                @else
                                    <span class="text-slate-600">Dezactivată</span>
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-200">
                        <button onclick="testConnection()"
                                class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Testează Conexiunea
                        </button>
                    </div>
                </div>
            </div>
    </div>

    <script>
        function testConnection() {
            fetch('{{ route('settings.banking.test-connection') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
            })
            .catch(error => {
                alert('Eroare la testarea conexiunii: ' + error.message);
            });
        }

        function copyToClipboard(text) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(() => {
                    // Show success feedback
                    const toast = document.createElement('div');
                    toast.className = 'fixed top-4 right-4 z-50 px-4 py-2 bg-green-600 text-white rounded-lg shadow-lg transition-opacity duration-300';
                    toast.textContent = '✓ Copiat în clipboard!';
                    document.body.appendChild(toast);

                    setTimeout(() => {
                        toast.style.opacity = '0';
                        setTimeout(() => document.body.removeChild(toast), 300);
                    }, 2000);
                }).catch(err => {
                    console.error('Failed to copy:', err);
                    fallbackCopy(text);
                });
            } else {
                fallbackCopy(text);
            }
        }

        function fallbackCopy(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                alert('Copiat în clipboard!');
            } catch (err) {
                alert('Nu s-a putut copia. Vă rugăm copiați manual: ' + text);
            }
            document.body.removeChild(textarea);
        }
    </script>
</x-app-layout>
