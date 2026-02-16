<?php

/**
 * Notification translations - Romanian
 */

return [
    // Subscription notifications
    'subscription' => [
        'overdue_title' => 'Abonament restant: :vendor',
        'due_today_title' => 'Abonament scadent azi: :vendor',
        'renewal_title' => 'Reinnoire abonament: :vendor',

        'overdue_body' => 'Abonamentul pentru :vendor este restant de :days. Va rugam sa revedeti si sa reinnoiti imediat.',
        'due_today_body' => 'Abonamentul pentru :vendor este scadent pentru reinnoire AZI.',
        'due_tomorrow_body' => 'Abonamentul pentru :vendor este scadent pentru reinnoire MAINE.',
        'renewal_body' => 'Abonamentul pentru :vendor se va reinnoi in :days.',

        'days_overdue' => 'Zile restante',
        'days_until_renewal' => 'Zile pana la reinnoire',
        'renewal_date' => 'Data reinnoirii',
        'price' => 'Pret',
        'status' => 'Status',

        // Pluralization for days
        'day' => '{1} zi|[2,*] zile',
    ],

    // Status translations
    'status' => [
        'active' => 'Activ',
        'paused' => 'In pauza',
        'cancelled' => 'Anulat',
        'expired' => 'Expirat',
    ],

    // Billing cycle translations
    'billing_cycle' => [
        'monthly' => 'lunar',
        'yearly' => 'anual',
        'quarterly' => 'trimestrial',
        'custom' => 'personalizat',
    ],

    // Common
    'view_details' => 'Vezi detalii',
    'not_available' => 'N/A',

    // Email
    'email' => [
        'subject_prefix_urgent' => 'URGENT: ',
        'category_domain' => 'Domeniu',
        'category_subscription' => 'Abonament',
        'category_financial' => 'Financiar',
        'category_client' => 'Client',
        'category_system' => 'Sistem',
        'category_alert' => 'Alerta',
        'sent_by' => 'Aceasta notificare a fost trimisa de',
    ],

    // Test notification
    'test' => [
        'title' => 'Test sistem de notificari',
        'body' => 'Acesta este un mesaj de test de la sistemul de notificari ERP. Daca vedeti acest email, notificarile prin email sunt configurate corect si functioneaza!',
        'environment' => 'Mediu',
        'timestamp' => 'Data si ora',
        'mail_driver' => 'Driver email',
        'banner' => 'Aceasta este o notificare de test - Email-ul este configurat corect!',
    ],
];
