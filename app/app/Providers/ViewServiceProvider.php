<?php

namespace App\Providers;

use App\Http\View\Composers\SettingsComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * View Service Provider
 *
 * Registers view composers for efficient data sharing across views
 */
class ViewServiceProvider extends ServiceProvider
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
        // Share client statuses with client-related views
        View::composer([
            'clients.*',
            'dashboard',
            'dashboard.*',
            'components.client-status-badge',
            'components.slide-panel-client-*',
            'components.client-form-fields',
        ], SettingsComposer::class);

        // You can add more composers here as needed
        // Example:
        // View::composer(['subscriptions.*'], SubscriptionSettingsComposer::class);
    }
}
