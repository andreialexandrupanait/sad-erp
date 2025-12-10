#!/bin/bash

# ============================================================================
# ERP Database Backup Script
# ============================================================================
# This script creates timestamped backups of the entire database
# Usage: ./backup_database.sh [optional_backup_name]
# ============================================================================

set -e  # Exit on any error

# Configuration
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="./backups/database"
BACKUP_NAME="${1:-backup_${TIMESTAMP}}"
BACKUP_FILE="${BACKUP_DIR}/${BACKUP_NAME}.sql"
COMPRESSED_FILE="${BACKUP_FILE}.gz"

# Database credentials from .env file
DB_CONTAINER="erp_db"
DB_NAME="laravel_erp"
DB_USER="root"

# Load DB password from .env file
if [ -f .env ]; then
    export $(grep -v '^#' .env | grep DB_ROOT_PASSWORD | xargs)
    DB_PASS="${DB_ROOT_PASSWORD}"
else
    echo "Error: .env file not found"
    exit 1
fi

if [ -z "$DB_PASS" ]; then
    echo "Error: DB_ROOT_PASSWORD not set in .env file"
    exit 1
fi

# Create backup directory if it doesn't exist
mkdir -p "${BACKUP_DIR}"

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  ERP Database Backup"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Date: $(date '+%Y-%m-%d %H:%M:%S')"
echo "  Database: ${DB_NAME}"
echo "  Backup file: ${BACKUP_NAME}.sql.gz"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Create the backup
echo "⏳ Creating database backup..."
docker compose exec -T ${DB_CONTAINER} mysqldump \
    -u ${DB_USER} \
    -p${DB_PASS} \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    --add-drop-table \
    --quick \
    --lock-tables=false \
    ${DB_NAME} > "${BACKUP_FILE}" 2>/dev/null

if [ $? -eq 0 ]; then
    echo "✓ Database exported successfully"
else
    echo "✗ Error: Database export failed"
    exit 1
fi

# Compress the backup
echo "⏳ Compressing backup..."
gzip -f "${BACKUP_FILE}"

if [ $? -eq 0 ]; then
    echo "✓ Backup compressed successfully"
else
    echo "✗ Error: Compression failed"
    exit 1
fi

# Get file size
FILE_SIZE=$(du -h "${COMPRESSED_FILE}" | cut -f1)

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  ✓ Backup completed successfully!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  File: ${COMPRESSED_FILE}"
echo "  Size: ${FILE_SIZE}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "To restore this backup, run:"
echo "  ./restore_database.sh ${BACKUP_NAME}.sql.gz"
echo ""

# Keep only the last 30 backups (delete older ones)
echo "🧹 Cleaning up old backups (keeping last 30)..."
cd "${BACKUP_DIR}"
ls -t *.sql.gz 2>/dev/null | tail -n +31 | xargs -r rm --
REMAINING=$(ls -1 *.sql.gz 2>/dev/null | wc -l)
echo "✓ Cleanup complete (${REMAINING} backups retained)"
