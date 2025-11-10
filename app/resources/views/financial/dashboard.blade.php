<x-app-layout>
    <div class="p-6 space-y-6">
        <!-- Header with Filters -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Tablou de bord financiar</h1>
                <p class="text-sm text-slate-600 mt-1">Prezentare generală a veniturilor și cheltuielilor</p>
            </div>
            <div class="flex gap-3">
                <form method="GET" class="flex gap-2">
                    <select name="year" onchange="this.form.submit()" class="rounded-lg border-slate-300 text-sm">
                        @foreach($availableYears as $availableYear)
                            <option value="{{ $availableYear }}" {{ $year == $availableYear ? 'selected' : '' }}>
                                {{ $availableYear }}
                            </option>
                        @endforeach
                    </select>
                    <select name="month" onchange="this.form.submit()" class="rounded-lg border-slate-300 text-sm">
                        <option value="">Toate lunile</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </form>
            </div>
        </div>

        <!-- KPI Cards by Currency -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($currencies as $currency)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <p class="text-sm font-medium text-slate-600 mb-1">Venituri ({{ $currency }})</p>
                    <p class="text-3xl font-bold text-green-600">{{ number_format($revenueTotals->get($currency, 0), 2) }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <p class="text-sm font-medium text-slate-600 mb-1">Cheltuieli ({{ $currency }})</p>
                    <p class="text-3xl font-bold text-red-600">{{ number_format($expenseTotals->get($currency, 0), 2) }}</p>
                </div>
            @endforeach
        </div>

        <!-- Profit Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($currencies as $currency)
                <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl shadow-sm p-6">
                    <p class="text-sm font-medium text-slate-300 mb-1">Profit ({{ $currency }})</p>
                    <p class="text-3xl font-bold {{ $profitTotals->get($currency, 0) >= 0 ? 'text-green-400' : 'text-red-400' }}">
                        {{ number_format($profitTotals->get($currency, 0), 2) }}
                    </p>
                </div>
            @endforeach
        </div>

        <!-- Recent Transactions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Revenues -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Venituri recente</h3>
                <div class="space-y-3">
                    @forelse($recentRevenues as $revenue)
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div class="flex-1">
                                <p class="font-medium text-slate-900">{{ $revenue->document_name }}</p>
                                <p class="text-sm text-slate-600">{{ $revenue->client?->name ?? 'Fără client' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-green-600">{{ $revenue->formatted_amount }}</p>
                                <p class="text-xs text-slate-500">{{ $revenue->occurred_at->format('d M Y') }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-slate-500 text-center py-4">Nu există venituri recente</p>
                    @endforelse
                </div>
                <a href="{{ route('financial.venituri.index') }}" class="block mt-4 text-center text-sm text-primary-600 hover:text-primary-700 font-medium">
                    Vezi toate veniturile →
                </a>
            </div>

            <!-- Recent Expenses -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Cheltuieli recente</h3>
                <div class="space-y-3">
                    @forelse($recentExpenses as $expense)
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                            <div class="flex-1">
                                <p class="font-medium text-slate-900">{{ $expense->document_name }}</p>
                                <p class="text-sm text-slate-600">{{ $expense->category?->option_label ?? 'Fără categorie' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-red-600">{{ $expense->formatted_amount }}</p>
                                <p class="text-xs text-slate-500">{{ $expense->occurred_at->format('d M Y') }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-slate-500 text-center py-4">Nu există cheltuieli recente</p>
                    @endforelse
                </div>
                <a href="{{ route('financial.cheltuieli.index') }}" class="block mt-4 text-center text-sm text-primary-600 hover:text-primary-700 font-medium">
                    Vezi toate cheltuielile →
                </a>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="flex gap-3">
            <a href="{{ route('financial.venituri.create') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                + Adaugă venit
            </a>
            <a href="{{ route('financial.cheltuieli.create') }}" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                + Adaugă cheltuială
            </a>
            <a href="{{ route('financial.export', $year) }}" class="px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 font-medium">
                Exportă CSV
            </a>
        </div>
    </div>
</x-app-layout>
