<?php

/**
 * Notification translations - English
 */

return [
    // Subscription notifications
    'subscription' => [
        'overdue_title' => 'Subscription Overdue: :vendor',
        'due_today_title' => 'Subscription Due Today: :vendor',
        'renewal_title' => 'Subscription Renewal: :vendor',

        'overdue_body' => 'The subscription for :vendor is :days overdue. Please review and renew immediately.',
        'due_today_body' => 'The subscription for :vendor is due for renewal TODAY.',
        'due_tomorrow_body' => 'The subscription for :vendor is due for renewal TOMORROW.',
        'renewal_body' => 'The subscription for :vendor will renew in :days.',

        'days_overdue' => 'Days Overdue',
        'days_until_renewal' => 'Days Until Renewal',
        'renewal_date' => 'Renewal Date',
        'price' => 'Price',
        'status' => 'Status',

        // Pluralization for days
        'day' => '{1} day|[2,*] days',
    ],

    // Status translations
    'status' => [
        'active' => 'Active',
        'paused' => 'Paused',
        'cancelled' => 'Cancelled',
        'expired' => 'Expired',
    ],

    // Billing cycle translations
    'billing_cycle' => [
        'monthly' => 'month',
        'yearly' => 'year',
        'quarterly' => 'quarter',
        'custom' => 'custom',
    ],

    // Common
    'view_details' => 'View Details',
    'not_available' => 'N/A',

    // Email
    'email' => [
        'subject_prefix_urgent' => 'URGENT: ',
        'category_domain' => 'Domain',
        'category_subscription' => 'Subscription',
        'category_financial' => 'Financial',
        'category_client' => 'Client',
        'category_system' => 'System',
        'category_alert' => 'Alert',
        'sent_by' => 'This notification was sent by',
    ],

    // Test notification
    'test' => [
        'title' => 'Notification System Test',
        'body' => 'This is a test message from your ERP notification system. If you\'re seeing this email, your email notifications are properly configured and working!',
        'environment' => 'Environment',
        'timestamp' => 'Timestamp',
        'mail_driver' => 'Mail Driver',
        'banner' => 'This is a test notification - Email is configured correctly!',
    ],
];
