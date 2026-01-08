<?php

/**
 * Traduceri comenzi consolă - output comenzi artisan
 */

return [
    // Comenzi backup
    'starting_backup' => 'Se pornește backup-ul :type...',
    'executing_mysqldump' => 'Se execută mysqldump în :path...',
    'backup_failed' => 'Backup eșuat: :error',
    'backup_file_not_created' => 'Fișierul backup nu a fost creat!',
    'backup_file_small' => 'Avertisment: Fișierul backup este suspect de mic (:size bytes)',
    'backup_file_corrupted' => 'Fișierul backup este corupt!',
    'backup_completed' => 'Backup bază de date finalizat cu succes!',
    'backup_file_info' => 'Fișier: :path',
    'backup_size_info' => 'Dimensiune: :size',
    'backup_duration_info' => 'Durată: :duration s',
    'directory_created' => 'Director creat: :dir',
    'backing_up_files' => 'Se face backup la fișierele încărcate...',
    'directory_backed_up' => 'Backup realizat: :name',
    'backup_directory_failed' => 'Backup eșuat pentru: :name',
    'files_backup_archived' => 'Arhivă backup fișiere: :path',

    // Comenzi curățare
    'dry_run_mode' => 'MOD SIMULARE - Niciun fișier nu va fi șters',
    'starting_cleanup' => 'Se pornește curățarea backup-urilor...',
    'retention_policy' => 'Politică retenție:',
    'keep_daily' => 'Păstrează :count backup-uri zilnice',
    'keep_weekly' => 'Păstrează :count backup-uri săptămânale',
    'keep_monthly' => 'Păstrează :count backup-uri lunare',
    'delete_older_than' => 'Șterge fișierele mai vechi de :days zile',
    'cleanup_complete' => 'Curățare finalizată!',
    'files_deleted_count' => 'Fișiere șterse: :count',
    'space_freed' => 'Spațiu eliberat: :size',
    'cleaning_backups' => 'Se curăță backup-urile :type în :dir...',
    'keep_file' => '[PĂSTRAT] :file (:size)',
    'would_delete_file' => '[S-AR ȘTERGE] :file - :reason',
    'deleted_file' => '[ȘTERS] :file - :reason',
    'delete_file_failed' => '[EȘUAT] Nu s-a putut șterge :file',

    // Comenzi restaurare
    'backup_file_not_found' => 'Fișier backup negăsit: :file',
    'file_size_info' => 'Dimensiune fișier: :size',
    'target_database_info' => 'Bază de date țintă: :database',
    'restore_warning' => "AVERTISMENT: Aceasta va ÎNLOCUI TOATE DATELE din baza de date ':database'!",
    'restore_cancelled' => 'Restaurare anulată.',
    'restoring_database' => 'Se restaurează baza de date...',
    'restore_failed' => 'Restaurare eșuată: :error',
    'restore_success' => 'Bază de date restaurată cu succes!',
    'restore_duration' => 'Durată: :duration s',
    'available_backups' => 'Backup-uri disponibile:',
    'no_backup_files' => 'Niciun fișier backup găsit.',
    'invalid_selection' => 'Selecție invalidă.',

    // Comenzi abonamente
    'checking_expired' => 'Verificare abonamente expirate...',
    'no_expired_subscriptions' => 'Nu s-au găsit abonamente expirate.',
    'expired_found' => 'S-au găsit :count abonament(e) expirat(e). Avansare date reînnoire...',
    'subscription_advanced' => '✓ :vendor: :old → :new',
    'subscription_advance_error' => '✗ Eroare la avansarea :vendor: :error',
    'subscriptions_advanced' => 'S-au avansat cu succes :count abonament(e).',

    // Comenzi domenii
    'checking_expiring_domains' => 'Verificare domenii care expiră...',
    'no_expiring_domains' => 'Niciun domeniu nu expiră în curând.',
    'expiring_domains_found' => 'S-au găsit :count domeniu(i) care expiră în :days zile.',

    // Comenzi Smartbill
    'organization_not_found' => 'Organizația cu ID :id nu a fost găsită',
    'user_not_found' => 'Utilizatorul cu ID :id nu a fost găsit',
    'user_not_in_org' => 'Utilizatorul nu aparține organizației specificate',
    'organization_info' => 'Organizație: :name',
    'user_info' => 'Utilizator: :name',
    'invalid_date_options' => 'Specificați fie --year fie ambele --from-date și --to-date',
    'invalid_date_format' => 'Format dată invalid. Folosiți AAAA-LL-ZZ',
    'preview_mode' => 'MOD PREVIZUALIZARE - Niciun dat nu va fi salvat',
    'importing_date_range' => 'Se importă facturile de la :from la :to',
    'download_pdfs_info' => 'Descărcare PDF-uri: :status',
    'import_cancelled' => 'Import anulat',
    'importer_init_failed' => 'Inițializare importer eșuată: :error',
    'starting_import' => 'Se pornește importul...',
    'import_completed' => 'Import finalizat cu succes!',
    'invoices_updated' => ':count factură(i) au fost actualizate cu date noi din Smartbill',
    'import_failed' => 'Import eșuat: :error',
    'partial_statistics' => 'Statistici parțiale:',
    'testing_smartbill' => 'Se testează conexiunea API Smartbill...',
    'smartbill_not_configured' => 'Credențialele Smartbill nu sunt configurate pentru această organizație',
    'configure_settings' => 'Configurați următoarele în setările organizației:',
    'smartbill_connection_success' => '✓ Conectare reușită la API Smartbill',
    'smartbill_credentials_valid' => '✓ Credențialele sunt valide',
    'smartbill_connection_failed' => '✗ Conectare eșuată la API Smartbill',
    'connection_test_failed' => '✗ Test conexiune eșuat',

    // Comenzi sincronizare bancară
    'credential_not_found' => 'Credențiala ID :id nu a fost găsită',
    'credential_cannot_sync' => 'Credențiala :id nu poate sincroniza (status: :status)',
    'dispatching_sync' => 'Se trimite job sincronizare pentru credențiala :id (:iban)',
    'sync_job_dispatched' => 'Job sincronizare trimis cu succes',
    'no_credentials_sync' => 'Nicio credențială nu necesită sincronizare',
    'credentials_found' => 'S-au găsit :count credențiale care necesită sincronizare',
    'skipping_credential' => 'Se omite credențiala :id (nu poate sincroniza)',
    'dispatching_sync_iban' => 'Se trimite sincronizare pentru :iban...',
    'sync_jobs_dispatched' => 'S-au trimis :count job-uri de sincronizare',

    // General
    'summary' => 'Sumar:',
    'updated_count' => 'Actualizate: :count',
    'skipped_count' => 'Omise: :count',
    'errors_count' => 'Erori: :count',
];
