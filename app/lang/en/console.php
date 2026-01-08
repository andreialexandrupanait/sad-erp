<?php

/**
 * Console command translations - artisan command output
 */

return [
    // Backup commands
    'starting_backup' => 'Starting :type backup...',
    'executing_mysqldump' => 'Executing mysqldump to :path...',
    'backup_failed' => 'Backup failed: :error',
    'backup_file_not_created' => 'Backup file was not created!',
    'backup_file_small' => 'Warning: Backup file is suspiciously small (:size bytes)',
    'backup_file_corrupted' => 'Backup file is corrupted!',
    'backup_completed' => 'Database backup completed successfully!',
    'backup_file_info' => 'File: :path',
    'backup_size_info' => 'Size: :size',
    'backup_duration_info' => 'Duration: :duration s',
    'directory_created' => 'Created directory: :dir',
    'backing_up_files' => 'Backing up uploaded files...',
    'directory_backed_up' => 'Backed up: :name',
    'backup_directory_failed' => 'Failed to backup: :name',
    'files_backup_archived' => 'Files backup archived: :path',

    // Cleanup commands
    'dry_run_mode' => 'DRY RUN MODE - No files will be deleted',
    'starting_cleanup' => 'Starting backup cleanup...',
    'retention_policy' => 'Retention policy:',
    'keep_daily' => 'Keep :count daily backups',
    'keep_weekly' => 'Keep :count weekly backups',
    'keep_monthly' => 'Keep :count monthly backups',
    'delete_older_than' => 'Delete files older than :days days',
    'cleanup_complete' => 'Cleanup complete!',
    'files_deleted_count' => 'Files deleted: :count',
    'space_freed' => 'Space freed: :size',
    'cleaning_backups' => 'Cleaning :type backups in :dir...',
    'keep_file' => '[KEEP] :file (:size)',
    'would_delete_file' => '[WOULD DELETE] :file - :reason',
    'deleted_file' => '[DELETED] :file - :reason',
    'delete_file_failed' => '[FAILED] Could not delete :file',

    // Restore commands
    'backup_file_not_found' => 'Backup file not found: :file',
    'file_size_info' => 'File size: :size',
    'target_database_info' => 'Target database: :database',
    'restore_warning' => "WARNING: This will REPLACE ALL DATA in the ':database' database!",
    'restore_cancelled' => 'Restore cancelled.',
    'restoring_database' => 'Restoring database...',
    'restore_failed' => 'Restore failed: :error',
    'restore_success' => 'Database restored successfully!',
    'restore_duration' => 'Duration: :duration s',
    'available_backups' => 'Available backups:',
    'no_backup_files' => 'No backup files found.',
    'invalid_selection' => 'Invalid selection.',

    // Subscription commands
    'checking_expired' => 'Checking expired subscriptions...',
    'no_expired_subscriptions' => 'No expired subscriptions found.',
    'expired_found' => 'Found :count expired subscription(s). Advancing renewal dates...',
    'subscription_advanced' => '✓ :vendor: :old → :new',
    'subscription_advance_error' => '✗ Error advancing :vendor: :error',
    'subscriptions_advanced' => 'Successfully advanced :count subscription(s).',

    // Domain commands
    'checking_expiring_domains' => 'Checking for expiring domains...',
    'no_expiring_domains' => 'No domains expiring soon.',
    'expiring_domains_found' => 'Found :count domain(s) expiring within :days days.',

    // Smartbill commands
    'organization_not_found' => 'Organization with ID :id not found',
    'user_not_found' => 'User with ID :id not found',
    'user_not_in_org' => 'User does not belong to the specified organization',
    'organization_info' => 'Organization: :name',
    'user_info' => 'User: :name',
    'invalid_date_options' => 'Please specify either --year or both --from-date and --to-date',
    'invalid_date_format' => 'Invalid date format. Please use YYYY-MM-DD',
    'preview_mode' => 'PREVIEW MODE - No data will be saved',
    'importing_date_range' => 'Importing invoices from :from to :to',
    'download_pdfs_info' => 'Download PDFs: :status',
    'import_cancelled' => 'Import cancelled',
    'importer_init_failed' => 'Failed to initialize importer: :error',
    'starting_import' => 'Starting import...',
    'import_completed' => 'Import completed successfully!',
    'invoices_updated' => ':count invoice(s) were updated with new data from Smartbill',
    'import_failed' => 'Import failed: :error',
    'partial_statistics' => 'Partial statistics:',
    'testing_smartbill' => 'Testing Smartbill API connection...',
    'smartbill_not_configured' => 'Smartbill credentials not configured for this organization',
    'configure_settings' => 'Please configure the following in organization settings:',
    'smartbill_connection_success' => '✓ Successfully connected to Smartbill API',
    'smartbill_credentials_valid' => '✓ Credentials are valid',
    'smartbill_connection_failed' => '✗ Failed to connect to Smartbill API',
    'connection_test_failed' => '✗ Connection test failed',

    // Bank sync commands
    'credential_not_found' => 'Credential ID :id not found',
    'credential_cannot_sync' => 'Credential :id cannot sync (status: :status)',
    'dispatching_sync' => 'Dispatching sync job for credential :id (:iban)',
    'sync_job_dispatched' => 'Sync job dispatched successfully',
    'no_credentials_sync' => 'No credentials need syncing',
    'credentials_found' => 'Found :count credentials needing sync',
    'skipping_credential' => 'Skipping credential :id (cannot sync)',
    'dispatching_sync_iban' => 'Dispatching sync for :iban...',
    'sync_jobs_dispatched' => 'Dispatched :count sync jobs',

    // General
    'summary' => 'Summary:',
    'updated_count' => 'Updated: :count',
    'skipped_count' => 'Skipped: :count',
    'errors_count' => 'Errors: :count',
];
