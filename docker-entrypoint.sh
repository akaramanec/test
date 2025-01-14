#!/usr/bin/env bash
set -e

cd /var/www/html
echo "Running migrations (safe) ..."
php artisan migrate --force || true

echo "Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

php artisan queue:restart || true

exec "$@"
