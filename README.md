# SAD-ERP

Internal ERP system for SimpleAd.

## Quick Start

```bash
# Start containers
docker compose up -d

# Run migrations
docker exec erp_app php artisan migrate

# Clear caches
docker exec erp_app php artisan optimize:clear
```

## Useful Commands

```bash
# Database backup
docker exec erp_db mysqldump -u root -p laravel_erp | gzip > backup.sql.gz

# View logs
docker exec erp_app tail -f storage/logs/laravel.log

# Clear all caches
docker exec erp_app php artisan optimize:clear

# Run tests
docker exec erp_app php artisan test
```

## Structure

- `app/` - Laravel application
- `docker/` - Docker configurations
- `backups/` - Database backups (not in git)

## Access

Production: https://intern.simplead.ro
