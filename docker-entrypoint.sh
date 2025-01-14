#!/bin/bash
set -e
cd /var/www/html
echo "Running migrations..."
php artisan migrate --force || true
exec "$@"
