<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sentry Laravel SDK Configuration
    |--------------------------------------------------------------------------
    |
    | This file configures the Sentry Laravel SDK for error tracking and
    | performance monitoring. Get your DSN from sentry.io.
    |
    */

    // The DSN tells the SDK where to send events. Get it from sentry.io
    'dsn' => env('SENTRY_LARAVEL_DSN', env('SENTRY_DSN')),

    // Capture unhandled exceptions
    'send_default_pii' => env('SENTRY_SEND_DEFAULT_PII', false),

    // The release version of your application
    // Set this to your deployment version for better error tracking
    'release' => env('SENTRY_RELEASE', config('app.version', '1.0.0')),

    // The environment (production, staging, local, etc.)
    'environment' => env('SENTRY_ENVIRONMENT', env('APP_ENV', 'production')),

    // Set a sampling rate for transactions (performance monitoring)
    // 1.0 = 100% of transactions, 0.1 = 10% of transactions
    'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE', 0.1),

    // Set a sampling rate for profiling (requires traces)
    // This is relative to traces_sample_rate
    'profiles_sample_rate' => env('SENTRY_PROFILES_SAMPLE_RATE', 0.1),

    // Controllers that should not report exceptions
    'controllers_base_namespace' => env('SENTRY_CONTROLLERS_BASE_NAMESPACE', 'App\\Http\\Controllers'),

    // Breadcrumbs settings
    'breadcrumbs' => [
        // Capture Laravel logs as breadcrumbs
        'logs' => true,

        // Capture Laravel cache events as breadcrumbs
        'cache' => true,

        // Capture Livewire events as breadcrumbs
        'livewire' => true,

        // Capture SQL queries as breadcrumbs
        'sql_queries' => true,

        // Capture SQL bindings in breadcrumbs (disable in production for security)
        'sql_bindings' => env('APP_DEBUG', false),

        // Capture queue job information as breadcrumbs
        'queue_info' => true,

        // Capture command information as breadcrumbs
        'command_info' => true,

        // Capture HTTP client requests as breadcrumbs
        'http_client_requests' => true,
    ],

    // Performance tracing settings
    'tracing' => [
        // Capture SQL queries for tracing
        'sql_queries' => true,

        // Capture SQL query origins (file/line that executed the query)
        'sql_origin' => true,

        // Capture views for tracing
        'views' => true,

        // Capture Livewire components for tracing
        'livewire' => true,

        // Capture HTTP client requests for tracing
        'http_client_requests' => true,

        // Capture queue jobs for tracing
        'queue_jobs' => true,

        // Capture queue job origins
        'queue_job_transactions' => true,

        // Capture Redis commands for tracing
        'redis_commands' => env('SENTRY_TRACE_REDIS_COMMANDS', false),

        // Set the Redis command duration threshold (ms)
        'redis_commands_duration_threshold_ms' => 1,

        // Control duration threshold for SQL query tracing (ms)
        'sql_queries_duration_threshold_ms' => 100,

        // Whether to capture missing routes
        'missing_routes' => false,

        // Adjust spans for long-running operations
        'continue_after_response' => true,

        // Default integration options
        'default_integrations' => true,
    ],
];
