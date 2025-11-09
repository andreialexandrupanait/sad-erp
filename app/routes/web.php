<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CredentialController;
use App\Http\Controllers\InternalAccountController;
use App\Http\Controllers\DomainController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Clients Module
    Route::resource('clients', ClientController::class);

    // Credentials Module
    Route::resource('credentials', CredentialController::class);

    // Internal Accounts Module (Conturi)
    Route::resource('internal-accounts', InternalAccountController::class);

    // Domains Module (Domenii)
    Route::resource('domains', DomainController::class);
});

require __DIR__.'/auth.php';
