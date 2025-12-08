#!/bin/bash
# =============================================================================
# Health Check Script
# =============================================================================
# Usage: ./scripts/health-check.sh
#
# Performs various health checks on the application.
# =============================================================================

APP_DIR="/var/www/erp/app"
cd "$APP_DIR"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

ERRORS=0

check_pass() {
    echo -e "${GREEN}[PASS]${NC} $1"
}

check_fail() {
    echo -e "${RED}[FAIL]${NC} $1"
    ERRORS=$((ERRORS + 1))
}

check_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

echo "=============================================="
echo "ERP Health Check"
echo "=============================================="
echo ""

# Check 1: PHP Version
echo "Checking PHP version..."
PHP_VERSION=$(php -v | head -n 1 | cut -d ' ' -f 2)
if [[ "$PHP_VERSION" =~ ^8\.[1-9] ]]; then
    check_pass "PHP version: $PHP_VERSION"
else
    check_fail "PHP version $PHP_VERSION (requires 8.1+)"
fi

# Check 2: Required PHP extensions
echo ""
echo "Checking PHP extensions..."
REQUIRED_EXTENSIONS=("pdo" "pdo_mysql" "mbstring" "openssl" "tokenizer" "xml" "ctype" "json" "bcmath" "fileinfo")

for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -m | grep -qi "^$ext$"; then
        check_pass "Extension: $ext"
    else
        check_fail "Extension missing: $ext"
    fi
done

# Check 3: Writable directories
echo ""
echo "Checking writable directories..."
WRITABLE_DIRS=("storage" "storage/logs" "storage/framework/cache" "storage/framework/sessions" "storage/framework/views" "bootstrap/cache")

for dir in "${WRITABLE_DIRS[@]}"; do
    if [ -w "$APP_DIR/$dir" ]; then
        check_pass "Writable: $dir"
    else
        check_fail "Not writable: $dir"
    fi
done

# Check 4: Environment file
echo ""
echo "Checking environment..."
if [ -f "$APP_DIR/.env" ]; then
    check_pass ".env file exists"

    # Check critical variables
    if grep -q "^APP_KEY=base64:" "$APP_DIR/.env"; then
        check_pass "APP_KEY is set"
    else
        check_fail "APP_KEY is not set"
    fi

    if grep -q "^APP_ENV=production" "$APP_DIR/.env"; then
        check_pass "APP_ENV is production"

        if grep -q "^APP_DEBUG=false" "$APP_DIR/.env"; then
            check_pass "APP_DEBUG is false"
        else
            check_warn "APP_DEBUG should be false in production"
        fi
    else
        check_warn "APP_ENV is not production"
    fi
else
    check_fail ".env file missing"
fi

# Check 5: Database connection
echo ""
echo "Checking database connection..."
if php artisan db:monitor 2>/dev/null | grep -q "OK"; then
    check_pass "Database connection OK"
else
    # Try alternative check
    if php artisan tinker --execute="DB::connection()->getPdo()" 2>/dev/null; then
        check_pass "Database connection OK"
    else
        check_fail "Database connection failed"
    fi
fi

# Check 6: Storage link
echo ""
echo "Checking storage link..."
if [ -L "$APP_DIR/public/storage" ]; then
    check_pass "Storage link exists"
else
    check_warn "Storage link missing (run: php artisan storage:link)"
fi

# Check 7: Queue worker
echo ""
echo "Checking queue status..."
QUEUE_SIZE=$(php artisan queue:monitor 2>/dev/null | grep -oP '\d+' || echo "unknown")
if [ "$QUEUE_SIZE" = "0" ] || [ "$QUEUE_SIZE" = "unknown" ]; then
    check_pass "Queue is healthy (size: $QUEUE_SIZE)"
else
    check_warn "Queue has $QUEUE_SIZE pending jobs"
fi

# Check 8: Disk space
echo ""
echo "Checking disk space..."
DISK_USAGE=$(df -h "$APP_DIR" | awk 'NR==2 {print $5}' | sed 's/%//')
if [ "$DISK_USAGE" -lt 80 ]; then
    check_pass "Disk usage: ${DISK_USAGE}%"
elif [ "$DISK_USAGE" -lt 90 ]; then
    check_warn "Disk usage: ${DISK_USAGE}% (getting high)"
else
    check_fail "Disk usage: ${DISK_USAGE}% (critical!)"
fi

# Check 9: Log file size
echo ""
echo "Checking log file..."
LOG_FILE="$APP_DIR/storage/logs/laravel.log"
if [ -f "$LOG_FILE" ]; then
    LOG_SIZE=$(du -m "$LOG_FILE" | cut -f1)
    if [ "$LOG_SIZE" -lt 100 ]; then
        check_pass "Log file size: ${LOG_SIZE}MB"
    else
        check_warn "Log file size: ${LOG_SIZE}MB (consider rotation)"
    fi
else
    check_pass "Log file not found (OK for fresh install)"
fi

# Summary
echo ""
echo "=============================================="
if [ $ERRORS -eq 0 ]; then
    echo -e "${GREEN}All checks passed!${NC}"
else
    echo -e "${RED}$ERRORS check(s) failed${NC}"
fi
echo "=============================================="

exit $ERRORS
