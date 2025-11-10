<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinancialRevenue;
use App\Models\Client;
use Illuminate\Support\Facades\DB;

class RevenueController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month');
        $currency = $request->get('currency');
        $clientId = $request->get('client_id');

        $revenues = FinancialRevenue::with('client')
            ->forYear($year)
            ->when($month, fn($q) => $q->where('month', $month))
            ->when($currency, fn($q) => $q->where('currency', $currency))
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->latest('occurred_at')
            ->paginate(15);

        // Calculate totals by currency for current filter
        $totals = FinancialRevenue::forYear($year)
            ->when($month, fn($q) => $q->where('month', $month))
            ->when($currency, fn($q) => $q->where('currency', $currency))
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->select('currency', DB::raw('SUM(amount) as total'))
            ->groupBy('currency')
            ->get()
            ->mapWithKeys(fn($item) => [$item->currency => $item->total]);

        $clients = Client::orderBy('name')->get();

        // Available years
        $availableYears = FinancialRevenue::select(DB::raw('DISTINCT year'))
            ->orderByDesc('year')
            ->pluck('year');

        return view('financial.revenues.index', compact(
            'revenues',
            'totals',
            'year',
            'month',
            'currency',
            'clientId',
            'clients',
            'availableYears'
        ));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        return view('financial.revenues.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|in:RON,EUR',
            'occurred_at' => 'required|date',
            'client_id' => 'nullable|exists:clients,id',
            'note' => 'nullable|string',
        ]);

        FinancialRevenue::create($validated);

        return redirect()->route('financial.revenues.index')
            ->with('success', 'Revenue added successfully.');
    }

    public function show(FinancialRevenue $revenue)
    {
        $revenue->load('client', 'files');
        return view('financial.revenues.show', compact('revenue'));
    }

    public function edit(FinancialRevenue $revenue)
    {
        $clients = Client::orderBy('name')->get();
        return view('financial.revenues.edit', compact('revenue', 'clients'));
    }

    public function update(Request $request, FinancialRevenue $revenue)
    {
        $validated = $request->validate([
            'document_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|in:RON,EUR',
            'occurred_at' => 'required|date',
            'client_id' => 'nullable|exists:clients,id',
            'note' => 'nullable|string',
        ]);

        // Update year and month based on occurred_at
        $date = \Carbon\Carbon::parse($validated['occurred_at']);
        $validated['year'] = $date->year;
        $validated['month'] = $date->month;

        $revenue->update($validated);

        return redirect()->route('financial.revenues.index')
            ->with('success', 'Revenue updated successfully.');
    }

    public function destroy(FinancialRevenue $revenue)
    {
        $revenue->delete();

        return redirect()->route('financial.revenues.index')
            ->with('success', 'Revenue deleted successfully.');
    }
}
