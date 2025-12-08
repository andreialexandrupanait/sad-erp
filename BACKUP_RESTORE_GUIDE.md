# Database Backup & Restore Guide

## Overview

Your ERP system includes a comprehensive automated backup solution with **mysqldump** for reliable daily, weekly, and monthly database backups. All backups are stored on the server at `/var/www/erp/backups/`.

## Backup System

### Automated Backups (via Laravel Scheduler)

The system automatically creates backups according to this schedule:

| Type | Frequency | Time | Compression | Files Included |
|------|-----------|------|-------------|----------------|
| **Daily** | Every day | 02:00 AM | ✅ Yes (.gz) | Database only |
| **Weekly** | Every Sunday | 03:00 AM | ✅ Yes (.gz) | Database + uploaded files |
| **Monthly** | 1st of month | 04:00 AM | ✅ Yes (.gz) | Database + uploaded files |

**Retention Policy** (runs every Sunday at 05:00 AM):
- Daily backups: Keep last 7 days
- Weekly backups: Keep last 4 weeks
- Monthly backups: Keep last 12 months

### Backup Locations

```
/var/www/erp/backups/
├── database/
│   ├── daily/          # Daily database backups
│   ├── weekly/         # Weekly database backups (with files)
│   ├── monthly/        # Monthly database backups (with files)
│   └── manual/         # Manual backups created via web interface
├── files/              # Archived uploaded files (weekly/monthly)
└── logs/               # Backup operation logs
```

### Manual Backups

#### Via Command Line
```bash
# Create a manual compressed backup
docker exec erp_app php artisan backup:database --type=manual --compress

# Create backup with uploaded files
docker exec erp_app php artisan backup:database --type=manual --compress --include-files

# Create specific backup type
docker exec erp_app php artisan backup:database --type=daily --compress
```

#### Via Web Interface
1. Navigate to **Settings** > **Backup**
2. Select tables to export (or keep all selected)
3. Click **Create Backup**
4. Download the backup file

## Restore Database

### Method 1: From Command Line (Recommended for Full Restores)

**⚠️ WARNING: This will overwrite your current database!**

```bash
# Navigate to backups directory
cd /var/www/erp/backups/database

# Decompress the backup (if .gz)
gunzip backup_daily_2025-12-08_020000.sql.gz

# Restore the database
docker exec -i erp_db mysql -u laravel_user -p laravel_erp < backup_daily_2025-12-08_020000.sql

# When prompted, enter the database password from .env
```

**Quick restore script:**
```bash
#!/bin/bash
# Restore from most recent daily backup

BACKUP_FILE=$(ls -t /var/www/erp/backups/database/daily/*.sql.gz | head -1)
echo "Restoring from: $BACKUP_FILE"

gunzip -c "$BACKUP_FILE" | docker exec -i erp_db mysql -u root -p${DB_ROOT_PASSWORD} laravel_erp

echo "Database restored successfully!"
```

### Method 2: Using Artisan Command

```bash
# List available backups
ls -lh /var/www/erp/backups/database/*/

# Restore from a specific backup
docker exec erp_app php artisan backup:restore /var/www/backups/database/daily/backup_daily_2025-12-08_020000.sql.gz
```

### Method 3: Via Web Interface (JSON Format Only)

The web interface at `/settings/backup` supports JSON-format backups:

1. Navigate to **Settings** > **Backup**
2. Under **Import Backup**, choose your backup file (.json)
3. Select restore mode:
   - **Merge**: Updates existing records, adds new ones (safer)
   - **Replace**: Deletes all data and imports fresh (⚠️ use with caution!)
4. Click **Import Backup**

**Note**: Mysqldump SQL files cannot be imported via web interface - use command line instead.

## Emergency Restore Scenarios

### Scenario 1: Database Completely Lost

```bash
# 1. Stop the application
docker-compose stop erp_app erp_queue erp_scheduler

# 2. Restore database from most recent backup
LATEST_BACKUP=$(ls -t /var/www/erp/backups/database/daily/*.sql.gz | head -1)
gunzip -c "$LATEST_BACKUP" | docker exec -i erp_db mysql -u root -p${DB_ROOT_PASSWORD} laravel_erp

# 3. Restart services
docker-compose start erp_app erp_queue erp_scheduler

# 4. Clear cache
docker exec erp_app php artisan cache:clear
docker exec erp_app php artisan config:clear
```

### Scenario 2: Partial Data Loss (e.g., accidentally deleted clients)

```bash
# 1. Extract specific table from backup
gunzip -c /var/www/erp/backups/database/daily/backup_daily_2025-12-08_020000.sql.gz | \
  sed -n '/DROP TABLE.*`clients`/,/UNLOCK TABLES/p' > clients_only.sql

# 2. Review the SQL file
less clients_only.sql

# 3. Restore only that table
docker exec -i erp_db mysql -u laravel_user -p laravel_erp < clients_only.sql
```

### Scenario 3: Server Crash - Moving to New Server

```bash
# On old server (if accessible):
# 1. Copy entire backups directory
scp -r /var/www/erp/backups user@newserver:/var/www/erp/

# On new server:
# 1. Set up Docker containers (follow main README.md)

# 2. Restore latest backup
cd /var/www/erp
LATEST=$(ls -t backups/database/*/*.sql.gz | head -1)
gunzip -c "$LATEST" | docker exec -i erp_db mysql -u root -p${DB_ROOT_PASSWORD} laravel_erp

# 3. Restore uploaded files (if weekly/monthly backup exists)
LATEST_FILES=$(ls -t backups/files/*.tar.gz | head -1)
if [ -f "$LATEST_FILES" ]; then
    tar -xzf "$LATEST_FILES" -C app/storage/app/
fi

# 4. Set correct permissions
docker exec -u root erp_app chown -R www:www /var/www/html/storage
```

## Backup Verification

### Verify Backup Integrity

```bash
# Test gzip integrity
gzip -t /var/www/erp/backups/database/daily/backup_daily_2025-12-08_020000.sql.gz

# Check backup size (should be > 50KB for production database)
ls -lh /var/www/erp/backups/database/daily/

# Test restore to temporary database
docker exec -i erp_db mysql -u root -p${DB_ROOT_PASSWORD} -e "CREATE DATABASE test_restore;"
gunzip -c backup.sql.gz | docker exec -i erp_db mysql -u root -p${DB_ROOT_PASSWORD} test_restore
docker exec -i erp_db mysql -u root -p${DB_ROOT_PASSWORD} -e "DROP DATABASE test_restore;"
```

### Monitor Backup Schedule

```bash
# Check if scheduler is running
docker ps | grep erp_scheduler

# View scheduler logs
docker logs erp_scheduler --tail 50

# List upcoming scheduled tasks
docker exec erp_app php artisan schedule:list
```

## Troubleshooting

### Issue: Backup Permission Denied

```bash
# Fix ownership of backup directory
docker exec -u root erp_app chown -R www:www /var/www/backups
docker exec -u root erp_app chmod -R 775 /var/www/backups
```

### Issue: Backup File Too Small (< 50KB)

This usually indicates an incomplete backup. Check:

```bash
# View backup logs
docker logs erp_scheduler --tail 100

# Check disk space
df -h

# Manually test mysqldump
docker exec erp_db mysqldump -u laravel_user -p laravel_erp | head -50
```

### Issue: Restore Fails with "Access Denied"

```bash
# Use root user for restore
gunzip -c backup.sql.gz | docker exec -i erp_db mysql -u root -p${DB_ROOT_PASSWORD} laravel_erp

# Or grant permissions
docker exec -i erp_db mysql -u root -p${DB_ROOT_PASSWORD} -e "GRANT ALL ON laravel_erp.* TO 'laravel_user'@'%';"
```

### Issue: Scheduler Not Running Backups

```bash
# Check if scheduler container is running
docker ps -a | grep scheduler

# Restart scheduler
docker-compose restart erp_scheduler

# Check Laravel logs
docker exec erp_app tail -100 storage/logs/laravel.log
```

## Best Practices

1. **Keep Backups Off-Server**: Periodically copy backups to external storage:
   ```bash
   # Example: Copy to remote server
   rsync -avz /var/www/erp/backups/ user@backup-server:/backups/erp/
   ```

2. **Test Restores Regularly**: Schedule monthly restore tests to verify backup integrity

3. **Monitor Disk Space**: Backups consume disk space - monitor with:
   ```bash
   du -sh /var/www/erp/backups/*
   df -h
   ```

4. **Before Major Changes**: Always create a manual backup before:
   - Database migrations
   - Major feature deployments
   - Bulk data imports/updates
   - System upgrades

5. **Document Backup Strategy**: Keep this guide updated with your specific needs

## Backup File Formats

### SQL Format (.sql.gz)
- Created by: `backup:database` command
- Contains: Full database schema + data
- Restore: Via mysql command line
- Best for: Complete database restores

### JSON Format (.json)
- Created by: Web interface export
- Contains: Selected tables as JSON
- Restore: Via web interface or API
- Best for: Partial restores, data migration

## Quick Reference Commands

```bash
# Create manual backup
docker exec erp_app php artisan backup:database --type=manual --compress

# List backups
ls -lh /var/www/erp/backups/database/*/

# Restore from backup
gunzip -c backup.sql.gz | docker exec -i erp_db mysql -u root -p${DB_ROOT_PASSWORD} laravel_erp

# Check scheduler status
docker exec erp_app php artisan schedule:list

# Clean old backups
docker exec erp_app php artisan backup:cleanup

# Fix permissions
docker exec -u root erp_app chown -R www:www /var/www/backups
```

## Support

For issues with backups:
1. Check `/var/www/erp/backups/logs/` for detailed logs
2. Review Docker container logs: `docker logs erp_scheduler`
3. Verify disk space: `df -h`
4. Contact system administrator

---

**Last Updated**: December 8, 2025
**System**: Laravel ERP v12.0
**Database**: MySQL 5.7
