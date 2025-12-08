<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\Profile\UserServiceController;
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
use App\Http\Controllers\Financial\RevenueImportController;
use App\Http\Controllers\Financial\ExpenseImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'module:dashboard'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Two-Factor Authentication
    Route::get("/profile/two-factor", [TwoFactorController::class, "show"])->name("profile.two-factor");
    Route::post("/profile/two-factor/enable", [TwoFactorController::class, "enable"])->name("profile.two-factor.enable");
    Route::post("/profile/two-factor/confirm", [TwoFactorController::class, "confirm"])->name("profile.two-factor.confirm");
    Route::post("/profile/two-factor/disable", [TwoFactorController::class, "disable"])->name("profile.two-factor.disable");
    Route::post("/profile/two-factor/recovery-codes", [TwoFactorController::class, "showRecoveryCodes"])->name("profile.two-factor.recovery-codes");
    Route::post("/profile/two-factor/regenerate-codes", [TwoFactorController::class, "regenerateRecoveryCodes"])->name("profile.two-factor.regenerate-codes");

    // Session Management
    Route::get("/profile/sessions", [SessionController::class, "index"])->name("profile.sessions");
    Route::delete("/profile/sessions/{session}", [SessionController::class, "destroy"])->name("profile.sessions.destroy");
    Route::delete("/profile/sessions", [SessionController::class, "destroyOthers"])->name("profile.sessions.destroy-others");

    // User Services (rates per service)
    Route::get("/profile/services", [UserServiceController::class, "index"])->name("profile.services");
    Route::post("/profile/services", [UserServiceController::class, "store"])->name("profile.services.store");
    Route::put("/profile/services/{userService}", [UserServiceController::class, "update"])->name("profile.services.update");
    Route::delete("/profile/services/{userService}", [UserServiceController::class, "destroy"])->name("profile.services.destroy");

    // Centralized Import/Export System
    Route::get('import-export', [ImportExportController::class, 'index'])->name('import-export.index');
    Route::get('import-export/{module}', [ImportExportController::class, 'showImportForm'])->name('import-export.import.form');
    Route::post('import-export/{module}', [ImportExportController::class, 'import'])
        ->middleware('throttle:5,1')  // 5 imports per minute
        ->name('import-export.import');
    Route::get('import-export/{module}/template', [ImportExportController::class, 'downloadTemplate'])->name('import-export.template');
    Route::get('export/{module}', [ImportExportController::class, 'export'])
        ->middleware('throttle:10,1')  // 10 exports per minute
        ->name('import-export.export');

    // Clients Module
    Route::middleware('module:clients')->group(function () {
        Route::resource('clients', ClientController::class);
        Route::patch('clients/{client}/status', [ClientController::class, 'updateStatus'])->name('clients.update-status');
        Route::patch('clients/{client}/reorder', [ClientController::class, 'reorder'])->name('clients.reorder');
        Route::post('clients/bulk-update', [ClientController::class, 'bulkUpdate'])
            ->middleware('throttle:10,1')  // 10 bulk updates per minute
            ->name('clients.bulk-update');
        Route::post('clients/bulk-export', [ClientController::class, 'bulkExport'])
            ->middleware('throttle:10,1')  // 10 bulk exports per minute
            ->name('clients.bulk-export');
    });

    // Credentials Module
    Route::middleware('module:credentials')->group(function () {
        Route::resource('credentials', CredentialController::class);
        Route::post('credentials/bulk-update', [CredentialController::class, 'bulkUpdate'])
            ->middleware('throttle:10,1')  // 10 bulk updates per minute
            ->name('credentials.bulk-update');
        Route::post('credentials/bulk-export', [CredentialController::class, 'bulkExport'])
            ->middleware('throttle:10,1')  // 10 bulk exports per minute
            ->name('credentials.bulk-export');
        Route::post('credentials/{credential}/reveal-password', [CredentialController::class, 'revealPassword'])->middleware('require.password.confirmation')
            ->middleware('throttle:3,1')  // Stricter limit: 3 requests per minute for sensitive password reveals
            ->name('credentials.reveal-password');
    });

    // Internal Accounts Module (Conturi)
    Route::middleware('module:internal_accounts')->group(function () {
        Route::resource('internal-accounts', InternalAccountController::class);
        Route::post('internal-accounts/{internalAccount}/reveal-password', [InternalAccountController::class, 'revealPassword'])->middleware('require.password.confirmation')
            ->middleware('throttle:3,1')  // Stricter limit: 3 requests per minute for sensitive password reveals
            ->name('internal-accounts.reveal-password');
    });

    // Domains Module (Domenii)
    Route::middleware('module:domains')->group(function () {
        Route::resource('domains', DomainController::class);
        Route::post('domains/bulk-update', [DomainController::class, 'bulkUpdate'])
            ->middleware('throttle:10,1')  // 10 bulk updates per minute
            ->name('domains.bulk-update');
        Route::post('domains/bulk-export', [DomainController::class, 'bulkExport'])
            ->middleware('throttle:10,1')  // 10 bulk exports per minute
            ->name('domains.bulk-export');
        Route::post('domains/bulk-auto-renew', [DomainController::class, 'bulkToggleAutoRenew'])
            ->middleware('throttle:10,1')  // 10 bulk operations per minute
            ->name('domains.bulk-auto-renew');
    });

    // Subscriptions Module (Abonamente)
    Route::middleware('module:subscriptions')->group(function () {
        Route::resource('subscriptions', SubscriptionController::class);
        Route::post('subscriptions/bulk-update', [SubscriptionController::class, 'bulkUpdate'])
            ->middleware('throttle:10,1')  // 10 bulk updates per minute
            ->name('subscriptions.bulk-update');
        Route::post('subscriptions/bulk-export', [SubscriptionController::class, 'bulkExport'])
            ->middleware('throttle:10,1')  // 10 bulk exports per minute
            ->name('subscriptions.bulk-export');
        Route::post('subscriptions/bulk-renew', [SubscriptionController::class, 'bulkRenew'])
            ->middleware('throttle:10,1')  // 10 bulk renewals per minute
            ->name('subscriptions.bulk-renew');
        Route::post('subscriptions/bulk-status', [SubscriptionController::class, 'bulkUpdateStatus'])
            ->middleware('throttle:10,1')  // 10 bulk status updates per minute
            ->name('subscriptions.bulk-status');
        Route::patch('subscriptions/{subscription}/status', [SubscriptionController::class, 'updateStatus'])->name('subscriptions.update-status');
        Route::post('subscriptions/{subscription}/renew', [SubscriptionController::class, 'renew'])->name('subscriptions.renew');
        Route::post('subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
        Route::post('subscriptions/{subscription}/reactivate', [SubscriptionController::class, 'reactivate'])->name('subscriptions.reactivate');
        Route::post('subscriptions/check-renewals', [SubscriptionController::class, 'checkRenewals'])->name('subscriptions.check-renewals');
    });

    // Settings Module
    Route::middleware('module:settings')->group(function () {
        // Settings - Main Hub
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');

        // Settings - Application
        Route::get('settings/application', [SettingsController::class, 'application'])->name('settings.application');
        Route::post('settings/application', [SettingsController::class, 'updateApplicationSettings'])->name('settings.application.update');

        // Settings - Business Hub
        Route::get('settings/business', [SettingsController::class, 'business'])->name('settings.business');
        Route::get('settings/business-info', [SettingsController::class, 'businessInfo'])->name('settings.business-info');
        Route::get('settings/invoice-settings', [SettingsController::class, 'invoiceSettings'])->name('settings.invoice-settings');

        // Settings - Integrations Hub
        Route::get('settings/integrations', [SettingsController::class, 'integrations'])->name('settings.integrations');

        // Settings - Nomenclature Hub
        Route::get('settings/nomenclature', [SettingsController::class, 'nomenclatureIndex'])->name('settings.nomenclature');

        // Settings - Notifications
        Route::get('settings/notifications', [SettingsController::class, 'notifications'])->name('settings.notifications');
        Route::post('settings/notifications', [SettingsController::class, 'updateNotifications'])->name('settings.notifications.update');
        Route::post('settings/notifications/test-email', [SettingsController::class, 'sendTestEmail'])->name('settings.notifications.test-email');

        // Settings - Yearly Objectives (Budget Thresholds)
        Route::get('settings/yearly-objectives', [SettingsController::class, 'yearlyObjectives'])->name('settings.yearly-objectives');
        Route::post('settings/yearly-objectives', [SettingsController::class, 'updateYearlyObjectives'])->name('settings.yearly-objectives.update');

        // Settings - Database Backup
        Route::get('settings/backup', [\App\Http\Controllers\Settings\BackupController::class, 'index'])->name('settings.backup');
        Route::post('settings/backup/export', [\App\Http\Controllers\Settings\BackupController::class, 'export'])
            ->middleware('throttle:2,10')  // 2 backups per 10 minutes (very resource-intensive)
            ->name('settings.backup.export');
        Route::post('settings/backup/import', [\App\Http\Controllers\Settings\BackupController::class, 'import'])
            ->middleware('throttle:5,1')  // 5 imports per minute
            ->name('settings.backup.import');
        Route::get('settings/backup/download/{filename}', [\App\Http\Controllers\Settings\BackupController::class, 'download'])->where('filename', '[a-zA-Z0-9_\-\.]+')->name('settings.backup.download');
        Route::post('settings/backup/restore/{filename}', [\App\Http\Controllers\Settings\BackupController::class, 'restore'])
            ->middleware('throttle:1,10')  // 1 restore per 10 minutes (extremely dangerous operation)
            ->where('filename', '[a-zA-Z0-9_\-\.]+')
            ->name('settings.backup.restore');
        Route::delete('settings/backup/{filename}', [\App\Http\Controllers\Settings\BackupController::class, 'destroy'])->where('filename', '[a-zA-Z0-9_\-\.]+')->name('settings.backup.destroy');

        // Slack Integration Settings
        Route::prefix('settings/slack')->name('settings.slack.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Settings\SlackController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Settings\SlackController::class, 'update'])->name('update');
            Route::post('/test', [\App\Http\Controllers\Settings\SlackController::class, 'test'])->name('test');
            Route::post('/disconnect', [\App\Http\Controllers\Settings\SlackController::class, 'disconnect'])->name('disconnect');
        });

        // Email Integration Settings - redirect to unified notifications page
        Route::get('settings/email', fn() => redirect()->route('settings.notifications'))->name('settings.email.index');

        // ClickUp Integration Settings
        Route::prefix('settings/clickup')->name('settings.clickup.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Settings\ClickUpController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Settings\ClickUpController::class, 'update'])->name('update');
            Route::post('/test', [\App\Http\Controllers\Settings\ClickUpController::class, 'test'])->name('test');
            Route::post('/disconnect', [\App\Http\Controllers\Settings\ClickUpController::class, 'disconnect'])->name('disconnect');
        });

        // Smartbill Integration Settings
        Route::prefix('settings/smartbill')->name('settings.smartbill.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Settings\SmartbillController::class, 'index'])->name('index');
            Route::post('/credentials', [\App\Http\Controllers\Settings\SmartbillController::class, 'updateCredentials'])->name('credentials.update');
            Route::post('/test-connection', [\App\Http\Controllers\Settings\SmartbillController::class, 'testConnection'])->name('test-connection');
            Route::get('/import', [\App\Http\Controllers\Settings\SmartbillController::class, 'showImportForm'])->name('import');
            Route::post('/import/process', [\App\Http\Controllers\Settings\SmartbillController::class, 'processImport'])
                ->middleware('throttle:5,1')  // 5 import initiations per minute
                ->name('import.process');
            Route::post('/import/{importId}/start', [\App\Http\Controllers\Settings\SmartbillController::class, 'startImport'])
                ->middleware('throttle:3,1')  // 3 import starts per minute
                ->name('import.start');
            Route::get('/import/{importId}/progress', [\App\Http\Controllers\Settings\SmartbillController::class, 'getProgress'])->name('import.progress');
        });


        // User Management Settings (Admin only - additional role check in controller)
        Route::prefix('settings/users')->name('settings.users.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Settings\UserController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Settings\UserController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Settings\UserController::class, 'store'])->name('store');
            Route::get('/{user}', [\App\Http\Controllers\Settings\UserController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [\App\Http\Controllers\Settings\UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [\App\Http\Controllers\Settings\UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [\App\Http\Controllers\Settings\UserController::class, 'destroy'])->name('destroy');
            Route::post('/{user}/restore', [\App\Http\Controllers\Settings\UserController::class, 'restore'])->name('restore');
            Route::put('/{user}/permissions', [\App\Http\Controllers\Settings\UserController::class, 'updatePermissions'])->name('permissions.update');
            Route::post('/{user}/reset-permissions', [\App\Http\Controllers\Settings\UserController::class, 'resetToRoleDefaults'])->name('permissions.reset');
            Route::post('/{user}/resend-invite', [\App\Http\Controllers\Settings\UserController::class, 'resendInvite'])->name('resend-invite');
        });

        // Nomenclature Settings Pages
        Route::get('settings/client-statuses', [SettingsController::class, 'clientStatuses'])->name('settings.client-statuses');
        Route::get('settings/domain-statuses', [SettingsController::class, 'domainStatuses'])->name('settings.domain-statuses');
        Route::get('settings/subscription-statuses', [SettingsController::class, 'subscriptionStatuses'])->name('settings.subscription-statuses');
        Route::get('settings/access-platforms', [SettingsController::class, 'accessPlatforms'])->name('settings.access-platforms');
        Route::get('settings/expense-categories', [SettingsController::class, 'expenseCategories'])->name('settings.expense-categories');
        Route::get('settings/payment-methods', [SettingsController::class, 'paymentMethods'])->name('settings.payment-methods');
        Route::get('settings/billing-cycles', [SettingsController::class, 'billingCycles'])->name('settings.billing-cycles');
        Route::get('settings/domain-registrars', [SettingsController::class, 'domainRegistrars'])->name('settings.domain-registrars');
        Route::get('settings/currencies', [SettingsController::class, 'currencies'])->name('settings.currencies');
        Route::get('settings/quick-actions', [SettingsController::class, 'quickActions'])->name('settings.quick-actions');

        // Nomenclature CRUD Operations
        Route::post('settings/nomenclature', [\App\Http\Controllers\NomenclatureController::class, 'store'])->name('settings.nomenclature.store');
        Route::patch('settings/nomenclature/{setting}', [\App\Http\Controllers\NomenclatureController::class, 'update'])->name('settings.nomenclature.update');
        Route::delete('settings/nomenclature/{setting}', [\App\Http\Controllers\NomenclatureController::class, 'destroy'])->name('settings.nomenclature.destroy');
        Route::post('settings/nomenclature/reorder', [\App\Http\Controllers\NomenclatureController::class, 'reorder'])->name('settings.nomenclature.reorder');
        Route::post('settings/nomenclature/bulk-delete', [\App\Http\Controllers\NomenclatureController::class, 'bulkDelete'])->name('settings.nomenclature.bulk-delete');

        // Services Management (organization-level)
        Route::get("settings/services", [ServiceController::class, "index"])->name("settings.services");
        Route::post("settings/services", [ServiceController::class, "store"])->name("settings.services.store");
        Route::put("settings/services/{service}", [ServiceController::class, "update"])->name("settings.services.update");
        Route::delete("settings/services/{service}", [ServiceController::class, "destroy"])->name("settings.services.destroy");
        Route::post("settings/services/reorder", [ServiceController::class, "reorder"])->name("settings.services.reorder");
    });

    // Financial Module
    Route::middleware('module:finance')->prefix('financial')->name('financial.')->group(function () {
        // Dashboard
        Route::get('/', [FinancialDashboardController::class, 'index'])->name('dashboard');
        Route::post('/budget-thresholds', [FinancialDashboardController::class, 'saveBudgetThresholds'])->name('budget-thresholds.save');
        Route::get('/cashflow', [FinancialDashboardController::class, 'cashflow'])->name('cashflow');
        Route::get('/history', [FinancialDashboardController::class, 'yearlyReport'])->name('yearly-report');
        Route::get('/history/export', [FinancialDashboardController::class, 'exportAllYearsCsv'])->name('history.export');
        Route::get('/export/{year}', [FinancialDashboardController::class, 'exportCsv'])->name('export');

        // Revenues - Import/Export
        Route::get('revenues/import', [RevenueImportController::class, 'showForm'])->name('revenues.import');
        Route::post('revenues/import', [RevenueImportController::class, 'import'])
            ->middleware('throttle:5,1')  // 5 imports per minute
            ->name('revenues.import.post');
        Route::post('revenues/import/preview', [RevenueImportController::class, 'preview'])
            ->middleware('throttle:10,1')  // 10 previews per minute
            ->name('revenues.import.preview');
        Route::get('revenues/import/template', [RevenueImportController::class, 'downloadTemplate'])->name('revenues.import.template');
        Route::get('revenues/import/{importId}/status', [RevenueImportController::class, 'getImportStatus'])->name('revenues.import.status');
        Route::post('revenues/import/{importId}/cancel', [RevenueImportController::class, 'cancelImport'])->name('revenues.import.cancel');
        Route::delete('revenues/import/{importId}', [RevenueImportController::class, 'deleteImport'])->name('revenues.import.delete');
        Route::resource('revenues', RevenueController::class)->parameters(['revenues' => 'revenue']);
        Route::post("revenues/bulk-update", [RevenueController::class, "bulkUpdate"])
            ->middleware('throttle:10,1')  // 10 bulk updates per minute
            ->name("revenues.bulk-update");
        Route::post("revenues/bulk-export", [RevenueController::class, "bulkExport"])
            ->middleware('throttle:10,1')  // 10 bulk exports per minute
            ->name("revenues.bulk-export");

        // Expenses - Import/Export
        Route::get('expenses/import', [ExpenseImportController::class, 'showForm'])->name('expenses.import');
        Route::post('expenses/import', [ExpenseImportController::class, 'import'])
            ->middleware('throttle:5,1')  // 5 imports per minute
            ->name('expenses.import.post');
        Route::get('expenses/import/template', [ExpenseImportController::class, 'downloadTemplate'])->name('expenses.import.template');
        Route::resource('expenses', ExpenseController::class)->parameters(['expenses' => 'expense']);
        Route::post("expenses/bulk-update", [ExpenseController::class, "bulkUpdate"])
            ->middleware('throttle:10,1')  // 10 bulk updates per minute
            ->name("expenses.bulk-update");
        Route::post("expenses/bulk-export", [ExpenseController::class, "bulkExport"])
            ->middleware('throttle:10,1')  // 10 bulk exports per minute
            ->name("expenses.bulk-export");


        // Files - Browse with explicit URL structure
        Route::get('files', [FinancialFileController::class, 'index'])->name('files.index');
        Route::get('files/{year}', [FinancialFileController::class, 'indexYear'])->name('files.year')->where('year', '[0-9]{4}');
        Route::get('files/{year}/{month}', [FinancialFileController::class, 'indexMonth'])->name('files.month')->where(['year' => '[0-9]{4}', 'month' => '[0-9]{1,2}']);
        Route::get('files/{year}/{month}/{category}', [FinancialFileController::class, 'indexCategory'])->name('files.category')->where(['year' => '[0-9]{4}', 'month' => '[0-9]{1,2}', 'category' => 'incasare|plata|extrase|general']);
        Route::get('files/api/{year}/{month}/{category}', [FinancialFileController::class, 'apiCategoryFiles'])->name('files.api-category')->where(['year' => '[0-9]{4}', 'month' => '[0-9]{1,2}', 'category' => 'incasare|plata|extrase|general']);

        // Files - Operations (must come after browse routes due to route priority)
        Route::get('files/create', [FinancialFileController::class, 'create'])->name('files.create');
        Route::post('files', [FinancialFileController::class, 'store'])->name('files.store');
        Route::post('files/upload', [FinancialFileController::class, 'upload'])->name('files.upload');
        Route::post('files/bulk-delete', [FinancialFileController::class, 'bulkDelete'])
            ->middleware('throttle:10,1')  // 10 bulk deletes per minute
            ->name('files.bulk-delete');
        Route::get('files/download-yearly-zip/{year}', [FinancialFileController::class, 'downloadYearlyZip'])->name('files.download-yearly-zip');
        Route::get('files/download-monthly-zip/{year}/{month}', [FinancialFileController::class, 'downloadMonthlyZip'])->name('files.download-monthly-zip');
        Route::get('files/view/{file}', [FinancialFileController::class, 'show'])->name('files.show');
        Route::get('files/download/{file}', [FinancialFileController::class, 'download'])->name('files.download');
        Route::patch('files/{file}/rename', [FinancialFileController::class, 'rename'])->name('files.rename');
        Route::delete('files/delete/{file}', [FinancialFileController::class, 'destroy'])->name('files.destroy');
    });
});

require __DIR__.'/auth.php';
