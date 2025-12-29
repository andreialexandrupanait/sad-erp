# Final Production Deployment Checklist

## Overview

This is the **FINAL CHECKLIST** before deploying to production. Complete all items in order.

**Estimated Time**: 30-45 minutes
**Prerequisites**: All Week 1-4 tasks completed

---

## Pre-Deployment Verification

### 1. Code & Configuration ✓

- [ ] All changes committed to git
- [ ] Production branch created and tested
- [ ] `.env.production.example` reviewed
- [ ] All sensitive data removed from code
- [ ] No debug code or console.logs remaining

### 2. Environment Setup ✓

- [ ] Production server meets requirements (4GB RAM, 2 CPU, 50GB disk)
- [ ] Docker and Docker Compose installed
- [ ] Domain name configured and DNS propagated
- [ ] Firewall rules configured (ports 80, 443, 22)
- [ ] SSL certificate ready or Let's Encrypt configured

### 3. Database ✓

- [ ] Database backup tested and verified
- [ ] Restore procedure tested successfully
- [ ] Automated backup cron job configured
- [ ] Slow query logging enabled
- [ ] Production database credentials secured (16+ characters)

---

## Deployment Steps

### Step 1: Server Preparation (15 minutes)

```bash
# 1.1 Update system
sudo apt update && sudo apt upgrade -y

# 1.2 Install required packages
sudo apt install -y docker.io docker-compose git certbot nginx

# 1.3 Configure firewall
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable

# 1.4 Clone repository
cd /var/www
sudo git clone https://github.com/your-org/sad-erp.git erp
cd erp
sudo chown -R $USER:$USER .
```

**Verification**:
- [ ] All packages installed without errors
- [ ] Firewall active and configured
- [ ] Repository cloned successfully

### Step 2: Environment Configuration (10 minutes)

```bash
# 2.1 Copy production environment
cp .env.production.example .env

# 2.2 Edit configuration
nano .env
```

**Critical Settings to Configure**:

```env
# Application
APP_NAME="Your Company ERP"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_KEY=                    # Generate after container start

# Database
DB_PASSWORD=                # STRONG PASSWORD (16+ chars)
DB_ROOT_PASSWORD=           # STRONG PASSWORD (16+ chars)

# Redis
REDIS_PASSWORD=             # STRONG PASSWORD (16+ chars)

# Mail
MAIL_HOST=smtp.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=              # Your mail password
MAIL_FROM_ADDRESS=noreply@yourdomain.com

# Security
SESSION_SECURE_COOKIE=true
CSP_ENFORCE=false           # Keep false initially
```

**Verification**:
- [ ] All passwords are strong (16+ characters, mixed case, numbers, symbols)
- [ ] Mail settings configured and tested
- [ ] APP_URL matches your domain with https://
- [ ] SESSION_SECURE_COOKIE=true

### Step 3: Container Deployment (5 minutes)

```bash
# 3.1 Start containers
docker compose up -d

# 3.2 Generate application key
docker exec erp_app php artisan key:generate

# 3.3 Verify all containers running
docker compose ps
```

**Verification**:
- [ ] All containers show "Up" status
- [ ] Health checks passing (if configured)
- [ ] APP_KEY generated in .env

### Step 4: Database Setup (5 minutes)

```bash
# 4.1 Run migrations
docker exec erp_app php artisan migrate --force

# 4.2 Create initial organization and admin user
docker exec -it erp_app php artisan tinker
```

In tinker:
```php
$org = \App\Models\Organization::create([
    'name' => 'Your Company Name'
]);

$user = \App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@yourdomain.com',
    'password' => bcrypt('CHANGE_THIS_STRONG_PASSWORD'),
    'organization_id' => $org->id,
    'role' => 'superadmin'
]);

exit
```

**Verification**:
- [ ] Migrations completed successfully
- [ ] Organization created
- [ ] Admin user created
- [ ] Admin password is strong and saved securely

### Step 5: SSL/TLS Setup (10 minutes)

```bash
# 5.1 Stop nginx (if running)
sudo systemctl stop nginx

# 5.2 Obtain SSL certificate
sudo certbot certonly --standalone \
  -d yourdomain.com \
  -d www.yourdomain.com \
  --agree-tos \
  --email admin@yourdomain.com

# 5.3 Create nginx configuration
sudo nano /etc/nginx/sites-available/erp
```

Paste the configuration from [PRODUCTION_DEPLOYMENT_GUIDE.md](PRODUCTION_DEPLOYMENT_GUIDE.md#ssltls-setup).

```bash
# 5.4 Enable site
sudo ln -s /etc/nginx/sites-available/erp /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl start nginx

# 5.5 Set up auto-renewal
sudo systemctl enable certbot.timer
sudo systemctl start certbot.timer
```

**Verification**:
- [ ] SSL certificate obtained successfully
- [ ] Nginx configuration valid (`nginx -t` passes)
- [ ] Nginx running and serving HTTPS
- [ ] Auto-renewal enabled

### Step 6: Performance Optimization (5 minutes)

```bash
# 6.1 Cache Laravel configuration
docker exec erp_app php artisan config:cache
docker exec erp_app php artisan route:cache
docker exec erp_app php artisan view:cache
docker exec erp_app php artisan event:cache

# 6.2 Optimize Composer
docker exec erp_app composer install --optimize-autoloader --no-dev

# 6.3 Switch to production PHP config (if not done)
# Edit docker-compose.yml to use php.production.ini
# Then restart:
docker compose restart erp_app
```

**Verification**:
- [ ] All caches created successfully
- [ ] Composer optimized for production
- [ ] Production PHP config active

### Step 7: Backup Configuration (5 minutes)

```bash
# 7.1 Test backup
./backup_database.sh production_test_backup

# 7.2 Set up automated backups
./cron-backup-schedule.sh

# 7.3 Verify cron job
crontab -l | grep backup
```

**Verification**:
- [ ] Test backup created successfully
- [ ] Automated backup scheduled (daily 2:00 AM)
- [ ] Cron job visible in crontab

---

## Post-Deployment Verification

### 8. Functional Tests (10 minutes)

**8.1 Application Access**
- [ ] Visit https://yourdomain.com
- [ ] No SSL errors or warnings
- [ ] Login page loads correctly
- [ ] Can log in with admin credentials

**8.2 Core Functionality**
- [ ] Can create a new client
- [ ] Can create a financial record
- [ ] Can upload a file
- [ ] Can generate a report
- [ ] Can send a test email (password reset)

**8.3 Security Verification**
```bash
# Check security headers
curl -I https://yourdomain.com | grep -i "strict-transport-security"
curl -I https://yourdomain.com | grep -i "x-content-type-options"
curl -I https://yourdomain.com | grep -i "x-frame-options"

# Verify no PHP version exposed
curl -I https://yourdomain.com | grep -i "x-powered-by"
# Should NOT show PHP version
```

- [ ] HSTS header present
- [ ] X-Content-Type-Options: nosniff
- [ ] X-Frame-Options: SAMEORIGIN
- [ ] PHP version NOT exposed

**8.4 Performance Check**
```bash
# Simple response time test
time curl -I https://yourdomain.com
```

- [ ] Response time < 1 second
- [ ] No 500 errors
- [ ] No 404 errors for assets

### 9. Log Verification (5 minutes)

```bash
# 9.1 Check application logs
docker exec erp_app tail -50 /var/www/html/storage/logs/laravel.log

# 9.2 Check nginx logs
sudo tail -50 /var/log/nginx/error.log

# 9.3 Check for errors
docker compose logs --tail=100 erp_app | grep -i error
```

- [ ] No critical errors in application logs
- [ ] No errors in nginx logs
- [ ] All containers logging properly

### 10. Monitoring Setup (5 minutes)

**10.1 Error Monitoring (Sentry - Optional)**
```bash
# If using Sentry:
docker exec erp_app composer require sentry/sentry-laravel
docker exec erp_app php artisan sentry:publish
```

Add to `.env`:
```env
SENTRY_LARAVEL_DSN=your-sentry-dsn
```

**10.2 Uptime Monitoring**
- [ ] Set up UptimeRobot or Pingdom
- [ ] Configure alerts to admin email
- [ ] Test alert system

### 11. Security Scan (5 minutes)

```bash
# 11.1 Run automated security audit
./security-audit.sh

# 11.2 Check for common vulnerabilities
# Visit: https://securityheaders.com/?q=yourdomain.com
# Expected: A or A+ rating
```

- [ ] Security audit passes
- [ ] Security headers rated A or better
- [ ] No critical vulnerabilities found

---

## Final Verification Checklist

### Application
- [ ] Application accessible via HTTPS
- [ ] Login system working
- [ ] All core features functional
- [ ] File uploads working
- [ ] Email sending working
- [ ] No errors in logs

### Security
- [ ] HTTPS enforced (HTTP redirects to HTTPS)
- [ ] Security headers present
- [ ] CSRF protection active
- [ ] Session security configured
- [ ] No sensitive data exposed
- [ ] Admin user password changed from default

### Performance
- [ ] All Laravel caches active
- [ ] OPcache enabled
- [ ] Redis working for cache/sessions
- [ ] Response times acceptable (< 1s)
- [ ] No slow queries (check slow query log)

### Operations
- [ ] Backups automated and tested
- [ ] Logs rotating properly
- [ ] Monitoring configured
- [ ] Error tracking active (if using Sentry)
- [ ] SSL auto-renewal configured

### Documentation
- [ ] Admin credentials documented (securely)
- [ ] Deployment notes recorded
- [ ] Rollback procedure understood
- [ ] Emergency contacts configured

---

## Go-Live Decision

**ALL CRITICAL ITEMS MUST BE CHECKED BEFORE GO-LIVE**

### Critical (Must Have)
- [ ] Application accessible and functional
- [ ] HTTPS working correctly
- [ ] Database migrated successfully
- [ ] Admin user can log in
- [ ] Backups configured and tested
- [ ] No critical errors in logs

### Important (Should Have)
- [ ] All security headers configured
- [ ] Performance optimizations applied
- [ ] Monitoring configured
- [ ] Email sending tested
- [ ] All core features working

### Nice to Have
- [ ] Error monitoring (Sentry)
- [ ] Uptime monitoring
- [ ] Advanced analytics
- [ ] Load testing completed

---

## Post-Go-Live Monitoring (First 48 Hours)

### Hour 1
- [ ] Check application is accessible
- [ ] Monitor error logs
- [ ] Verify first user logins
- [ ] Check email sending
- [ ] Monitor response times

### Hour 24
- [ ] Review error logs
- [ ] Check backup completion
- [ ] Monitor disk space
- [ ] Review slow query log
- [ ] Check for security alerts

### Hour 48
- [ ] Comprehensive log review
- [ ] Performance analysis
- [ ] User feedback review
- [ ] Security scan
- [ ] Backup restoration test

---

## Emergency Contacts

**Critical Issues**:
- [ ] Server admin contact: _______________
- [ ] Database admin contact: _______________
- [ ] Application developer: _______________
- [ ] Domain/DNS provider: _______________
- [ ] Hosting provider support: _______________

**Escalation Path**:
1. Check logs first
2. Review troubleshooting guide
3. Attempt rollback if necessary
4. Contact appropriate team member

---

## Rollback Procedure

**If Critical Issues Occur**:

```bash
# 1. Stop application
docker compose down

# 2. Restore previous code
git checkout <previous-commit>

# 3. Restore database
gunzip -c backups/database/backup_YYYYMMDD_HHMMSS.sql.gz | \
  docker exec -i erp_db mysql -u root -p laravel_erp

# 4. Restart application
docker compose up -d
docker exec erp_app php artisan config:clear
docker exec erp_app php artisan cache:clear

# 5. Verify rollback
curl -I https://yourdomain.com
```

**Document Reason for Rollback**: _______________

---

## Sign-Off

**Deployment Completed By**: _______________
**Date**: _______________
**Time**: _______________

**Verified By**: _______________
**Date**: _______________

**Issues Encountered**:
_______________
_______________

**Notes**:
_______________
_______________

---

## Success! 🎉

Your Laravel ERP application is now **LIVE IN PRODUCTION**.

**Next Steps**:
1. Monitor logs closely for first 48 hours
2. Implement high-priority security recommendations
3. Schedule first backup restoration test (within 7 days)
4. Plan for CSP enforcement migration
5. Regular security updates and maintenance

**Support Resources**:
- [PRODUCTION_DEPLOYMENT_GUIDE.md](PRODUCTION_DEPLOYMENT_GUIDE.md)
- [SECURITY_AUDIT_RESULTS.md](SECURITY_AUDIT_RESULTS.md)
- [CSP_MIGRATION_GUIDE.md](CSP_MIGRATION_GUIDE.md)
- [WEEK1_COMPLETION_SUMMARY.md](WEEK1_COMPLETION_SUMMARY.md)
- [WEEK2_DAY6-10_TESTING_SUMMARY.md](WEEK2_DAY6-10_TESTING_SUMMARY.md)
- [WEEK3_COMPLETION_SUMMARY.md](WEEK3_COMPLETION_SUMMARY.md)

---

**Version**: 1.0
**Last Updated**: December 28, 2025
**Production Readiness**: 98%
