# Laravel ERP Docker Setup

Complete Docker orchestration setup for Laravel 10/11 ERP application on Ubuntu.

## System Requirements

- Ubuntu 20.04+ or compatible Linux distribution
- Docker 20.10+
- Docker Compose 2.0+
- Minimum 2GB RAM
- Minimum 10GB disk space

## Quick Start

### Option 1: Automated Setup (Recommended)

```bash
cd /var/www/erp
./SETUP.sh
```

The script will automatically:
1. Verify Docker installation
2. Create directory structure
3. Build Docker images
4. Install Laravel (if not present)
5. Configure environment
6. Start all containers
7. Initialize Laravel application

### Option 2: Manual Setup

Follow the step-by-step commands below for manual installation.

## Architecture

This setup includes 3 main containers:

- **erp_db** - MySQL 8.0 database server
- **erp_app** - PHP 8.3 FPM application server
- **erp_web** - Nginx web server (port 8085)
- **erp_composer** - One-time Composer utility container

All containers run on the `erpnet` Docker network.

## Manual Installation Steps

### Step 1: Verify Docker Installation

```bash
# Check Docker version
docker --version

# Check Docker Compose version
docker-compose --version

# Test Docker access
docker ps

# If needed, add user to docker group
sudo usermod -aG docker $USER
# Then log out and log back in
```

### Step 2: Create Project Structure

```bash
# Navigate to project root
cd /var/www/erp

# Create necessary directories
mkdir -p app
mkdir -p docker/php
mkdir -p docker/nginx
mkdir -p mysql
mkdir -p logs/nginx

# Verify structure
tree -L 2
```

### Step 3: Verify Configuration Files

All configuration files should be in place:

```bash
# Check all required files exist
ls -la docker-compose.yml
ls -la docker/php/Dockerfile
ls -la docker/php/php.ini
ls -la docker/php/www.conf
ls -la docker/nginx/default.conf
ls -la mysql/my.cnf
ls -la .env.example
```

### Step 4: Build Docker Images

```bash
# Build the PHP-FPM image
docker-compose build erp_app

# Verify image was created
docker images | grep erp
```

### Step 5: Install Laravel

**If app directory is empty**, install Laravel:

```bash
# Install Laravel 11 using Composer container
docker-compose run --rm erp_composer create-project laravel/laravel . --prefer-dist

# Set proper permissions
sudo chown -R $USER:$USER app/
chmod -R 775 app/storage
chmod -R 775 app/bootstrap/cache
```

**If Laravel is already installed**, skip to Step 6.

### Step 6: Configure Laravel Environment

```bash
# Copy environment file
cp app/.env.example app/.env

# Edit database configuration
nano app/.env
```

Update the following values in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=erp_db
DB_PORT=3306
DB_DATABASE=laravel_erp
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_secure_pass_2025
```

### Step 7: Start Docker Containers

```bash
# Start all containers in detached mode
docker-compose up -d

# Check container status
docker-compose ps

# View logs
docker-compose logs -f
```

Expected output:
```
NAME        IMAGE            STATUS         PORTS
erp_db      mysql:8.0        Up (healthy)   0.0.0.0:3307->3306/tcp
erp_app     erp_app:latest   Up             9000/tcp
erp_web     nginx:latest     Up             0.0.0.0:8085->80/tcp
```

### Step 8: Initialize Laravel Application

```bash
# Generate application key
docker-compose exec erp_app php artisan key:generate

# Clear all caches
docker-compose exec erp_app php artisan config:clear
docker-compose exec erp_app php artisan cache:clear
docker-compose exec erp_app php artisan view:clear

# Run database migrations
docker-compose exec erp_app php artisan migrate

# Check Laravel version
docker-compose exec erp_app php artisan --version
```

### Step 9: Set Proper Permissions

```bash
# Set ownership
sudo chown -R $USER:$USER app/

# Set storage permissions
chmod -R 775 app/storage
chmod -R 775 app/bootstrap/cache
```

### Step 10: Access Application

Open your browser and navigate to:

```
http://YOUR_SERVER_IP:8085
```

Or from the server itself:

```
http://localhost:8085
```

You should see the Laravel welcome page.

## Docker Commands Reference

### Container Management

```bash
# Start all containers
docker-compose up -d

# Stop all containers
docker-compose down

# Restart all containers
docker-compose restart

# Restart specific container
docker-compose restart erp_app

# View container logs
docker-compose logs -f
docker-compose logs -f erp_app
docker-compose logs -f erp_web
docker-compose logs -f erp_db

# Check container status
docker-compose ps

# View resource usage
docker stats
```

### Laravel Artisan Commands

```bash
# Run any artisan command
docker-compose exec erp_app php artisan [command]

# Examples:
docker-compose exec erp_app php artisan migrate
docker-compose exec erp_app php artisan db:seed
docker-compose exec erp_app php artisan make:controller UserController
docker-compose exec erp_app php artisan make:model Product -m
docker-compose exec erp_app php artisan route:list
docker-compose exec erp_app php artisan tinker
```

### Composer Commands

```bash
# Run composer commands
docker-compose exec erp_app composer [command]

# Examples:
docker-compose exec erp_app composer install
docker-compose exec erp_app composer update
docker-compose exec erp_app composer require vendor/package
docker-compose exec erp_app composer dump-autoload
```

### Database Access

```bash
# Access MySQL CLI as laravel_user
docker-compose exec erp_db mysql -u laravel_user -p
# Password: laravel_secure_pass_2025

# Access MySQL CLI as root
docker-compose exec erp_db mysql -u root -p
# Password: root_secure_password_2025

# Export database
docker-compose exec erp_db mysqldump -u laravel_user -p laravel_erp > backup.sql

# Import database
docker-compose exec -T erp_db mysql -u laravel_user -p laravel_erp < backup.sql
```

### Shell Access

```bash
# Access PHP container shell
docker-compose exec erp_app sh

# Access Nginx container shell
docker-compose exec erp_web sh

# Access MySQL container shell
docker-compose exec erp_db bash
```

### File Permissions

```bash
# Fix Laravel permissions
docker-compose exec erp_app sh -c "chmod -R 775 storage bootstrap/cache"
docker-compose exec erp_app sh -c "chown -R www:www storage bootstrap/cache"
```

## Configuration Details

### Port Mappings

- **8085:80** - Web server (Nginx)
- **3307:3306** - MySQL database

### Volume Mounts

- `./app` → `/var/www/html` (Laravel application)
- `erp_mysql_data` → `/var/lib/mysql` (MySQL persistent data)

### Environment Variables

Database credentials (configured in docker-compose.yml):

```
MYSQL_DATABASE=laravel_erp
MYSQL_USER=laravel_user
MYSQL_PASSWORD=laravel_secure_pass_2025
MYSQL_ROOT_PASSWORD=root_secure_password_2025
```

## Troubleshooting

### Container won't start

```bash
# Check logs
docker-compose logs -f erp_app

# Rebuild containers
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Database connection errors

```bash
# Check if database is healthy
docker-compose ps erp_db

# Test database connection
docker-compose exec erp_app php artisan migrate:status

# Verify .env file
cat app/.env | grep DB_
```

### Permission errors

```bash
# Fix ownership
sudo chown -R $USER:$USER app/

# Fix Laravel permissions
chmod -R 775 app/storage
chmod -R 775 app/bootstrap/cache

# Inside container
docker-compose exec erp_app sh -c "chown -R www:www /var/www/html"
```

### Port 8085 already in use

```bash
# Check what's using the port
sudo lsof -i :8085

# Edit docker-compose.yml and change the port
nano docker-compose.yml
# Change "8085:80" to another port like "8086:80"
```

### Clear all Docker resources

```bash
# Stop and remove containers
docker-compose down -v

# Remove images
docker rmi $(docker images | grep erp | awk '{print $3}')

# Remove volumes
docker volume rm erp_mysql_data
```

## Performance Optimization

### PHP-FPM Tuning

Edit [docker/php/www.conf](docker/php/www.conf):

```ini
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
```

### MySQL Tuning

Edit [mysql/my.cnf](mysql/my.cnf):

```ini
innodb_buffer_pool_size = 512M
max_connections = 200
```

### Nginx Caching

Already configured in [docker/nginx/default.conf](docker/nginx/default.conf) for static assets.

## Security Recommendations

1. **Change default passwords** in docker-compose.yml
2. **Set APP_DEBUG=false** in production
3. **Use HTTPS** with SSL certificates (configure Nginx)
4. **Restrict database access** (firewall rules)
5. **Regular backups** of database and application
6. **Keep Docker images updated**

## Backup and Restore

### Backup

```bash
# Backup database
docker-compose exec erp_db mysqldump -u root -proot_secure_password_2025 laravel_erp > backup_$(date +%Y%m%d).sql

# Backup application files
tar -czf app_backup_$(date +%Y%m%d).tar.gz app/

# Backup entire project
tar -czf erp_full_backup_$(date +%Y%m%d).tar.gz \
    docker-compose.yml \
    docker/ \
    mysql/ \
    app/ \
    --exclude=app/node_modules \
    --exclude=app/vendor
```

### Restore

```bash
# Restore database
docker-compose exec -T erp_db mysql -u root -proot_secure_password_2025 laravel_erp < backup_20251105.sql

# Restore application
tar -xzf app_backup_20251105.tar.gz

# Run migrations after restore
docker-compose exec erp_app php artisan migrate:status
```

## Maintenance

### Update Laravel

```bash
docker-compose exec erp_app composer update
docker-compose exec erp_app php artisan migrate
docker-compose exec erp_app php artisan config:clear
```

### Update Docker Images

```bash
docker-compose pull
docker-compose build --no-cache
docker-compose up -d
```

## Directory Structure

```
/var/www/erp/
├── app/                    # Laravel application
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── tests/
│   ├── .env
│   └── artisan
├── docker/
│   ├── nginx/
│   │   └── default.conf   # Nginx virtual host
│   └── php/
│       ├── Dockerfile      # PHP 8.3 FPM image
│       ├── php.ini         # PHP configuration
│       └── www.conf        # PHP-FPM pool config
├── logs/
│   └── nginx/              # Nginx logs
├── mysql/
│   └── my.cnf              # MySQL configuration
├── docker-compose.yml      # Main orchestration file
├── .env.example            # Environment example
├── SETUP.sh                # Automated setup script
└── README.md               # This file
```

## Support

For issues and questions:
- Check logs: `docker-compose logs -f`
- Laravel docs: https://laravel.com/docs
- Docker docs: https://docs.docker.com

## License

This Docker setup is provided as-is for Laravel ERP development.
