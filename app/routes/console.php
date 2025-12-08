<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ============================================
// BACKUP SCHEDULES (Critical - Data Protection)
// ============================================

// Daily database backup at 02:00 AM
Schedule::command('backup:database --compress --type=daily')
    ->dailyAt('02:00')
    ->onSuccess(fn() => Log::info('Daily backup completed successfully'))
    ->onFailure(fn() => Log::critical('DAILY BACKUP FAILED - IMMEDIATE ATTENTION REQUIRED'));

// Weekly full backup with files on Sunday at 03:00 AM
Schedule::command('backup:database --compress --include-files --type=weekly')
    ->weeklyOn(0, '03:00')
    ->onSuccess(fn() => Log::info('Weekly backup completed successfully'))
    ->onFailure(fn() => Log::critical('WEEKLY BACKUP FAILED'));

// Monthly backup on 1st of each month at 04:00 AM
Schedule::command('backup:database --compress --include-files --type=monthly')
    ->monthlyOn(1, '04:00')
    ->onSuccess(fn() => Log::info('Monthly backup completed successfully'))
    ->onFailure(fn() => Log::critical('MONTHLY BACKUP FAILED'));

// Cleanup old backups every Sunday at 05:00 AM
Schedule::command('backup:cleanup')
    ->weeklyOn(0, '05:00');

// ============================================
// BUSINESS LOGIC SCHEDULES
// ============================================

// Automatically advance overdue subscriptions every day at midnight
Schedule::command('subscriptions:advance-overdue')->daily();

// Check for expiring domains daily at 09:00 (business hours)
Schedule::command('domains:check-expiring')->dailyAt('09:00');

// Check for subscriptions due for renewal daily at 09:00 (business hours)
Schedule::command('subscriptions:check-renewals')->dailyAt('09:00');

// Sync bank transactions every 12 hours (configurable)
Schedule::command('banking:sync-transactions --all')
    ->cron('0 */' . config('banking.sync.frequency_hours', 12) . ' * * *');
