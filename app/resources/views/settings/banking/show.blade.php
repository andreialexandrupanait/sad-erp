<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                    {{ __('Detalii Cont Bancar') }}
                </h2>
                <p class="mt-1 text-sm text-slate-600">{{ $credential->account_iban }}</p>
            </div>
            <a href="{{ route('settings.banking.index') }}" class="text-sm text-blue-600 hover:text-blue-700">
                ← Înapoi la Conturi
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Statistics Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="text-sm text-slate-600 mb-1">Total Tranzacții</div>
                    <div class="text-2xl font-bold text-slate-900">{{ number_format($stats['total_transactions']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="text-sm text-slate-600 mb-1">Nepotrivite</div>
                    <div class="text-2xl font-bold text-orange-600">{{ number_format($stats['unmatched_transactions']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="text-sm text-slate-600 mb-1">Potrivite</div>
                    <div class="text-2xl font-bold text-green-600">{{ number_format($stats['matched_transactions']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="text-sm text-slate-600 mb-1">Ultima Sincronizare</div>
                    <div class="text-lg font-semibold text-slate-900">{{ $stats['last_sync'] ?? 'Niciodată' }}</div>
                </div>
            </div>

            {{-- Account Info --}}
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-800">Informații Cont</h3>
                </div>
                <div class="px-6 py-5">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-slate-600">Bancă</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ $credential->bank_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-slate-600">IBAN</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ $credential->account_iban }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-slate-600">Monedă</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ $credential->currency }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-slate-600">Status</dt>
                            <dd class="mt-1">
                                @if ($credential->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Activ</span>
                                @elseif ($credential->status === 'error')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Eroare</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">Inactiv</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-slate-600">Consimțământ Valabil Până</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ $credential->consent_expires_at?->format('d.m.Y H:i') ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-slate-600">Ultimă Sincronizare</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ $credential->last_successful_sync_at?->format('d.m.Y H:i') ?? 'Niciodată' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Recent Sync Logs --}}
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-800">Istoric Sincronizări</h3>
                </div>
                @if ($credential->syncLogs->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Tip</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Tranzacții</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Durată</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                @foreach ($credential->syncLogs as $log)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                            {{ $log->started_at->format('d.m.Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                            {{ ucfirst($log->sync_type) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($log->status === 'success')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Succes</span>
                                            @elseif ($log->status === 'failed')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Eșuat</span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">În curs</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                            {{ $log->transactions_new }} noi, {{ $log->transactions_updated }} actualizate
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                            {{ $log->duration_formatted ?? 'N/A' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-8 text-center text-sm text-slate-500">
                        Nicio sincronizare încă
                    </div>
                @endif
            </div>

            {{-- Recent Transactions --}}
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-slate-800">Tranzacții Recente</h3>
                    <a href="{{ route('financial.revenues.index') }}" class="text-sm text-blue-600 hover:text-blue-700">
                        Vezi toate →
                    </a>
                </div>
                @if ($credential->transactions->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Tip</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Descriere</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Sumă</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status Potrivire</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                @foreach ($credential->transactions as $transaction)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                            {{ $transaction->booking_date->format('d.m.Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if ($transaction->type === 'incoming')
                                                <span class="text-green-600">↓ Intrare</span>
                                            @else
                                                <span class="text-red-600">↑ Ieșire</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600 max-w-xs truncate">
                                            {{ $transaction->display_description }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">
                                            <span class="{{ $transaction->type === 'incoming' ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $transaction->type === 'incoming' ? '+' : '-' }}{{ number_format($transaction->amount, 2) }} {{ $transaction->currency }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if ($transaction->match_status === 'unmatched')
                                                <span class="text-orange-600">Nepotrivit</span>
                                            @elseif ($transaction->match_status === 'auto_matched')
                                                <span class="text-green-600">Auto-potrivit</span>
                                            @elseif ($transaction->match_status === 'manual_matched')
                                                <span class="text-blue-600">Manual-potrivit</span>
                                            @else
                                                <span class="text-slate-600">Ignorat</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-8 text-center text-sm text-slate-500">
                        Nicio tranzacție încă
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
