# Laravel ERP - Docker Architecture

## System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────────┐
│                           HOST MACHINE (Ubuntu)                         │
│                              /var/www/erp                               │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌───────────────────────────────────────────────────────────────┐    │
│  │                    Docker Network: erpnet                     │    │
│  │                      (Bridge Network)                         │    │
│  │                                                               │    │
│  │  ┌──────────────┐      ┌──────────────┐      ┌────────────┐ │    │
│  │  │              │      │              │      │            │ │    │
│  │  │   erp_web    │      │   erp_app    │      │   erp_db   │ │    │
│  │  │              │      │              │      │            │ │    │
│  │  │  Nginx       │─────▶│  PHP 8.3     │─────▶│  MySQL 8.0 │ │    │
│  │  │  (Latest)    │      │  FPM Alpine  │      │            │ │    │
│  │  │              │      │              │      │            │ │    │
│  │  │  Port: 80    │      │  Port: 9000  │      │ Port: 3306 │ │    │
│  │  │              │      │              │      │            │ │    │
│  │  └──────┬───────┘      └──────┬───────┘      └─────┬──────┘ │    │
│  │         │                     │                    │        │    │
│  └─────────┼─────────────────────┼────────────────────┼────────┘    │
│            │                     │                    │             │
│            │                     │                    │             │
│       Port 8085            Volume Mount          Volume Mount       │
│            │              ./app ──▶ /var/www/html                   │
│            │              configs                Named Volume        │
│            │                     │              erp_mysql_data      │
│            ▼                     ▼                    │             │
│  ┌─────────────────┐   ┌─────────────────┐    ┌──────────────┐    │
│  │  Host Port      │   │  App Directory  │    │  Persistent  │    │
│  │  0.0.0.0:8085   │   │  ./app/         │    │  DB Storage  │    │
│  │                 │   │                 │    │              │    │
│  │  Access via:    │   │  Laravel App    │    │  MySQL Data  │    │
│  │  Browser        │   │  Source Code    │    │  (/var/lib/  │    │
│  └─────────────────┘   └─────────────────┘    │   mysql)     │    │
│                                                └──────────────┘    │
│                                                                     │
│  ┌────────────────────────────────────────────────────────────┐   │
│  │              Optional: erp_composer                        │   │
│  │              (One-time use container)                      │   │
│  │              composer:latest                               │   │
│  │              Used for: Laravel installation & dependencies │   │
│  └────────────────────────────────────────────────────────────┘   │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

## Request Flow

```
┌─────────────┐
│   Browser   │
│ (Client)    │
└──────┬──────┘
       │
       │ HTTP Request
       │ http://SERVER_IP:8085
       ▼
┌─────────────────────────────────────────┐
│  Host Machine Port 8085                 │
└──────┬──────────────────────────────────┘
       │
       │ Port Mapping
       │ 8085:80
       ▼
┌─────────────────────────────────────────┐
│  Container: erp_web (Nginx)             │
│  ┌────────────────────────────────┐     │
│  │  Listen on port 80             │     │
│  │  Document root:                │     │
│  │  /var/www/html/public          │     │
│  └────────────┬───────────────────┘     │
│               │                         │
│               │ Static files? Serve     │
│               │ PHP files? Forward ─────┼──┐
└───────────────┼─────────────────────────┘  │
                │                            │
                │                            │
                │                            │
                ▼                            │
         ┌─────────────────────────────────────────┐
         │  Container: erp_app (PHP-FPM)           │
         │  ┌────────────────────────────────┐     │
         │  │  Listen on port 9000           │     │◀─┘
         │  │  FastCGI Process Manager       │     │
         │  │  Execute PHP code              │     │
         │  │  Laravel Framework             │     │
         │  └────────────┬───────────────────┘     │
         │               │                         │
         │               │ Need database?          │
         │               │ Connect via PDO ────────┼──┐
         └───────────────┼─────────────────────────┘  │
                         │                            │
                         │ Response                   │
                         │                            │
                         ▼                            │
                  ┌─────────────────────────────────────────┐
                  │  Container: erp_db (MySQL)              │
                  │  ┌────────────────────────────────┐     │
                  │  │  Listen on port 3306           │     │◀─┘
                  │  │  Database: laravel_erp         │     │
                  │  │  Execute SQL queries           │     │
                  │  │  Return results                │     │
                  │  └────────────────────────────────┘     │
                  │                                         │
                  │  Persistent Storage:                    │
                  │  erp_mysql_data volume                  │
                  └─────────────────────────────────────────┘
```

## Container Details

### erp_web (Nginx Web Server)

**Image:** `nginx:latest`

**Purpose:** Web server and reverse proxy

**Exposed Ports:**
- Host: `8085` → Container: `80`

**Volume Mounts:**
- `./app:/var/www/html` - Laravel application files
- `./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf` - Virtual host config
- `./logs/nginx:/var/log/nginx` - Access and error logs

**Configuration:**
- Document root: `/var/www/html/public`
- FastCGI proxy to `erp_app:9000`
- Static file caching enabled
- Gzip compression enabled
- Security headers configured

**Dependencies:** `erp_app`

---

### erp_app (PHP-FPM Application Server)

**Image:** Custom built from `php:8.3-fpm-alpine`

**Purpose:** Execute PHP code and run Laravel framework

**Exposed Ports:**
- `9000` (internal only, accessed by Nginx)

**Volume Mounts:**
- `./app:/var/www/html` - Laravel application (shared with Nginx)
- `./docker/php/php.ini:/usr/local/etc/php/conf.d/custom.ini` - PHP config
- `./docker/php/www.conf:/usr/local/etc/php-fpm.d/www.conf` - FPM pool config

**Installed Extensions:**
- PDO, PDO_MySQL, MySQLi
- mbstring, exif, pcntl, bcmath
- GD (with JPEG and FreeType)
- ZIP, XML, SOAP, intl
- OPcache (performance)
- Redis (optional caching)

**Additional Tools:**
- Composer (dependency management)
- Git, Curl
- MySQL client

**User:** `www` (UID 1000, GID 1000)

**Dependencies:** `erp_db` (with health check)

---

### erp_db (MySQL Database Server)

**Image:** `mysql:8.0`

**Purpose:** Persistent data storage

**Exposed Ports:**
- Host: `3307` → Container: `3306` (avoid conflict with local MySQL)

**Volume Mounts:**
- `erp_mysql_data:/var/lib/mysql` - Named volume for data persistence
- `./mysql/my.cnf:/etc/mysql/conf.d/my.cnf` - Custom MySQL config

**Environment Variables:**
- `MYSQL_DATABASE=laravel_erp`
- `MYSQL_USER=laravel_user`
- `MYSQL_PASSWORD=laravel_secure_pass_2025`
- `MYSQL_ROOT_PASSWORD=root_secure_password_2025`

**Configuration:**
- Character set: `utf8mb4`
- Collation: `utf8mb4_unicode_ci`
- InnoDB buffer pool: 512MB
- Max connections: 200
- Slow query log enabled

**Health Check:**
- Command: `mysqladmin ping`
- Interval: 10s
- Timeout: 5s
- Retries: 5
- Start period: 30s

**Dependencies:** None (base service)

---

### erp_composer (Composer Utility)

**Image:** `composer:latest`

**Purpose:** Dependency management and Laravel installation

**Usage:** One-time execution only

**Volume Mounts:**
- `./app:/app` - Laravel application directory

**Profiles:** `tools` (won't start with `docker-compose up`)

**Common Commands:**
```bash
# Install Laravel
docker-compose run --rm erp_composer create-project laravel/laravel .

# Install dependencies
docker-compose run --rm erp_composer install

# Update dependencies
docker-compose run --rm erp_composer update
```

---

## Network Configuration

**Network Name:** `erpnet`

**Driver:** Bridge

**Subnet:** Auto-assigned by Docker

**Container Connectivity:**
- All containers can communicate using container names as hostnames
- Example: `erp_app` connects to `erp_db:3306`
- Isolated from other Docker networks

---

## Volume Configuration

### Named Volumes

**erp_mysql_data**
- Driver: local
- Purpose: Persistent MySQL data storage
- Location: `/var/lib/docker/volumes/erp_mysql_data/_data`
- Survives container removal

### Bind Mounts

**Application Directory**
- Host: `/var/www/erp/app`
- Container: `/var/www/html`
- Shared between: `erp_app`, `erp_web`

**Configuration Files**
- Individual file mounts for configs
- Allows hot-reload of configuration changes

---

## File Structure Mapping

```
Host: /var/www/erp/
│
├── app/                           ──▶  /var/www/html (in erp_app, erp_web)
│   ├── app/                            Laravel app logic
│   ├── bootstrap/                      Application bootstrap
│   ├── config/                         Configuration files
│   ├── database/                       Migrations, seeders
│   ├── public/                         Web root (Nginx points here)
│   ├── resources/                      Views, assets
│   ├── routes/                         Route definitions
│   ├── storage/                        Logs, cache, uploads
│   ├── .env                            Environment config
│   └── artisan                         CLI tool
│
├── docker/
│   ├── nginx/
│   │   └── default.conf           ──▶  /etc/nginx/conf.d/default.conf (in erp_web)
│   └── php/
│       ├── Dockerfile                  Build instructions for erp_app
│       ├── php.ini                ──▶  /usr/local/etc/php/conf.d/custom.ini (in erp_app)
│       └── www.conf               ──▶  /usr/local/etc/php-fpm.d/www.conf (in erp_app)
│
├── mysql/
│   └── my.cnf                     ──▶  /etc/mysql/conf.d/my.cnf (in erp_db)
│
├── logs/
│   └── nginx/                     ──▶  /var/log/nginx (in erp_web)
│
├── docker-compose.yml                  Orchestration configuration
├── .env.example                        Example environment variables
├── SETUP.sh                            Automated setup script
├── README.md                           Full documentation
├── QUICK_REFERENCE.md                  Command reference
└── ARCHITECTURE.md                     This file
```

---

## Security Architecture

### Network Security
- All containers isolated in private bridge network
- Only Nginx port (8085) exposed to host
- Database accessible from host on 3307 for admin tasks

### File Permissions
- PHP-FPM runs as `www` user (UID 1000)
- Application files owned by host user
- Storage and cache directories: 775 permissions

### Configuration Security
- Environment variables for sensitive data
- No hardcoded credentials in application
- `.env` file excluded from version control

### Web Server Security
- Direct access to `.env`, `.git` blocked
- X-Frame-Options header enabled
- X-Content-Type-Options enabled
- X-XSS-Protection enabled

---

## Performance Optimizations

### PHP-FPM
- Process manager: Dynamic
- Max children: 50
- Min spare servers: 5
- Max spare servers: 20
- OPcache enabled

### MySQL
- InnoDB buffer pool: 512MB
- Query cache disabled (MySQL 8.0 default)
- Slow query log enabled

### Nginx
- Static file caching (365 days)
- Gzip compression enabled
- FastCGI caching possible (not enabled by default)

### Laravel
- Config caching available
- Route caching available
- View compilation caching
- OPcache for PHP bytecode

---

## Scalability Considerations

### Horizontal Scaling
To scale horizontally, you can:
1. Run multiple `erp_app` containers
2. Use Nginx as load balancer
3. Share sessions via Redis
4. Use external database server

### Vertical Scaling
Adjust resources in docker-compose.yml:
```yaml
services:
  erp_app:
    deploy:
      resources:
        limits:
          cpus: '2'
          memory: 2G
```

---

## Monitoring & Logging

### Container Logs
```bash
docker-compose logs -f              # All containers
docker-compose logs -f erp_app      # PHP-FPM logs
docker-compose logs -f erp_web      # Nginx access logs
docker-compose logs -f erp_db       # MySQL logs
```

### Application Logs
- Laravel logs: `./app/storage/logs/laravel.log`
- Nginx access: `./logs/nginx/access.log`
- Nginx errors: `./logs/nginx/error.log`

### Resource Monitoring
```bash
docker stats                        # Real-time resource usage
docker-compose ps                   # Container status
```

---

## Backup Strategy

### What to Backup
1. **Database** (erp_mysql_data volume)
2. **Application files** (./app directory)
3. **Uploaded files** (./app/storage/app)
4. **Configuration** (docker-compose.yml, configs)

### Automated Backup Script
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
docker-compose exec erp_db mysqldump -u root -p laravel_erp > backup_${DATE}.sql
tar -czf app_backup_${DATE}.tar.gz app/
```

---

## Production Considerations

For production deployment, consider:

1. **SSL/TLS Certificate**
   - Use Let's Encrypt
   - Configure Nginx for HTTPS
   - Redirect HTTP to HTTPS

2. **Environment Variables**
   - Change all default passwords
   - Set `APP_DEBUG=false`
   - Set `APP_ENV=production`

3. **Performance**
   - Enable OPcache aggressively
   - Use Redis for sessions/cache
   - Configure CDN for static assets

4. **Monitoring**
   - Add application monitoring (New Relic, etc.)
   - Set up log aggregation
   - Configure alerts

5. **Backups**
   - Automated daily backups
   - Off-site backup storage
   - Regular restore testing

6. **Updates**
   - Regular security updates
   - Dependency updates
   - Database maintenance

---

## Technology Stack Summary

| Component | Technology | Version | Purpose |
|-----------|-----------|---------|---------|
| Web Server | Nginx | Latest | Serve static files, reverse proxy |
| Application | PHP-FPM | 8.3 | Execute PHP code |
| Framework | Laravel | 10/11 | Web application framework |
| Database | MySQL | 8.0 | Persistent data storage |
| Dependency Manager | Composer | Latest | PHP package management |
| Orchestration | Docker Compose | 3.8 | Container orchestration |
| Container Platform | Docker | 20.10+ | Containerization |

---

## Port Reference

| Service | Internal Port | External Port | Protocol | Access |
|---------|--------------|---------------|----------|--------|
| Nginx | 80 | 8085 | HTTP | Public |
| PHP-FPM | 9000 | - | FastCGI | Internal only |
| MySQL | 3306 | 3307 | TCP | Admin access |

---

## Credential Reference

| Service | Username | Password | Database |
|---------|----------|----------|----------|
| MySQL User | laravel_user | laravel_secure_pass_2025 | laravel_erp |
| MySQL Root | root | root_secure_password_2025 | all |

**⚠️ IMPORTANT:** Change these passwords in production!

---

## Next Steps

1. Review and understand this architecture
2. Run `./SETUP.sh` to deploy
3. Access application at `http://YOUR_SERVER_IP:8085`
4. Start developing your Laravel ERP application
5. Customize as needed for your requirements

---

**Last Updated:** 2025-11-05
**Documentation Version:** 1.0
