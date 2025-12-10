<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractAnnex;
use App\Models\Client;
use App\Models\Offer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of contracts.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->wantsJson() || $request->ajax()) {
            return $this->indexJson($request);
        }

        $stats = Contract::getStatistics();

        return view('contracts.index', compact('stats'));
    }

    /**
     * Return contracts data as JSON.
     */
    private function indexJson(Request $request): JsonResponse
    {
        $query = Contract::with(['client', 'offer']);

        // Status filter
        if ($request->filled('status')) {
            $statuses = array_filter(explode(',', $request->status));
            if (!empty($statuses)) {
                $query->whereIn('status', $statuses);
            }
        }

        // Client filter
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Search
        if ($request->filled('q')) {
            $query->search($request->q);
        }

        // Sort
        $sort = $request->get('sort', 'created_at:desc');
        [$column, $direction] = $this->parseSort($sort);
        $query->orderBy($column, $direction);

        // Pagination
        $perPage = min((int) $request->get('limit', 25), 100);
        $contracts = $query->paginate($perPage);

        return response()->json([
            'contracts' => $contracts->map(function ($contract) {
                return [
                    'id' => $contract->id,
                    'contract_number' => $contract->contract_number,
                    'title' => $contract->title,
                    'status' => $contract->status,
                    'status_label' => $contract->status_label,
                    'status_color' => $contract->status_color,
                    'total_value' => $contract->total_value,
                    'currency' => $contract->currency,
                    'start_date' => $contract->start_date?->format('Y-m-d'),
                    'end_date' => $contract->end_date?->format('Y-m-d'),
                    'days_until_expiry' => $contract->days_until_expiry,
                    'expiry_urgency' => $contract->expiry_urgency,
                    'auto_renew' => $contract->auto_renew,
                    'client' => $contract->client ? [
                        'id' => $contract->client->id,
                        'name' => $contract->client->display_name,
                        'slug' => $contract->client->slug,
                    ] : null,
                    'created_at' => $contract->created_at?->toISOString(),
                ];
            }),
            'pagination' => [
                'total' => $contracts->total(),
                'per_page' => $contracts->perPage(),
                'current_page' => $contracts->currentPage(),
                'last_page' => $contracts->lastPage(),
            ],
            'stats' => Contract::getStatistics(),
        ]);
    }

    private function parseSort(string $sort): array
    {
        $parts = explode(':', $sort);
        $column = $parts[0];
        $direction = $parts[1] ?? 'desc';

        $columnMap = [
            'number' => 'contract_number',
            'title' => 'title',
            'total' => 'total_value',
            'status' => 'status',
            'start_date' => 'start_date',
            'end_date' => 'end_date',
            'created_at' => 'created_at',
        ];

        return [
            $columnMap[$column] ?? 'created_at',
            in_array($direction, ['asc', 'desc']) ? $direction : 'desc',
        ];
    }

    /**
     * Display the specified contract.
     */
    public function show(Contract $contract): View
    {
        $contract->load(['client', 'offer.items', 'annexes.offer', 'template']);

        return view('contracts.show', compact('contract'));
    }

    /**
     * Show form to add annex from an existing offer.
     */
    public function addAnnexForm(Contract $contract): View
    {
        // Get accepted offers for this client that are not yet linked to this contract
        $availableOffers = Offer::where('client_id', $contract->client_id)
            ->where('status', 'accepted')
            ->where(function ($q) use ($contract) {
                $q->whereNull('contract_id')
                  ->orWhere('contract_id', '!=', $contract->id);
            })
            ->get();

        return view('contracts.add-annex', compact('contract', 'availableOffers'));
    }

    /**
     * Add annex from an accepted offer.
     */
    public function addAnnex(Request $request, Contract $contract): RedirectResponse
    {
        $validated = $request->validate([
            'offer_id' => 'required|exists:offers,id',
        ]);

        $offer = Offer::findOrFail($validated['offer_id']);

        if (!$offer->isAccepted()) {
            return back()->with('error', __('Only accepted offers can be added as annexes.'));
        }

        try {
            $annex = $contract->addAnnexFromOffer($offer);

            return redirect()
                ->route('contracts.show', $contract)
                ->with('success', __('Annex added successfully.'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Terminate a contract.
     */
    public function terminate(Contract $contract): RedirectResponse
    {
        if (!$contract->isActive()) {
            return back()->with('error', __('Only active contracts can be terminated.'));
        }

        $contract->terminate();

        return back()->with('success', __('Contract terminated successfully.'));
    }

    /**
     * Download contract PDF.
     */
    public function downloadPdf(Contract $contract)
    {
        if (!$contract->pdf_path || !file_exists(storage_path('app/' . $contract->pdf_path))) {
            return back()->with('error', __('PDF not available.'));
        }

        return response()->download(
            storage_path('app/' . $contract->pdf_path),
            $contract->contract_number . '.pdf'
        );
    }

    /**
     * Download annex PDF.
     */
    public function downloadAnnexPdf(Contract $contract, ContractAnnex $annex)
    {
        if ($annex->contract_id !== $contract->id) {
            abort(404);
        }

        if (!$annex->pdf_path || !file_exists(storage_path('app/' . $annex->pdf_path))) {
            return back()->with('error', __('PDF not available.'));
        }

        return response()->download(
            storage_path('app/' . $annex->pdf_path),
            $annex->annex_code . '.pdf'
        );
    }

    /**
     * Get contracts for a specific client (API).
     */
    public function forClient(Client $client): JsonResponse
    {
        $contracts = Contract::where('client_id', $client->id)
            ->where('status', 'active')
            ->orderBy('contract_number')
            ->get(['id', 'contract_number', 'title', 'start_date', 'end_date']);

        return response()->json([
            'contracts' => $contracts,
        ]);
    }
}
