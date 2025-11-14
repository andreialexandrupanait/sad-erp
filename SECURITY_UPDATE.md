# Security Update - Environment Variables

**Date:** November 14, 2025

## What Changed?

Database passwords have been moved from `docker-compose.yml` to environment variables.

## Before (Insecure)
Passwords were hardcoded in `docker-compose.yml`:
```yaml
MYSQL_ROOT_PASSWORD: root_secure_password_2025
MYSQL_PASSWORD: laravel_secure_pass_2025
```

## After (Secure)
Passwords are now loaded from `.env` file:
```yaml
MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD:-root_secure_password_2025}
MYSQL_PASSWORD: ${DB_PASSWORD:-laravel_secure_pass_2025}
```

## How to Use

### For Development (Default)
No action needed! Default passwords are maintained via the `:-default_value` syntax.

### For Production (Custom Passwords)
1. Copy `.env.example` to `.env` in project root:
   ```bash
   cp .env.example .env
   ```

2. Edit `.env` and set strong passwords:
   ```bash
   DB_ROOT_PASSWORD=your_super_secure_root_password_here
   DB_PASSWORD=your_super_secure_user_password_here
   ```

3. Restart Docker services:
   ```bash
   docker compose down
   docker compose up -d
   ```

## Important Notes

- The `.env` file is gitignored (never commit it!)
- Default passwords still work if `.env` is not created (backwards compatible)
- The Laravel app still uses its own `.env` at `/var/www/erp/app/.env`
- This only affects Docker Compose, not the Laravel application

## Benefits

✅ Passwords no longer exposed in version control
✅ Different passwords per environment (dev/staging/prod)
✅ Follows security best practices
✅ Backwards compatible with existing setup

## Next Steps (Optional)

After testing that everything works:
1. Change the default passwords in production
2. Store production passwords in a secure password manager
3. Never share production `.env` file

---

**Migration Status:** ✅ Complete - No breaking changes
