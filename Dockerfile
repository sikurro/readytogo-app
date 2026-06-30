# ==========================================
# Stage 1: Build PHP Dependencies (Composer)
# ==========================================
FROM composer:2.6 AS composer-builder
WORKDIR /app

COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --optimize --no-dev --no-scripts

# ==========================================
# Stage 2: Build Frontend Assets (Vite)
# ==========================================
FROM node:20-alpine AS node-builder
WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci --legacy-peer-deps || npm install --legacy-peer-deps

COPY . .
COPY --from=composer-builder /app/vendor /app/vendor
RUN npm run build


# ==========================================
# Stage 3: Staging / Production Runtime
# ==========================================
FROM php:8.2-fpm-bullseye

# Install system dependencies & Nginx
RUN apt-get update && apt-get install -y \
    nginx \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configure & Install Ekstensi PHP Wajib (termasuk GD dan ZIP untuk Excel/DOMPDF)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

WORKDIR /var/www/html

# Hapus konfigurasi default Nginx dan salin konfigurasi custom
RUN rm -f /etc/nginx/sites-enabled/default
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

# Salin entrypoint script & berikan izin eksekusi
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Salin source code aplikasi
COPY . /var/www/html

# Salin hasil build dari Stage 2 (Composer) & Stage 1 (Node.js)
COPY --from=composer-builder /app/vendor /var/www/html/vendor
COPY --from=node-builder /app/public/build /var/www/html/public/build

# Set ownership ke www-data
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
