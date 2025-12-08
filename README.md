# Laravel ERP - Enterprise Resource Planning System

> A comprehensive ERP system for managing clients, subscriptions, domains, financials, and business operations.

[![Laravel](https://img.shields.io/badge/Laravel-12.0-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

[Live Demo](https://intern.simplead.ro) | [Technical Audit](TECHNICAL_AUDIT.md) | [Report Bug](https://github.com/andreialexandrupanait/sad-erp/issues)

---

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Architecture](#architecture)
- [Installation](#installation)
- [Configuration](#configuration)
- [Deployment](#deployment)
- [Development](#development)
- [Security](#security)
- [Contributing](#contributing)
- [License](#license)
- [Credits](#credits)

---

## 🎯 Overview

Laravel ERP is a modern, multi-tenant enterprise resource planning system designed for agencies and service providers. It provides comprehensive tools for managing the entire business lifecycle from client onboarding to financial reporting.

**Core Capabilities:**
- **Client Management** - Track clients, contacts, and business relationships
- **Financial Operations** - Revenue/expense tracking with SmartBill integration
- **Subscription Management** - Recurring service subscriptions with renewal alerts
- **Domain Management** - Domain registrations with expiry notifications
- **Credential Vault** - Encrypted storage for client access credentials
- **Multi-Channel Notifications** - Email, Slack, WhatsApp alerts
- **User & Permission Management** - Role-based access with module permissions
- **Banking Integration** - Transaction sync with Banca Transilvania (optional)

**Target Users:**
- Digital agencies managing multiple clients
- IT service providers
- Subscription-based businesses
- Organizations needing centralized credential management

**Production Status:** ✅ Currently deployed and operational at https://intern.simplead.ro

---

## ✨ Features

### Core Modules

#### 📊 Dashboard
- Real-time financial metrics (revenue, expenses, profit margin)
- Subscription renewal alerts
- Domain expiry warnings
- Quick action buttons
- Customizable widgets
- Month-over-month growth tracking

#### 👥 Client Management
- Complete client profiles with CRM data
- Client status tracking (active, inactive, archived)
- Bulk operations (import CSV, export, status updates)
- Revenue aggregation per client
- Linked subscriptions and domains
- Romanian tax ID (CUI/CIF) validation
- Contact person management

#### 💰 Financial Module
- Revenue & Expense tracking
- Multi-currency support (RON, EUR, USD)
- Year/Month/Category organization
- SmartBill invoice import
- Bank transaction matching (Banca Transilvania)
- File attachment system (PDF, Excel, images)
- Financial dashboard with analytics
- Profit margin calculations
- Revenue concentration metrics

#### 🔄 Subscription Management
- Recurring service subscriptions
- Automatic renewal calculations
- Status tracking (active, pending, overdue, cancelled)
- Email notifications for renewals
- Cost tracking and reporting
- Billing cycle management (monthly, quarterly, yearly)
- Subscription history logging

#### 🌐 Domain Management
- Domain registration tracking
- Registrar management
- Expiry date monitoring (30-day alerts)
- Renewal cost tracking
- Domain status (active, expired, pending transfer)
- Transfer code management

#### 🔐 Credential Vault
- Encrypted password storage (AES-256-CBC)
- Platform/service categorization
- Access tracking and logging
- Rate-limited password reveal (3 requests/minute)
- Bulk credential management
- Auto-masked password display

#### 🔔 Notification System
- Multi-channel delivery (Email, Slack, WhatsApp)
- Event-driven notifications
- Configurable per organization
- Notification log with delivery tracking
- Templates for all notification types
- Scheduled notifications for renewals

#### 👤 User Management
- Role-based access control (superadmin, admin, user)
- Module-level permissions (5 actions per module: view, create, update, delete, export)
- Two-factor authentication (Google Authenticator)
- Session management
- Activity audit logging
- Last login tracking

### Integrations

- **SmartBill API** - Romanian invoicing system synchronization
- **Banca Transilvania** - PSD2 banking API for transaction sync (optional)
- **Slack** - Webhook notifications for team collaboration
- **WhatsApp** - API notifications for critical alerts
- **ClickUp** - Project management (legacy, can be removed)

---

## 🛠️ Tech Stack

### Backend
- **Framework:** Laravel 12.0
- **PHP:** 8.2+
- **Database:** MySQL 8.0
- **Cache/Queue:** Redis 7-alpine
- **Authentication:** Laravel Breeze + Custom 2FA

### Frontend
- **CSS Framework:** Tailwind CSS 3.1
- **JavaScript:** Alpine.js 3.4.2
- **Build Tool:** Vite 7.0.7
- **HTTP Client:** Axios 1.11.0

### DevOps & Infrastructure
- **Containerization:** Docker Compose
- **Web Server:** Nginx with SSL (Let's Encrypt)
- **Reverse Proxy:** nginx-proxy
- **SSL Management:** letsencrypt-nginx-proxy-companion

### Key Packages
- **maatwebsite/excel** ^3.1 - Excel import/export
- **consoletvs/charts** 6.* - Data visualization
- **pragmarx/google2fa** ^2.3 - Two-factor authentication
- **spatie/eloquent-sortable** ^4.5 - Model ordering
- **smalot/pdfparser** ^2.12 - PDF parsing

---

## 🏗️ Architecture

### Directory Structure
```
/var/www/erp/
├── app/                          # Laravel application
│   ├── Console/Commands/         # 18 Artisan commands
│   ├── Events/                   # 7 Domain events
│   ├── Http/
│   │   ├── Controllers/          # 37 Controllers
│   │   ├── Middleware/           # 5 Custom middleware
│   │   └── Requests/             # 24 Form validation classes
│   ├── Jobs/                     # 3 Queued jobs
│   ├── Models/                   # 24 Eloquent models
│   ├── Observers/                # 6 Model observers
│   ├── Policies/                 # 7 Authorization policies
│   ├── Services/                 # 28 Service classes
│   └── Traits/                   # Reusable code traits
├── database/
│   ├── migrations/               # 84 Database migrations
│   └── seeders/                  # 22 Data seeders
├── docker/                       # Docker configurations
├── resources/
│   ├── js/                       # Alpine.js components
│   ├── css/                      # Tailwind styles
│   └── views/                    # 182 Blade templates
├── docker-compose.yml
├── backup_database.sh
└── restore_database.sh
```

### Service Layer Architecture

The application implements a robust service layer pattern organizing business logic into focused classes:

**Financial Services:**
- `RevenueImportService` - CSV/Excel revenue import
- `ExpenseImportService` - Expense data import
- `FinancialDashboardService` - Metrics and analytics

**Banking Services:**
- `TransactionImportService` - Bank statement sync
- `TransactionMatchingService` - Auto-match transactions
- `BancaTransilvaniaService` - PSD2 API integration

**Notification Services:**
- `NotificationService` - Multi-channel dispatcher
- Channel abstraction (Email, Slack, WhatsApp)
- Event-driven message formatting

**Domain Services:**
- `CredentialService` - Credential management
- `DomainService` - Domain operations
- `SubscriptionService` - Subscription processing

### Event-Driven Architecture

Events trigger multi-channel notifications:
```
Domain Expiring Soon → DomainExpiringSoon Event
                     → SendDomainExpiryNotification Listener
                     → NotificationService
                     → [Email Channel + Slack Channel]
                     → Multi-channel delivery

Subscription Overdue → SubscriptionOverdue Event
                     → SendSubscriptionNotification Listener
                     → Email delivery
```

### Multi-Tenancy

Organization-scoped data isolation at three levels:
1. **Global Query Scopes** - Automatic filtering by `organization_id`
2. **Middleware** - `EnsureOrganizationScope` enforces organization membership
3. **Policies** - Authorization checks organization ownership

### Data Model Summary

**Core Entities:**
- Organization (tenant root)
- User (authentication + permissions)
- Client (business customers)
- Subscription (recurring services)
- Domain (domain registrations)
- FinancialRevenue & FinancialExpense
- Credential (encrypted passwords)

**Configuration:**
- SettingOption (dynamic dropdowns)
- ApplicationSetting (key-value store)
- Module (feature flags)
- UserModulePermission (granular access)

---

## 🚀 Installation

### Prerequisites

- Docker 20.10+ and Docker Compose 2.0+
- Git
- Minimum 2GB RAM
- 10GB disk space

### Quick Start (Docker)

1. **Clone the repository**
```bash
git clone https://github.com/andreialexandrupanait/sad-erp.git
cd sad-erp
```

2. **Set up environment variables**
```bash
# Copy environment files
cp .env.example .env
cp app/.env.example app/.env

# Generate secure database passwords (32+ characters)
openssl rand -base64 32  # Use for DB_ROOT_PASSWORD
openssl rand -base64 32  # Use for DB_PASSWORD

# Edit .env files with your values
nano .env         # Set DB passwords
nano app/.env     # Set DB password + APP_URL
```

**Important .env configuration:**
```env
# Root .env (Docker)
DB_ROOT_PASSWORD=<your_secure_32_char_password>
DB_PASSWORD=<your_secure_32_char_password>

# app/.env (Laravel)
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=erp_db
DB_PORT=3306
DB_DATABASE=laravel_erp
DB_USERNAME=laravel_user
DB_PASSWORD=<same_as_docker_DB_PASSWORD>

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_HOST=erp_redis
```

3. **Generate application key**
```bash
docker compose run --rm erp_app php artisan key:generate
```

4. **Start Docker containers**
```bash
docker compose up -d
```

5. **Run database migrations**
```bash
docker compose exec erp_app php artisan migrate --seed
```

6. **Create admin user**
```bash
docker compose exec erp_app php artisan tinker

# In tinker, run:
User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => Hash::make('your-secure-password'),
    'role' => 'superadmin',
    'organization_id' => 1,
]);
```

7. **Access the application**
```
http://localhost:8085
# or
https://your-domain.com
```

---

## ⚙️ Configuration

### Environment Variables

#### Required Variables
```env
# Application
APP_NAME="Laravel ERP"
APP_ENV=production                  # MUST be 'production' in production
APP_KEY=base64:...                  # Generated by artisan key:generate
APP_DEBUG=false                     # MUST be false in production
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=erp_db
DB_PORT=3306
DB_DATABASE=laravel_erp
DB_USERNAME=laravel_user
DB_PASSWORD=<your_secure_password>  # CHANGE THIS

# Redis
REDIS_HOST=erp_redis
REDIS_PORT=6379
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Logging
LOG_LEVEL=error                     # error|warning|info|debug
```

#### Optional Integrations
```env
# SmartBill (Romanian invoicing)
SMARTBILL_USERNAME=<your_username>
SMARTBILL_TOKEN=<your_api_token>
SMARTBILL_TAX_ID=<your_tax_id>

# Slack Notifications
SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL

# Email Notifications
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=<your_email>
MAIL_PASSWORD=<your_password>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com

# Banking (disabled by default)
# BT_SANDBOX_MODE=true
# BT_CLIENT_ID=<your_client_id>
# BT_CLIENT_SECRET=<your_client_secret>
```

### Application Settings

Configure via Settings UI after login (`/settings`):
- Client statuses
- Subscription statuses
- Domain registrars
- Expense categories
- Payment methods
- Currency options
- Billing cycles

### Module Permissions

Enable/disable modules per user role:
- Dashboard
- Clients
- Subscriptions
- Domains
- Credentials
- Finance
- Settings

---

## 🌍 Deployment

### Production Deployment (Docker)

1. **Set up server with nginx-proxy**
```bash
# Install Docker
curl -fsSL https://get.docker.com | sh

# Create proxy network
docker network create nginx-proxy

# Start nginx-proxy
docker run -d -p 80:80 -p 443:443 \
  --name nginx-proxy \
  --net nginx-proxy \
  -v /var/run/docker.sock:/tmp/docker.sock:ro \
  nginxproxy/nginx-proxy

# Start Let's Encrypt companion
docker run -d \
  --name nginx-proxy-letsencrypt \
  --volumes-from nginx-proxy \
  -v /var/run/docker.sock:/var/run/docker.sock:ro \
  nginxproxy/acme-companion
```

2. **Configure production environment**
```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error
```

3. **Deploy application**
```bash
docker compose up -d
docker compose exec erp_app php artisan migrate --force
docker compose exec erp_app php artisan config:cache
docker compose exec erp_app php artisan route:cache
docker compose exec erp_app php artisan view:cache
docker compose exec erp_app php artisan storage:link
```

4. **Verify automated backups**
```bash
# Backups run automatically via Laravel Scheduler:
# - Daily: 02:00 AM
# - Weekly: Sunday 03:00 AM (includes files)
# - Monthly: 1st of month 04:00 AM (includes files)

# Check backup schedule
docker exec erp_app php artisan schedule:list | grep backup

# Create manual backup
docker exec erp_app php artisan backup:database --type=manual --compress

# Restore from latest backup
./restore_latest_backup.sh
```

**📖 For complete backup/restore documentation, see:** [BACKUP_RESTORE_GUIDE.md](BACKUP_RESTORE_GUIDE.md)

**See:** [TECHNICAL_AUDIT.md](TECHNICAL_AUDIT.md) for deployment best practices

---

## 👨‍💻 Development

### Development Workflow

1. **Create feature branch**
```bash
git checkout -b feature/your-feature-name
```

2. **Make changes and test**
```bash
# Run tests
docker compose exec erp_app php artisan test

# Check code style
docker compose exec erp_app ./vendor/bin/phpstan analyse
```

3. **Commit changes**
```bash
git add .
git commit -m "feat: your feature description"
```

4. **Push and create PR**
```bash
git push origin feature/your-feature-name
```

### Coding Standards

- **PSR-12** coding style
- **Type hints** on all methods
- **DocBlocks** for complex logic
- **Single Responsibility Principle**
- **Service layer** for business logic

### Artisan Commands

```bash
# Database backups
php artisan backup:database

# SmartBill import
php artisan smartbill:import

# Client import from CSV
php artisan clients:import clients.csv

# Check expiring domains
php artisan domains:check-expiring

# Check renewing subscriptions
php artisan subscriptions:check-renewing

# Sync bank transactions
php artisan banking:sync-transactions

# Test notifications
php artisan notification:test email
```

---

## 🔒 Security

### Security Features

- ✅ **Authentication:** Laravel Breeze + 2FA (Google Authenticator)
- ✅ **Authorization:** Role-based + Module-level permissions
- ✅ **Password Encryption:** AES-256-CBC for credential vault
- ✅ **CSRF Protection:** Enabled on all forms
- ✅ **XSS Prevention:** Input sanitization + Blade escaping
- ✅ **SQL Injection:** Parameterized queries (Eloquent ORM)
- ✅ **Mass Assignment:** Explicit `$fillable` on all models
- ✅ **Rate Limiting:** Sensitive operations (password reveal: 3/min)
- ✅ **Audit Logging:** All state-changing operations logged
- ✅ **Session Security:** Regeneration on login/logout
- ✅ **SSL/TLS:** Let's Encrypt certificates
- ✅ **Security Headers:** CSP, HSTS, X-Frame-Options

### Reporting Vulnerabilities

Please report security vulnerabilities to: **security@simplead.ro**

**Do not** create public GitHub issues for security vulnerabilities.

---

## 🤝 Contributing

We welcome contributions! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Pull Request Guidelines

- Follow PSR-12 coding standards
- Add tests for new features
- Update documentation
- Ensure all tests pass
- Keep commits atomic and well-described

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🙏 Credits

### Development Team
- **Andrei Alexandru Panait** - Lead Developer (andrei.panait@simplead.ro)
- **SimpleAd Agency** - Product Owner

### Key Dependencies
- [Laravel Framework](https://laravel.com) - Web application framework
- [Tailwind CSS](https://tailwindcss.com) - CSS framework
- [Alpine.js](https://alpinejs.dev) - JavaScript framework
- [Maatwebsite Excel](https://github.com/SpartnerNL/Laravel-Excel) - Excel integration
- [Charts](https://github.com/ConsoleTVs/Charts) - Data visualization

### Special Thanks
- Laravel community for excellent documentation
- All open-source contributors

---

## 📞 Support

- **Documentation:** [Technical Audit](TECHNICAL_AUDIT.md)
- **Issues:** [GitHub Issues](https://github.com/andreialexandrupanait/sad-erp/issues)
- **Email:** andrei.panait@simplead.ro

---

## 📊 Project Statistics

- **24** Eloquent Models
- **37** Controllers
- **28** Service Classes
- **84** Database Migrations
- **182** Blade Templates
- **75** Reusable Components
- **7** Domain Events
- **6** Model Observers
- **7** Authorization Policies

---

**Built with ❤️ using Laravel**

**Production URL:** https://intern.simplead.ro
