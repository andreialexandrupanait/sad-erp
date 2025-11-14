# Changelog

All notable changes to the SimpleAd ERP project are documented in this file.

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
