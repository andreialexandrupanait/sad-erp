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
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskServiceController;
use App\Http\Controllers\Financial\DashboardController as FinancialDashboardController;
use App\Http\Controllers\Financial\RevenueController;
use App\Http\Controllers\Financial\ExpenseController;
use App\Http\Controllers\Financial\FileController as FinancialFileController;
use App\Http\Controllers\Financial\ImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
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
    Route::patch('clients/{client}/reorder', [ClientController::class, 'reorder'])->name('clients.reorder');

    // Credentials Module
    Route::resource('credentials', CredentialController::class);

    // Internal Accounts Module (Conturi)
    Route::resource('internal-accounts', InternalAccountController::class);

    // Domains Module (Domenii)
    Route::resource('domains', DomainController::class);

    // Subscriptions Module (Abonamente)
    Route::resource('subscriptions', SubscriptionController::class);
    Route::patch('subscriptions/{subscription}/status', [SubscriptionController::class, 'updateStatus'])->name('subscriptions.update-status');
    Route::post('subscriptions/check-renewals', [SubscriptionController::class, 'checkRenewals'])->name('subscriptions.check-renewals');

    // Task Management Module
    Route::resource('tasks', TaskController::class);
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::patch('tasks/{task}/time', [TaskController::class, 'updateTime'])->name('tasks.update-time');
    Route::patch('tasks/{task}/position', [TaskController::class, 'updatePosition'])->name('tasks.update-position');

    // Task Side Panel & ClickUp-style features
    Route::get('tasks/{task}/details', [TaskController::class, 'getDetails'])->name('tasks.details');
    Route::patch('tasks/{task}/quick-update', [TaskController::class, 'quickUpdate'])->name('tasks.quick-update');
    Route::post('tasks/{task}/subtasks', [TaskController::class, 'addSubtask'])->name('tasks.subtasks.store');
    Route::patch('tasks/{task}/toggle-status', [TaskController::class, 'toggleStatus'])->name('tasks.toggle-status');
    Route::post('tasks/{task}/comments', [TaskController::class, 'addComment'])->name('tasks.comments.store');
    Route::delete('tasks/comments/{comment}', [TaskController::class, 'deleteComment'])->name('tasks.comments.destroy');
    Route::post('tasks/{task}/attachments', [TaskController::class, 'uploadAttachment'])->name('tasks.attachments.store');
    Route::get('tasks/attachments/{attachment}/download', [TaskController::class, 'downloadAttachment'])->name('tasks.attachments.download');
    Route::delete('tasks/attachments/{attachment}', [TaskController::class, 'deleteAttachment'])->name('tasks.attachments.destroy');

    // Task Hierarchy Management (Spaces, Folders, Lists)
    Route::post('task-spaces', [\App\Http\Controllers\TaskSpaceController::class, 'store'])->name('task-spaces.store');
    Route::patch('task-spaces/{space}', [\App\Http\Controllers\TaskSpaceController::class, 'update'])->name('task-spaces.update');
    Route::delete('task-spaces/{space}', [\App\Http\Controllers\TaskSpaceController::class, 'destroy'])->name('task-spaces.destroy');
    Route::patch('task-spaces/{space}/position', [\App\Http\Controllers\TaskSpaceController::class, 'updatePosition'])->name('task-spaces.update-position');

    Route::post('task-folders', [\App\Http\Controllers\TaskFolderController::class, 'store'])->name('task-folders.store');
    Route::patch('task-folders/{folder}', [\App\Http\Controllers\TaskFolderController::class, 'update'])->name('task-folders.update');
    Route::delete('task-folders/{folder}', [\App\Http\Controllers\TaskFolderController::class, 'destroy'])->name('task-folders.destroy');
    Route::patch('task-folders/{folder}/position', [\App\Http\Controllers\TaskFolderController::class, 'updatePosition'])->name('task-folders.update-position');

    Route::post('task-lists', [\App\Http\Controllers\TaskListController::class, 'store'])->name('task-lists.store');
    Route::patch('task-lists/{list}', [\App\Http\Controllers\TaskListController::class, 'update'])->name('task-lists.update');
    Route::delete('task-lists/{list}', [\App\Http\Controllers\TaskListController::class, 'destroy'])->name('task-lists.destroy');
    Route::patch('task-lists/{list}/position', [\App\Http\Controllers\TaskListController::class, 'updatePosition'])->name('task-lists.update-position');

    // Task Services Management
    Route::resource('task-services', TaskServiceController::class);

    // Settings Module
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('settings/application', [SettingsController::class, 'updateApplicationSettings'])->name('settings.application.update');

    // Business Settings (NEW)
    Route::get('settings/business-info', [SettingsController::class, 'businessInfo'])->name('settings.business-info');
    Route::get('settings/invoice-settings', [SettingsController::class, 'invoiceSettings'])->name('settings.invoice-settings');
    Route::get('settings/notifications', [SettingsController::class, 'notifications'])->name('settings.notifications');
    Route::post('settings/notifications', [SettingsController::class, 'updateNotifications'])->name('settings.notifications.update');
    Route::post('settings/notifications/test-email', [SettingsController::class, 'sendTestEmail'])->name('settings.notifications.test-email');

    // Smartbill Integration Settings
    Route::prefix('settings/smartbill')->name('settings.smartbill.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Settings\SmartbillController::class, 'index'])->name('index');
        Route::post('/credentials', [\App\Http\Controllers\Settings\SmartbillController::class, 'updateCredentials'])->name('credentials.update');
        Route::post('/test-connection', [\App\Http\Controllers\Settings\SmartbillController::class, 'testConnection'])->name('test-connection');
        Route::get('/import', [\App\Http\Controllers\Settings\SmartbillController::class, 'showImportForm'])->name('import');
        Route::post('/import/process', [\App\Http\Controllers\Settings\SmartbillController::class, 'processImport'])->name('import.process');
        Route::post('/import/{importId}/start', [\App\Http\Controllers\Settings\SmartbillController::class, 'startImport'])->name('import.start');
        Route::get('/import/{importId}/progress', [\App\Http\Controllers\Settings\SmartbillController::class, 'getProgress'])->name('import.progress');
    });

    // Nomenclature Settings Pages (Individual routes for each section)
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

    // Nomenclature CRUD Operations (Generic for all nomenclature types)
    Route::post('settings/nomenclature', [\App\Http\Controllers\NomenclatureController::class, 'store'])->name('settings.nomenclature.store');
    Route::patch('settings/nomenclature/{setting}', [\App\Http\Controllers\NomenclatureController::class, 'update'])->name('settings.nomenclature.update');
    Route::delete('settings/nomenclature/{setting}', [\App\Http\Controllers\NomenclatureController::class, 'destroy'])->name('settings.nomenclature.destroy');
    Route::post('settings/nomenclature/reorder', [\App\Http\Controllers\NomenclatureController::class, 'reorder'])->name('settings.nomenclature.reorder');

    // Financial Module
    Route::prefix('financial')->name('financial.')->group(function () {
        // Dashboard
        Route::get('/', [FinancialDashboardController::class, 'index'])->name('dashboard');
        Route::get('/history/{year}', [FinancialDashboardController::class, 'yearlyReport'])->name('yearly-report');
        Route::get('/export/{year}', [FinancialDashboardController::class, 'exportCsv'])->name('export');

        // Revenues
        Route::get('revenues/import', [ImportController::class, 'showRevenueImportForm'])->name('revenues.import');
        Route::post('revenues/import', [ImportController::class, 'importRevenues'])->name('revenues.import.post');
        Route::get('revenues/import/template', [ImportController::class, 'downloadRevenueTemplate'])->name('revenues.import.template');
        Route::resource('revenues', RevenueController::class)->parameters(['revenues' => 'revenue']);

        // Expenses
        Route::get('expenses/import', [ImportController::class, 'showExpenseImportForm'])->name('expenses.import');
        Route::post('expenses/import', [ImportController::class, 'importExpenses'])->name('expenses.import.post');
        Route::get('expenses/import/template', [ImportController::class, 'downloadExpenseTemplate'])->name('expenses.import.template');
        Route::resource('expenses', ExpenseController::class)->parameters(['expenses' => 'expense']);

        // Files
        Route::get('files', [FinancialFileController::class, 'index'])->name('files.index');
        Route::get('files/create', [FinancialFileController::class, 'create'])->name('files.create');
        Route::post('files', [FinancialFileController::class, 'store'])->name('files.store');
        Route::post('files/upload', [FinancialFileController::class, 'upload'])->name('files.upload');
        Route::get('files/download-yearly-zip/{year}', [FinancialFileController::class, 'downloadYearlyZip'])->name('files.download-yearly-zip');
        Route::get('files/download-monthly-zip/{year}/{month}', [FinancialFileController::class, 'downloadMonthlyZip'])->name('files.download-monthly-zip');
        Route::get('files/{file}', [FinancialFileController::class, 'show'])->name('files.show');
        Route::get('files/{file}/download', [FinancialFileController::class, 'download'])->name('files.download');
        Route::patch('files/{file}/rename', [FinancialFileController::class, 'rename'])->name('files.rename');
        Route::delete('files/{file}', [FinancialFileController::class, 'destroy'])->name('files.destroy');
    });
});

require __DIR__.'/auth.php';
