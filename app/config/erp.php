<?php

/**
 * ERP Application Configuration
 *
 * Centralized configuration for application-specific settings like
 * cache TTLs, pagination limits, and default values.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Cache time-to-live values in seconds for various cached data.
    | These can be overridden via environment variables.
    |
    */

    'cache' => [
        // Dashboard metrics cache (5 minutes)
        'dashboard_metrics_ttl' => env('CACHE_DASHBOARD_METRICS_TTL', 300),

        // Client dropdown cache (5 minutes)
        'client_dropdown_ttl' => env('CACHE_CLIENT_DROPDOWN_TTL', 300),

        // Client status filter cache (1 hour)
        'client_status_ttl' => env('CACHE_CLIENT_STATUS_TTL', 3600),

        // Application settings cache (24 hours)
        'app_settings_ttl' => env('CACHE_APP_SETTINGS_TTL', 86400),

        // Financial overview cache (5 minutes)
        'financial_overview_ttl' => env('CACHE_FINANCIAL_OVERVIEW_TTL', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Settings
    |--------------------------------------------------------------------------
    |
    | Default pagination limits for various list views.
    |
    */

    'pagination' => [
        // Default items per page
        'default' => env('PAGINATION_DEFAULT', 25),

        // Maximum items per page (for API requests)
        'max' => env('PAGINATION_MAX', 100),

        // Allowed per-page options for dropdowns
        'allowed' => [10, 25, 50, 100],

        // Specific page limits
        'clients' => env('PAGINATION_CLIENTS', 25),
        'domains' => env('PAGINATION_DOMAINS', 15),
        'offers' => env('PAGINATION_OFFERS', 25),
        'contracts' => env('PAGINATION_CONTRACTS', 25),
        'revenues' => env('PAGINATION_REVENUES', 25),
        'expenses' => env('PAGINATION_EXPENSES', 25),
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency Settings
    |--------------------------------------------------------------------------
    |
    | Default currency and supported currencies.
    |
    */

    'currency' => [
        // Default currency for new records
        'default' => env('DEFAULT_CURRENCY', 'RON'),

        // Supported currencies
        'supported' => ['RON', 'EUR', 'USD'],

        // Currency for financial reporting
        'reporting' => env('REPORTING_CURRENCY', 'RON'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Document Settings
    |--------------------------------------------------------------------------
    |
    | Settings for offers, contracts, and other documents.
    |
    */

    'documents' => [
        // Default offer validity in days
        'offer_validity_days' => env('OFFER_VALIDITY_DAYS', 30),

        // Lock expiry time for contracts (minutes)
        'contract_lock_minutes' => env('CONTRACT_LOCK_MINUTES', 15),
    ],

];
