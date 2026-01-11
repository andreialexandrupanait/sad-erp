<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Currency\BnrExchangeRateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ExchangeRateController extends Controller
{
    public function __construct(
        protected BnrExchangeRateService $bnrService
    ) {}

    /**
     * Get exchange rate for a currency pair on a specific date
     *
     * GET /api/exchange-rate?from=EUR&to=RON&date=2024-01-15
     */
    public function getRate(Request $request): JsonResponse
    {
        $request->validate([
            'from' => 'required|string|size:3',
            'to' => 'required|string|size:3',
            'date' => 'nullable|date',
        ]);

        $from = strtoupper($request->input('from'));
        $to = strtoupper($request->input('to'));
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : now();

        // Only support EUR -> RON for now
        if ($from !== 'EUR' || $to !== 'RON') {
            return response()->json([
                'success' => false,
                'error' => 'Only EUR to RON conversion is currently supported',
            ], 400);
        }

        $rate = $this->bnrService->getRate($from, $to, $date);

        if ($rate === null) {
            return response()->json([
                'success' => false,
                'error' => 'Rate not available for the specified date',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'from' => $from,
            'to' => $to,
            'rate' => $rate,
            'date' => $date->format('Y-m-d'),
            'source' => 'bnr',
        ]);
    }

    /**
     * Convert an amount from one currency to another
     *
     * GET /api/exchange-rate/convert?amount=100&from=EUR&to=RON&date=2024-01-15
     */
    public function convert(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'from' => 'required|string|size:3',
            'to' => 'required|string|size:3',
            'date' => 'nullable|date',
        ]);

        $amount = (float) $request->input('amount');
        $from = strtoupper($request->input('from'));
        $to = strtoupper($request->input('to'));
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : now();

        // Only support EUR -> RON for now
        if ($from !== 'EUR' || $to !== 'RON') {
            return response()->json([
                'success' => false,
                'error' => 'Only EUR to RON conversion is currently supported',
            ], 400);
        }

        $result = $this->bnrService->convertEurToRon($amount, $date);

        if ($result === null) {
            return response()->json([
                'success' => false,
                'error' => 'Rate not available for the specified date',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'original_amount' => $result['amount_eur'],
            'original_currency' => 'EUR',
            'converted_amount' => $result['amount_ron'],
            'target_currency' => 'RON',
            'exchange_rate' => $result['exchange_rate'],
            'date' => $result['date']->format('Y-m-d'),
            'source' => 'bnr',
        ]);
    }

    /**
     * Fetch latest rates from BNR (admin only)
     *
     * POST /api/exchange-rate/fetch
     */
    public function fetchRates(): JsonResponse
    {
        $result = $this->bnrService->fetchTodayRates();

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'date' => $result['date'],
                'rates' => collect($result['rates'])
                    ->filter(fn($rate, $currency) => in_array($currency, ['EUR', 'USD', 'GBP']))
                    ->toArray(),
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'] ?? 'Unknown error',
        ], 500);
    }
}
