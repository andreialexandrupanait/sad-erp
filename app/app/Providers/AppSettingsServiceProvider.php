<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\ApplicationSetting;

class AppSettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share application settings with all views
        View::composer('*', function ($view) {
            $appSettings = [
                'app_name' => ApplicationSetting::get('app_name', 'ERP System'),
                'app_logo' => ApplicationSetting::get('app_logo'),
                'app_favicon' => ApplicationSetting::get('app_favicon'),
                'primary_color' => ApplicationSetting::get('primary_color', '#3b82f6'),
                'language' => ApplicationSetting::get('language', 'ro'),
                'timezone' => ApplicationSetting::get('timezone', 'Europe/Bucharest'),
                'date_format' => ApplicationSetting::get('date_format', 'd/m/Y'),
            ];

            $view->with('globalAppSettings', $appSettings);
        });
    }
}
