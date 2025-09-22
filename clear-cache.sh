#!/bin/bash
php artisan view:clear
php artisan route:clear
php artisan config:clear
php artisan cache:clear
echo "Laravel cache cleared successfully!"

