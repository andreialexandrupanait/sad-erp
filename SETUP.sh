#!/bin/bash

# ============================================================================
# Laravel ERP Docker Setup Script
# This script sets up and deploys a Laravel 10/11 application using Docker
# Environment: Ubuntu with Docker and Docker Compose installed
# ============================================================================

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_ROOT="/var/www/erp"
APP_DIR="${PROJECT_ROOT}/app"
DOCKER_DIR="${PROJECT_ROOT}/docker"

# ============================================================================
# Helper Functions
# ============================================================================

print_header() {
    echo -e "\n${BLUE}============================================================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}============================================================================${NC}\n"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}ℹ $1${NC}"
}

# ============================================================================
# Step 1: Verify Prerequisites
# ============================================================================

print_header "Step 1: Verifying Prerequisites"

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    print_error "Docker is not installed. Please install Docker first."
    echo "Install with: curl -fsSL https://get.docker.com | sh"
    exit 1
else
    print_success "Docker is installed: $(docker --version)"
fi

# Check if Docker Compose (v1 or v2) is installed
if command -v docker-compose &> /dev/null; then
    COMPOSE_CMD="docker-compose"
    print_success "Docker Compose (v1) is installed: $($COMPOSE_CMD --version)"
elif docker compose version &> /dev/null; then
    COMPOSE_CMD="docker compose"
    print_success "Docker Compose (v2) is installed: $($COMPOSE_CMD version)"
else
    print_error "Docker Compose is not installed. Please install Docker Compose first."
    echo "Install with: sudo apt-get install docker-compose-plugin"
    exit 1
fi

# Check if current user can run Docker
if ! docker ps &> /dev/null; then
    print_error "Current user cannot run Docker commands."
    echo "Add user to docker group: sudo usermod -aG docker \$USER"
    echo "Then log out and log back in."
    exit 1
else
    print_success "Docker permissions are correct"
fi

# ============================================================================
# Step 2: Create Directory Structure
# ============================================================================

print_header "Step 2: Creating Directory Structure"

cd "${PROJECT_ROOT}"

# Create necessary directories
mkdir -p "${APP_DIR}"
mkdir -p "${DOCKER_DIR}/php"
mkdir -p "${DOCKER_DIR}/nginx"
mkdir -p "${PROJECT_ROOT}/mysql"
mkdir -p "${PROJECT_ROOT}/logs/nginx"

print_success "Directory structure created"

# Set proper permissions
chmod -R 755 "${PROJECT_ROOT}"
print_success "Permissions set correctly"

# ============================================================================
# Step 3: Check if Laravel is Already Installed
# ============================================================================

print_header "Step 3: Checking Laravel Installation"

if [ -f "${APP_DIR}/artisan" ]; then
    print_info "Laravel is already installed in ${APP_DIR}"
    INSTALL_LARAVEL=false
else
    print_info "Laravel is not installed. Will install Laravel 11"
    INSTALL_LARAVEL=true
fi

# ============================================================================
# Step 4: Build Docker Images
# ============================================================================

print_header "Step 4: Building Docker Images"

cd "${PROJECT_ROOT}"

# Build PHP-FPM image
print_info "Building PHP 8.3 FPM image (this may take a few minutes)..."
$COMPOSE_CMD build erp_app

print_success "Docker images built successfully"

# ============================================================================
# Step 5: Install Laravel (if needed)
# ============================================================================

if [ "$INSTALL_LARAVEL" = true ]; then
    print_header "Step 5: Installing Laravel 11"

    # Ensure app directory is empty
    if [ "$(ls -A ${APP_DIR})" ]; then
        print_error "App directory is not empty. Please backup and clear ${APP_DIR}"
        exit 1
    fi

    print_info "Installing Laravel via Composer (this may take several minutes)..."

    # Install Laravel using Composer container
    $COMPOSE_CMD run --rm erp_composer create-project laravel/laravel . --prefer-dist

    print_success "Laravel installed successfully"

    # Set proper permissions
    print_info "Setting Laravel permissions..."
    sudo chown -R $(whoami):$(whoami) "${APP_DIR}"
    chmod -R 775 "${APP_DIR}/storage"
    chmod -R 775 "${APP_DIR}/bootstrap/cache"

    print_success "Permissions set correctly"
else
    print_header "Step 5: Skipping Laravel Installation"
    print_info "Using existing Laravel installation"
fi

# ============================================================================
# Step 6: Configure Laravel Environment
# ============================================================================

print_header "Step 6: Configuring Laravel Environment"

# Copy .env file if it doesn't exist
if [ ! -f "${APP_DIR}/.env" ]; then
    if [ -f "${APP_DIR}/.env.example" ]; then
        cp "${APP_DIR}/.env.example" "${APP_DIR}/.env"
        print_success "Created .env from .env.example"
    else
        print_error ".env.example not found"
    fi
else
    print_info ".env file already exists"
fi

# Update database configuration in .env
print_info "Updating database configuration in .env..."

sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=mysql/' "${APP_DIR}/.env"
sed -i 's/DB_HOST=.*/DB_HOST=erp_db/' "${APP_DIR}/.env"
sed -i 's/DB_PORT=.*/DB_PORT=3306/' "${APP_DIR}/.env"
sed -i 's/DB_DATABASE=.*/DB_DATABASE=laravel_erp/' "${APP_DIR}/.env"
sed -i 's/DB_USERNAME=.*/DB_USERNAME=laravel_user/' "${APP_DIR}/.env"
sed -i 's/DB_PASSWORD=.*/DB_PASSWORD=laravel_secure_pass_2025/' "${APP_DIR}/.env"

print_success "Database configuration updated"

# ============================================================================
# Step 7: Start Docker Containers
# ============================================================================

print_header "Step 7: Starting Docker Containers"

cd "${PROJECT_ROOT}"

# Stop any existing containers
print_info "Stopping any existing containers..."
$COMPOSE_CMD down || true

# Start containers in detached mode
print_info "Starting containers (database, app, web server)..."
$COMPOSE_CMD up -d

# Wait for containers to be healthy
print_info "Waiting for containers to be ready..."
sleep 10

print_success "All containers started successfully"

# ============================================================================
# Step 8: Initialize Laravel Application
# ============================================================================

print_header "Step 8: Initializing Laravel Application"

# Generate application key if not set
if ! grep -q "APP_KEY=base64:" "${APP_DIR}/.env"; then
    print_info "Generating application key..."
    $COMPOSE_CMD exec -T erp_app php artisan key:generate
    print_success "Application key generated"
else
    print_info "Application key already set"
fi

# Clear and cache configuration
print_info "Clearing and caching configuration..."
$COMPOSE_CMD exec -T erp_app php artisan config:clear
$COMPOSE_CMD exec -T erp_app php artisan cache:clear
$COMPOSE_CMD exec -T erp_app php artisan view:clear
print_success "Cache cleared"

# Run database migrations
print_info "Running database migrations..."
$COMPOSE_CMD exec -T erp_app php artisan migrate --force || print_error "Migration failed (database may not be ready yet - try manually)"

print_success "Laravel application initialized"

# ============================================================================
# Step 9: Set Final Permissions
# ============================================================================

print_header "Step 9: Setting Final Permissions"

sudo chown -R $(whoami):$(whoami) "${APP_DIR}"
chmod -R 775 "${APP_DIR}/storage"
chmod -R 775 "${APP_DIR}/bootstrap/cache"

print_success "Final permissions set"

# ============================================================================
# Step 10: Display Summary
# ============================================================================

print_header "Setup Complete!"

echo -e "${GREEN}Your Laravel ERP application is now running!${NC}\n"

echo -e "${YELLOW}Container Status:${NC}"
$COMPOSE_CMD ps

echo -e "\n${YELLOW}Access Information:${NC}"
echo -e "  Application URL: ${GREEN}http://$(hostname -I | awk '{print $1}'):8085${NC}"
echo -e "  Alternative URL: ${GREEN}http://localhost:8085${NC}"
echo -e ""
echo -e "${YELLOW}Database Information:${NC}"
echo -e "  Host: ${GREEN}erp_db${NC} (or localhost:3307 from host)"
echo -e "  Database: ${GREEN}laravel_erp${NC}"
echo -e "  Username: ${GREEN}laravel_user${NC}"
echo -e "  Password: ${GREEN}laravel_secure_pass_2025${NC}"
echo -e "  Root Password: ${GREEN}root_secure_password_2025${NC}"

echo -e "\n${YELLOW}Useful Commands:${NC}"
echo -e "  View logs:           ${GREEN}$COMPOSE_CMD logs -f${NC}"
echo -e "  Stop containers:     ${GREEN}$COMPOSE_CMD down${NC}"
echo -e "  Start containers:    ${GREEN}$COMPOSE_CMD up -d${NC}"
echo -e "  Restart containers:  ${GREEN}$COMPOSE_CMD restart${NC}"
echo -e "  Run artisan:         ${GREEN}$COMPOSE_CMD exec erp_app php artisan [command]${NC}"
echo -e "  Run composer:        ${GREEN}$COMPOSE_CMD exec erp_app composer [command]${NC}"
echo -e "  Access app shell:    ${GREEN}$COMPOSE_CMD exec erp_app sh${NC}"
echo -e "  Access database:     ${GREEN}$COMPOSE_CMD exec erp_db mysql -u laravel_user -p${NC}"

echo -e "\n${YELLOW}Next Steps:${NC}"
echo -e "  1. Open your browser and navigate to ${GREEN}http://$(hostname -I | awk '{print $1}'):8085${NC}"
echo -e "  2. You should see the Laravel welcome page"
echo -e "  3. Start building your ERP application!"

echo -e "\n${BLUE}============================================================================${NC}\n"

print_success "Setup script completed successfully!"
