<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Profit Margin Thresholds
    |--------------------------------------------------------------------------
    |
    | These thresholds determine the status levels for profit margin metrics.
    | Values are percentages.
    |
    */
    'profit_margin' => [
        'thresholds' => [
            'excellent' => 20,  // >= 20% is excellent
            'good' => 10,       // >= 10% is good
            // < 10% is considered low
        ],
        'colors' => [
            'excellent' => [
                'bg' => 'bg-green-50',
                'text' => 'text-green-600',
                'badge' => 'bg-green-100 text-green-700',
            ],
            'good' => [
                'bg' => 'bg-yellow-50',
                'text' => 'text-yellow-600',
                'badge' => 'bg-yellow-100 text-yellow-700',
            ],
            'low' => [
                'bg' => 'bg-red-50',
                'text' => 'text-red-600',
                'badge' => 'bg-red-100 text-red-700',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Revenue Concentration Risk Thresholds
    |--------------------------------------------------------------------------
    |
    | These thresholds determine risk levels based on revenue concentration
    | from top clients. Values are percentages.
    |
    */
    'revenue_concentration' => [
        'thresholds' => [
            'high_risk' => 50,    // >= 50% is high risk
            'medium_risk' => 30,  // >= 30% is medium risk
            // < 30% is considered low risk
        ],
        'colors' => [
            'high' => [
                'bg' => 'bg-red-50',
                'text' => 'text-red-600',
                'badge' => 'bg-red-100 text-red-700',
            ],
            'medium' => [
                'bg' => 'bg-yellow-50',
                'text' => 'text-yellow-600',
                'badge' => 'bg-yellow-100 text-yellow-700',
            ],
            'low' => [
                'bg' => 'bg-green-50',
                'text' => 'text-green-600',
                'badge' => 'bg-green-100 text-green-700',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Chart Colors
    |--------------------------------------------------------------------------
    |
    | Color palettes for various chart types used in dashboard widgets.
    |
    */
    'chart_colors' => [
        'expense_categories' => [
            'rgb(239, 68, 68)',   // red-500
            'rgb(248, 113, 113)', // red-400
            'rgb(252, 165, 165)', // red-300
            'rgb(254, 202, 202)', // red-200
            'rgb(249, 115, 22)',  // orange-500
            'rgb(251, 146, 60)',  // orange-400
            'rgb(253, 186, 116)', // orange-300
            'rgb(234, 179, 8)',   // yellow-500
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Cache TTL (time to live) in seconds for various dashboard metrics.
    |
    */
    'cache' => [
        'financial_metrics_ttl' => 300,    // 5 minutes
        'client_metrics_ttl' => 600,       // 10 minutes
        'domain_metrics_ttl' => 900,       // 15 minutes
        'subscription_metrics_ttl' => 300, // 5 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Widget Settings
    |--------------------------------------------------------------------------
    |
    | General settings for dashboard widgets.
    |
    */
    'widgets' => [
        'domain_expiry_days' => 30,           // Show domains expiring within this many days
        'subscription_renewal_days' => 30,    // Show subscriptions renewing within this many days
        'top_clients_limit' => 5,             // Number of top clients to display
        'expense_categories_scroll_height' => 'max-h-64', // Tailwind class for scrollable height
    ],
];
