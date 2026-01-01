<?php

namespace App\Providers;

use App\Http\View\Composers\SettingsComposer;
use App\Http\View\Composers\SidebarComposer;
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
            'credentials.*',
            'dashboard',
            'dashboard.*',
            'components.client-status-badge',
            'components.slide-panel-client-*',
            'components.client-form-fields',
            'components.credential-form',
            'components.ui.client-select',
        ], SettingsComposer::class);

        // Share task workspace hierarchy with sidebar
        View::composer([
            'components.sidebar',
        ], SidebarComposer::class);
    }
}
