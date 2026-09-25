#!/bin/bash
set -e

echo "=========================================================="
echo " Starting COGNOS 2K26 Setup for cognos.rvrjc.me"
echo "=========================================================="

export DEBIAN_FRONTEND=noninteractive

# 1. Clean up any broken PPA references and update apt
sudo rm -f /etc/apt/sources.list.d/*ondrej* || true
sudo apt-get clean
sudo apt-get update && sudo apt-get upgrade -y
sudo apt-get install -y debian-keyring debian-archive-keyring apt-transport-https \
                        curl gnupg2 git unzip software-properties-common

# 2. Add Caddy Official Repository & Install Caddy
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' | sudo gpg --dearmor --yes -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' | sudo tee /etc/apt/sources.list.d/caddy-stable.list
sudo apt-get update
sudo apt-get install -y caddy

# 3. Install PHP & PHP-FPM directly from official Ubuntu repositories
sudo apt-get install -y php-fpm php-cli php-mysql php-curl php-gd php-mbstring php-xml php-zip

# Detect active PHP version dynamically (supports 8.2, 8.3, etc.)
PHP_VER=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
echo "Detected active PHP Version: $PHP_VER"

PHP_INI="/etc/php/$PHP_VER/fpm/php.ini"
PHP_SOCK="/run/php/php$PHP_VER-fpm.sock"

# Configure PHP upload limits for College ID cards
if [ -f "$PHP_INI" ]; then
    sudo sed -i 's/upload_max_filesize = .*/upload_max_filesize = 12M/' "$PHP_INI"
    sudo sed -i 's/post_max_size = .*/post_max_size = 15M/' "$PHP_INI"
    sudo sed -i 's/memory_limit = .*/memory_limit = 512M/' "$PHP_INI"
    sudo sed -i 's/max_execution_time = .*/max_execution_time = 120/' "$PHP_INI"
fi

# 4. Install & Configure MySQL Server (using modern standard auth)
sudo apt-get install -y mysql-server
sudo systemctl start mysql
sudo systemctl enable mysql

DB_PASS="Cognos2026_$(openssl rand -hex 6)"

sudo mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS \`cognos_2k26\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'cognos_admin'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER 'cognos_admin'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`cognos_2k26\`.* TO 'cognos_admin'@'localhost';
FLUSH PRIVILEGES;
EOF

# 5. Clone Repository into /var/www/cognos
sudo mkdir -p /var/www/cognos
sudo rm -rf /var/www/cognos/*
sudo git clone https://github.com/chalamalauday/cognos-backend.git /var/www/cognos

# Import SQL Schema
if [ -f /var/www/cognos/database.sql ]; then
    sudo mysql -u root cognos_2k26 < /var/www/cognos/database.sql
fi

# Configure local MySQL credentials in config.php
sudo sed -i "s/define('DB_HOST', .*/define('DB_HOST', 'localhost');/" /var/www/cognos/backend/config.php
sudo sed -i "s/define('DB_PORT', .*/define('DB_PORT', '3306');/" /var/www/cognos/backend/config.php
sudo sed -i "s/define('DB_USER', .*/define('DB_USER', 'cognos_admin');/" /var/www/cognos/backend/config.php
sudo sed -i "s/define('DB_PASS', .*/define('DB_PASS', '${DB_PASS}');/" /var/www/cognos/backend/config.php

echo "Database: cognos_2k26" | sudo tee /root/db_credentials.txt
echo "User: cognos_admin" | sudo tee -a /root/db_credentials.txt
echo "Password: ${DB_PASS}" | sudo tee -a /root/db_credentials.txt
sudo chmod 600 /root/db_credentials.txt

# 6. File Permissions for Caddy and PHP-FPM
sudo mkdir -p /var/www/cognos/backend/uploads/id_cards
sudo usermod -aG www-data caddy || true
sudo chown -R www-data:www-data /var/www/cognos
sudo chmod -R 775 /var/www/cognos/backend/uploads

# 7. Configure Caddyfile with Automatic HTTPS & Full CORS
sudo cat <<EOF | sudo tee /etc/caddy/Caddyfile
cognos.rvrjc.me {
    root * /var/www/cognos
    encode gzip zstd
    file_server

    # Handle CORS Preflight (OPTIONS) instantly
    @cors_preflight method OPTIONS
    handle @cors_preflight {
        header Access-Control-Allow-Origin "{header.Origin}"
        header Access-Control-Allow-Methods "GET, POST, OPTIONS, PUT, DELETE"
        header Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With, Origin, Accept"
        header Access-Control-Allow-Credentials "true"
        header Access-Control-Max-Age "86400"
        respond 204
    }

    # Route all PHP through detected PHP-FPM socket
    php_fastcgi unix/$PHP_SOCK

    # Block access to sensitive files
    @protected {
        path /backend/config.php
        path /database*.sql
        path /.git*
        path /Dockerfile
        path /*.md
        path /*.sh
    }
    respond @protected 403
}

:80 {
    root * /var/www/cognos
    encode gzip zstd
    file_server
    php_fastcgi unix/$PHP_SOCK
}
EOF

# 8. Start & Enable All Services
sudo systemctl restart "php$PHP_VER-fpm" || sudo systemctl restart php-fpm
sudo systemctl enable "php$PHP_VER-fpm" || sudo systemctl enable php-fpm
sudo systemctl restart caddy
sudo systemctl enable caddy

echo "=========================================================="
echo " COGNOS 2K26 Deployment Finished Successfully!"
echo " Web Server: Active on Port 80 & 443 (Caddy)"
echo " PHP: Version $PHP_VER (FPM) Active"
echo " MySQL: Active (Credentials saved to /root/db_credentials.txt)"
echo " Domain: https://cognos.rvrjc.me"
echo "=========================================================="
