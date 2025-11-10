<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientImportController;
use App\Http\Controllers\CredentialController;
use App\Http\Controllers\InternalAccountController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SettingsController;
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
    Route::patch('clients/{client}/status', [ClientController::class, 'updateStatus'])->name('clients.update-status');

    // Client Import/Export
    Route::get('clients-import', [ClientImportController::class, 'showImportForm'])->name('clients.import.form');
    Route::post('clients-import', [ClientImportController::class, 'import'])->name('clients.import');
    Route::get('clients-import/template', [ClientImportController::class, 'downloadTemplate'])->name('clients.import.template');
    Route::get('clients-export', [ClientImportController::class, 'export'])->name('clients.export');

    // Credentials Module
    Route::resource('credentials', CredentialController::class);

    // Internal Accounts Module (Conturi)
    Route::resource('internal-accounts', InternalAccountController::class);

    // Domains Module (Domenii)
    Route::resource('domains', DomainController::class);

    // Subscriptions Module (Abonamente)
    Route::resource('subscriptions', SubscriptionController::class);
    Route::post('subscriptions/check-renewals', [SubscriptionController::class, 'checkRenewals'])->name('subscriptions.check-renewals');

    // Settings Module
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');

    // Setting Groups API
    Route::post('settings/categories/{category}/groups', [SettingsController::class, 'storeGroup'])->name('settings.groups.store');
    Route::patch('settings/groups/{group}', [SettingsController::class, 'updateGroup'])->name('settings.groups.update');
    Route::delete('settings/groups/{group}', [SettingsController::class, 'deleteGroup'])->name('settings.groups.delete');

    // Setting Options API
    Route::post('settings/groups/{group}/options', [SettingsController::class, 'storeOption'])->name('settings.options.store');
    Route::patch('settings/options/{option}', [SettingsController::class, 'updateOption'])->name('settings.options.update');
    Route::delete('settings/options/{option}', [SettingsController::class, 'deleteOption'])->name('settings.options.delete');
    Route::post('settings/groups/{group}/options/reorder', [SettingsController::class, 'reorderOptions'])->name('settings.options.reorder');
});

require __DIR__.'/auth.php';
