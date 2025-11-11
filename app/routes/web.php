<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\CredentialController;
use App\Http\Controllers\InternalAccountController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Financial\DashboardController as FinancialDashboardController;
use App\Http\Controllers\Financial\RevenueController;
use App\Http\Controllers\Financial\ExpenseController;
use App\Http\Controllers\Financial\FileController as FinancialFileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Temporary HTTPS test route - DELETE AFTER TESTING
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

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Centralized Import/Export System
    Route::get('import-export', [ImportExportController::class, 'index'])->name('import-export.index');
    Route::get('import-export/{module}', [ImportExportController::class, 'showImportForm'])->name('import-export.import.form');
    Route::post('import-export/{module}', [ImportExportController::class, 'import'])->name('import-export.import');
    Route::get('import-export/{module}/template', [ImportExportController::class, 'downloadTemplate'])->name('import-export.template');
    Route::get('export/{module}', [ImportExportController::class, 'export'])->name('import-export.export');

    // Clients Module
    Route::resource('clients', ClientController::class);
    Route::patch('clients/{client}/status', [ClientController::class, 'updateStatus'])->name('clients.update-status');

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
    Route::post('settings/application', [SettingsController::class, 'updateApplicationSettings'])->name('settings.application.update');

    // Setting Groups API
    Route::post('settings/categories/{category}/groups', [SettingsController::class, 'storeGroup'])->name('settings.groups.store');
    Route::patch('settings/groups/{group}', [SettingsController::class, 'updateGroup'])->name('settings.groups.update');
    Route::delete('settings/groups/{group}', [SettingsController::class, 'deleteGroup'])->name('settings.groups.delete');

    // Setting Options API (Generic system - for domains, access, financial, subscriptions)
    Route::post('settings/groups/{group}/options', [SettingsController::class, 'storeOption'])->name('settings.options.store');
    Route::patch('settings/options/{option}', [SettingsController::class, 'updateOption'])->name('settings.options.update');
    Route::delete('settings/options/{option}', [SettingsController::class, 'deleteOption'])->name('settings.options.delete');
    Route::post('settings/groups/{group}/options/reorder', [SettingsController::class, 'reorderOptions'])->name('settings.options.reorder');

    // Module-Specific Settings (using *_settings tables)
    Route::post('settings/client-settings', [\App\Http\Controllers\ClientSettingsController::class, 'store'])->name('settings.client-settings.store');
    Route::patch('settings/client-settings/{setting}', [\App\Http\Controllers\ClientSettingsController::class, 'update'])->name('settings.client-settings.update');
    Route::delete('settings/client-settings/{setting}', [\App\Http\Controllers\ClientSettingsController::class, 'destroy'])->name('settings.client-settings.destroy');
    Route::post('settings/client-settings/reorder', [\App\Http\Controllers\ClientSettingsController::class, 'reorder'])->name('settings.client-settings.reorder');

    // Financial Module
    Route::prefix('financial')->name('financial.')->group(function () {
        // Dashboard
        Route::get('/', [FinancialDashboardController::class, 'index'])->name('dashboard');
        Route::get('/history/{year}', [FinancialDashboardController::class, 'yearlyReport'])->name('yearly-report');
        Route::get('/export/{year}', [FinancialDashboardController::class, 'exportCsv'])->name('export');

        // Revenues
        Route::resource('revenues', RevenueController::class)->parameters(['revenues' => 'revenue']);

        // Expenses
        Route::resource('expenses', ExpenseController::class)->parameters(['expenses' => 'expense']);

        // Files
        Route::get('files', [FinancialFileController::class, 'index'])->name('files.index');
        Route::get('files/create', [FinancialFileController::class, 'create'])->name('files.create');
        Route::post('files', [FinancialFileController::class, 'store'])->name('files.store');
        Route::post('files/upload', [FinancialFileController::class, 'upload'])->name('files.upload');
        Route::get('files/{file}', [FinancialFileController::class, 'show'])->name('files.show');
        Route::get('files/{file}/download', [FinancialFileController::class, 'download'])->name('files.download');
        Route::patch('files/{file}/rename', [FinancialFileController::class, 'rename'])->name('files.rename');
        Route::delete('files/{file}', [FinancialFileController::class, 'destroy'])->name('files.destroy');
    });
});

require __DIR__.'/auth.php';
