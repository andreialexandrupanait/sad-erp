# Database Backup Quick Reference Guide

## Setup (One-Time)

Enable automatic daily backups at 2:00 AM:

```bash
./setup_auto_backup.sh
```

---

## Daily Usage

### Create Manual Backup

Before any import or major changes:

```bash
./backup_database.sh "before_import"
./backup_database.sh "before_client_migration"
./backup_database.sh "weekly_manual"
```

Or simply:

```bash
./backup_database.sh
```

This creates a timestamped backup automatically.

---

### Restore Database

List available backups:

```bash
ls -lh ./backups/database/
```

Restore a specific backup:

```bash
./restore_database.sh backup_20251111_140000.sql.gz
```

The script will:
1. Ask for confirmation
2. Create a safety backup of current data
3. Restore the selected backup

---

## Important Notes

### When to Create Manual Backups

✓ **Before importing data**
✓ **Before bulk deletions**
✓ **Before database migrations**
✓ **Before major application updates**
✓ **Weekly (good practice)**

### Backup Location

All backups are stored in:
```
./backups/database/
```

### Retention

- Automatic backups: Last 30 are kept
- Manual backups: Kept until manually deleted

### Backup Logs

View backup history:
```bash
tail -f logs/backup.log
```

---

## Emergency Recovery

If you accidentally delete data:

1. **Stop immediately** - don't make more changes
2. List backups: `ls -lh ./backups/database/`
3. Choose most recent backup before the deletion
4. Restore: `./restore_database.sh <backup_file>`

---

## File Sizes (Empty Database)

Initial backup size: ~12 KB (compressed)

As data grows, backup sizes will increase proportionally.

---

## Cron Schedule

Automatic backups run daily at **2:00 AM**

To check cron status:
```bash
crontab -l
```

To view last backup:
```bash
ls -lht ./backups/database/ | head -5
```

---

## Best Practices

1. Always backup before imports
2. Test restores quarterly
3. Keep important backups off-site
4. Monitor backup logs weekly
5. Verify backup files exist after creation

---

**Last Updated:** 2025-11-11
**Scripts Version:** 1.0
