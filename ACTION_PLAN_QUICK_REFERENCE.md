# ERP APPLICATION - ACTION PLAN QUICK REFERENCE
**Last Updated:** November 14, 2025

## 🚨 CRITICAL ISSUES - FIX IMMEDIATELY (This Week)

### 1. Authorization Policies (5 days)
```bash
# Create policies for all models
php artisan make:policy ClientPolicy --model=Client
php artisan make:policy SubscriptionPolicy --model=Subscription
php artisan make:policy DomainPolicy --model=Domain
php artisan make:policy InternalAccountPolicy --model=InternalAccount
php artisan make:policy FinancialRevenuePolicy --model=FinancialRevenue
php artisan make:policy FinancialExpensePolicy --model=FinancialExpense
```

**Then update controllers:**
```php
// Add to each controller method
$this->authorize('view', $client);
$this->authorize('update', $client);
$this->authorize('delete', $client);
```

### 2. Remove Hardcoded Secrets (2 hours)
**Edit `docker-compose.yml`:**
```yaml
# BEFORE (BAD):
MYSQL_ROOT_PASSWORD: root_password_123
MYSQL_PASSWORD: erp_password_456

# AFTER (GOOD):
MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
MYSQL_PASSWORD: ${DB_PASSWORD}
```

**Add to `.env`:**
```bash
DB_ROOT_PASSWORD=your_secure_root_password
DB_PASSWORD=your_secure_user_password
```

### 3. Implement Audit Logging (3 days)
**Edit `app/Http/Middleware/AuditLogger.php`:**
```php
public function handle($request, Closure $next)
{
    $response = $next($request);

    // Log only important actions
    if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
        AuditLog::create([
            'user_id' => auth()->id(),
            'organization_id' => auth()->user()->organization_id,
            'action' => $request->method() . ' ' . $request->path(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'changes' => json_encode($request->except(['_token', 'password'])),
        ]);
    }

    return $response;
}
```

### 4. Add Rate Limiting (1 day)
**Edit `routes/web.php`:**
```php
// Add to login/register routes
Route::middleware(['throttle:5,1'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

// Add to API routes (if any)
Route::middleware(['throttle:60,1'])->group(function () {
    // API routes here
});
```

### 5. Add Missing Database Indexes (1 day)
```bash
php artisan make:migration add_search_indexes_to_tables
```

```php
// In migration
public function up()
{
    Schema::table('clients', function (Blueprint $table) {
        $table->index('email');
        $table->index('company_name');
    });

    Schema::table('domains', function (Blueprint $table) {
        $table->index('domain_name');
    });

    Schema::table('subscriptions', function (Blueprint $table) {
        $table->index('vendor_name');
    });
}
```

---

## ⚡ HIGH PRIORITY - Next 2 Weeks

### 6. Implement Redis Caching (3 days)

**Update `docker-compose.yml`:**
```yaml
redis:
  image: redis:7-alpine
  ports:
    - "6379:6379"
  volumes:
    - redis_data:/data
```

**Update `.env`:**
```bash
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=redis
```

**Cache settings:**
```php
// In SettingsController or helper
public static function getCachedOptions($category)
{
    return Cache::remember("settings_{$category}", 3600, function() use ($category) {
        return SettingOption::where('category', $category)->get();
    });
}
```

### 7. Cache Financial Dashboard (2 days)
```php
// In DashboardController
public function index()
{
    $stats = Cache::remember('dashboard_stats_' . auth()->id(), 300, function() {
        return [
            'monthly_revenue' => $this->calculateMonthlyRevenue(),
            'monthly_expenses' => $this->calculateMonthlyExpenses(),
            // ... other stats
        ];
    });
}
```

### 8. Fix N+1 Queries (5 days)
```php
// Install Laravel Debugbar (development only)
composer require barryvdh/laravel-debugbar --dev

// Add eager loading to index methods
$clients = Client::with(['status', 'revenues', 'domains'])->paginate(15);
$subscriptions = Subscription::with('user')->paginate(15);
$domains = Domain::with(['client', 'status'])->paginate(15);
```

### 9. Implement Queue System (1 week)
```bash
# Update .env
QUEUE_CONNECTION=redis

# Create job for CSV import
php artisan make:job ProcessCsvImport

# In ImportExportController
ProcessCsvImport::dispatch($file, $type, auth()->user());
```

---

## 🧪 TESTING - Weeks 3-4

### 10. Write Feature Tests (1 week)

```bash
# Create feature tests
php artisan make:test ClientControllerTest
php artisan make:test SubscriptionControllerTest
php artisan make:test DomainControllerTest
php artisan make:test FinancialControllerTest
```

**Example test:**
```php
public function test_user_can_create_client()
{
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/clients', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'company_name' => 'ACME Inc',
        'tax_id' => '12345678',
        'status_id' => 1,
    ]);

    $response->assertRedirect('/clients');
    $this->assertDatabaseHas('clients', [
        'email' => 'john@example.com',
        'user_id' => $user->id,
    ]);
}
```

### 11. Set Up CI/CD (2 days)

**Create `.github/workflows/tests.yml`:**
```yaml
name: Tests

on: [push, pull_request]

jobs:
  tests:
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_DATABASE: testing
          MYSQL_ROOT_PASSWORD: password

    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2

      - name: Install dependencies
        run: composer install

      - name: Run tests
        run: php artisan test --coverage
```

---

## 🎯 FEATURE COMPLETION - Weeks 5-6

### 12. Complete Credentials Module (3 days)
```bash
# Create controller
php artisan make:controller CredentialController --resource

# Add routes
Route::resource('credentials', CredentialController::class);

# Create views (copy from clients module structure)
```

### 13. Build Contracts UI (1 week)
```bash
php artisan make:controller ContractController --resource
php artisan make:controller AnnexController --resource

# Add routes
Route::resource('contracts', ContractController::class);
Route::resource('contracts.annexes', AnnexController::class);
```

### 14. Email Notifications (1 week)
```bash
# Create notification classes
php artisan make:notification SubscriptionRenewalDue
php artisan make:notification DomainExpiryWarning

# Configure mail in .env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io  # or your SMTP server
MAIL_PORT=587
```

---

## 🔒 SECURITY HARDENING - Week 7

### 15. Implement 2FA (1 week)
```bash
composer require pragmarx/google2fa-laravel

php artisan vendor:publish --provider="PragmaRX\Google2FALaravel\ServiceProvider"
```

### 16. Add Password Complexity (1 day)
```php
// In RegisterController validation
'password' => [
    'required',
    'string',
    'min:12',
    'regex:/[a-z]/',      // lowercase
    'regex:/[A-Z]/',      // uppercase
    'regex:/[0-9]/',      // numbers
    'regex:/[@$!%*#?&]/', // special chars
    'confirmed'
],
```

### 17. Run Security Audit (3 days)
```bash
# Install Enlightn
composer require --dev enlightn/enlightn

# Run security audit
php artisan enlightn

# Check dependencies
composer audit

# Install and run PHPStan
composer require --dev phpstan/phpstan
./vendor/bin/phpstan analyse app
```

---

## 📊 MONITORING - Week 8

### 18. Install Laravel Telescope (Development)
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

### 19. Set Up Error Tracking (Production)
```bash
# Install Sentry
composer require sentry/sentry-laravel

# Configure .env
SENTRY_LARAVEL_DSN=your-dsn-here
```

### 20. Configure Backups
```bash
# Install Spatie backup
composer require spatie/laravel-backup

# Configure schedule in app/Console/Kernel.php
$schedule->command('backup:clean')->daily()->at('01:00');
$schedule->command('backup:run')->daily()->at('02:00');
```

---

## 🔧 QUICK FIXES (Do Anytime)

### Update MySQL to 8.0
```yaml
# docker-compose.yml
erp_db:
  image: mysql:8.0  # Change from 5.7
```

### Fix TODO in AppServiceProvider
```php
// app/Providers/AppServiceProvider.php line 26
// Update observer to clear cache when needed
SettingOption::updated(function($option) {
    Cache::forget("settings_{$option->category}");
});
```

### Standardize Scoping
**Decision needed:** Choose ONE strategy:
- Option A: All models scope by `organization_id` (recommended)
- Option B: All models scope by `user_id`
- Option C: Financial by user, others by organization (current, inconsistent)

---

## 📋 PROGRESS TRACKING

### Weekly Checklist

**Week 1: Critical Security**
- [ ] Authorization policies created for all models
- [ ] Policies registered in AuthServiceProvider
- [ ] Controllers updated with authorization checks
- [ ] Hardcoded credentials removed from repo
- [ ] AuditLogger middleware implemented
- [ ] CheckRole middleware implemented
- [ ] Rate limiting added to auth routes
- [ ] Database indexes added

**Week 2: Performance**
- [ ] Redis installed and configured
- [ ] Settings cached
- [ ] Dashboard cached
- [ ] N+1 queries identified and fixed
- [ ] Queue system configured
- [ ] CSV imports moved to queues

**Week 3: Testing**
- [ ] Feature tests written (all CRUD)
- [ ] Unit tests for business logic
- [ ] CI/CD pipeline configured
- [ ] Code coverage >70%

**Week 4: Features**
- [ ] Credentials controller implemented
- [ ] Contracts UI built
- [ ] Email notifications working
- [ ] Import/Export complete for all modules

**Week 5: Hardening**
- [ ] 2FA implemented
- [ ] Password complexity enforced
- [ ] Security audit completed
- [ ] Load testing passed
- [ ] Monitoring configured

---

## 🎯 SUCCESS CRITERIA

Before deploying to production, ALL of these must be ✅:

- [ ] No critical security vulnerabilities (Enlightn scan)
- [ ] All controllers have authorization
- [ ] Audit logging captures all changes
- [ ] Test coverage >70%
- [ ] Dashboard loads <500ms
- [ ] No N+1 queries detected
- [ ] All secrets in environment variables
- [ ] 2FA available for users
- [ ] Automated backups configured
- [ ] Monitoring and alerts active
- [ ] Load test: 100 concurrent users
- [ ] Security audit passed
- [ ] Staging environment tested
- [ ] Runbook documented

---

## 📞 NEED HELP?

### Common Commands

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Run tests
php artisan test
php artisan test --coverage

# Run security audit
php artisan enlightn
composer audit

# Check for N+1 queries
# Install: composer require barryvdh/laravel-debugbar --dev
# Visit any page, check "Queries" tab

# Database
php artisan migrate
php artisan db:seed
php artisan migrate:fresh --seed

# Queue workers
php artisan queue:work
php artisan queue:listen

# Monitoring
php artisan telescope:prune
```

### Resources

- **Laravel Docs:** https://laravel.com/docs/12.x
- **Security Best Practices:** https://laravel.com/docs/12.x/security
- **Testing Guide:** https://laravel.com/docs/12.x/testing
- **Performance:** https://laravel.com/docs/12.x/optimization
- **OWASP Top 10:** https://owasp.org/www-project-top-ten/

---

**Remember:** Security first, features second. Don't skip the critical fixes!
