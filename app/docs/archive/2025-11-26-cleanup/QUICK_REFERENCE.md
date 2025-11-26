# Quick Reference - Copy-Paste Commands

## Initial Setup (First Time Only)

### Step 1: Navigate to Project Directory
```bash
cd /var/www/erp
```

### Step 2: Run Automated Setup
```bash
./SETUP.sh
```

**OR** Manual Setup:

### Step 2a: Build Docker Images
```bash
docker-compose build erp_app
```

### Step 2b: Install Laravel (if app directory is empty)
```bash
docker-compose run --rm erp_composer create-project laravel/laravel . --prefer-dist
sudo chown -R $USER:$USER app/
chmod -R 775 app/storage app/bootstrap/cache
```

### Step 2c: Configure Environment
```bash
cp app/.env.example app/.env
nano app/.env
```

Update these lines:
```env
DB_CONNECTION=mysql
DB_HOST=erp_db
DB_PORT=3306
DB_DATABASE=laravel_erp
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_secure_pass_2025
```

### Step 2d: Start Containers
```bash
docker-compose up -d
```

### Step 2e: Initialize Laravel
```bash
docker-compose exec erp_app php artisan key:generate
docker-compose exec erp_app php artisan migrate
```

### Step 2f: Access Application
```
http://YOUR_SERVER_IP:8085
```

---

## Daily Operations

### Start Application
```bash
cd /var/www/erp
docker-compose up -d
```

### Stop Application
```bash
cd /var/www/erp
docker-compose down
```

### Restart Application
```bash
cd /var/www/erp
docker-compose restart
```

### View All Logs
```bash
docker-compose logs -f
```

### View Specific Container Logs
```bash
docker-compose logs -f erp_app
docker-compose logs -f erp_web
docker-compose logs -f erp_db
```

---

## Laravel Commands

### Run Artisan Commands
```bash
docker-compose exec erp_app php artisan [command]
```

### Common Artisan Commands
```bash
# Database migrations
docker-compose exec erp_app php artisan migrate
docker-compose exec erp_app php artisan migrate:fresh
docker-compose exec erp_app php artisan migrate:rollback

# Database seeding
docker-compose exec erp_app php artisan db:seed

# Clear caches
docker-compose exec erp_app php artisan cache:clear
docker-compose exec erp_app php artisan config:clear
docker-compose exec erp_app php artisan view:clear
docker-compose exec erp_app php artisan route:clear

# Generate key
docker-compose exec erp_app php artisan key:generate

# Create resources
docker-compose exec erp_app php artisan make:controller HomeController
docker-compose exec erp_app php artisan make:model Product -m
docker-compose exec erp_app php artisan make:migration create_products_table
docker-compose exec erp_app php artisan make:seeder ProductSeeder

# View routes
docker-compose exec erp_app php artisan route:list

# Enter Tinker
docker-compose exec erp_app php artisan tinker

# Queue workers
docker-compose exec erp_app php artisan queue:work
```

---

## Composer Commands

```bash
# Install dependencies
docker-compose exec erp_app composer install

# Update dependencies
docker-compose exec erp_app composer update

# Add package
docker-compose exec erp_app composer require vendor/package

# Remove package
docker-compose exec erp_app composer remove vendor/package

# Dump autoload
docker-compose exec erp_app composer dump-autoload
```

---

## Database Operations

### Access MySQL CLI
```bash
# As laravel_user
docker-compose exec erp_db mysql -u laravel_user -p
# Password: laravel_secure_pass_2025

# As root
docker-compose exec erp_db mysql -u root -p
# Password: root_secure_password_2025
```

### Backup Database
```bash
docker-compose exec erp_db mysqldump -u root -proot_secure_password_2025 laravel_erp > backup_$(date +%Y%m%d).sql
```

### Restore Database
```bash
docker-compose exec -T erp_db mysql -u root -proot_secure_password_2025 laravel_erp < backup_20251105.sql
```

### Check Database Connection
```bash
docker-compose exec erp_app php artisan migrate:status
```

---

## File Permissions

### Fix Laravel Permissions
```bash
sudo chown -R $USER:$USER app/
chmod -R 775 app/storage
chmod -R 775 app/bootstrap/cache
```

### Fix Permissions Inside Container
```bash
docker-compose exec erp_app sh -c "chown -R www:www /var/www/html"
docker-compose exec erp_app sh -c "chmod -R 775 storage bootstrap/cache"
```

---

## Container Management

### Check Container Status
```bash
docker-compose ps
```

### Access Container Shell
```bash
# PHP container
docker-compose exec erp_app sh

# Nginx container
docker-compose exec erp_web sh

# MySQL container
docker-compose exec erp_db bash
```

### View Resource Usage
```bash
docker stats
```

### Rebuild Containers
```bash
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

---

## Troubleshooting

### Check Container Logs
```bash
docker-compose logs -f
```

### Restart Specific Container
```bash
docker-compose restart erp_app
docker-compose restart erp_web
docker-compose restart erp_db
```

### Remove and Rebuild Everything
```bash
docker-compose down -v
docker-compose build --no-cache
docker-compose up -d
```

### Check .env Configuration
```bash
cat app/.env | grep DB_
```

### Test PHP Version
```bash
docker-compose exec erp_app php -v
```

### Test PHP Extensions
```bash
docker-compose exec erp_app php -m
```

---

## Network Testing

### Get Server IP
```bash
hostname -I | awk '{print $1}'
```

### Test Port Availability
```bash
sudo lsof -i :8085
sudo netstat -tulpn | grep 8085
```

### Test Application Response
```bash
curl http://localhost:8085
```

---

## Complete Reset (Nuclear Option)

**WARNING: This will delete all data!**

```bash
# Stop and remove everything
cd /var/www/erp
docker-compose down -v

# Remove all related Docker resources
docker volume rm erp_mysql_data
docker network rm erpnet

# Clear Laravel app (backup first!)
rm -rf app/*

# Start fresh installation
./SETUP.sh
```

---

## Installation Verification Checklist

After setup, verify:

```bash
# 1. Check containers are running
docker-compose ps
# All containers should show "Up" status

# 2. Test web access
curl -I http://localhost:8085
# Should return HTTP 200 OK

# 3. Check Laravel version
docker-compose exec erp_app php artisan --version

# 4. Test database connection
docker-compose exec erp_app php artisan migrate:status

# 5. View application in browser
echo "Visit: http://$(hostname -I | awk '{print $1}'):8085"
```

---

## Credentials Reference

### Database Credentials
```
Host: erp_db (or localhost:3307 from host machine)
Database: laravel_erp
Username: laravel_user
Password: laravel_secure_pass_2025
Root Password: root_secure_password_2025
```

### Container Names
```
PHP-FPM: erp_app
Nginx: erp_web
MySQL: erp_db
Composer: erp_composer
```

### Network
```
Name: erpnet
```

### Ports
```
Web Server: 8085
MySQL: 3307 (external), 3306 (internal)
PHP-FPM: 9000 (internal only)
```

---

## Useful One-Liners

```bash
# View real-time logs
docker-compose logs -f --tail=100

# Execute multiple artisan commands
docker-compose exec erp_app sh -c "php artisan config:clear && php artisan cache:clear && php artisan view:clear"

# Quick restart
docker-compose restart && docker-compose logs -f

# Check disk usage
docker system df

# Clean unused Docker resources
docker system prune -a

# Get container IP addresses
docker-compose exec erp_app hostname -i
docker-compose exec erp_db hostname -i

# Monitor all containers
watch -n 1 'docker-compose ps'
```

---

## Environment URLs

**Replace `YOUR_SERVER_IP` with actual IP**

```
Application: http://YOUR_SERVER_IP:8085
```

Get server IP:
```bash
hostname -I | awk '{print $1}'
```
