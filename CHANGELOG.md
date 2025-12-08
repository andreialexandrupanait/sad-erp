# Changelog

All notable changes to the SimpleAd ERP project are documented in this file.

## [2025-11-20] - Smartbill Import Optimizations for Historical Data (2019-2025)

### Added
- **Database Performance Index**: Added composite index on `[organization_id, smartbill_series, smartbill_invoice_number]` for fast duplicate detection
  - Migration: [2025_11_20_125537_add_composite_index_to_financial_revenues_for_duplicate_detection.php](app/database/migrations/2025_11_20_125537_add_composite_index_to_financial_revenues_for_duplicate_detection.php)
  - Dramatically speeds up imports of large datasets (2019-2025)
  - Optimizes duplicate checking queries

- **Client Name Conversion Command**: Created command to convert ALL CAPS client names to Title Case
  - Command: `php artisan clients:convert-to-title-case`
  - Options: `--dry-run` to preview changes, `--all` to convert all clients
  - Location: [ConvertClientNamesToTitleCaseCommand.php](app/app/Console/Commands/ConvertClientNamesToTitleCaseCommand.php)

### Fixed
- **Client Name Capitalization**: Auto-convert ALL CAPS client names to Title Case
  - Updated [SmartbillImporter.php:235](app/app/Services/SmartbillImporter.php#L235) to format names on creation
  - Updated [ImportController.php:187,163](app/app/Http/Controllers/Financial/ImportController.php) for CSV imports
  - Converts "ASOCIATIA ROMANA" → "Asociatia Romana"
  - Added `formatClientName()` helper method using `mb_convert_case()` for proper UTF-8 handling
  - All future imports will use Title Case automatically

- **PDF Storage Location in SmartbillImporter**: Fixed inconsistent storage between ImportController and SmartbillImporter
  - Updated [SmartbillImporter.php:303-316](app/app/Services/SmartbillImporter.php#L303-L316) to match ImportController pattern
  - Now uses `Storage::disk('financial')` instead of default disk
  - Uses year/month/type structure: `{year}/{MonthName}/Incasari/Factura SAD0XXX.pdf`
  - Consistent naming convention: `Factura SAD0XXX.pdf` with zero-padding
  - All PDFs from both import methods now stored in same location

- **Import Dry-Run Mode & Duplicate Reporting**: Enhanced import preview capabilities
  - **CSV Import Dry-Run**: Added `dry_run` parameter to web-based CSV import
    - New validation field in [ImportController.php:29](app/app/Http/Controllers/Financial/ImportController.php#L29)
    - Logs all operations without making changes
    - Returns summary: "DRY RUN: Would import X revenues, Y duplicates would be skipped"
  - **Duplicate Tracking**: Added detailed tracking of duplicates found during import
    - Collects invoice numbers, dates, and amounts of all duplicates
    - Flash message includes `duplicates_found` array for display
    - Helps verify existing 2025 data won't be duplicated before importing
  - **Command-Line Preview**: Smartbill API import already had `--preview` flag (confirmed working)
  - **Result**: Can safely test large historical imports (2019-2025) without risking data

### Performance Improvements
- Composite database index reduces duplicate check time from O(n) to O(log n)
- Import process optimized for handling thousands of invoices
- Ready for historical data import from 2019 to present

## [2025-11-20] - Smartbill Integration & File Storage Fixes

### Fixed
- **Smartbill PDF Storage & Naming Issues**: Fixed incorrect storage disk usage and naming convention for downloaded Smartbill PDFs
  - **Storage Location**: PDFs were being stored to default 'local' disk instead of 'financial' disk
    - Updated [ImportController.php:340-353](app/app/Http/Controllers/Financial/ImportController.php#L340-L353) to use `Storage::disk('financial')`
    - Migrated 180 existing Smartbill PDFs from `storage/app/private/financial/smartbill/` to `storage/app/financial_files/`
  - **File Naming Convention**: Updated to match existing invoice files
    - Changed from `smartbill_SAD_0XXX.pdf` to `Factura SAD0XXX.pdf`
    - Now consistent with manually uploaded invoices
    - Renamed all 180 existing files to match convention
  - **Directory Structure**: PDFs now stored in proper structure: `{year}/{MonthName}/Incasari/Factura SAD0XXX.pdf`
  - **Result**: All files are now viewable and downloadable correctly through the application

- **Client Matching, Auto-Creation & Auto-Update in Imports**: Complete client management automation for Smartbill imports
  - **Updated Import Logic** [ImportController.php:140-209](app/app/Http/Controllers/Financial/ImportController.php#L140-L209):
    - Prioritizes CIF (tax_id) matching for Smartbill imports
    - Handles CIF variations (with/without 'RO' prefix, whitespace)
    - **Auto-creates clients** if not found (Smartbill imports only)
    - **Auto-updates placeholder clients** with real names from CSV when re-importing
    - Falls back to name matching for non-Smartbill imports
    - Works for both Smartbill and regular CSV imports
  - **Retroactive Matching & Client Creation**:
    - Matched 132 existing revenues with clients by CIF
    - Auto-created 10 placeholder clients for the remaining 48 revenues
    - Client association rate improved from 16.3% to **100%**
  - **Placeholder Client Update Feature**:
    - Re-importing Smartbill data automatically updates placeholder clients
    - Detects clients with "Client CIF XXX" names or auto-creation notes
    - Updates with real company names from CSV without breaking revenue links
  - **Result**:
    - Future Smartbill imports automatically match, create, OR update clients by CIF
    - All revenues now properly linked to clients
    - Re-importing the same Smartbill file updates placeholder names automatically
    - No manual intervention needed for client management

- **Duplicate Prevention in Imports**: Added duplicate detection logic to prevent re-importing existing revenues
  - **Duplicate Detection** [ImportController.php:239-270](app/app/Http/Controllers/Financial/ImportController.php#L239-L270):
    - For Smartbill imports: checks by `smartbill_series` + `smartbill_invoice_number`
    - For regular imports: checks by `document_name` + `occurred_at` date
    - Updates client link if changed (e.g., placeholder client was updated)
    - Skips creating duplicate revenue entries
  - **Cleanup Command**: Created `smartbill:cleanup-duplicates` artisan command
    - Location: [CleanupDuplicateRevenuesCommand.php](app/app/Console/Commands/CleanupDuplicateRevenuesCommand.php)
    - Interactive mode: prompts which duplicate to keep
    - Auto mode (`--auto`): automatically keeps revenues with files attached
    - Dry-run mode (`--dry-run`): preview changes without applying
    - Successfully cleaned up 180 duplicate revenues that were created before duplicate prevention was added
  - **Result**:
    - Re-importing the same Smartbill file no longer creates duplicates
    - Safe to re-import for updating placeholder client names
    - Import process is now fully idempotent

## [2025-11-20] - Smartbill Integration (CSV-Based Approach)

### Added
- **Smartbill Integration Infrastructure**:
  - Installed `necenzurat/smartbill` Laravel package (v1.1.3)
  - Database migration for Smartbill fields in `financial_revenues` table:
    - `smartbill_invoice_number` - Invoice number from Smartbill
    - `smartbill_series` - Invoice series from Smartbill
    - `smartbill_client_cif` - Client CIF from Smartbill
    - `smartbill_imported_at` - Import timestamp
    - `smartbill_raw_data` - Full invoice data as JSON
    - Indexed for fast lookups by organization and invoice number

- **Smartbill Services**:
  - `SmartbillService` - API communication layer with methods:
    - `getInvoice($series, $number)` - Fetch specific invoice details
    - `downloadInvoicePdf($series, $number)` - Download invoice PDF
    - `testConnection()` - Verify API credentials
    - `importFromCsvData($csvData)` - Process Smartbill CSV exports
  - `SmartbillImporter` - Data transformation and import logic (ready for future use)
  - Artisan command: `php artisan smartbill:import` (ready for future API updates)

- **Enhanced CSV Import**:
  - Automatic detection of Smartbill CSV exports (checks for Serie/Numar/CIF columns)
  - Stores Smartbill invoice metadata (series, number, CIF) when importing
  - Optional PDF download checkbox in import form
  - Automatically downloads and attaches invoice PDFs from Smartbill API
  - Progress tracking with statistics (imported, skipped, PDFs downloaded)
  - Updated import form with Smartbill integration information

- **Organization Settings**:
  - Smartbill API credentials stored in organization settings JSON:
    - `smartbill.username` - API email
    - `smartbill.token` - API token
    - `smartbill.cif` - Company CIF

### Technical Implementation
- **Controllers**: Enhanced `Financial/ImportController.php` with:
  - `detectSmartbillExport()` - Auto-detect Smartbill CSV format
  - `downloadSmartbillPdf()` - Fetch and store invoice PDFs via API
- **Services**:
  - `app/Services/SmartbillService.php` - API client
  - `app/Services/SmartbillImporter.php` - Import orchestration
- **Commands**: `app/Console/Commands/SmartbillImportCommand.php`
- **Views**: Updated `financial/revenues/import.blade.php` with Smartbill info
- **Models**: Updated `FinancialRevenue` model with Smartbill fields

### Important Notes
- **API Limitation Discovered**: Smartbill Cloud REST API v1 does NOT have a "list all invoices" endpoint
- **Recommended Workflow**:
  1. Export invoices from Smartbill web interface (Rapoarte > Export) as CSV
  2. Import CSV file using the enhanced CSV import feature
  3. Check "Download invoice PDFs from Smartbill" to automatically fetch PDFs
  4. System stores invoice series/numbers and downloads PDFs via API
- **Future-Ready**: Infrastructure prepared for when/if Smartbill adds bulk list API

### Configuration
- Smartbill credentials configured for organization ID 1:
  - Username: andrei.panait@simplead.ro
  - CIF: 41501661
  - Token: [configured in database]

---

## [2025-11-14] - Major Settings Implementation & Project Cleanup

### Added
- **Notification Settings Page** - Complete implementation with:
  - Master notification toggle
  - 5 notification types (Domain Expiry, Subscription Renewal, Payment Due, Low Balance, Client Status Change)
  - Configurable timing for alerts (days before expiry/renewal)
  - Email recipient management (primary + CC)
  - Full SMTP configuration with password encryption
  - Test email functionality via AJAX
  - Form validation and error handling
  - Multi-language support (EN/RO)

- **Settings Infrastructure** - Placeholder pages for:
  - Business Information Settings
  - Invoice Settings
  - "Coming Soon" template for future settings pages

- **Translations** - 40+ new translation keys for notification settings in both English and Romanian

### Fixed
- **500 Server Error** on settings page caused by undefined routes (`settings.business-info`, `settings.invoice-settings`, `settings.notifications`)
- **Storage symlink** pointing to incorrect directory preventing logo/favicon display
- **Route definitions** for all settings pages with proper authentication middleware

### Changed
- **Project Documentation** - Cleaned up project root:
  - Archived 12 historical markdown files to `app/docs/archive/2025-11-14-cleanup/`
  - Kept 7 essential operational documents in project root
  - Reduced documentation clutter by 63%

### Technical Details
- Controller: `app/Http/Controllers/SettingsController.php`
  - Added `notifications()`, `updateNotifications()`, `sendTestEmail()` methods
- Routes: Added notification settings routes (GET, POST, AJAX)
- Views: Created comprehensive notification settings blade template
- Storage: Fixed symlink `/var/www/html/public/storage` → `/var/www/html/storage/app/public`

---

## Historical Progress (2025-11-01 to 2025-11-13)

For detailed historical progress, see archived documents in `app/docs/archive/2025-11-14-cleanup/`:

### Completed Major Features
- Dashboard with financial widgets and trend charts
- Clients module (CRUD, statuses, search, filters)
- Domains module with expiry tracking
- Subscriptions management with billing cycles
- Expenses tracking with categories
- Financial reports and analytics
- Multi-language support (English/Romanian)
- Settings infrastructure with Application Settings page
- 9 nomenclature management pages (Client Statuses, Domain Statuses, Subscription Statuses, Platform Categories, Expense Categories, Payment Methods, Billing Cycles, Domain Registrars, Currencies)

### Database
- Complete schema with 15+ tables
- ApplicationSetting model with automatic caching
- Robust relationships and foreign keys
- Regular backups in `app/storage/backups/`

### Security
- Laravel Sanctum authentication
- Password encryption for sensitive data
- CSRF protection on all forms
- Input validation on all endpoints

---

## Pending Work

### High Priority
- Implement notification sending infrastructure:
  - Create Mailable classes for each notification type
  - Set up scheduled tasks (cron jobs) for automated notifications
  - Create email templates
- Complete Business Information Settings page
- Complete Invoice Settings page
- ~~**SmartBill Integration**~~ - **COMPLETED** (2025-11-20) - See above for CSV-based implementation
- **Task Management Module** - ClickUp-like task system:
  - **Structure**: Spaces → Folders → Lists (linked to Clients) → Tasks
  - **Default Spaces**: Simplead, FEAA Galati (ability to add more)
  - **Client Integration**: Auto-create list when client added (prompt for folder assignment)
  - **Task Fields**: Name, Service, Due date, Status, Time tracked, Amount, Total amount (auto-calculated)
  - **Services Nomenclature**: Manage services with default hourly rates
  - **Client-specific Rates**: Override rates per client per service
  - **Auto-calculation**: Total amount = (time_tracked / 60) * hourly_rate
  - **Views**:
    - Everything view (all tasks from all lists)
    - Per-client list view
    - Kanban board (drag & drop by status)
  - **Status Integration**: Use existing client statuses from nomenclature
  - **Sidebar**: New "Task Management" section (before Financial)
  - **Custom Fields**: Extensible column system for future additions
  - **Database**: 7 new tables (spaces, folders, lists, tasks, services, client_service_rates, custom_fields)
  - Estimated time: 12-16 hours

### Medium Priority
- Implement log rotation strategy
- Archive/cleanup log files (7 MB in laravel.log + nginx logs)
- Move database backups to external storage

### Low Priority
- Dark theme implementation
- Additional timezone options
- Invoice template customization

---

## Notes

- All settings are stored in `settings_app` table with 1-hour cache TTL
- SMTP passwords are encrypted using Laravel's `encrypt()` function
- Test email functionality requires proper SMTP configuration in `.env`
- Project follows Laravel 11 conventions and best practices
