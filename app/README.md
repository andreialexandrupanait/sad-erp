# SimpleAD ERP

Enterprise Resource Planning system for client, contract, offer, and financial management.

## Tech Stack

- **Backend:** Laravel 12, PHP 8.2+
- **Frontend:** Tailwind CSS 3, Alpine.js 3, Livewire 3
- **Database:** MySQL 8
- **Cache/Queue:** Redis
- **Build:** Vite 7

## Requirements

- PHP 8.2+
- Composer 2.x
- Node.js 18+ & npm
- MySQL 8.0+
- Redis 6+

## Quick Start

```bash
# Clone repository
git clone <repository-url>
cd erp

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed  # Development only - will NOT run in production

# Build assets
npm run build

# Start development server
php artisan serve
```

## Project Structure

```
app/
├── Http/Controllers/    # Request handlers
├── Models/              # Eloquent models (~30)
├── Services/            # Business logic (67 services)
├── Policies/            # Authorization
└── Events/Listeners/    # Event handling

resources/views/
├── components/          # Reusable Blade components
├── layouts/             # App layouts
└── [module]/            # Module-specific views

config/
├── erp.php             # Custom ERP configuration
├── smartbill.php       # SmartBill API config
└── services.php        # Third-party services
```

## Key Modules

| Module | Description |
|--------|-------------|
| Clients | Client management with CUI auto-fill |
| Contracts | Document management with versioning |
| Offers | Quote generation with PDF export |
| Financial | Revenue/expense tracking, SmartBill integration |
| Credentials | Secure password management |

## Configuration

Key environment variables:

```env
# Application
APP_URL=https://your-domain.com
APP_LOCALE=ro

# Database
DB_CONNECTION=mysql
DB_DATABASE=laravel_erp

# Cache & Sessions
CACHE_STORE=redis
SESSION_DRIVER=redis

# Integrations
SMARTBILL_USERNAME=
SMARTBILL_TOKEN=
```

## Development

```bash
# Run tests
php artisan test

# Code formatting
./vendor/bin/pint

# Static analysis
./vendor/bin/phpstan analyse

# Watch assets
npm run dev
```

## Quality Checks

```bash
# Run all quality checks
composer quality

# Individual checks
composer lint        # Check code style
composer lint:fix    # Fix code style issues
composer analyse     # Static analysis
composer test        # Run tests
```

## Deployment

```bash
composer install --no-dev --optimize-autoloader
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## License

Proprietary - All rights reserved.
