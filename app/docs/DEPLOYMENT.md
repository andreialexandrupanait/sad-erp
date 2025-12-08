# Deployment Checklist

## Pre-Deployment

### 1. Code Review
- [ ] All changes reviewed and approved
- [ ] No TODO/FIXME comments in production code
- [ ] No debug statements (dd(), dump(), var_dump())
- [ ] No hardcoded credentials or sensitive data

### 2. Environment Configuration
- [ ] `.env` file configured for production
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] Database credentials set
- [ ] Mail configuration set
- [ ] Cache driver configured (redis/memcached recommended)
- [ ] Queue driver configured
- [ ] Session driver configured

### 3. Dependencies
- [ ] Run `composer install --no-dev --optimize-autoloader`
- [ ] Run `npm install && npm run build` (if assets need compilation)

## Deployment Steps

### 1. Put Application in Maintenance Mode
```bash
php artisan down --secret="your-bypass-token"
```

### 2. Pull Latest Code
```bash
git pull origin main
```

### 3. Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

### 4. Run Database Migrations
```bash
php artisan migrate --force
```

### 5. Clear and Rebuild Caches
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan icons:cache  # If using Blade Icons
```

### 6. Restart Queue Workers
```bash
php artisan queue:restart
```

### 7. Bring Application Back Online
```bash
php artisan up
```

## Post-Deployment Verification

### 1. Smoke Tests
- [ ] Homepage loads
- [ ] Login works
- [ ] Dashboard displays data
- [ ] Create/update operations work

### 2. Monitor Logs
```bash
tail -f storage/logs/laravel.log
```

### 3. Check Queue Processing
```bash
php artisan queue:monitor
```

## Rollback Procedure

### 1. Put in Maintenance Mode
```bash
php artisan down
```

### 2. Rollback Code
```bash
git checkout <previous-commit>
```

### 3. Rollback Migrations (if needed)
```bash
php artisan migrate:rollback --step=1
```

### 4. Reinstall Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

### 5. Clear Caches
```bash
php artisan cache:clear
php artisan config:cache
php artisan route:cache
```

### 6. Bring Back Online
```bash
php artisan up
```

## Docker Deployment

### Using Docker Compose
```bash
# Pull latest images
docker compose pull

# Stop and recreate containers
docker compose down
docker compose up -d

# Run migrations inside container
docker compose exec app php artisan migrate --force

# Clear caches
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

## Security Checklist

- [ ] HTTPS configured and enforced
- [ ] CSRF protection enabled
- [ ] XSS protection headers set
- [ ] SQL injection prevention (use Eloquent/Query Builder)
- [ ] File upload validation configured
- [ ] Rate limiting configured
- [ ] Sensitive routes protected with authentication
- [ ] API routes protected with Sanctum/authentication
- [ ] Audit logging enabled

## Performance Checklist

- [ ] Database indexes created (run pending migrations)
- [ ] Query caching enabled
- [ ] Asset compilation optimized
- [ ] Opcache enabled
- [ ] Redis/Memcached configured for cache
- [ ] Queue workers running for background jobs

## Monitoring

### Health Check Endpoint
```
GET /up
```

### Log Files
- Application: `storage/logs/laravel.log`
- Audit: `storage/logs/audit-*.log`

### Key Metrics to Monitor
- Response times
- Error rates
- Queue job failures
- Database query times
- Memory usage
