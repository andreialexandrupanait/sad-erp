#!/bin/bash
# =============================================================================
# Cache Clear Script
# =============================================================================
# Usage: ./scripts/cache-clear.sh [--all]
#
# Clears various Laravel caches. Use --all to clear everything.
# =============================================================================

set -e

APP_DIR="/var/www/erp/app"
cd "$APP_DIR"

CLEAR_ALL=false

for arg in "$@"; do
    case $arg in
        --all)
            CLEAR_ALL=true
            shift
            ;;
    esac
done

echo "Clearing caches..."

if [ "$CLEAR_ALL" = true ]; then
    echo "-> Clearing application cache..."
    php artisan cache:clear

    echo "-> Clearing config cache..."
    php artisan config:clear

    echo "-> Clearing route cache..."
    php artisan route:clear

    echo "-> Clearing view cache..."
    php artisan view:clear

    echo "-> Clearing event cache..."
    php artisan event:clear

    echo "-> Clearing compiled classes..."
    php artisan clear-compiled

    echo ""
    echo "All caches cleared!"
else
    echo "-> Clearing application cache..."
    php artisan cache:clear

    echo ""
    echo "Application cache cleared!"
    echo "Use --all to clear all caches (config, route, view, event)"
fi
