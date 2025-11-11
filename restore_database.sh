#!/bin/bash

# ============================================================================
# ERP Database Restore Script
# ============================================================================
# This script restores a database backup
# Usage: ./restore_database.sh <backup_file.sql.gz>
# ============================================================================

set -e  # Exit on any error

# Check if backup file is provided
if [ -z "$1" ]; then
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "  Error: No backup file specified"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo "Usage: ./restore_database.sh <backup_file>"
    echo ""
    echo "Available backups:"
    ls -lh ./backups/database/*.sql.gz 2>/dev/null | awk '{print "  - " $9 " (" $5 ")"}'
    echo ""
    exit 1
fi

# Configuration
BACKUP_FILE="$1"
BACKUP_DIR="./backups/database"

# If file doesn't include path, look in backup directory
if [[ ! "$BACKUP_FILE" =~ / ]]; then
    BACKUP_FILE="${BACKUP_DIR}/${BACKUP_FILE}"
fi

# Check if backup file exists
if [ ! -f "${BACKUP_FILE}" ]; then
    echo "✗ Error: Backup file not found: ${BACKUP_FILE}"
    exit 1
fi

# Database credentials
DB_CONTAINER="erp_db"
DB_NAME="laravel_erp"
DB_USER="root"
DB_PASS="root_secure_password_2025"

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  ⚠️  WARNING: Database Restore Operation"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  This will REPLACE all current data in the database!"
echo "  Database: ${DB_NAME}"
echo "  Backup file: $(basename ${BACKUP_FILE})"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
read -p "Are you sure you want to continue? (yes/no): " CONFIRM

if [ "$CONFIRM" != "yes" ]; then
    echo "✗ Restore cancelled"
    exit 0
fi

# Create a safety backup before restore
echo ""
echo "⏳ Creating safety backup of current database..."
SAFETY_BACKUP="pre_restore_$(date +%Y%m%d_%H%M%S).sql.gz"
./backup_database.sh "${SAFETY_BACKUP%.sql.gz}" > /dev/null 2>&1
echo "✓ Safety backup created: ${SAFETY_BACKUP}"
echo ""

# Decompress if needed
TEMP_FILE="/tmp/restore_temp.sql"
if [[ "${BACKUP_FILE}" == *.gz ]]; then
    echo "⏳ Decompressing backup..."
    gunzip -c "${BACKUP_FILE}" > "${TEMP_FILE}"
    echo "✓ Backup decompressed"
else
    cp "${BACKUP_FILE}" "${TEMP_FILE}"
fi

# Restore the database
echo "⏳ Restoring database..."
docker compose exec -T ${DB_CONTAINER} mysql \
    -u ${DB_USER} \
    -p${DB_PASS} \
    ${DB_NAME} < "${TEMP_FILE}" 2>/dev/null

if [ $? -eq 0 ]; then
    echo "✓ Database restored successfully"
else
    echo "✗ Error: Database restore failed"
    rm -f "${TEMP_FILE}"
    exit 1
fi

# Clean up temporary file
rm -f "${TEMP_FILE}"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  ✓ Database restore completed successfully!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "Safety backup stored at:"
echo "  ./backups/database/${SAFETY_BACKUP}"
echo ""
