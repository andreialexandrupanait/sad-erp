<?php

// Temporary HTTPS test route - DELETE THIS FILE AFTER TESTING

use Illuminate\Support\Facades\Route;

Route::get('/test-https-detection', function () {
    return response()->json([
        'app_url' => config('app.url'),
        'current_url' => url()->current(),
        'request_is_secure' => request()->secure(),
        'request_scheme' => request()->getScheme(),
        'server_https' => $_SERVER['HTTPS'] ?? 'not set',
        'x_forwarded_proto' => request()->header('X-Forwarded-Proto'),
        'x_forwarded_host' => request()->header('X-Forwarded-Host'),
        'x_forwarded_port' => request()->header('X-Forwarded-Port'),
        'x_forwarded_for' => request()->header('X-Forwarded-For'),
        'route_url_example' => route('clients.index'),
        'asset_url_example' => asset('css/app.css'),
    ]);
})->name('test.https');
