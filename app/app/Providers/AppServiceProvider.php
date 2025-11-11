<?php

namespace App\Providers;

use App\Models\SettingOption;
use App\Observers\ClientSettingObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers for automatic cache invalidation
        // SettingOption::observe(ClientSettingObserver::class);
        // TODO: Update observer to work with unified SettingOption model
    }
}
