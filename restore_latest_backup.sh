#!/bin/bash

###############################################################################
# Database Restore Script - ERP System
# Restores the most recent backup from /var/www/erp/backups/database/
###############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
BACKUP_BASE_DIR="/var/www/erp/backups/database"
DB_CONTAINER="erp_db"
DB_NAME="laravel_erp"
DB_USER="root"

# Load environment variables
if [ -f "/var/www/erp/app/.env" ]; then
    export $(grep -v '^#' /var/www/erp/app/.env | grep DB_ROOT_PASSWORD | xargs)
fi

echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}   ERP Database Restore Script${NC}"
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

# Function to list available backups
list_backups() {
    echo -e "${GREEN}Available backup types:${NC}"
    echo "1. Daily backups (last 7 days)"
    echo "2. Weekly backups (last 4 weeks)"
    echo "3. Monthly backups (last 12 months)"
    echo "4. Manual backups"
    echo ""
}

# Function to find latest backup
find_latest_backup() {
    local backup_type=$1
    local latest_backup=""

    case $backup_type in
        daily|1)
            latest_backup=$(ls -t ${BACKUP_BASE_DIR}/daily/*.sql.gz 2>/dev/null | head -1)
            ;;
        weekly|2)
            latest_backup=$(ls -t ${BACKUP_BASE_DIR}/weekly/*.sql.gz 2>/dev/null | head -1)
            ;;
        monthly|3)
            latest_backup=$(ls -t ${BACKUP_BASE_DIR}/monthly/*.sql.gz 2>/dev/null | head -1)
            ;;
        manual|4)
            latest_backup=$(ls -t ${BACKUP_BASE_DIR}/manual/*.sql.gz 2>/dev/null | head -1)
            ;;
        *)
            # Find absolute latest from all directories
            latest_backup=$(ls -t ${BACKUP_BASE_DIR}/*/*.sql.gz 2>/dev/null | head -1)
            ;;
    esac

    echo "$latest_backup"
}

# Function to restore backup
restore_backup() {
    local backup_file=$1

    if [ ! -f "$backup_file" ]; then
        echo -e "${RED}✗ Backup file not found: $backup_file${NC}"
        exit 1
    fi

    # Get file info
    local file_size=$(du -h "$backup_file" | cut -f1)
    local file_date=$(stat -c %y "$backup_file" | cut -d'.' -f1)

    echo -e "${YELLOW}Backup Information:${NC}"
    echo "  File: $(basename $backup_file)"
    echo "  Size: $file_size"
    echo "  Date: $file_date"
    echo "  Path: $backup_file"
    echo ""

    # Verify gzip integrity
    echo -e "${YELLOW}Verifying backup integrity...${NC}"
    if ! gzip -t "$backup_file" 2>/dev/null; then
        echo -e "${RED}✗ Backup file is corrupted!${NC}"
        exit 1
    fi
    echo -e "${GREEN}✓ Backup file is valid${NC}"
    echo ""

    # Confirm restore
    echo -e "${RED}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${RED}   ⚠️  WARNING: This will OVERWRITE your current database!${NC}"
    echo -e "${RED}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
    read -p "Are you sure you want to continue? (yes/no): " confirm

    if [ "$confirm" != "yes" ]; then
        echo -e "${YELLOW}Restore cancelled.${NC}"
        exit 0
    fi

    echo ""
    echo -e "${YELLOW}Starting database restore...${NC}"

    # Create pre-restore backup
    echo -e "${YELLOW}Creating safety backup of current database...${NC}"
    local safety_backup="/var/www/erp/backups/database/manual/pre_restore_$(date +%Y%m%d_%H%M%S).sql.gz"
    docker exec ${DB_CONTAINER} mysqldump -u ${DB_USER} -p${DB_ROOT_PASSWORD} \
        --ssl=0 --no-tablespaces --single-transaction ${DB_NAME} 2>/dev/null | gzip > "$safety_backup"

    if [ -f "$safety_backup" ]; then
        echo -e "${GREEN}✓ Safety backup created: $(basename $safety_backup)${NC}"
    else
        echo -e "${YELLOW}⚠ Could not create safety backup (continuing anyway)${NC}"
    fi

    # Stop services to prevent data corruption
    echo -e "${YELLOW}Stopping application services...${NC}"
    docker-compose -f /var/www/erp/docker-compose.yml stop erp_app erp_queue erp_scheduler 2>/dev/null || true

    # Restore database
    echo -e "${YELLOW}Restoring database from backup...${NC}"
    if gunzip -c "$backup_file" | docker exec -i ${DB_CONTAINER} mysql -u ${DB_USER} -p${DB_ROOT_PASSWORD} ${DB_NAME} 2>/dev/null; then
        echo -e "${GREEN}✓ Database restored successfully!${NC}"
    else
        echo -e "${RED}✗ Database restore failed!${NC}"
        echo -e "${YELLOW}Attempting to restore safety backup...${NC}"
        if [ -f "$safety_backup" ]; then
            gunzip -c "$safety_backup" | docker exec -i ${DB_CONTAINER} mysql -u ${DB_USER} -p${DB_ROOT_PASSWORD} ${DB_NAME} 2>/dev/null
            echo -e "${GREEN}✓ Original database restored from safety backup${NC}"
        fi
        exit 1
    fi

    # Start services
    echo -e "${YELLOW}Starting application services...${NC}"
    docker-compose -f /var/www/erp/docker-compose.yml start erp_app erp_queue erp_scheduler 2>/dev/null

    # Clear cache
    echo -e "${YELLOW}Clearing application cache...${NC}"
    docker exec erp_app php artisan cache:clear 2>/dev/null || true
    docker exec erp_app php artisan config:clear 2>/dev/null || true

    echo ""
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${GREEN}   ✓ Restore completed successfully!${NC}"
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
    echo "Safety backup location: $safety_backup"
    echo "You can now access your application at: https://intern.simplead.ro"
}

# Main script
main() {
    # Check if running as root or with sudo
    if [ "$EUID" -ne 0 ] && ! docker ps >/dev/null 2>&1; then
        echo -e "${RED}This script requires root privileges or Docker access.${NC}"
        echo "Please run with sudo: sudo ./restore_latest_backup.sh"
        exit 1
    fi

    # Check if Docker is running
    if ! docker ps >/dev/null 2>&1; then
        echo -e "${RED}Docker is not running or you don't have permission to access it.${NC}"
        exit 1
    fi

    # Check if database container is running
    if ! docker ps | grep -q ${DB_CONTAINER}; then
        echo -e "${RED}Database container (${DB_CONTAINER}) is not running.${NC}"
        echo "Start it with: docker-compose up -d erp_db"
        exit 1
    fi

    # Check if DB_ROOT_PASSWORD is set
    if [ -z "$DB_ROOT_PASSWORD" ]; then
        echo -e "${YELLOW}DB_ROOT_PASSWORD not found in .env${NC}"
        read -sp "Enter MySQL root password: " DB_ROOT_PASSWORD
        export DB_ROOT_PASSWORD
        echo ""
    fi

    # Show menu
    list_backups

    if [ -z "$1" ]; then
        read -p "Select backup type (1-4) or press Enter for latest from all: " choice
    else
        choice=$1
    fi

    # Find backup
    echo ""
    echo -e "${YELLOW}Searching for backup...${NC}"
    backup_file=$(find_latest_backup "$choice")

    if [ -z "$backup_file" ]; then
        echo -e "${RED}No backups found!${NC}"
        exit 1
    fi

    # Restore backup
    restore_backup "$backup_file"
}

# Run main function
main "$@"
