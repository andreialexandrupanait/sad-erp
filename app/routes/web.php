<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\ServiceController;
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
use App\Http\Controllers\OfferController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ContractTemplateController;
use App\Http\Controllers\DocumentTemplateController;
use App\Http\Controllers\ClientNoteController;
use App\Http\Controllers\ShareController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Health check endpoint (no auth required)
Route::get('/health', App\Http\Controllers\HealthController::class)
    ->middleware('throttle:60,1')
    ->name('health');

// Public Share Routes (signed URLs - no auth required)
Route::prefix('share')->name('share.')->middleware('throttle:60,1')->group(function () {
    Route::get('invoice/{file}', [ShareController::class, 'invoice'])->name('invoice');
    Route::get('invoice/{file}/download', [ShareController::class, 'download'])->name('invoice.download');
});

// Public Offer Routes (no auth required) - Rate limited for security
// Legacy token-based routes (kept for backward compatibility with existing shared links)
Route::prefix('offers/view')->name('offers.public')->group(function () {
    Route::get('{token}', [OfferController::class, 'publicView'])
        ->middleware('throttle:60,1')  // 60 views per minute per IP
        ->name('');
    Route::get('{token}/state', [OfferController::class, 'publicState'])
        ->middleware('throttle:30,1')  // 30 state checks per minute (reduced from 120 to prevent scraping)
        ->name('.state');
    Route::post('{token}/selections', [OfferController::class, 'publicUpdateSelections'])
        ->middleware('throttle:30,1')  // 30 selection updates per minute
        ->name('.selections');
    Route::post('{token}/accept', [OfferController::class, 'publicAccept'])
        ->middleware('throttle:15,1')  // 15 accept attempts per minute (increased for better UX)
        ->name('.accept');
    Route::post('{token}/reject', [OfferController::class, 'publicReject'])
        ->middleware('throttle:10,1')  // 10 reject attempts per minute
        ->name('.reject');
    Route::post('{token}/request-code', [OfferController::class, 'requestVerificationCode'])
        ->middleware('throttle:5,1')  // 5 verification code requests per minute
        ->name('.request-code');
});

// Signed URL routes for offers (more secure - URLs expire and can't be guessed)
// These routes use Laravel's signed URL verification
Route::prefix('offers/s')->name('offers.public.signed')->middleware('signed')->group(function () {
    Route::get('{offer}', [OfferController::class, 'publicViewSigned'])
        ->middleware('throttle:60,1')
        ->name('');
    Route::get('{offer}/state', [OfferController::class, 'publicStateSigned'])
        ->middleware('throttle:30,1')
        ->name('.state');
    Route::post('{offer}/selections', [OfferController::class, 'publicUpdateSelectionsSigned'])
        ->middleware('throttle:30,1')
        ->name('.selections');
    Route::post('{offer}/accept', [OfferController::class, 'publicAcceptSigned'])
        ->middleware('throttle:15,1')
        ->name('.accept');
    Route::post('{offer}/reject', [OfferController::class, 'publicRejectSigned'])
        ->middleware('throttle:10,1')
        ->name('.reject');
});

// Two-Factor Authentication Challenge (login verification)
// These routes require auth but NOT 2FA verification (to avoid redirect loop)
Route::middleware('auth')->group(function () {
    Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'create'])
        ->name('2fa.challenge');
    Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'store'])
        ->middleware('throttle:5,1')  // 5 attempts per minute
        ->name('2fa.verify');
    Route::post('/two-factor-challenge/cancel', [TwoFactorChallengeController::class, 'destroy'])
        ->name('2fa.cancel');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', '2fa', 'module:dashboard'])
    ->name('dashboard');

// Profile routes - auth only, no 2FA requirement (so users can manage their 2FA settings)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Two-Factor Authentication Management
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

    // User Preferences & Notifications
    Route::patch("/profile/preferences", [ProfileController::class, "updatePreferences"])->name("profile.preferences.update");
    Route::patch("/profile/notifications", [ProfileController::class, "updateNotifications"])->name("profile.notifications.update");
    Route::get("/profile/activities", [ProfileController::class, "activities"])->name("profile.activities");
});

// Protected routes - require both auth and 2FA verification
Route::middleware(['auth', '2fa'])->group(function () {
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

        // Notes
        Route::resource('notes', ClientNoteController::class)->parameters(['notes' => 'clientNote']);
        Route::patch('notes/{clientNote}/update-client', [ClientNoteController::class, 'updateClient'])->name('notes.update-client');
        Route::get('api/clients/{client}/notes', [ClientNoteController::class, 'forClient'])->name('api.clients.notes');
        Route::get('api/notes/tag-stats', [ClientNoteController::class, 'tagStats'])->name('api.notes.tag-stats');
    });

    // Credentials Module
    Route::middleware('module:credentials')->group(function () {
        // Export and email routes MUST be defined BEFORE the resource to avoid being caught by credentials/{credential}
        Route::get('credentials/export-site/{siteName}', [CredentialController::class, 'exportSite'])
            ->middleware('throttle:60,1')
            ->name('credentials.site.export')
            ->where('siteName', '.*');
        Route::post('credentials/email-site/{siteName}', [CredentialController::class, 'emailSite'])
            ->middleware('throttle:5,1')
            ->name('credentials.site.email')
            ->where('siteName', '.*');
        Route::get('credentials/site/{siteName}', [CredentialController::class, 'siteCredentials'])
            ->name('credentials.site')
            ->where('siteName', '.*');
        Route::post('credentials/bulk-update', [CredentialController::class, 'bulkUpdate'])
            ->middleware('throttle:10,1')
            ->name('credentials.bulk-update');
        Route::post('credentials/bulk-export', [CredentialController::class, 'bulkExport'])
            ->middleware('throttle:10,1')
            ->name('credentials.bulk-export');

        // Resource routes - these have catch-all patterns so must come after explicit routes
        Route::resource('credentials', CredentialController::class);

        // Credential-specific routes (use model binding, so they're safe after resource)
        Route::post('credentials/{credential}/reveal-password', [CredentialController::class, 'revealPassword'])
            ->middleware('require.password.confirmation')
            ->middleware('throttle:3,1')
            ->name('credentials.reveal-password');
        Route::get('credentials/{credential}/password', [CredentialController::class, 'getPassword'])
            ->middleware('throttle:60,1')
            ->name('credentials.get-password');
    });

    // Internal Accounts Module (Conturi)
    Route::middleware('module:internal_accounts')->group(function () {
        Route::post('internal-accounts/bulk-delete', [InternalAccountController::class, 'bulkDelete'])->name('internal-accounts.bulk-delete');
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
        Route::put('settings/business-info', [SettingsController::class, 'updateBusinessInfo'])->name('settings.business-info.update');
        Route::get('settings/invoice-settings', [SettingsController::class, 'invoiceSettings'])->name('settings.invoice-settings');

        // Settings - Offer Defaults (API only - UI is in offer builder sidebar)
        Route::put('settings/offer-defaults', [SettingsController::class, 'updateOfferDefaults'])->name('settings.offer-defaults.update');

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
            ->middleware('throttle:1,60')  // 1 restore per hour (extremely dangerous operation)
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

        // Anthropic (Claude AI) Integration Settings
        Route::prefix('settings/anthropic')->name('settings.anthropic.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Settings\AnthropicController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Settings\AnthropicController::class, 'update'])->name('update');
            Route::post('/test', [\App\Http\Controllers\Settings\AnthropicController::class, 'test'])->name('test');
            Route::post('/disconnect', [\App\Http\Controllers\Settings\AnthropicController::class, 'disconnect'])->name('disconnect');
        });

        // Smartbill Integration Settings
        Route::prefix('settings/smartbill')->name('settings.smartbill.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Settings\SmartbillController::class, 'index'])->name('index');
            Route::post('/credentials', [\App\Http\Controllers\Settings\SmartbillController::class, 'updateCredentials'])->name('credentials.update');
            Route::post('/test-connection', [\App\Http\Controllers\Settings\SmartbillController::class, 'testConnection'])->name('test-connection');
            Route::get('/import', [\App\Http\Controllers\Settings\SmartbillController::class, 'showImportForm'])->name('import');
            Route::post('/import/process', [\App\Http\Controllers\Settings\SmartbillController::class, 'processImport'])
                ->middleware('throttle:20,1')  // 20 import initiations per minute (allows retries)
                ->name('import.process');
            Route::post('/import/{importId}/start', [\App\Http\Controllers\Settings\SmartbillController::class, 'startImport'])
                ->middleware('throttle:30,1')  // 30 import starts per minute (allows retries)
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
        Route::get("settings/services/create", [ServiceController::class, "create"])->name("settings.services.create");
        Route::post("settings/services", [ServiceController::class, "store"])->name("settings.services.store");
        Route::get("settings/services/{service}/edit", [ServiceController::class, "edit"])->name("settings.services.edit");
        Route::put("settings/services/{service}", [ServiceController::class, "update"])->name("settings.services.update");
        Route::delete("settings/services/{service}", [ServiceController::class, "destroy"])->name("settings.services.destroy");
        Route::post("settings/services/reorder", [ServiceController::class, "reorder"])->name("settings.services.reorder");
    });

    // Offers Module
    Route::middleware('module:offers')->group(function () {
        // Offer Routes - Simple Builder is now the default
        Route::get('offers', [OfferController::class, 'index'])->name('offers.index');
        Route::get('offers/create', [OfferController::class, 'simpleCreate'])->name('offers.create');
        Route::post('offers', [OfferController::class, 'simpleStore'])->name('offers.store');
        Route::get('offers/{offer}', [OfferController::class, 'show'])->name('offers.show');
        Route::get('offers/{offer}/edit', [OfferController::class, 'simpleEdit'])->name('offers.edit');
        Route::put('offers/{offer}', [OfferController::class, 'simpleUpdate'])->name('offers.update');
        Route::delete('offers/{offer}', [OfferController::class, 'destroy'])->name('offers.destroy');

        // Additional Offer Actions
        Route::post('offers/bulk-action', [OfferController::class, 'bulkAction'])->name('offers.bulk-action');
        Route::post('offers/bulk-delete', [OfferController::class, 'bulkDelete'])->name('offers.bulk-delete');
        Route::post('offers/bulk-export', [OfferController::class, 'bulkExport'])
            ->middleware('throttle:10,1')
            ->name('offers.bulk-export');
        Route::post('offers/{offer}/send', [OfferController::class, 'send'])->name('offers.send');
        Route::post('offers/{offer}/resend', [OfferController::class, 'resend'])->name('offers.resend');
        Route::get('offers/{offer}/pdf', [OfferController::class, 'downloadPdf'])->name('offers.pdf');
        Route::post('offers/{offer}/duplicate', [OfferController::class, 'duplicate'])->name('offers.duplicate');
        Route::post('offers/{offer}/approve', [OfferController::class, 'approve'])->name('offers.approve');
        Route::post('offers/{offer}/convert-to-contract', [OfferController::class, 'convertToContract'])->name('offers.convert-to-contract');
        Route::post('offers/{offer}/regenerate-contract', [OfferController::class, 'regenerateContract'])->name('offers.regenerate-contract');
        Route::patch('offers/{offer}/temp-client', [OfferController::class, 'updateTempClient'])->name('offers.update-temp-client');
        Route::post('offers/save-as-template', [OfferController::class, 'saveAsTemplate'])->name('offers.save-as-template');
        Route::post('offers/upload-image', [OfferController::class, 'uploadImage'])->name('offers.upload-image');
    });

    // Contracts Module
    Route::middleware('module:contracts')->group(function () {
        Route::get('contracts', [ContractController::class, 'index'])->name('contracts.index');
        Route::post('contracts/bulk-delete', [ContractController::class, 'bulkDelete'])->name('contracts.bulk-delete');
        Route::post('contracts/bulk-export', [ContractController::class, 'bulkExport'])
            ->middleware('throttle:10,1')
            ->name('contracts.bulk-export');
        Route::get('contracts/create', [ContractController::class, 'create'])->name('contracts.create');
        Route::post('contracts', [ContractController::class, 'store'])->name('contracts.store');
        Route::get('contracts/{contract}', [ContractController::class, 'show'])->name('contracts.show');
        Route::get('contracts/{contract}/edit', [ContractController::class, 'edit'])->name('contracts.edit');
        // Legacy redirect: /builder -> /edit
        Route::get('contracts/{contract}/builder', fn($contract) => redirect()->route('contracts.edit', $contract));
        Route::put('contracts/{contract}/content', [ContractController::class, 'updateContent'])->name('contracts.update-content');
        Route::patch('contracts/{contract}/temp-client', [ContractController::class, 'updateTempClient'])->name('contracts.update-temp-client');
        Route::get('contracts/{contract}/content-hash', [ContractController::class, 'getContentHash'])->name('contracts.content-hash');
        Route::get('contracts/{contract}/validate', [ContractController::class, 'validateForPdf'])->name('contracts.validate');
        Route::put('contracts/{contract}/number', [ContractController::class, 'updateNumber'])->name('contracts.update-number');
        Route::post('contracts/{contract}/finalize', [ContractController::class, 'finalize'])->name('contracts.finalize');
        Route::post('contracts/{contract}/finalize-and-download', [ContractController::class, 'finalizeAndDownload'])->name('contracts.finalize-and-download');
        Route::post('contracts/{contract}/apply-template', [ContractController::class, 'applyTemplate'])->name('contracts.apply-template');
        Route::post('contracts/{contract}/save-as-template', [ContractController::class, 'saveAsTemplate'])->name('contracts.save-as-template');
        Route::post('contracts/{contract}/generate-pdf', [ContractController::class, 'generatePdf'])->name('contracts.generate-pdf');
        Route::get('contracts/{contract}/add-annex', [ContractController::class, 'addAnnexForm'])->name('contracts.add-annex');
        Route::post('contracts/{contract}/add-annex', [ContractController::class, 'addAnnex'])->name('contracts.add-annex.store');
        Route::post('contracts/{contract}/terminate', [ContractController::class, 'terminate'])->name('contracts.terminate');
        Route::post('contracts/{contract}/toggle-auto-renew', [ContractController::class, 'toggleAutoRenew'])->name('contracts.toggle-auto-renew');
        Route::delete('contracts/{contract}', [ContractController::class, 'destroy'])->name('contracts.destroy');
        Route::get('contracts/{contract}/preview', [ContractController::class, 'previewPdf'])->name('contracts.preview');
        Route::get('contracts/{contract}/download', [ContractController::class, 'downloadPdf'])->name('contracts.download');
        Route::get('contracts/{contract}/annexes/{annex}/download', [ContractController::class, 'downloadAnnexPdf'])->name('contracts.annex.download');
        Route::get('api/clients/{client}/contracts', [ContractController::class, 'forClient'])->name('api.clients.contracts');

        // Contract Audit Trail & Versioning (Phase 4)
        Route::get('contracts/{contract}/activities', [ContractController::class, 'activities'])->name('contracts.activities');
        Route::get('contracts/{contract}/versions', [ContractController::class, 'versions'])->name('contracts.versions');
        Route::get('contracts/{contract}/versions/{versionNumber}', [ContractController::class, 'getVersion'])->name('contracts.versions.show');
        Route::post('contracts/{contract}/versions/{versionNumber}/restore', [ContractController::class, 'restoreVersion'])->name('contracts.versions.restore');

        // Contract Locking (Phase 4)
        Route::get('contracts/{contract}/lock-status', [ContractController::class, 'lockStatus'])->name('contracts.lock-status');
        Route::post('contracts/{contract}/lock', [ContractController::class, 'acquireLock'])->name('contracts.lock');
        Route::post('contracts/{contract}/unlock', [ContractController::class, 'releaseLock'])->name('contracts.unlock');
        Route::post('contracts/{contract}/refresh-lock', [ContractController::class, 'refreshLock'])->name('contracts.refresh-lock');
    });

    // Document Templates (Settings)
    Route::middleware('module:settings')->prefix('settings')->name('settings.')->group(function () {
        Route::resource('document-templates', DocumentTemplateController::class);
        Route::post('document-templates/bulk-delete', [DocumentTemplateController::class, 'bulkDelete'])->name('document-templates.bulk-delete');
        Route::post('document-templates/{documentTemplate}/duplicate', [DocumentTemplateController::class, 'duplicate'])->name('document-templates.duplicate');
        Route::post('document-templates/{documentTemplate}/set-default', [DocumentTemplateController::class, 'setDefault'])->name('document-templates.set-default');
        Route::post('document-templates/{documentTemplate}/toggle-active', [DocumentTemplateController::class, 'toggleActive'])->name('document-templates.toggle-active');
        Route::get('document-templates/{documentTemplate}/preview', [DocumentTemplateController::class, 'preview'])->name('document-templates.preview');
        Route::get('document-templates/{documentTemplate}/builder', [DocumentTemplateController::class, 'builder'])->name('document-templates.builder');
        Route::put('document-templates/{documentTemplate}/builder', [DocumentTemplateController::class, 'updateBuilder'])->name('document-templates.builder.update');
        Route::get('api/template-variables', [DocumentTemplateController::class, 'variables'])->name('api.template-variables');

        // Contract Templates
        Route::resource('contract-templates', ContractTemplateController::class);
        Route::post('contract-templates/{contractTemplate}/duplicate', [ContractTemplateController::class, 'duplicate'])->name('contract-templates.duplicate');
        Route::post('contract-templates/{contractTemplate}/set-default', [ContractTemplateController::class, 'setDefault'])->name('contract-templates.set-default');
        Route::post('contract-templates/{contractTemplate}/toggle-active', [ContractTemplateController::class, 'toggleActive'])->name('contract-templates.toggle-active');
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
        Route::get('files/{file}/import-transactions', [FinancialFileController::class, 'importTransactions'])->name('files.import-transactions');
        Route::post('files/{file}/import-transactions', [FinancialFileController::class, 'processImportTransactions'])->name('files.process-import-transactions');
    });
});

require __DIR__.'/auth.php';
