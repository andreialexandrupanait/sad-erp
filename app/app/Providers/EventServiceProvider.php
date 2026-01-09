<?php

namespace App\Providers;

// Notification Events
use App\Events\Client\ClientStatusChanged;
use App\Events\Domain\DomainExpired;
use App\Events\Domain\DomainExpiringSoon;
use App\Events\FinancialRevenue\RevenueCreated;
use App\Events\Subscription\SubscriptionOverdue;
use App\Events\Subscription\SubscriptionRenewalDue;
use App\Events\System\SystemErrorOccurred;

// Notification Listeners
use App\Listeners\Notification\SendClientNotification;
use App\Listeners\Notification\SendDomainExpiryNotification;
use App\Listeners\Notification\SendRevenueNotification;
use App\Listeners\Notification\SendSubscriptionNotification;
use App\Listeners\Notification\SendSystemErrorNotification;
use App\Listeners\SendOfferPushNotification;
use App\Listeners\UpdateLastLogin;

use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Auth Events
        Login::class => [
            UpdateLastLogin::class,
        ],

        // Domain Notification Events
        DomainExpiringSoon::class => [
            SendDomainExpiryNotification::class . '@handleDomainExpiringSoon',
        ],
        DomainExpired::class => [
            SendDomainExpiryNotification::class . '@handleDomainExpired',
        ],

        // Subscription Notification Events
        SubscriptionRenewalDue::class => [
            SendSubscriptionNotification::class . '@handleSubscriptionRenewalDue',
        ],
        SubscriptionOverdue::class => [
            SendSubscriptionNotification::class . '@handleSubscriptionOverdue',
        ],

        // Financial Notification Events
        RevenueCreated::class => [
            SendRevenueNotification::class,
        ],

        // Client Notification Events
        ClientStatusChanged::class => [
            SendClientNotification::class,
        ],

        // System Notification Events
        SystemErrorOccurred::class => [
            SendSystemErrorNotification::class,
        ],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        SendOfferPushNotification::class,
    ];

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
        //
    }
}
