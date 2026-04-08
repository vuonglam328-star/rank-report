#!/bin/bash
# Update RankReport Pro từ GitHub
set -e

APP_DIR="/var/www/rank-report"
cd ${APP_DIR}

echo "[1/5] Pulling latest code..."
git fetch origin && git reset --hard origin/main

echo "[2/5] Installing dependencies..."
COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction

echo "[3/5] Running migrations..."
php artisan migrate --force

echo "[4/5] Clearing caches..."
php artisan config:cache
php artisan route:cache
php artisan view:clear

echo "[5/5] Restarting queue workers..."
supervisorctl restart rank-report-worker:*

echo ""
echo "✓ Update hoàn thành!"
