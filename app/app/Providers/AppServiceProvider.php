<?php

namespace App\Providers;

use App\Models\Credential;
use App\Models\Domain;
use App\Models\InternalAccount;
use App\Models\SettingOption;
use App\Models\Subscription;
use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;
use App\Models\Task;
use App\Observers\ClientSettingObserver;
use App\Observers\FinancialRevenueObserver;
use App\Observers\FinancialExpenseObserver;
use App\Observers\TaskObserver;
use App\Policies\CredentialPolicy;
use App\Policies\DomainPolicy;
use App\Policies\InternalAccountPolicy;
use App\Policies\SubscriptionPolicy;
use Illuminate\Support\Facades\Gate;
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

        // Register Task observer for activity logging
        Task::observe(TaskObserver::class);

        // Register FinancialRevenue observer for client total_incomes sync and cache invalidation
        FinancialRevenue::observe(FinancialRevenueObserver::class);

        // Register FinancialExpense observer for cache invalidation
        FinancialExpense::observe(FinancialExpenseObserver::class);

        // Register authorization policies
        Gate::policy(Domain::class, DomainPolicy::class);
        Gate::policy(Subscription::class, SubscriptionPolicy::class);
        Gate::policy(InternalAccount::class, InternalAccountPolicy::class);
        Gate::policy(Credential::class, CredentialPolicy::class);
    }
}
