<x-app-layout>
    <x-slot name="pageTitle">Cheltuieli</x-slot>

    <x-slot name="headerActions">
        <button onclick="window.location.href='{{ route('financial.expenses.create') }}'" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm font-medium transition-colors">
            + Adaugă cheltuială
        </button>
    </x-slot>

    <div class="p-6" x-data>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <!-- Filters -->
        <form method="GET" class="mb-6 flex gap-2 flex-wrap">
            <select name="year" class="rounded-lg border-slate-300">
                @foreach($availableYears as $availableYear)
                    <option value="{{ $availableYear }}" {{ $year == $availableYear ? 'selected' : '' }}>{{ $availableYear }}</option>
                @endforeach
            </select>
            <select name="month" class="rounded-lg border-slate-300">
                <option value="">Toate lunile</option>
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                    </option>
                @endfor
            </select>
            <select name="currency" class="rounded-lg border-slate-300">
                <option value="">Toate valutele</option>
                <option value="RON" {{ $currency == 'RON' ? 'selected' : '' }}>RON</option>
                <option value="EUR" {{ $currency == 'EUR' ? 'selected' : '' }}>EUR</option>
            </select>
            <select name="category_id" class="rounded-lg border-slate-300">
                <option value="">Toate categoriile</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                        {{ $category->option_label }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700">Filtrează</button>
        </form>

        <!-- Totals -->
        <div class="mb-4 flex gap-4">
            @foreach($totals as $curr => $total)
                <div class="px-4 py-2 bg-red-50 rounded-lg">
                    <span class="text-sm text-slate-600">Total {{ $curr }}:</span>
                    <span class="ml-2 font-bold text-red-700">{{ number_format($total, 2) }}</span>
                </div>
            @endforeach
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Dată</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Document</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Categorie</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Sumă</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Acțiuni</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($expenses as $expense)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $expense->occurred_at->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-sm font-medium">{{ $expense->document_name }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if($expense->category)
                                    <span class="px-2 py-1 rounded text-xs {{ $expense->category->badge_class }}">
                                        {{ $expense->category->option_label }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-red-600">{{ number_format($expense->amount, 2) }} {{ $expense->currency }}</td>
                            <td class="px-6 py-4 text-right text-sm space-x-2">
                                <button onclick="window.location.href='{{ route('financial.expenses.edit', $expense) }}'" class="text-blue-600 hover:text-blue-900">
                                    Editează
                                </button>
                                <form method="POST" action="{{ route('financial.expenses.destroy', $expense) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Ești sigur?')" class="text-red-600 hover:text-red-900">Șterge</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-slate-500">Nu există cheltuieli</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $expenses->links() }}
        </div>
    </div>

    <!-- Toast Notifications -->
    <x-toast />
</x-app-layout>
