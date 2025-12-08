<?php

namespace App\Providers;

use App\Models\BankingCredential;
use App\Models\Client;
use App\Models\Credential;
use App\Models\Domain;
use App\Models\InternalAccount;
use App\Models\SettingOption;
use App\Models\Subscription;
use App\Models\FinancialRevenue;
use App\Models\FinancialExpense;
use App\Observers\ClientObserver;
use App\Observers\ClientSettingObserver;
use App\Observers\DomainObserver;
use App\Observers\FinancialRevenueObserver;
use App\Observers\FinancialExpenseObserver;
use App\Observers\SubscriptionObserver;
use App\Policies\BankingCredentialPolicy;
use App\Policies\ClientPolicy;
use App\Policies\CredentialPolicy;
use App\Policies\DomainPolicy;
use App\Policies\FinancialExpensePolicy;
use App\Policies\FinancialRevenuePolicy;
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
        // Register FinancialRevenue observer for client total_incomes sync and cache invalidation
        FinancialRevenue::observe(FinancialRevenueObserver::class);

        // Register FinancialExpense observer for cache invalidation
        FinancialExpense::observe(FinancialExpenseObserver::class);

        // Register notification-related observers
        Domain::observe(DomainObserver::class);
        Subscription::observe(SubscriptionObserver::class);
        Client::observe(ClientObserver::class);

        // Register authorization policies
        Gate::policy(BankingCredential::class, BankingCredentialPolicy::class);
        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(Domain::class, DomainPolicy::class);
        Gate::policy(Subscription::class, SubscriptionPolicy::class);
        Gate::policy(InternalAccount::class, InternalAccountPolicy::class);
        Gate::policy(Credential::class, CredentialPolicy::class);
        Gate::policy(FinancialRevenue::class, FinancialRevenuePolicy::class);
        Gate::policy(FinancialExpense::class, FinancialExpensePolicy::class);
    }
}
