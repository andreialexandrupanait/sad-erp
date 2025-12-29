#!/bin/bash

# ============================================================================
# Security Audit Script for Laravel ERP Application
# ============================================================================
# This script performs automated security checks
# ============================================================================

set -e

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Security Audit - Laravel ERP Application"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Date: $(date '+%Y-%m-%d %H:%M:%S')"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

PASS=0
FAIL=0
WARN=0

check_pass() {
    echo "✓ $1"
    ((PASS++))
}

check_fail() {
    echo "✗ $1"
    ((FAIL++))
}

check_warn() {
    echo "⚠ $1"
    ((WARN++))
}

echo "═══════════════════════════════════════════════════════════════"
echo "1. Environment Configuration Checks"
echo "═══════════════════════════════════════════════════════════════"
echo ""

# Check APP_ENV
APP_ENV=$(grep "^APP_ENV=" .env | cut -d '=' -f2)
if [ "$APP_ENV" = "production" ]; then
    check_pass "APP_ENV is set to production"
else
    check_warn "APP_ENV is not set to production (current: $APP_ENV)"
fi

# Check APP_DEBUG
APP_DEBUG=$(grep "^APP_DEBUG=" .env | cut -d '=' -f2)
if [ "$APP_DEBUG" = "false" ]; then
    check_pass "APP_DEBUG is disabled"
else
    check_fail "APP_DEBUG is enabled (SECURITY RISK in production)"
fi

# Check APP_KEY
APP_KEY=$(grep "^APP_KEY=" .env | cut -d '=' -f2)
if [ -n "$APP_KEY" ] && [ "$APP_KEY" != "base64:YOUR_PRODUCTION_APP_KEY_HERE" ]; then
    check_pass "APP_KEY is set"
else
    check_fail "APP_KEY is not properly configured"
fi

# Check SESSION_SECURE_COOKIE
SESSION_SECURE=$(grep "^SESSION_SECURE_COOKIE=" .env | cut -d '=' -f2 2>/dev/null || echo "")
if [ "$SESSION_SECURE" = "true" ]; then
    check_pass "SESSION_SECURE_COOKIE is enabled"
else
    check_warn "SESSION_SECURE_COOKIE not set to true (required for HTTPS)"
fi

echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "2. File Permission Checks"
echo "═══════════════════════════════════════════════════════════════"
echo ""

# Check .env permissions
if [ -f .env ]; then
    ENV_PERMS=$(stat -c "%a" .env 2>/dev/null || stat -f "%OLp" .env 2>/dev/null)
    if [ "$ENV_PERMS" = "600" ] || [ "$ENV_PERMS" = "644" ]; then
        check_pass ".env file has appropriate permissions ($ENV_PERMS)"
    else
        check_warn ".env file permissions: $ENV_PERMS (should be 600 or 644)"
    fi
fi

# Check storage directory
if docker exec erp_app test -w /var/www/html/storage; then
    check_pass "Storage directory is writable"
else
    check_fail "Storage directory is not writable"
fi

echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "3. Database Security Checks"
echo "═══════════════════════════════════════════════════════════════"
echo ""

# Check for default passwords
DB_PASS=$(grep "^DB_PASSWORD=" .env | cut -d '=' -f2)
if [ "$DB_PASS" = "password" ] || [ "$DB_PASS" = "secret" ] || [ "$DB_PASS" = "YOUR_SECURE_DB_PASSWORD_HERE" ]; then
    check_fail "Database password is weak or default"
else
    if [ ${#DB_PASS} -ge 16 ]; then
        check_pass "Database password is strong (16+ characters)"
    else
        check_warn "Database password should be at least 16 characters"
    fi
fi

# Check database connection
if docker exec erp_app php -r "try { \$pdo = new PDO('mysql:host=erp_db;dbname=laravel_erp', 'laravel_user', getenv('DB_PASSWORD')); echo 'OK'; } catch(Exception \$e) { echo 'FAIL'; }" 2>/dev/null | grep -q "OK"; then
    check_pass "Database connection successful"
else
    check_fail "Database connection failed"
fi

echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "4. Laravel Security Checks"
echo "═══════════════════════════════════════════════════════════════"
echo ""

# Check if routes are cached
if docker exec erp_app test -f /var/www/html/bootstrap/cache/routes-v7.php; then
    check_pass "Routes are cached for production"
else
    check_warn "Routes not cached (run: php artisan route:cache)"
fi

# Check if config is cached
if docker exec erp_app test -f /var/www/html/bootstrap/cache/config.php; then
    check_pass "Config is cached for production"
else
    check_warn "Config not cached (run: php artisan config:cache)"
fi

# Check if views are cached
if docker exec erp_app test -d /var/www/html/storage/framework/views && [ "$(docker exec erp_app find /var/www/html/storage/framework/views -name '*.php' | wc -l)" -gt 0 ]; then
    check_pass "Views are compiled"
else
    check_warn "Views not compiled (run: php artisan view:cache)"
fi

echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "5. Dependency Security Checks"
echo "═══════════════════════════════════════════════════════════════"
echo ""

# Check for outdated packages with known vulnerabilities
echo "Checking for outdated packages..."
OUTDATED=$(docker exec erp_app composer outdated --direct 2>/dev/null | wc -l)
if [ "$OUTDATED" -eq 0 ]; then
    check_pass "All dependencies are up to date"
else
    check_warn "$OUTDATED direct dependencies are outdated"
fi

echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "6. Web Server Security Checks"
echo "═══════════════════════════════════════════════════════════════"
echo ""

# Check if .env is accessible via web (simulate)
if docker exec erp_nginx test -f /var/www/html/public/.env 2>/dev/null; then
    check_fail ".env file is in public directory (CRITICAL SECURITY RISK)"
else
    check_pass ".env file is not in public directory"
fi

# Check if .git is accessible
if docker exec erp_nginx test -d /var/www/html/public/.git 2>/dev/null; then
    check_fail ".git directory is in public directory (SECURITY RISK)"
else
    check_pass ".git directory is not in public directory"
fi

echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "7. PHP Configuration Checks"
echo "═══════════════════════════════════════════════════════════════"
echo ""

# Check display_errors
DISPLAY_ERRORS=$(docker exec erp_app php -r "echo ini_get('display_errors') ? 'On' : 'Off';")
if [ "$DISPLAY_ERRORS" = "Off" ]; then
    check_pass "display_errors is disabled"
else
    check_fail "display_errors is enabled (SECURITY RISK)"
fi

# Check expose_php
EXPOSE_PHP=$(docker exec erp_app php -r "echo ini_get('expose_php') ? 'On' : 'Off';")
if [ "$EXPOSE_PHP" = "Off" ]; then
    check_pass "expose_php is disabled"
else
    check_warn "expose_php is enabled (information disclosure)"
fi

# Check OPcache
OPCACHE=$(docker exec erp_app php -r "echo extension_loaded('opcache') ? 'On' : 'Off';")
if [ "$OPCACHE" = "On" ]; then
    check_pass "OPcache is enabled"
else
    check_warn "OPcache is not enabled (performance impact)"
fi

echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "8. Docker Container Security"
echo "═══════════════════════════════════════════════════════════════"
echo ""

# Check if containers are running
RUNNING=$(docker compose ps --services --filter "status=running" | wc -l)
TOTAL=$(docker compose ps --services | wc -l)
if [ "$RUNNING" -eq "$TOTAL" ]; then
    check_pass "All containers are running ($RUNNING/$TOTAL)"
else
    check_warn "Some containers are not running ($RUNNING/$TOTAL)"
fi

# Check container health
HEALTHY=$(docker compose ps --format json 2>/dev/null | grep -c '"Health":"healthy"' || echo "0")
if [ "$HEALTHY" -gt 0 ]; then
    check_pass "$HEALTHY containers report healthy status"
else
    check_warn "No containers report health status"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Security Audit Summary"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Passed:   $PASS checks"
echo "  Failed:   $FAIL checks"
echo "  Warnings: $WARN checks"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

if [ $FAIL -eq 0 ]; then
    echo "✓ Security audit passed!"
    if [ $WARN -gt 0 ]; then
        echo "⚠ Please review warnings above"
    fi
    exit 0
else
    echo "✗ Security audit failed with $FAIL critical issues"
    echo "Please fix the issues above before deploying to production"
    exit 1
fi
