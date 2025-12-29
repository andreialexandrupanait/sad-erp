# Production Deployment Guide

## Overview

This guide provides step-by-step instructions for deploying the Laravel ERP application to production.

**Estimated Time**: 2-3 hours
**Prerequisites**: Linux server with Docker and Docker Compose installed

---

## Table of Contents

1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [Server Setup](#server-setup)
3. [Application Deployment](#application-deployment)
4. [Database Configuration](#database-configuration)
5. [SSL/TLS Setup](#ssltls-setup)
6. [Performance Optimization](#performance-optimization)
7. [Monitoring & Logging](#monitoring--logging)
8. [Backup Configuration](#backup-configuration)
9. [Post-Deployment Verification](#post-deployment-verification)
10. [Rollback Procedure](#rollback-procedure)

---

## Pre-Deployment Checklist

### Server Requirements

- **OS**: Ubuntu 22.04 LTS or similar
- **RAM**: Minimum 4GB (8GB recommended)
- **CPU**: 2 cores minimum (4 cores recommended)
- **Disk**: 50GB SSD minimum (100GB recommended)
- **Network**: Public IP address with ports 80 and 443 accessible

### Required Software

- Docker 24.0+ (`docker --version`)
- Docker Compose 2.20+ (`docker compose version`)
- Git (`git --version`)
- Certbot (for Let's Encrypt SSL)

###  Domain Configuration

- [ ] Domain name configured and pointing to server IP
- [ ] DNS propagation complete (check with `nslookup yourdomain.com`)
- [ ] Ports 80 and 443 open in firewall

---

## Server Setup

### 1. Update System

```bash
sudo apt update && sudo apt upgrade -y
```

### 2. Install Docker

```bash
# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Add user to docker group
sudo usermod -aG docker $USER

# Verify installation
docker --version
docker compose version
```

### 3. Install Required Packages

```bash
sudo apt install -y git certbot nginx
```

### 4. Configure Firewall

```bash
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw enable
sudo ufw status
```

---

## Application Deployment

### 1. Clone Repository

```bash
cd /var/www
sudo git clone https://github.com/your-org/sad-erp.git erp
cd erp
sudo chown -R $USER:$USER .
```

### 2. Configure Environment

```bash
# Copy production environment template
cp .env.production.example .env

# Edit configuration
nano .env
```

**Critical Settings to Update**:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_KEY=base64:... # Generate with: php artisan key:generate

DB_PASSWORD=YOUR_STRONG_PASSWORD
DB_ROOT_PASSWORD=YOUR_STRONG_ROOT_PASSWORD
REDIS_PASSWORD=YOUR_STRONG_REDIS_PASSWORD

MAIL_HOST=smtp.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=YOUR_MAIL_PASSWORD

SESSION_SECURE_COOKIE=true
CSP_ENFORCE=false  # Keep false initially, enforce after testing
```

### 3. Generate Application Key

```bash
docker compose up -d erp_app
docker exec erp_app php artisan key:generate
```

### 4. Build and Start Containers

```bash
docker compose up -d
```

### 5. Verify Containers

```bash
docker compose ps
```

All containers should show "Up" and "healthy" status.

---

## Database Configuration

### 1. Run Migrations

```bash
docker exec erp_app php artisan migrate --force
```

### 2. Seed Initial Data

```bash
# Create initial organization
docker exec -it erp_app php artisan tinker

# In tinker:
$org = \App\Models\Organization::create(['name' => 'Your Company']);
$user = \App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@yourdomain.com',
    'password' => bcrypt('CHANGE_THIS_PASSWORD'),
    'organization_id' => $org->id,
    'role' => 'superadmin'
]);
exit
```

### 3. Verify Database

```bash
docker exec erp_db mysql -u root -p -e "USE laravel_erp; SHOW TABLES;"
```

### 4. Configure Slow Query Logging

```bash
# Slow query logging is configured via docker/mysql/my.cnf
# Verify it's enabled after container restart:
docker compose restart erp_db
docker exec erp_db mysql -u root -p -e "SHOW VARIABLES LIKE 'slow_query_log';"
```

---

## SSL/TLS Setup

### 1. Stop Nginx (if running)

```bash
sudo systemctl stop nginx
```

### 2. Obtain SSL Certificate

```bash
sudo certbot certonly --standalone -d yourdomain.com -d www.yourdomain.com
```

### 3. Configure Nginx for Production

Create `/etc/nginx/sites-available/erp`:

```nginx
# HTTP - Redirect to HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;

    location /.well-known/acme-challenge/ {
        root /var/www/html;
    }

    location / {
        return 301 https://$server_name$request_uri;
    }
}

# HTTPS
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

    # SSL Configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # Security Headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Proxy to Docker Nginx
    location / {
        proxy_pass http://localhost:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        # WebSocket support
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }

    # File upload size
    client_max_body_size 250M;
}
```

### 4. Enable Site and Restart Nginx

```bash
sudo ln -s /etc/nginx/sites-available/erp /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 5. Set Up Auto-Renewal

```bash
sudo systemctl enable certbot.timer
sudo systemctl start certbot.timer
```

---

## Performance Optimization

### 1. Cache Laravel Configuration

```bash
docker exec erp_app php artisan config:cache
docker exec erp_app php artisan route:cache
docker exec erp_app php artisan view:cache
docker exec erp_app php artisan event:cache
```

### 2. Optimize Composer Autoloader

```bash
docker exec erp_app composer install --optimize-autoloader --no-dev
```

### 3. Switch to Production PHP Configuration

Edit `docker-compose.yml`:

```yaml
# Change this line:
- ./docker/php/php.ini:/usr/local/etc/php/conf.d/custom.ini

# To:
- ./docker/php/php.production.ini:/usr/local/etc/php/conf.d/custom.ini
```

Restart:

```bash
docker compose restart erp_app
```

### 4. Verify OPcache

```bash
docker exec erp_app php -i | grep opcache
```

---

## Monitoring & Logging

### 1. Configure Error Monitoring (Sentry)

```bash
docker exec erp_app composer require sentry/sentry-laravel
docker exec erp_app php artisan sentry:publish
```

Update `.env`:

```env
SENTRY_LARAVEL_DSN=your-sentry-dsn-here
SENTRY_TRACES_SAMPLE_RATE=0.2
```

### 2. Log Files Locations

- **Application Logs**: `app/storage/logs/laravel.log`
- **PHP Error Logs**: `/var/log/php_errors.log`
- **Nginx Access Logs**: `/var/log/nginx/access.log`
- **Nginx Error Logs**: `/var/log/nginx/error.log`
- **MySQL Slow Queries**: Check `docker exec erp_db cat /var/log/mysql/slow-query.log`

### 3. Set Up Log Rotation

Create `/etc/logrotate.d/erp`:

```
/var/www/erp/app/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0644 www-data www-data
    sharedscripts
}
```

---

## Backup Configuration

### 1. Test Backup

```bash
cd /var/www/erp
./backup_database.sh test_production_backup
```

### 2. Set Up Automated Backups

```bash
./cron-backup-schedule.sh
```

This creates a daily backup at 2:00 AM.

### 3. Test Restoration

```bash
# Restore to test database
gunzip -c backups/database/test_production_backup.sql.gz | docker exec -i erp_db mysql -u root -p laravel_erp_test
```

### 4. Off-Site Backup Storage

```bash
# Example: Copy to AWS S3 (install AWS CLI first)
aws s3 sync backups/database/ s3://your-bucket/erp-backups/ --exclude "*" --include "*.sql.gz"
```

---

## Post-Deployment Verification

### 1. Health Checks

```bash
# Check all containers
docker compose ps

# Check application health
curl -I https://yourdomain.com

# Check database connection
docker exec erp_app php artisan tinker --execute="DB::connection()->getPdo();"
```

### 2. Functionality Tests

- [ ] Login with admin credentials
- [ ] Create a test client
- [ ] Create a test financial record
- [ ] Upload a file
- [ ] Generate a report
- [ ] Check email sending (test password reset)
- [ ] Verify organization isolation

### 3. Performance Tests

```bash
# Simple load test with Apache Bench
ab -n 1000 -c 10 https://yourdomain.com/
```

### 4. Security Verification

```bash
# Test SSL configuration
curl -I https://yourdomain.com | grep -i strict-transport

# Test CSP headers
curl -I https://yourdomain.com | grep -i content-security-policy

# Check for exposed version info
curl -I https://yourdomain.com | grep -i x-powered-by
# Should NOT show PHP version
```

### 5. Monitoring Dashboard

Check these metrics:

- Response time < 200ms average
- Memory usage < 70%
- Disk usage < 80%
- No critical errors in logs

---

## Rollback Procedure

If deployment fails, follow these steps:

### 1. Stop New Version

```bash
cd /var/www/erp
docker compose down
```

### 2. Restore Previous Code

```bash
git checkout <previous-commit-hash>
```

### 3. Restore Database

```bash
gunzip -c backups/database/backup_YYYYMMDD_HHMMSS.sql.gz | docker exec -i erp_db mysql -u root -p laravel_erp
```

### 4. Restart Application

```bash
docker compose up -d
docker exec erp_app php artisan config:clear
docker exec erp_app php artisan cache:clear
```

### 5. Verify Rollback

```bash
curl -I https://yourdomain.com
```

---

## Troubleshooting

### Application Not Loading

```bash
# Check logs
docker compose logs erp_app
docker compose logs erp_nginx

# Check nginx error log
sudo tail -f /var/log/nginx/error.log
```

### Database Connection Issues

```bash
# Check database container
docker compose logs erp_db

# Test connection
docker exec erp_db mysql -u root -p -e "SELECT 1"
```

### Permission Issues

```bash
# Fix storage permissions
docker exec erp_app chmod -R 775 storage bootstrap/cache
docker exec erp_app chown -R www-data:www-data storage bootstrap/cache
```

### SSL Certificate Issues

```bash
# Test certificate renewal
sudo certbot renew --dry-run

# Force renewal
sudo certbot renew --force-renewal
```

---

## Maintenance Tasks

### Daily

- Monitor error logs
- Check backup completion
- Monitor disk space

### Weekly

- Review slow query log
- Check for failed queue jobs
- Review security alerts

### Monthly

- Update dependencies (security patches)
- Review and optimize database indexes
- Test backup restoration
- Review user access logs

---

## Security Best Practices

1. **Never commit** `.env` to version control
2. **Use strong passwords** for all services (minimum 16 characters)
3. **Enable 2FA** for admin accounts
4. **Regular security updates**: `sudo apt update && sudo apt upgrade`
5. **Monitor failed login attempts**
6. **Restrict SSH access** (use SSH keys, disable password auth)
7. **Regular security audits**

---

## Support & Documentation

- **Application Docs**: See `README.md`
- **CSP Migration**: See `CSP_MIGRATION_GUIDE.md`
- **Testing Guide**: See `WEEK2_DAY6-10_TESTING_SUMMARY.md`
- **Week 1 Security Fixes**: See `WEEK1_COMPLETION_SUMMARY.md`

---

**Last Updated**: December 28, 2025
**Version**: 1.0
**Production Readiness**: 92%
