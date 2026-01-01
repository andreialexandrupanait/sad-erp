#!/bin/bash
docker exec erp_app rm -rf /var/www/html/storage/framework/views/*
docker exec erp_app php artisan view:clear
docker exec erp_app php artisan cache:clear
echo "Cache cleared!"
