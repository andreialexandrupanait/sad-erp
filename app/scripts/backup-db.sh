#!/bin/bash
# =============================================================================
# Database Backup Script
# =============================================================================
# Usage: ./scripts/backup-db.sh [--compress]
#
# Creates a timestamped backup of the database.
# Use --compress to gzip the backup file.
# =============================================================================

set -e

# Configuration - Override via environment variables
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-erp}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD:-}"

BACKUP_DIR="/var/www/erp/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="${BACKUP_DIR}/${DB_DATABASE}_${TIMESTAMP}.sql"

COMPRESS=false

for arg in "$@"; do
    case $arg in
        --compress)
            COMPRESS=true
            shift
            ;;
    esac
done

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

echo "=============================================="
echo "Database Backup"
echo "=============================================="
echo "Database: $DB_DATABASE"
echo "Timestamp: $TIMESTAMP"
echo ""

# Create backup
echo "Creating backup..."
mysqldump \
    --host="$DB_HOST" \
    --port="$DB_PORT" \
    --user="$DB_USERNAME" \
    --password="$DB_PASSWORD" \
    --single-transaction \
    --routines \
    --triggers \
    --quick \
    "$DB_DATABASE" > "$BACKUP_FILE"

# Compress if requested
if [ "$COMPRESS" = true ]; then
    echo "Compressing backup..."
    gzip "$BACKUP_FILE"
    BACKUP_FILE="${BACKUP_FILE}.gz"
fi

# Get file size
FILE_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)

echo ""
echo "=============================================="
echo "Backup completed!"
echo "File: $BACKUP_FILE"
echo "Size: $FILE_SIZE"
echo "=============================================="

# Cleanup old backups (keep last 7 days)
echo ""
echo "Cleaning up old backups (keeping last 7 days)..."
find "$BACKUP_DIR" -name "*.sql*" -mtime +7 -delete
echo "Done!"
