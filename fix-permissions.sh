#!/bin/bash
#
# Fix Laravel Storage Permissions for Multi-User Development
# This script ensures both UID 1000 and UID 1001 can work on the app
#

echo "🔧 Fixing Laravel storage permissions for multi-user access..."

# Run inside the Docker container as root
docker exec -u root erp_app bash -c "
    # Set ownership to www:www
    chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache

    # Set permissions: 775 (rwxrwxr-x)
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

    # Set setgid bit on directories (g+s)
    # This ensures new files inherit the group ownership
    find /var/www/html/storage -type d -exec chmod g+s {} \;
    find /var/www/html/bootstrap/cache -type d -exec chmod g+s {} \;

    echo '✅ Permissions fixed!'
"

# Clear Laravel caches
echo "🧹 Clearing Laravel caches..."
docker exec erp_app php artisan view:clear
docker exec erp_app php artisan cache:clear
docker exec erp_app php artisan config:clear

echo ""
echo "✅ All done! Both users can now work on the app."
echo ""
echo "Permissions set:"
echo "  - Owner: www (UID 1000)"
echo "  - Group: www (with setgid bit)"
echo "  - Mode: 775 (rwxrwxr-x)"
echo ""
echo "This allows:"
echo "  - PHP-FPM (www user) to read/write"
echo "  - Users in www group to read/write"
echo "  - New files automatically get www group ownership"
