#!/bin/bash

# ============================================================================
# ERP Automated Backup Schedule Configuration
# ============================================================================
# This script sets up automated database backups using cron
# Run this script once to configure automated backups
# ============================================================================

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BACKUP_SCRIPT="${SCRIPT_DIR}/backup_database.sh"

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  ERP Automated Backup Configuration"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Check if backup script exists
if [ ! -f "$BACKUP_SCRIPT" ]; then
    echo "✗ Error: Backup script not found at $BACKUP_SCRIPT"
    exit 1
fi

echo "Backup script found: $BACKUP_SCRIPT"
echo ""

# Create cron job entry
CRON_JOB="0 2 * * * cd ${SCRIPT_DIR} && ${BACKUP_SCRIPT} >> ${SCRIPT_DIR}/backups/backup.log 2>&1"

echo "Proposed cron job:"
echo "  Schedule: Every day at 2:00 AM"
echo "  Command: $CRON_JOB"
echo ""

# Check if cron job already exists
if crontab -l 2>/dev/null | grep -F "${BACKUP_SCRIPT}" >/dev/null; then
    echo "⚠️  A backup cron job already exists"
    echo ""
    echo "Current cron jobs for backup script:"
    crontab -l 2>/dev/null | grep -F "${BACKUP_SCRIPT}"
    echo ""
    read -p "Do you want to replace it? (y/N): " -n 1 -r
    echo ""
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Aborted. No changes made."
        exit 0
    fi

    # Remove existing backup cron jobs
    crontab -l 2>/dev/null | grep -v -F "${BACKUP_SCRIPT}" | crontab -
    echo "✓ Removed existing backup cron job"
fi

# Add new cron job
(crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -

if [ $? -eq 0 ]; then
    echo "✓ Automated backup scheduled successfully!"
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "  Backup Schedule Configuration"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "  Frequency: Daily at 2:00 AM"
    echo "  Retention: Last 30 backups"
    echo "  Log file: ${SCRIPT_DIR}/backups/backup.log"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo "To view scheduled cron jobs:"
    echo "  crontab -l"
    echo ""
    echo "To manually trigger a backup:"
    echo "  ${BACKUP_SCRIPT}"
    echo ""
else
    echo "✗ Error: Failed to schedule automated backup"
    exit 1
fi
