#!/bin/bash
# =============================================================================
# RankReport Pro — Deploy Script
# VPS: 45.32.112.215 | Ubuntu 22.04 | Nginx
# Domain: rank-report.ngocminh.info
# =============================================================================
set -e

DOMAIN="rank-report.ngocminh.info"
APP_DIR="/var/www/rank-report"
DB_NAME="rankreport_pro"
DB_USER="rankreport_user"
DB_PASS=$(openssl rand -base64 24 | tr -dc 'A-Za-z0-9' | head -c 32)
REPO="https://github.com/vuonglam328-star/rank-report.git"
PHP_VER="8.2"
SSL_EMAIL="lamdepmeo@gmail.com"

echo ""
echo "============================================="
echo " RankReport Pro — Auto Deploy"
echo "============================================="
echo " Domain : $DOMAIN"
echo " App Dir: $APP_DIR"
echo " PHP    : $PHP_VER"
echo "============================================="
echo ""

# =============================================================================
# 1. System update
# =============================================================================
echo "[1/10] Updating system..."
apt-get update -qq
apt-get upgrade -y -qq
apt-get install -y -qq curl wget git unzip software-properties-common ufw

# =============================================================================
# 2. PHP 8.2 + extensions
# =============================================================================
echo "[2/10] Installing PHP $PHP_VER..."
add-apt-repository ppa:ondrej/php -y
apt-get update -qq
apt-get install -y -qq \
    php${PHP_VER}-fpm \
    php${PHP_VER}-mysql \
    php${PHP_VER}-mbstring \
    php${PHP_VER}-xml \
    php${PHP_VER}-curl \
    php${PHP_VER}-zip \
    php${PHP_VER}-bcmath \
    php${PHP_VER}-gd \
    php${PHP_VER}-intl \
    php${PHP_VER}-opcache \
    php${PHP_VER}-redis \
    php${PHP_VER}-cli

# Tune PHP-FPM for production
sed -i "s/^;opcache.enable=.*/opcache.enable=1/" /etc/php/${PHP_VER}/fpm/php.ini
sed -i "s/^;opcache.memory_consumption=.*/opcache.memory_consumption=128/" /etc/php/${PHP_VER}/fpm/php.ini
sed -i "s/^upload_max_filesize = .*/upload_max_filesize = 50M/" /etc/php/${PHP_VER}/fpm/php.ini
sed -i "s/^post_max_size = .*/post_max_size = 55M/" /etc/php/${PHP_VER}/fpm/php.ini
sed -i "s/^memory_limit = .*/memory_limit = 256M/" /etc/php/${PHP_VER}/fpm/php.ini
sed -i "s/^max_execution_time = .*/max_execution_time = 120/" /etc/php/${PHP_VER}/fpm/php.ini

systemctl enable php${PHP_VER}-fpm
systemctl restart php${PHP_VER}-fpm

# =============================================================================
# 3. Composer
# =============================================================================
echo "[3/10] Installing Composer..."
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --quiet

# =============================================================================
# 4. Node.js 20 LTS
# =============================================================================
echo "[4/10] Installing Node.js 20 LTS..."
curl -fsSL https://deb.nodesource.com/setup_20.x | bash - -q
apt-get install -y -qq nodejs

# =============================================================================
# 5. MySQL 8.0
# =============================================================================
echo "[5/10] Installing MySQL 8.0..."
apt-get install -y -qq mysql-server

# Secure MySQL & create DB/user
mysql -u root <<SQL
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

echo ""
echo ">>> MySQL DB created: ${DB_NAME}"
echo ">>> MySQL User      : ${DB_USER}"
echo ">>> MySQL Password  : ${DB_PASS}  <-- LƯU LẠI MẬT KHẨU NÀY!"
echo ""
# Save DB password to a temp file for .env generation later
echo "${DB_PASS}" > /root/.rankreport_db_pass

# =============================================================================
# 6. Nginx
# =============================================================================
echo "[6/10] Installing Nginx..."
apt-get install -y -qq nginx

# Remove default site
rm -f /etc/nginx/sites-enabled/default

# Create site config (HTTP only — Certbot will upgrade to HTTPS)
cat > /etc/nginx/sites-available/rank-report <<'NGINX'
server {
    listen 80;
    listen [::]:80;
    server_name rank-report.ngocminh.info;

    root /var/www/rank-report/public;
    index index.php;

    # Security headers (also set by Laravel middleware, belt-and-suspenders)
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;

    # Hide Nginx version
    server_tokens off;

    # Block .env and hidden files
    location ~ /\.(?!well-known) {
        deny all;
        return 404;
    }

    # Block direct access to storage
    location ~ ^/storage/ {
        deny all;
        return 404;
    }

    # Increase upload size for CSV
    client_max_body_size 60M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 120;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
    }

    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Gzip
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;
    gzip_min_length 1000;

    access_log /var/log/nginx/rank-report.access.log;
    error_log  /var/log/nginx/rank-report.error.log;
}
NGINX

ln -sf /etc/nginx/sites-available/rank-report /etc/nginx/sites-enabled/
nginx -t
systemctl enable nginx
systemctl reload nginx

# =============================================================================
# 7. Clone & Setup Application
# =============================================================================
echo "[7/10] Cloning application..."
mkdir -p /var/www
git clone ${REPO} ${APP_DIR}
cd ${APP_DIR}

# Create required storage directories
mkdir -p storage/app/imports/csv
mkdir -p storage/app/reports/pdf
mkdir -p storage/app/mpdf_tmp
mkdir -p storage/framework/{sessions,views,cache/data}
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Install PHP dependencies (no dev)
composer install --no-dev --optimize-autoloader --no-interaction --quiet

# Install & build frontend assets
npm ci --silent
npm run build

# Generate app key & set up .env
DB_PASS_SAVED=$(cat /root/.rankreport_db_pass)
APP_KEY=$(php artisan key:generate --show --no-interaction)

cat > .env <<ENV
APP_NAME="RankReport Pro"
APP_ENV=production
APP_KEY=${APP_KEY}
APP_DEBUG=false
APP_URL=https://${DOMAIN}
APP_TIMEZONE=Asia/Ho_Chi_Minh
APP_LOCALE=vi
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASS_SAVED}

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=database

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=${DOMAIN}
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

MAIL_MAILER=log

CSV_MAX_SIZE_MB=20
REPORTS_DISK=local
ENV

# Set permissions
chown -R www-data:www-data ${APP_DIR}
chmod -R 755 ${APP_DIR}
chmod -R 775 ${APP_DIR}/storage
chmod -R 775 ${APP_DIR}/bootstrap/cache
chmod 640 ${APP_DIR}/.env

# Run migrations
php artisan migrate --force --no-interaction

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

# =============================================================================
# 8. Queue Worker (Supervisor)
# =============================================================================
echo "[8/10] Setting up Queue Worker..."
apt-get install -y -qq supervisor

cat > /etc/supervisor/conf.d/rank-report-worker.conf <<SUPERVISOR
[program:rank-report-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/rank-report/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/rank-report-worker.log
stdout_logfile_maxbytes=10MB
stopwaitsecs=3600
SUPERVISOR

supervisorctl reread
supervisorctl update

# =============================================================================
# 9. Cron Job (Laravel Scheduler)
# =============================================================================
echo "[9/10] Setting up Scheduler..."
(crontab -l 2>/dev/null; echo "* * * * * www-data php /var/www/rank-report/artisan schedule:run >> /dev/null 2>&1") | crontab -

# =============================================================================
# 10. SSL Certificate (Let's Encrypt)
# =============================================================================
echo "[10/10] Installing SSL certificate..."
apt-get install -y -qq certbot python3-certbot-nginx

certbot --nginx \
    --non-interactive \
    --agree-tos \
    --email ${SSL_EMAIL} \
    --domains ${DOMAIN} \
    --redirect

# Auto-renew cron
(crontab -l 2>/dev/null; echo "0 3 * * * certbot renew --quiet --post-hook 'systemctl reload nginx'") | crontab -

# =============================================================================
# 11. Firewall
# =============================================================================
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable

# =============================================================================
# Cleanup
# =============================================================================
rm -f /root/.rankreport_db_pass

# =============================================================================
# Done!
# =============================================================================
echo ""
echo "============================================="
echo " DEPLOY HOÀN THÀNH!"
echo "============================================="
echo " URL: https://${DOMAIN}"
echo " App: ${APP_DIR}"
echo ""
echo " Bước cuối: Tạo tài khoản admin"
echo " Chạy lệnh sau:"
echo ""
echo "   cd ${APP_DIR} && php artisan tinker"
echo "   >>> \App\Models\User::create(['name'=>'Admin','email'=>'lamdepmeo@gmail.com','password'=>bcrypt('YOUR_STRONG_PASSWORD')]);"
echo "   >>> exit"
echo "============================================="
