#!/bin/bash

# ============================================================================
# SimplEAD ERP - Complete Structure Generator
# This script creates all migrations, models, controllers, and basic views
# ============================================================================

set -e

COMPOSE_CMD="docker compose"

echo "======================================"
echo "SimplEAD ERP Structure Generator"
echo "======================================"
echo ""

# Create all migrations
echo "Creating migrations..."

$COMPOSE_CMD exec -T erp_app php artisan make:migration add_role_and_org_to_users_table
$COMPOSE_CMD exec -T erp_app php artisan make:migration create_clients_table
$COMPOSE_CMD exec -T erp_app php artisan make:migration create_offers_table
$COMPOSE_CMD exec -T erp_app php artisan make:migration create_contracts_table
$COMPOSE_CMD exec -T erp_app php artisan make:migration create_annexes_table
$COMPOSE_CMD exec -T erp_app php artisan make:migration create_subscriptions_table
$COMPOSE_CMD exec -T erp_app php artisan make:migration create_access_credentials_table
$COMPOSE_CMD exec -T erp_app php artisan make:migration create_files_table
$COMPOSE_CMD exec -T erp_app php artisan make:migration create_expenses_table
$COMPOSE_CMD exec -T erp_app php artisan make:migration create_revenues_table
$COMPOSE_CMD exec -T erp_app php artisan make:migration create_audit_logs_table
$COMPOSE_CMD exec -T erp_app php artisan make:migration create_system_settings_table

echo "✓ Migrations created"

# Create all models
echo "Creating models..."

$COMPOSE_CMD exec -T erp_app php artisan make:model Organization
$COMPOSE_CMD exec -T erp_app php artisan make:model Client
$COMPOSE_CMD exec -T erp_app php artisan make:model Offer
$COMPOSE_CMD exec -T erp_app php artisan make:model Contract
$COMPOSE_CMD exec -T erp_app php artisan make:model Annex
$COMPOSE_CMD exec -T erp_app php artisan make:model Subscription
$COMPOSE_CMD exec -T erp_app php artisan make:model AccessCredential
$COMPOSE_CMD exec -T erp_app php artisan make:model File
$COMPOSE_CMD exec -T erp_app php artisan make:model Expense
$COMPOSE_CMD exec -T erp_app php artisan make:model Revenue
$COMPOSE_CMD exec -T erp_app php artisan make:model AuditLog
$COMPOSE_CMD exec -T erp_app php artisan make:model SystemSetting

echo "✓ Models created"

# Create all controllers
echo "Creating controllers..."

$COMPOSE_CMD exec -T erp_app php artisan make:controller DashboardController
$COMPOSE_CMD exec -T erp_app php artisan make:controller OrganizationController --resource
$COMPOSE_CMD exec -T erp_app php artisan make:controller ClientController --resource
$COMPOSE_CMD exec -T erp_app php artisan make:controller OfferController --resource
$COMPOSE_CMD exec -T erp_app php artisan make:controller ContractController --resource
$COMPOSE_CMD exec -T erp_app php artisan make:controller AnnexController --resource
$COMPOSE_CMD exec -T erp_app php artisan make:controller SubscriptionController --resource
$COMPOSE_CMD exec -T erp_app php artisan make:controller AccessCredentialController --resource
$COMPOSE_CMD exec -T erp_app php artisan make:controller FileController --resource
$COMPOSE_CMD exec -T erp_app php artisan make:controller ExpenseController --resource
$COMPOSE_CMD exec -T erp_app php artisan make:controller RevenueController --resource
$COMPOSE_CMD exec -T erp_app php artisan make:controller AuditLogController
$COMPOSE_CMD exec -T erp_app php artisan make:controller SettingsController

echo "✓ Controllers created"

# Create middleware
echo "Creating middleware..."

$COMPOSE_CMD exec -T erp_app php artisan make:middleware CheckRole
$COMPOSE_CMD exec -T erp_app php artisan make:middleware EnsureOrganizationScope
$COMPOSE_CMD exec -T erp_app php artisan make:middleware AuditLogger

echo "✓ Middleware created"

# Create seeders
echo "Creating seeders..."

$COMPOSE_CMD exec -T erp_app php artisan make:seeder OrganizationSeeder
$COMPOSE_CMD exec -T erp_app php artisan make:seeder UserSeeder
$COMPOSE_CMD exec -T erp_app php artisan make:seeder RoleSeeder

echo "✓ Seeders created"

echo ""
echo "======================================"
echo "Structure created successfully!"
echo "======================================"
echo ""
echo "Next steps:"
echo "1. Edit migration files in app/database/migrations/"
echo "2. Edit model files in app/app/Models/"
echo "3. Run: docker compose exec erp_app php artisan migrate"
echo ""
