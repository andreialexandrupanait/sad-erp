# SimpleAD ERP

Internal business management system for domains, subscriptions, credentials, and client management.

## Quick Start

```bash
# Install dependencies
composer install
npm install && npm run build

# Configure environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Start development server
php artisan serve
```

## Stack

- **Backend:** Laravel 11, PHP 8.2+
- **Frontend:** Blade, Alpine.js, Tailwind CSS
- **Database:** MySQL 8
- **Auth:** Laravel Breeze

## Modules

| Module | Description |
|--------|-------------|
| Domains | Track domain registrations, expiry dates, registrars |
| Subscriptions | Recurring service billing with cycle management |
| Internal Accounts | Secure credential storage for team/personal use |
| Credentials | Client access credentials (encrypted) |
| Clients | Customer management with contacts |
| Tasks | Task tracking and services |
| Settings | Dynamic configuration via SettingOption |

## Development

```bash
# Clear caches
php artisan optimize:clear

# Run tests
php artisan test

# Check code style
./vendor/bin/pint
```

## Documentation

- [ROADMAP.md](ROADMAP.md) - Project status, priorities, and plans

## License

Proprietary - All rights reserved.
