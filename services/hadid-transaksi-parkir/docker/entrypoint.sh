#!/bin/sh
set -e

echo "[entrypoint] Memeriksa koneksi MySQL ke ${DB_HOST}:${DB_PORT}..."
attempt=0
max_attempts=30
until php -r "try { new PDO('mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}'); } catch (Throwable \$e) { fwrite(STDERR, \$e->getMessage().PHP_EOL); exit(1); }"; do
    attempt=$((attempt + 1))
    if [ "$attempt" -ge "$max_attempts" ]; then
        echo "[entrypoint] MySQL belum siap setelah $max_attempts percobaan, gagal start."
        exit 1
    fi
    echo "[entrypoint] MySQL belum siap, retry dalam 2 detik... ($attempt/$max_attempts)"
    sleep 2
done
echo "[entrypoint] MySQL siap."

echo "[entrypoint] Menjalankan migrasi & seeder..."
php artisan config:clear || true
php artisan migrate --force --seed

echo "[entrypoint] Menyalakan Laravel pada port ${PORT:-3002}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-3002}"
