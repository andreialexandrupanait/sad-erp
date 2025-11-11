#!/bin/bash

# ============================================================================
# ERP Automatic Backup Setup Script
# ============================================================================
# This script sets up automatic daily database backups using cron
# ============================================================================

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BACKUP_SCRIPT="${SCRIPT_DIR}/backup_database.sh"
CRON_TIME="0 2 * * *"  # 2:00 AM daily

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  ERP Automatic Backup Setup"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Make backup scripts executable
chmod +x "${BACKUP_SCRIPT}"
chmod +x "${SCRIPT_DIR}/restore_database.sh"
echo "✓ Made backup scripts executable"

# Create cron job
CRON_JOB="${CRON_TIME} cd ${SCRIPT_DIR} && ./backup_database.sh daily_auto >> ${SCRIPT_DIR}/logs/backup.log 2>&1"

# Check if cron job already exists
if crontab -l 2>/dev/null | grep -q "${BACKUP_SCRIPT}"; then
    echo "⚠️  Automatic backup is already configured"
else
    # Add cron job
    (crontab -l 2>/dev/null; echo "${CRON_JOB}") | crontab -
    echo "✓ Added daily backup to cron (2:00 AM)"
fi

# Create directories with proper permissions
mkdir -p "${SCRIPT_DIR}/logs"
mkdir -p "${SCRIPT_DIR}/backups/database"
chmod -R 755 "${SCRIPT_DIR}/logs"
chmod -R 755 "${SCRIPT_DIR}/backups"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  ✓ Automatic backup setup complete!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "  Daily backups will run at: 2:00 AM"
echo "  Backups are stored in: ./backups/database/"
echo "  Logs are stored in: ./logs/backup.log"
echo "  Retention: Last 30 backups"
echo ""
echo "Manual backup commands:"
echo "  Create backup: ./backup_database.sh"
echo "  Restore backup: ./restore_database.sh <file>"
echo "  List backups: ls -lh ./backups/database/"
echo ""
