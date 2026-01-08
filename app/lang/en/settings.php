<?php

/**
 * Settings translations
 */

return [
    // Main settings page
    'title' => 'Settings',
    'manage_app' => 'Manage your application',

    // Application settings
    'application_settings' => 'Application Settings',
    'app_settings_desc' => 'Configure your application preferences and branding',
    'application_name' => 'Application Name',
    'application_logo' => 'Application Logo',
    'current_logo' => 'Current logo',
    'logo_hint' => 'Recommended size: 200x50px. Accepted formats: PNG, JPG, SVG',
    'favicon' => 'Favicon',
    'current_favicon' => 'Current favicon',
    'favicon_hint' => 'Recommended size: 32x32px or 64x64px. Accepted formats: PNG, ICO',
    'primary_color' => 'Primary Color',
    'primary_color_hint' => 'This color will be used throughout the application for buttons, links, and accents',

    // Appearance
    'appearance' => 'Appearance',
    'theme_mode' => 'Theme Mode',
    'light' => 'Light',
    'dark' => 'Dark',
    'auto_system' => 'Auto (System)',
    'theme_hint' => 'Currently only Light theme is implemented',

    // Regional settings
    'regional_settings' => 'Regional Settings',
    'timezone' => 'Timezone',
    'date_format' => 'Date Format',
    'language' => 'Language',

    // Business settings
    'business' => 'Business',
    'business_information' => 'Business Information',
    'invoice_settings' => 'Invoice Settings',

    // Notification settings
    'notification_settings' => 'Notification Settings',
    'enable_email_notifications' => 'Enable Email Notifications',
    'days_before' => 'days before',

    // Integrations
    'integrations' => 'Integrations',
    'smartbill_integration' => 'Smartbill Integration',
    'smartbill_desc' => 'Configure your Smartbill API credentials and import invoices automatically',
    'api_credentials' => 'API Credentials',
    'api_credentials_hint' => 'Enter your Smartbill API credentials. You can find these in your Smartbill account settings.',
    'api_token' => 'API Token',
    'your_api_token' => 'Your API token',
    'cif' => 'CIF (Company Tax ID)',
    'save_credentials' => 'Save Credentials',
    'test_connection' => 'Test Connection',
    'connection_success' => 'Connection successful! Your credentials are working.',
    'credentials_updated' => 'Smartbill credentials updated successfully!',
    'configure_first' => 'Configure your credentials first',
    'configure_first_hint' => 'Please enter your Smartbill API credentials above before you can start importing invoices.',

    // Import section
    'import_invoices' => 'Import Invoices',
    'import_invoices_title' => 'Import Smartbill Invoices',
    'import_invoices_hint' => 'Upload a CSV or Excel file exported from Smartbill to import invoices with automatic PDF download.',
    'upload_csv_excel' => 'Upload your CSV or Excel export from Smartbill',
    'how_to_export' => 'How to export from Smartbill:',
    'step1_export' => 'Step 1: Export from Smartbill',
    'step2_upload' => 'Step 2: Upload File',
    'log_into_smartbill' => 'Log in to your Smartbill account',
    'go_to' => 'Go to',
    'reports' => 'Reports',
    'select_date_range' => 'Select your date range',
    'export_csv_excel' => 'Export as CSV or Excel',
    'upload_file_here' => 'Upload the file here',
    'download_pdfs' => 'Download invoice PDFs from Smartbill',
    'download_pdfs_hint' => 'Automatically fetch and attach PDF files for each invoice (recommended)',
    'start_import' => 'Start Import',

    // Import progress
    'importing' => 'Importing Invoices...',
    'initializing' => 'Initializing...',
    'processing' => 'Processing your invoices...',
    'import_complete' => 'Import Complete!',
    'import_another' => 'Import Another File',
    'view_revenues' => 'View Revenues',
    'errors_encountered' => 'Errors Encountered:',
    'skipped' => 'Skipped',
    'errors' => 'Errors',
    'go_to_reports_export' => 'Go to Reports → Export',
    'download_the_file' => 'Download the file',
    'file_types_hint' => 'CSV, XLS, or XLSX (max 10MB)',
    'invalid_file_type' => 'Invalid file type. Please upload a CSV, XLS, or XLSX file.',
    'please_select_file' => 'Please select a file first',
    'uploading' => 'Uploading...',
    'import_failed' => 'Import Failed',
    'try_again' => 'Try Again',
    'error_processing' => 'There was an error processing your import.',

    // Nomenclature
    'nomenclature' => 'Nomenclature',
    'client_statuses' => 'Client Statuses',
    'domain_statuses' => 'Domain Statuses',
    'subscription_statuses' => 'Subscription Statuses',
    'platform_categories' => 'Platform Categories',
    'expense_categories' => 'Expense Categories',
    'payment_methods' => 'Payment Methods',
    'billing_cycles' => 'Billing Cycles',
    'domain_registrars' => 'Domain Registrars',
    'currencies' => 'Currencies',
    'services' => 'Services',

    // Nomenclature - Actions
    'add_category' => 'Add category',
    'add_option' => 'Add option',
    'add_subcategory' => 'Add subcategory',
    'edit' => 'Edit',
    'delete' => 'Delete',
    'delete_selected' => 'Delete selected',
    'name' => 'Name',
    'main_category' => '-- Main category --',
    'main' => '-- Main --',
    'confirm_delete_items' => 'Are you sure you want to delete :count item(s)?',
    'error_deleting' => 'Error deleting',
    'save' => 'Save',
    'cancel_action' => 'Cancel',
    'active' => 'Active',
    'inactive' => 'Inactive',
    'status' => 'Status',
    'color' => 'Color',
    'change_color' => 'Change color',
    'no_categories' => 'No categories available.',
    'no_options' => 'No options available.',
    'error_saving' => 'Error saving. Please try again.',
    'confirm_delete_option' => 'Are you sure you want to delete this option?',
    'error_occurred' => 'An error occurred. Please try again.',
    'categories' => 'categories',
    'options' => 'options',
    'element_selected' => 'element selected',
    'elements_selected' => 'elements selected',
];
