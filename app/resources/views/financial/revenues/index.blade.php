<x-app-layout>
    <x-slot name="pageTitle">Venituri</x-slot>

    <x-slot name="headerActions">
        <x-ui.button variant="default" onclick="window.location.href='{{ route('financial.revenues.create') }}'">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Adaugă venit
        </x-ui.button>
    </x-slot>

    <div class="p-6" x-data>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <!-- Filters -->
        <form method="GET" id="filterForm" class="mb-6 space-y-4">
            <div class="flex gap-2 flex-wrap">
                <select name="year" class="rounded-lg border-slate-300" onchange="this.form.submit()">
                    @foreach($availableYears as $availableYear)
                        <option value="{{ $availableYear }}" {{ $year == $availableYear ? 'selected' : '' }}>{{ $availableYear }}</option>
                    @endforeach
                </select>
                <select name="currency" class="rounded-lg border-slate-300">
                    <option value="">Toate valutele</option>
                    <option value="RON" {{ $currency == 'RON' ? 'selected' : '' }}>RON</option>
                    <option value="EUR" {{ $currency == 'EUR' ? 'selected' : '' }}>EUR</option>
                </select>
                <select name="client_id" class="rounded-lg border-slate-300">
                    <option value="">{{ __('All Clients') }}</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ $clientId == $client->id ? 'selected' : '' }}>
                            {{ $client->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700">Filtrează</button>
            </div>

            <!-- Visual Month Selector -->
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-medium text-slate-700">Selectează luna</h3>
                    @if($month)
                        <button type="button" onclick="document.getElementById('month-input').value = ''; document.getElementById('filterForm').submit();" class="text-xs text-slate-500 hover:text-slate-700">
                            Toate lunile
                        </button>
                    @endif
                </div>
                <input type="hidden" name="month" id="month-input" value="{{ $month }}">
                <div class="grid grid-cols-6 gap-2 sm:grid-cols-12">
                    @foreach(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'] as $index => $monthName)
                        @php
                            $monthNum = $index + 1;
                            $hasTransactions = isset($monthsWithTransactions[$monthNum]);
                            $isSelected = $month == $monthNum;
                            $transactionCount = $hasTransactions ? $monthsWithTransactions[$monthNum]['count'] : 0;
                        @endphp
                        <div class="relative group">
                            <button
                                type="button"
                                onclick="document.getElementById('month-input').value = '{{ $monthNum }}'; document.getElementById('filterForm').submit();"
                                class="w-full relative flex flex-col items-center justify-center p-3 rounded-lg border-2 transition-all
                                    {{ $isSelected ? 'border-green-500 bg-green-50 text-green-700 font-semibold' : ($hasTransactions ? 'border-slate-200 bg-slate-50 hover:border-green-300 hover:bg-green-50' : 'border-slate-100 bg-white text-slate-400') }}"
                                title="{{ $hasTransactions ? $transactionCount . ' ' . __('transactions') : __('No items') }}"
                            >
                                <span class="text-xs font-medium">{{ $monthName }}</span>
                                @if($hasTransactions)
                                    <span class="absolute -top-1 -right-1 flex h-3 w-3">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $isSelected ? 'bg-green-400' : 'bg-blue-400' }} opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-3 w-3 {{ $isSelected ? 'bg-green-500' : 'bg-blue-500' }}"></span>
                                    </span>
                                    <span class="mt-1 text-[10px] font-semibold {{ $isSelected ? 'text-green-600' : 'text-slate-600' }}">
                                        {{ $transactionCount }}
                                    </span>
                                @endif
                            </button>
                            @if($hasTransactions)
                                <a href="{{ route('financial.files.download-monthly-zip', ['year' => $year, 'month' => $monthNum]) }}"
                                   class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity bg-blue-600 hover:bg-blue-700 text-white rounded-full p-1.5 shadow-lg z-10"
                                   title="{{ __('Download') }} ZIP"
                                   onclick="event.stopPropagation();">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </form>

        <!-- Enhanced Widgets -->
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Current Filter Total -->
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase">{{ $month ? __('Month Total') : __('Year Total') }}</p>
                        <div class="mt-2 space-y-1">
                            @forelse($totals as $curr => $total)
                                <p class="text-lg font-bold text-green-700">{{ number_format($total, 2) }} {{ $curr }}</p>
                            @empty
                                <p class="text-lg font-bold text-slate-400">0.00</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="p-3 bg-green-50 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Year Total -->
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase">{{ __('Year') }} {{ $year }}</p>
                        <div class="mt-2 space-y-1">
                            @forelse($yearTotals as $curr => $total)
                                <p class="text-lg font-bold text-blue-700">{{ number_format($total, 2) }} {{ $curr }}</p>
                            @empty
                                <p class="text-lg font-bold text-slate-400">0.00</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Current Month Total -->
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-indigo-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase">{{ \Carbon\Carbon::now()->format('F Y') }}</p>
                        <div class="mt-2 space-y-1">
                            @forelse($monthTotals as $curr => $total)
                                <p class="text-lg font-bold text-indigo-700">{{ number_format($total, 2) }} {{ $curr }}</p>
                            @empty
                                <p class="text-lg font-bold text-slate-400">0.00</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="p-3 bg-indigo-50 rounded-full">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Record Count -->
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-slate-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase">{{ __('Records') }}</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($recordCount) }}</p>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-full">
                        <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">{{ __('Date') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">{{ __('Document') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">{{ __('Client') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">{{ __('Amount') }}</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase">{{ __('Files') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($revenues as $revenue)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $revenue->occurred_at->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-sm font-medium">{{ $revenue->document_name }}</td>
                            <td class="px-6 py-4 text-sm">{{ $revenue->client?->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-green-600">{{ number_format($revenue->amount, 2) }} {{ $revenue->currency }}</td>

                            <!-- Files Column -->
                            <td class="px-6 py-4 text-center">
                                @if($revenue->files_count > 0)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $revenue->files_count }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>

                            <!-- Actions Column -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- View -->
                                    <a href="{{ route('financial.revenues.show', $revenue) }}" class="text-slate-600 hover:text-slate-900" title="{{ __('View') }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                    <!-- Edit -->
                                    <a href="{{ route('financial.revenues.edit', $revenue) }}" class="text-blue-600 hover:text-blue-900" title="{{ __('Edit') }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    <!-- Download files -->
                                    @if($revenue->files_count > 0)
                                        <a href="{{ route('financial.files.index', ['year' => $revenue->year, 'month' => $revenue->month, 'tip' => 'incasare']) }}" class="text-green-600 hover:text-green-900" title="{{ __('Download') }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                        </a>
                                    @endif

                                    <!-- Delete -->
                                    <form method="POST" action="{{ route('financial.revenues.destroy', $revenue) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="{{ __('Delete') }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-slate-500">{{ __('No revenues') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $revenues->links() }}
        </div>
    </div>

    <!-- Toast Notifications -->
    <x-toast />
</x-app-layout>
