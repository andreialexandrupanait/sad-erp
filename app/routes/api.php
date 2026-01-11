<?php

use App\Http\Controllers\Api\ExchangeRateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Exchange Rate API (authenticated)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/exchange-rate', [ExchangeRateController::class, 'getRate']);
    Route::get('/exchange-rate/convert', [ExchangeRateController::class, 'convert']);
    Route::post('/exchange-rate/fetch', [ExchangeRateController::class, 'fetchRates']);
});
