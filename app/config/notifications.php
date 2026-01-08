<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Notifications Enabled
    |--------------------------------------------------------------------------
    |
    | This option controls whether the notification system is globally enabled.
    | When disabled, no notifications will be sent regardless of other settings.
    |
    */

    'enabled' => env('NOTIFICATIONS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Notification Channels
    |--------------------------------------------------------------------------
    |
    | Configure the available notification channels. Each channel can be
    | enabled/disabled independently and has its own configuration options.
    |
    */

    'channels' => [
        'slack' => [
            'enabled' => env('SLACK_NOTIFICATIONS_ENABLED', true),
            'webhook_url' => env('SLACK_WEBHOOK_URL'),
            'username' => env('SLACK_USERNAME', 'ERP Notifications'),
            'icon' => env('SLACK_ICON_EMOJI', ':bell:'),
            'timeout' => 5, // HTTP request timeout in seconds
        ],

        'email' => [
            'enabled' => env('EMAIL_NOTIFICATIONS_ENABLED', false),
            'admin_email' => env('NOTIFICATION_ADMIN_EMAIL'),
        ],

        'whatsapp' => [
            'enabled' => env('WHATSAPP_NOTIFICATIONS_ENABLED', false),
            'api_url' => env('WHATSAPP_API_URL'),
            'api_token' => env('WHATSAPP_API_TOKEN'),
            'timeout' => 10,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Notification listeners are queued for asynchronous processing.
    | Configure the queue connection and queue name here.
    |
    */

    'queue' => [
        'connection' => env('NOTIFICATION_QUEUE_CONNECTION', 'database'),
        'queue' => env('NOTIFICATION_QUEUE', 'notifications'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how many times to retry failed notifications and the
    | delay between retries (in seconds).
    |
    */

    'retry' => [
        'times' => env('NOTIFICATION_RETRY_TIMES', 3),
        'delay' => env('NOTIFICATION_RETRY_DELAY', 300), // 5 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Exception Notifications
    |--------------------------------------------------------------------------
    |
    | Configure whether to send notifications for system errors/exceptions.
    | You can also set a threshold to only notify for certain severity levels.
    |
    */

    'notify_on_exceptions' => env('NOTIFY_ON_EXCEPTIONS', true),

    'exception_threshold' => env('EXCEPTION_NOTIFICATION_THRESHOLD', 'error'),

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    |
    | Default values for various notification timing and thresholds.
    |
    */

    'defaults' => [
        'domain_expiry_warning_days' => env('DOMAIN_EXPIRY_WARNING_DAYS', 30),
        'domain_intervals' => [30, 14, 7, 3, 1, 0], // Days before expiry to send notifications
        'domain_overdue_interval' => 7, // Days between overdue notifications

        'subscription_intervals' => [30, 14, 7, 3, 1], // Days before renewal to send notifications
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Type Definitions
    |--------------------------------------------------------------------------
    |
    | Define all available notification types with their labels and default
    | enabled state. Users can override these in their preferences.
    |
    */

    'types' => [
        // Domain notifications
        'domain_expiring_30d' => [
            'label' => 'Domain Expiring in 30 Days',
            'default_enabled' => true,
            'category' => 'domain',
        ],
        'domain_expiring_14d' => [
            'label' => 'Domain Expiring in 14 Days',
            'default_enabled' => true,
            'category' => 'domain',
        ],
        'domain_expiring_7d' => [
            'label' => 'Domain Expiring in 7 Days',
            'default_enabled' => true,
            'category' => 'domain',
        ],
        'domain_expiring_3d' => [
            'label' => 'Domain Expiring in 3 Days',
            'default_enabled' => true,
            'category' => 'domain',
        ],
        'domain_expiring_1d' => [
            'label' => 'Domain Expiring Tomorrow',
            'default_enabled' => true,
            'category' => 'domain',
        ],
        'domain_expiring_today' => [
            'label' => 'Domain Expiring Today',
            'default_enabled' => true,
            'category' => 'domain',
        ],
        'domain_expired' => [
            'label' => 'Domain Expired',
            'default_enabled' => true,
            'category' => 'domain',
        ],

        // Subscription notifications
        'subscription_renewal_30d' => [
            'label' => 'Subscription Renewal in 30 Days',
            'default_enabled' => true,
            'category' => 'subscription',
        ],
        'subscription_renewal_14d' => [
            'label' => 'Subscription Renewal in 14 Days',
            'default_enabled' => true,
            'category' => 'subscription',
        ],
        'subscription_renewal_7d' => [
            'label' => 'Subscription Renewal in 7 Days (Urgent)',
            'default_enabled' => true,
            'category' => 'subscription',
        ],
        'subscription_renewal_3d' => [
            'label' => 'Subscription Renewal in 3 Days (Urgent)',
            'default_enabled' => true,
            'category' => 'subscription',
        ],
        'subscription_renewal_1d' => [
            'label' => 'Subscription Renewal Tomorrow (Urgent)',
            'default_enabled' => true,
            'category' => 'subscription',
        ],
        'subscription_overdue' => [
            'label' => 'Subscription Overdue',
            'default_enabled' => true,
            'category' => 'subscription',
        ],

        // Financial notifications
        'revenue_created' => [
            'label' => 'New Revenue Transaction',
            'default_enabled' => false, // Disabled by default to avoid noise
            'category' => 'financial',
        ],

        // Client notifications
        'client_status_changed' => [
            'label' => 'Client Status Changed',
            'default_enabled' => true,
            'category' => 'client',
        ],

        // System notifications
        'system_error' => [
            'label' => 'System Errors & Exceptions',
            'default_enabled' => true,
            'category' => 'system',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Categories
    |--------------------------------------------------------------------------
    |
    | Group notification types into categories for easier management.
    |
    */

    'categories' => [
        'domain' => [
            'label' => 'Domain Management',
            'description' => 'Notifications about domain expiry and renewals',
        ],
        'subscription' => [
            'label' => 'Subscriptions',
            'description' => 'Notifications about subscription renewals',
        ],
        'financial' => [
            'label' => 'Financial',
            'description' => 'Notifications about revenue and expenses',
        ],
        'client' => [
            'label' => 'Clients',
            'description' => 'Notifications about client status changes',
        ],
        'system' => [
            'label' => 'System',
            'description' => 'System errors and critical events',
        ],
    ],

];
