# Multi-User Permissions Setup - Complete Guide

## Problem Solved

The application was experiencing widespread 500 errors because:
- Storage directories were owned by UID 1001
- PHP-FPM runs as user `www` (UID 1000)
- PHP-FPM couldn't write compiled Blade views to `storage/framework/views/`

## Solution: Group-Based Permissions

The solution uses **group permissions** to allow multiple users to work on the same application:

```bash
# Group: www
# Members: www (UID 1000), and any other users added to the group
# Permissions: 775 with setgid bit on directories
```

## Current Setup

### Directory Ownership
```
Owner: www (UID 1000)
Group: www (with setgid bit 's')
Mode: 775 (rwxrwxr-x)
```

### What This Means

1. **PHP-FPM (www user)**: Full read/write/execute access
2. **Group members**: Full read/write/execute access
3. **Others**: Read and execute only
4. **Setgid bit ('s')**: New files automatically inherit `www` group ownership

### Key Directories
- `/var/www/html/storage/` - All subdirectories
- `/var/www/html/bootstrap/cache/` - Laravel bootstrap cache

## How to Fix Permissions (If Needed)

If you encounter permission errors again, simply run:

```bash
cd /var/www/erp
./fix-permissions.sh
```

This script will:
1. Set ownership to `www:www`
2. Set permissions to `775`
3. Enable setgid bit on directories
4. Clear Laravel caches

## Manual Fix (Alternative)

If you prefer to fix permissions manually:

```bash
# Inside the container as root
docker exec -u root erp_app bash -c "
    chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
    find /var/www/html/storage -type d -exec chmod g+s {} \;
    find /var/www/html/bootstrap/cache -type d -exec chmod g+s {} \;
"

# Clear caches
docker exec erp_app php artisan view:clear
docker exec erp_app php artisan cache:clear
```

## Understanding the Setgid Bit

The `s` in `drwxrwsr-x` is the **setgid bit**:

```bash
# Without setgid
drwxrwxr-x  # New files get creator's group

# With setgid
drwxrwsr-x  # New files inherit directory's group (www)
```

This is crucial because:
- User A (UID 1000) creates a file → owned by `www:www`
- User B (UID 1001) creates a file → owned by `user_b:www`
- Both users can modify files because group `www` has write permissions

## Adding New Users

To allow another user to work on the app:

```bash
# Add user to www group inside container
docker exec -u root erp_app usermod -a -G www <username>

# Or from host (if user exists on host)
# Note: This depends on your Docker volume mount configuration
```

## Verification

Check permissions are correct:

```bash
# Check directory permissions
docker exec erp_app ls -la storage/framework/views/ | head -5

# Should show:
# drwxrwsr-x (note the 's' - setgid bit is set)
#     ^^^  ^
#     |||  |-- Group: read/write/execute
#     ||+-- Group: setgid bit
#     |+-- Owner: write
#     +-- Owner: execute

# Check if you can write
docker exec erp_app touch storage/framework/test.txt && \
docker exec erp_app rm storage/framework/test.txt && \
echo "✅ Write access works!"
```

## Why This Solution Works

1. **Group ownership**: Both PHP-FPM and development users share the `www` group
2. **Group write permissions**: `775` allows group members to read/write/execute
3. **Setgid bit**: Ensures new files inherit the group, maintaining access
4. **No more permission conflicts**: Everyone in the group can modify files

## Common Issues and Solutions

### Issue: "Permission denied" errors return

**Solution**: Run the fix script
```bash
./fix-permissions.sh
```

### Issue: New files owned by wrong user

**Solution**: Setgid bit not set. Re-run:
```bash
docker exec -u root erp_app find /var/www/html/storage -type d -exec chmod g+s {} \;
```

### Issue: User still can't write

**Solution**: User not in www group. Add them:
```bash
docker exec -u root erp_app usermod -a -G www <username>
```

## Best Practices

1. **Always run fix-permissions.sh after**:
   - Pulling code from Git
   - Restoring from backup
   - Major file system changes

2. **Include in deployment**:
   Add to your deployment script:
   ```bash
   ./fix-permissions.sh
   ```

3. **Git ignore permission changes**:
   Git doesn't track Unix permissions beyond executable bit, but be aware that:
   - Cloning resets ownership
   - You may need to run fix script after clone/pull

## Technical Details

### Permission Breakdown: 775

```
7 (rwx) - Owner (www): read, write, execute
7 (rwx) - Group (www): read, write, execute
5 (r-x) - Others: read, execute (no write)
```

### Setgid Bit: 2775

```
2 - Setgid bit
775 - Standard permissions
```

When a directory has setgid:
- Files created inherit the directory's group
- Essential for multi-user environments
- Prevents permission fragmentation

## Files Modified in This Session

1. **Storage directories**: Fixed ownership and permissions
2. **Bootstrap cache**: Fixed ownership and permissions
3. **Fix script**: Created `/var/www/erp/fix-permissions.sh`
4. **Environment**: Restored to production mode (`APP_ENV=production`, `APP_DEBUG=false`)

## Summary

✅ **Problem**: File permission mismatch causing 500 errors
✅ **Solution**: Group-based permissions with setgid bit
✅ **Result**: Both UID 1000 and UID 1001 can work on the app
✅ **Maintenance**: Run `./fix-permissions.sh` when needed

The application now supports multi-user development environments while maintaining security and proper access control.
