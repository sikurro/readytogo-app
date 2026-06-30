#!/bin/sh
set -e

echo "=== [1/5] Memastikan permissions folder storage & cache..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "=== [2/5] Menghubungkan storage public (storage:link)..."
php artisan storage:link --force || true

echo "=== [3/5] Menjalankan migrasi database..."
php artisan migrate --force || echo "Peringatan: Migrasi tertunda atau gagal dieksekusi."

echo "=== [4/5] Mengoptimalkan & caching konfigurasi Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "=== [5/5] Memulai service Nginx & PHP-FPM..."
# Menjalankan PHP-FPM di background
php-fpm -D
# Menjalankan Nginx di foreground agar kontainer tetap hidup dan log termonitor
exec nginx -g "daemon off;"
