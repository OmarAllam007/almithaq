# Hetzner Deployment Guide

This guide covers deploying this Laravel 12 + Inertia.js + Vue 3 application on a fresh Hetzner VPS (Ubuntu 24.04 LTS recommended).

---

## 1. Provision the Server

On Hetzner Cloud, create a server:
- **Image**: Ubuntu 24.04
- **Type**: CX22 or higher (2 vCPU, 4 GB RAM minimum recommended)
- **Networking**: Enable a public IPv4

Once created, SSH in as root:

```bash
ssh root@YOUR_SERVER_IP
```

---

## 2. Initial Server Setup

```bash
# Update system
apt update && apt upgrade -y

# Create a deploy user
adduser deploy
usermod -aG sudo deploy

# Copy SSH keys to deploy user
rsync --archive --chown=app:app ~/.ssh /home/app

# Switch to deploy user for the rest of the setup
su - app
```

---

## 3. Install Required Software

### PHP 8.3

```bash
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

sudo apt install -y \
  php8.3 php8.3-fpm php8.3-cli \
  php8.3-mbstring php8.3-xml php8.3-bcmath \
  php8.3-curl php8.3-zip php8.3-gd \
  php8.3-mysql php8.3-redis php8.3-intl \
  php8.3-tokenizer php8.3-fileinfo
```

### Nginx

```bash
sudo apt install -y nginx
sudo systemctl enable nginx
```

### MySQL

```bash
sudo apt install -y mysql-server
sudo mysql_secure_installation
```

Create the database and user:

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE almithaq CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'almithaq'@'localhost' IDENTIFIED BY '@Matador99';
GRANT ALL PRIVILEGES ON almithaq.* TO 'almithaq'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Redis

```bash
sudo apt install -y redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

### Node.js (via nvm)

```bash
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.1/install.sh | bash
source ~/.bashrc
nvm install 22
nvm use 22
nvm alias default 22
```

### Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

### Supervisor (for queues & Reverb)

```bash
sudo apt install -y supervisor
sudo systemctl enable supervisor
```

---

## 4. Deploy the Application

```bash
sudo mkdir -p /var/www/almithaq
sudo chown app:app /var/www/almithaq

cd /var/www
git clone YOUR_REPO_URL almithaq
cd almithaq
```

### Install dependencies

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

> The app uses SSR (`ssr.ts`) — build it too:
> ```bash
> npm run build:ssr
> ```

---

## 5. Configure Environment

```bash
cp .env.example .env
nano .env
```

Update these values:

```env
APP_NAME="Almithaq"
APP_ENV=production
APP_KEY=                          # Generated below
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=almithaq
DB_USERNAME=almithaq
DB_PASSWORD=@Matador99

SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

BROADCAST_CONNECTION=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=0.0.0.0
REVERB_PORT=8080
REVERB_SCHEME=https

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="yourdomain.com"
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS="hello@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Generate the app key:

```bash
php artisan key:generate
```

---

## 6. Run Migrations & Storage Setup

```bash
php artisan migrate --force
php artisan storage:link

# Cache config/routes for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set correct permissions
sudo chown -R app:www-data /var/www/almithaq
sudo chmod -R 775 /var/www/almithaq/storage
sudo chmod -R 775 /var/www/almithaq/bootstrap/cache
```

---

## 7. Configure Nginx

```bash
sudo nano /etc/nginx/sites-available/almithaq
```

Paste:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    root /var/www/almithaq/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    include             /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam         /etc/letsencrypt/ssl-dhparams.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Proxy Reverb WebSocket connections
    location /app {
        proxy_pass             http://127.0.0.1:8080;
        proxy_http_version     1.1;
        proxy_set_header       Upgrade $http_upgrade;
        proxy_set_header       Connection "Upgrade";
        proxy_set_header       Host $host;
        proxy_set_header       X-Real-IP $remote_addr;
        proxy_set_header       X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header       X-Forwarded-Proto $scheme;
        proxy_read_timeout     60;
    }
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/almithaq /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 8. SSL with Let's Encrypt

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

Auto-renewal is set up automatically. Verify with:

```bash
sudo certbot renew --dry-run
```

---

## 9. Supervisor: Queue Worker & Reverb

### Queue Worker

```bash
sudo nano /etc/supervisor/conf.d/almithaq-worker.conf
```

```ini
[program:almithaq-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/almithaq/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=deploy
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/almithaq/storage/logs/worker.log
stopwaitsecs=3600
```

### Reverb WebSocket Server

```bash
sudo nano /etc/supervisor/conf.d/almithaq-reverb.conf
```

```ini
[program:almithaq-reverb]
command=php /var/www/almithaq/artisan reverb:start --host=0.0.0.0 --port=8080 --no-interaction
autostart=true
autorestart=true
user=deploy
redirect_stderr=true
stdout_logfile=/var/www/almithaq/storage/logs/reverb.log
```

Reload Supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start almithaq-worker:*
sudo supervisorctl start almithaq-reverb
sudo supervisorctl status
```

---

## 10. Firewall

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
sudo ufw status
```

> Port 8080 (Reverb) does **not** need to be opened — Nginx proxies it internally.

---

## 11. Verify Everything Works

```bash
# Check PHP-FPM
sudo systemctl status php8.3-fpm

# Check Nginx
sudo systemctl status nginx

# Check Redis
redis-cli ping   # Should return PONG

# Check Supervisor processes
sudo supervisorctl status

# Test the app
curl -I https://yourdomain.com
```

---

## 12. Deployment Script (for future updates)

Save this as `deploy.sh` in the project root:

```bash
#!/bin/bash
set -e

cd /var/www/almithaq

echo "Pulling latest code..."
git pull origin master

echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "Installing JS dependencies..."
npm ci

echo "Building frontend assets..."
npm run build:ssr

echo "Running migrations..."
php artisan migrate --force

echo "Clearing caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Restarting queue workers..."
sudo supervisorctl restart almithaq-worker:*

echo "Restarting Reverb..."
sudo supervisorctl restart almithaq-reverb

echo "Done."
```

Make it executable:

```bash
chmod +x deploy.sh
```

Give the deploy user passwordless sudo for supervisorctl by running `sudo visudo` and adding:

```
deploy ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl
```

---

## Troubleshooting

| Issue | Fix |
|---|---|
| 500 error | Check `storage/logs/laravel.log` |
| Blank page | Run `npm run build:ssr` and clear view cache |
| WebSockets not connecting | Verify Reverb supervisor process is running and Nginx proxy config |
| Queue jobs not processing | Check `storage/logs/worker.log` and supervisor status |
| Permission denied | Re-run `sudo chown -R deploy:www-data storage bootstrap/cache && sudo chmod -R 775 storage bootstrap/cache` |
