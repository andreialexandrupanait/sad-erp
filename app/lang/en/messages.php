<?php

/**
 * Message translations - flash messages, notifications, confirmations
 */

return [
    // Success messages
    'saved' => 'Saved.',
    'created_success' => 'Successfully created!',
    'updated_success' => 'Successfully updated!',
    'deleted_success' => 'Successfully deleted!',
    'imported_success' => 'Successfully imported!',
    'exported_success' => 'Successfully exported!',
    'settings_updated' => 'Settings updated successfully!',
    'copied' => 'Copied!',
    'link_copied' => 'Link copied to clipboard!',

    // Error messages
    'error_occurred' => 'An error occurred. Please try again.',
    'could_not_copy' => 'Could not copy link',
    'status_update_error' => 'Error updating status. Please try again.',

    // Confirmation prompts
    'are_you_sure' => 'Are you sure?',
    'confirm_action' => 'Confirm Action',
    'confirm_delete' => 'Are you sure you want to delete this?',
    'action_cannot_undone' => 'This action cannot be undone.',
    'confirm_delete_file' => 'Are you sure you want to delete this file?',
    'select_at_least_one' => 'Please select at least one item',
    'perform_bulk_action' => 'Are you sure you want to perform this action on :count item(s)?',

    // Specific entity confirmations
    'confirm_delete_client' => 'Are you sure you want to delete this client?',
    'confirm_delete_domain' => 'Are you sure you want to delete this domain?',
    'confirm_delete_subscription' => 'Are you sure you want to delete this subscription?',
    'confirm_delete_credential' => 'Are you sure you want to delete this credential?',
    'confirm_delete_internal_account' => 'Are you sure you want to delete this internal account?',
    'confirm_delete_revenue' => 'Are you sure you want to delete this revenue?',
    'confirm_delete_expense' => 'Are you sure you want to delete this expense?',

    // Bulk action confirmations
    'confirm_delete_clients' => 'Are you sure you want to delete the selected clients? This action cannot be undone.',
    'confirm_delete_domains' => 'Are you sure you want to delete the selected domains? This action cannot be undone.',
    'confirm_delete_subscriptions' => 'Are you sure you want to delete the selected subscriptions? This action cannot be undone.',
    'confirm_delete_expenses' => 'Are you sure you want to delete the selected expenses? This action cannot be undone.',
    'confirm_delete_revenues' => 'Are you sure you want to delete the selected revenues? This action cannot be undone.',
    'confirm_export_clients' => 'Export selected clients to CSV?',
    'confirm_export_domains' => 'Export selected domains to CSV?',
    'confirm_export_subscriptions' => 'Export selected subscriptions to CSV?',
    'confirm_export_expenses' => 'Export selected expenses to CSV?',
    'confirm_toggle_autorenew' => 'Are you sure you want to toggle auto-renew for the selected domains?',
    'confirm_renew_subscriptions' => 'Renew selected subscriptions? The next renewal dates will be advanced according to their billing cycles.',
    'confirm_update_status_to' => 'Update status for selected clients to :status?',

    // Bulk action success
    'clients_updated' => 'Client statuses updated successfully!',
    'clients_exported' => 'Clients exported successfully!',
    'clients_deleted' => 'Clients deleted successfully!',
    'domains_exported' => 'Domains exported successfully!',
    'domains_deleted' => 'Domains deleted successfully!',
    'autorenew_toggled' => 'Auto-renew toggled successfully!',
    'subscriptions_renewed' => 'Subscriptions renewed successfully!',
    'subscriptions_exported' => 'Subscriptions exported successfully!',
    'subscriptions_deleted' => 'Subscriptions deleted successfully!',
    'expenses_exported' => 'Expenses exported successfully!',
    'expenses_deleted' => 'Expenses deleted successfully!',

    // Subscription renewal
    'confirm_subscription_renewal' => 'Confirm renewal of subscription',
    'renewal_date_advanced' => 'The next renewal date will be advanced according to the billing cycle.',
    'error_renewing' => 'Error renewing subscription.',
    'error_renewing_with_msg' => 'Error renewing subscription: :message',

    // File messages
    'file_too_large' => ':name is too large. Maximum size is 10MB.',
    'unsupported_file_type' => ':name has an unsupported file type.',
    'file_duplicate_saved_as' => 'A file with this name already exists. Saved as: :name',
    'file_deleted' => 'File deleted successfully',
    'files_deleted' => ':count files deleted successfully',
    'no_files_selected' => 'No files selected',
    'file_renamed' => 'File renamed successfully',

    // Status change
    'click_to_change_status' => 'Click to change status',
    'click_to_set_status' => 'Click to set status',
    'select_status' => 'Select status...',
    'change_status' => 'Change Status',
    'clear_status' => 'Clear Status',

    // Empty states
    'no_clients' => 'No clients',
    'no_domains' => 'No domains',
    'no_subscriptions' => 'No subscriptions',
    'no_credentials' => 'No credentials',
    'no_internal_accounts' => 'No internal accounts',
    'no_revenues' => 'No revenues',
    'no_expenses' => 'No expenses',
    'get_started_client' => 'Get started by creating your first client',
    'get_started_domain' => 'Get started by creating your first domain',
    'get_started_subscription' => 'Get started by creating your first subscription',
    'get_started_credential' => 'Get started by creating your first credential',
    'get_started_internal_account' => 'Get started by creating your first internal account',
    'get_started_revenue' => 'Get started by creating your first revenue',
    'get_started_expense' => 'Get started by creating your first expense',

    // Coming soon
    'coming_soon' => 'Coming Soon',
    'coming_soon_desc' => 'This settings page is currently under development. It will be available in a future update.',

    // Bulk actions
    'items_not_found_or_access' => 'Some items were not found or you do not have access to them.',
    'no_permission_update_all' => 'You do not have permission to update all selected items.',
    'items_updated_count' => ':count items updated successfully.',
    'no_permission_export_all' => 'You do not have permission to export all selected items.',
    'subscriptions_renewed_count' => ':count subscriptions renewed',
    'subscriptions_updated_count' => ':count subscriptions updated',

    // Expense/Revenue
    'expense_created' => 'Expense created successfully!',
    'expense_added' => 'Expense added successfully.',
    'expense_updated' => 'Expense updated successfully!',
    'expense_deleted' => 'Expense deleted successfully.',
    'revenue_created' => 'Revenue created successfully!',
    'revenue_added' => 'Revenue added successfully.',
    'revenue_updated' => 'Revenue updated successfully!',
    'revenue_deleted' => 'Revenue deleted successfully.',

    // Import
    'import_started' => 'Import started! Processing :count rows in the background. You can track progress below.',
    'import_completed' => 'Import completed: :imported expenses imported, :skipped skipped',
    'import_failed' => 'Import failed. Please check your file format.',
    'import_not_found' => 'Import not found',
    'unauthorized' => 'Unauthorized',
    'import_cancel_running_only' => 'Only running or pending imports can be cancelled',
    'import_cancelled' => 'Import cancelled successfully',
    'import_delete_running' => 'Cannot delete a running import. Cancel it first.',
    'import_deleted' => 'Import deleted successfully',
    'invalid_setting_category' => 'Invalid setting category',

    // Files
    'files_uploaded_single' => ':count file uploaded successfully',
    'files_uploaded_plural' => ':count files uploaded successfully',
    'no_files_for_month' => 'No files exist for the selected month.',
    'no_files_for_year' => 'No files exist for the selected year.',
    'zip_create_error' => 'Could not create ZIP archive.',
    'no_permission_view_password' => 'You do not have permission to view this password.',

    // Module access
    'no_module_access' => 'You do not have permission to :action the :module module.',
];
