#!/bin/bash
# =============================================================================
# ERP Deployment Script
# =============================================================================
# Usage: ./scripts/deploy.sh [--skip-maintenance] [--skip-migrations]
#
# This script handles the deployment process including:
# - Putting the app in maintenance mode
# - Pulling latest code
# - Installing dependencies
# - Running migrations
# - Clearing and rebuilding caches
# - Restarting queue workers
# =============================================================================

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
APP_DIR="/var/www/erp/app"
BYPASS_SECRET="deploy-bypass-$(date +%s)"

# Parse arguments
SKIP_MAINTENANCE=false
SKIP_MIGRATIONS=false

for arg in "$@"; do
    case $arg in
        --skip-maintenance)
            SKIP_MAINTENANCE=true
            shift
            ;;
        --skip-migrations)
            SKIP_MIGRATIONS=true
            shift
            ;;
    esac
done

# Functions
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Change to app directory
cd "$APP_DIR"

echo "=============================================="
echo "Starting ERP Deployment"
echo "=============================================="
echo ""

# Step 1: Maintenance mode
if [ "$SKIP_MAINTENANCE" = false ]; then
    log_info "Putting application in maintenance mode..."
    php artisan down --secret="$BYPASS_SECRET"
    echo "Bypass URL: ${APP_URL}/${BYPASS_SECRET}"
fi

# Step 2: Pull latest code
log_info "Pulling latest code from repository..."
git pull origin main

# Step 3: Install dependencies
log_info "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Step 4: Run migrations
if [ "$SKIP_MIGRATIONS" = false ]; then
    log_info "Running database migrations..."
    php artisan migrate --force
else
    log_warn "Skipping migrations (--skip-migrations flag set)"
fi

# Step 5: Clear and rebuild caches
log_info "Clearing and rebuilding caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Step 6: Clear application cache
log_info "Clearing application cache..."
php artisan cache:clear

# Step 7: Restart queue workers
log_info "Restarting queue workers..."
php artisan queue:restart

# Step 8: Bring application back online
if [ "$SKIP_MAINTENANCE" = false ]; then
    log_info "Bringing application back online..."
    php artisan up
fi

echo ""
echo "=============================================="
log_info "Deployment completed successfully!"
echo "=============================================="
