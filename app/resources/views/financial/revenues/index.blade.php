<x-app-layout>
    <x-slot name="pageTitle">{{ __('Revenues') }}</x-slot>

    <x-slot name="headerActions">
        <div class="flex items-center gap-3">
            <form method="GET" id="filterForm" class="flex items-center gap-3">
                <input type="hidden" name="month" value="{{ $month }}">

                <!-- Search -->
                <div class="relative">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="{{ __('Caută...') }}"
                        class="w-40 pl-8 pr-3 py-1.5 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900"
                        onchange="this.form.submit()">
                    <svg class="absolute left-2.5 top-1/2 transform -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-slate-700">{{ __('An') }}:</label>
                    <x-ui.select name="year" onchange="this.form.submit()">
                        @foreach($availableYears as $availableYear)
                            <option value="{{ $availableYear }}" {{ $year == $availableYear ? 'selected' : '' }}>{{ $availableYear }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-slate-700">{{ __('Valută') }}:</label>
                    <x-ui.select name="currency" onchange="this.form.submit()">
                        <option value="">{{ __('Toate') }}</option>
                        @foreach($currencies as $curr)
                            <option value="{{ $curr->value }}" {{ $currency == $curr->value ? 'selected' : '' }}>{{ $curr->label }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
            </form>

            <x-ui.button variant="default" onclick="window.location.href='{{ route('financial.revenues.create', ['month' => $month, 'year' => $year]) }}'">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('Add Revenue') }}
            </x-ui.button>
        </div>
    </x-slot>

    <div class="p-6" x-data="revenueBulkSelection()">

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <!-- Widgets Container -->
        <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Widget 1: Filtered Total -->
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-medium text-slate-500 uppercase">
                            @if($month)
                                @php
                                    $romanianMonths = ['Ianuarie', 'Februarie', 'Martie', 'Aprilie', 'Mai', 'Iunie', 'Iulie', 'August', 'Septembrie', 'Octombrie', 'Noiembrie', 'Decembrie'];
                                @endphp
                                {{ __('Total pentru') }} {{ $romanianMonths[$month - 1] }}
                            @else
                                {{ __('Total an') }} {{ $year }}
                            @endif
                        </p>
                        <div class="mt-2">
                            @if($filteredTotals->count() > 0)
                                <p class="text-lg font-bold text-green-700">
                                    @foreach($filteredTotals as $curr => $total)
                                        {{ number_format($total, 2) }} {{ $curr }}@if(!$loop->last) / @endif
                                    @endforeach
                                </p>
                            @else
                                <p class="text-lg font-bold text-slate-400">0.00</p>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-1">
                            {{ $currency ?: __('Toate valutele') }}
                        </p>
                    </div>
                    <div class="p-3 bg-green-50 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Widget 2: Yearly Total (All Currencies) -->
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-medium text-slate-500 uppercase">{{ __('Total pe an') }} {{ $year }}</p>
                        <div class="mt-2">
                            @if($yearTotals->count() > 0)
                                <p class="text-lg font-bold text-blue-700">
                                    @foreach($yearTotals as $curr => $total)
                                        {{ number_format($total, 2) }} {{ $curr }}@if(!$loop->last) / @endif
                                    @endforeach
                                </p>
                            @else
                                <p class="text-lg font-bold text-slate-400">0.00</p>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-1">{{ __('Toate valutele') }}</p>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Month Selector -->
        <div class="mb-6 bg-white rounded-lg shadow p-4">
            <form method="GET" id="monthForm">
                <!-- Keep year and currency hidden to maintain filter state -->
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="currency" value="{{ $currency }}">

                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-medium text-slate-700">{{ __('Selectează luna') }}</h3>
                    @if($month)
                        <button type="button" onclick="document.getElementById('month-input').value = ''; document.getElementById('monthForm').submit();" class="text-xs text-slate-500 hover:text-slate-700">
                            {{ __('Toate lunile') }}
                        </button>
                    @endif
                </div>
                <input type="hidden" name="month" id="month-input" value="{{ $month }}">

                <!-- Month Navigation with Arrows -->
                <div class="flex items-center gap-3">
                    <!-- Left Arrow -->
                    <button type="button"
                            onclick="navigateMonth(-1)"
                            class="flex-shrink-0 p-2 rounded-lg border-2 border-slate-200 hover:border-green-500 hover:bg-green-50 transition-all group disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:border-slate-200 disabled:hover:bg-transparent"
                            {{ $month == 1 ? 'disabled' : '' }}
                            title="{{ __('Previous month') }}">
                        <svg class="w-5 h-5 text-slate-600 group-hover:text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>

                    <!-- Month Grid -->
                    <div class="flex-1 grid grid-cols-6 gap-2 sm:grid-cols-12">
                        @foreach(['Ian', 'Feb', 'Mar', 'Apr', 'Mai', 'Iun', 'Iul', 'Aug', 'Sep', 'Oct', 'Noi', 'Dec'] as $index => $monthName)
                            @php
                                $monthNum = $index + 1;
                                $hasTransactions = isset($monthsWithTransactions[$monthNum]);
                                $isSelected = $month == $monthNum;
                                $transactionCount = $hasTransactions ? $monthsWithTransactions[$monthNum]['count'] : 0;
                                $monthTotal = $hasTransactions ? $monthsWithTransactions[$monthNum]['total'] : 0;
                            @endphp
                            <div class="relative group">
                                <button
                                    type="button"
                                    onclick="document.getElementById('month-input').value = '{{ $monthNum }}'; document.getElementById('monthForm').submit();"
                                    class="w-full relative flex flex-col items-center justify-center p-3 rounded-lg border-2 transition-all
                                        {{ $isSelected ? 'border-green-500 bg-green-50 text-green-700 font-semibold' : ($hasTransactions ? 'border-slate-200 bg-slate-50 hover:border-green-300 hover:bg-green-50' : 'border-slate-100 bg-white text-slate-400') }}"
                                    title="{{ $hasTransactions ? $transactionCount . ' ' . __('transactions') . ' | ' . number_format($monthTotal, 0) . ' Lei' : __('No items') }}"
                                >
                                    <span class="text-xs font-medium">{{ $monthName }}</span>
                                    @if($hasTransactions)
                                        <span class="mt-1 text-[10px] font-semibold {{ $isSelected ? 'text-green-600' : 'text-slate-600' }}">
                                            {{ $transactionCount }} | {{ number_format($monthTotal, 0) }}
                                        </span>
                                    @endif
                                </button>
                            </div>
                        @endforeach
                    </div>

                    <!-- Right Arrow -->
                    <button type="button"
                            onclick="navigateMonth(1)"
                            class="flex-shrink-0 p-2 rounded-lg border-2 border-slate-200 hover:border-green-500 hover:bg-green-50 transition-all group disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:border-slate-200 disabled:hover:bg-transparent"
                            {{ $month == 12 ? 'disabled' : '' }}
                            title="{{ __('Next month') }}">
                        <svg class="w-5 h-5 text-slate-600 group-hover:text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </form>

            <!-- Navigation JavaScript -->
            <script>
            function navigateMonth(direction) {
                const currentMonth = {{ $month ?: 0 }};
                const newMonth = currentMonth + direction;

                if (newMonth >= 1 && newMonth <= 12) {
                    document.getElementById('month-input').value = newMonth;
                    document.getElementById('monthForm').submit();
                }
            }
            </script>
        </div>

        <!-- Bulk Actions Toolbar -->
        <x-bulk-toolbar>
            <x-ui.button
                variant="outline"
                @click="performBulkAction('export', '{{ route('financial.revenues.bulk-export') }}', {
                    confirmTitle: '{{ __('Export Revenues') }}',
                    confirmMessage: '{{ __('Export selected revenues to CSV?') }}',
                    successMessage: '{{ __('Revenues exported successfully!') }}'
                })"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                {{ __('Export to CSV') }}
            </x-ui.button>
            <x-ui.button
                variant="destructive"
                @click="performBulkAction('delete', '{{ route('financial.revenues.bulk-update') }}', {
                    confirmTitle: '{{ __('Delete Revenues') }}',
                    confirmMessage: '{{ __('Are you sure you want to delete the selected revenues? This action cannot be undone.') }}',
                    successMessage: '{{ __('Revenues deleted successfully!') }}'
                })"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                {{ __('Delete Selected') }}
            </x-ui.button>
        </x-bulk-toolbar>

        <!-- Table -->
        <x-ui.card>
            <div class="overflow-x-auto">
                <table class="w-full caption-bottom text-sm">
                    <thead class="bg-slate-100">
                        <tr class="border-b border-slate-200">
                            <th class="px-6 py-4 text-left align-middle font-medium text-slate-500 w-12">
                                <input type="checkbox" x-model="selectAll" @change="toggleAll()"
                                       class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <x-ui.sortable-header column="occurred_at" label="{{ __('Date') }}" />
                            <x-ui.sortable-header column="document_name" label="{{ __('Factura') }}" />
                            <x-ui.sortable-header column="client_id" label="{{ __('Client') }}" />
                            <x-ui.sortable-header column="amount" label="{{ __('Amount') }}" class="text-right" />
                            <x-ui.table-head class="text-center">{{ __('Files') }}</x-ui.table-head>
                            <x-ui.table-head class="text-right">{{ __('Actions') }}</x-ui.table-head>
                        </tr>
                    </thead>
                    <tbody class="[&_tr:last-child]:border-0">
                        @forelse($revenues as $revenue)
                            <x-ui.table-row data-selectable data-revenue-id="{{ $revenue->id }}">
                                <x-ui.table-cell>
                                    <input type="checkbox"
                                           :checked="selectedIds.includes({{ $revenue->id }})"
                                           @change="toggleItem({{ $revenue->id }})"
                                           class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                </x-ui.table-cell>
                                <x-ui.table-cell>
                                    <div class="text-sm text-slate-900">{{ $revenue->occurred_at->format('d M Y') }}</div>
                                </x-ui.table-cell>
                                <x-ui.table-cell>
                                    <div class="text-sm font-medium text-slate-900">{{ $revenue->document_name }}</div>
                                </x-ui.table-cell>
                                <x-ui.table-cell>
                                    <div class="text-sm text-slate-700">{{ $revenue->client?->name ?? '—' }}</div>
                                </x-ui.table-cell>
                                <x-ui.table-cell class="text-right">
                                    <div class="text-sm font-bold text-green-600">{{ number_format($revenue->amount, 2) }} {{ $revenue->currency }}</div>
                                </x-ui.table-cell>

                                <!-- Files Column -->
                                <x-ui.table-cell class="text-center">
                                    @if($revenue->files_count > 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                            {{ $revenue->files_count }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-xs">—</span>
                                    @endif
                                </x-ui.table-cell>

                                <!-- Actions Column -->
                                <x-ui.table-cell class="text-right">
                                    <x-table-actions
                                        :viewUrl="route('financial.revenues.show', $revenue)"
                                        :editUrl="route('financial.revenues.edit', $revenue)"
                                        :deleteAction="route('financial.revenues.destroy', $revenue)"
                                        :deleteConfirm="__('Are you sure you want to delete this revenue?')">
                                        {{-- Download files action --}}
                                        @if($revenue->files_count > 0)
                                            @if($revenue->files_count == 1)
                                                <a href="{{ route('financial.files.download', $revenue->files->first()) }}" class="text-green-600 hover:text-green-900 transition-colors" title="{{ __('Download') }}">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                    </svg>
                                                </a>
                                            @else
                                                <a href="{{ route('financial.revenues.show', $revenue) }}#files" class="text-green-600 hover:text-green-900 transition-colors" title="{{ __('View files') }}">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                    </svg>
                                                </a>
                                            @endif
                                        @endif
                                    </x-table-actions>
                                </x-ui.table-cell>
                            </x-ui.table-row>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="text-slate-500">
                                        <svg class="mx-auto h-12 w-12 text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p class="text-sm">{{ __('No revenues') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination & Info --}}
            <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-between">
                <p class="text-sm text-slate-600">
                    {{ __('Afișare') }} <span class="font-semibold text-slate-900">{{ $revenues->firstItem() ?? 0 }}-{{ $revenues->lastItem() ?? 0 }}</span> {{ __('din') }} <span class="font-semibold text-slate-900">{{ $recordCount }}</span> {{ __('înregistrări') }}
                    @if($month)
                        @php
                            $romanianMonths = ['Ianuarie', 'Februarie', 'Martie', 'Aprilie', 'Mai', 'Iunie', 'Iulie', 'August', 'Septembrie', 'Octombrie', 'Noiembrie', 'Decembrie'];
                        @endphp
                        <span class="text-slate-500">{{ __('pentru') }} {{ $romanianMonths[$month - 1] }} {{ $year }}</span>
                    @else
                        <span class="text-slate-500">{{ __('pentru anul') }} {{ $year }}</span>
                    @endif
                </p>
                @if($revenues->hasPages())
                    <div>
                        {{ $revenues->links() }}
                    </div>
                @endif
            </div>
        </x-ui.card>
    </div>

    <!-- Toast Notifications -->
    <x-toast />

    @push('scripts')
    <script>
    function revenueBulkSelection() {
        return {
            selectedIds: [],
            selectAll: false,
            isLoading: false,

            get selectedCount() {
                return this.selectedIds.length;
            },

            get hasSelection() {
                return this.selectedIds.length > 0;
            },

            toggleItem(id) {
                const index = this.selectedIds.indexOf(id);
                if (index > -1) {
                    this.selectedIds.splice(index, 1);
                } else {
                    this.selectedIds.push(id);
                }
                this.updateSelectAllState();
            },

            toggleAll() {
                if (this.selectAll) {
                    this.selectAllVisible();
                } else {
                    this.selectedIds = [];
                }
            },

            selectAllVisible() {
                const rows = document.querySelectorAll('[data-selectable]');
                this.selectedIds = Array.from(rows).map(row => parseInt(row.dataset.revenueId));
            },

            updateSelectAllState() {
                const rows = document.querySelectorAll('[data-selectable]');
                this.selectAll = rows.length > 0 && this.selectedIds.length === rows.length;
            },

            clearSelection() {
                this.selectedIds = [];
                this.selectAll = false;
            },

            async performBulkAction(action, endpoint, options = {}) {
                if (this.selectedIds.length === 0) return;
                if (!confirm(options.confirmMessage || 'Are you sure?')) return;

                this.isLoading = true;
                try {
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ ids: this.selectedIds, action: action })
                    });

                    const data = await response.json();
                    if (response.ok && data.success) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: options.successMessage || 'Success', type: 'success' } }));
                        this.clearSelection();
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message || 'Error', type: 'error' } }));
                    }
                } catch (error) {
                    console.error('Error:', error);
                } finally {
                    this.isLoading = false;
                }
            }
        };
    }
    </script>
    @endpush
</x-app-layout>
