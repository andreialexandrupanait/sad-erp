#!/bin/bash

echo "🔧 Fixing Subscriptions Module..."
echo ""

# Run migration to add currency field
echo "1. Adding currency field to subscriptions table..."
docker compose exec app php artisan migrate --path=/database/migrations/2025_11_25_120000_add_currency_to_subscriptions.php

# Re-seed billing cycles with English values
echo ""
echo "2. Updating billing cycles to use English values..."
docker compose exec app php artisan db:seed --class=BillingCyclesSeeder

echo ""
echo "✅ All done! Your subscriptions module should now work correctly."
echo ""
echo "You can now:"
echo "  - Create subscriptions without error 500"
echo "  - Select EUR currency in the form"
echo "  - Click once to change status"
