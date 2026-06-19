#!/bin/bash
set -e

echo "Waiting for MySQL to be ready..."
until php -r "try { new PDO(\"mysql:host=${DB_HOST};port=3306\", \"${DB_USERNAME}\", \"${DB_PASSWORD}\"); echo 'connected'; } catch(Exception \$e) { exit(1); }" 2>/dev/null; do
    echo "MySQL is not ready yet. Retrying in 3s..."
    sleep 3
done
echo "MySQL is ready!"

# Overwrite .env with Docker environment variables
cat > /app/.env <<EOF
APP_NAME="${APP_NAME:-Service C}"
APP_ENV=${APP_ENV:-local}
APP_KEY=
APP_DEBUG=${APP_DEBUG:-true}
APP_URL=${APP_URL:-http://localhost:8000}

LOG_CHANNEL=${LOG_CHANNEL:-stderr}

DB_CONNECTION=${DB_CONNECTION:-mysql}
DB_HOST=${DB_HOST:-mysql}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-smart_parking}
DB_USERNAME=${DB_USERNAME:-root}
DB_PASSWORD=${DB_PASSWORD:-secret}

SESSION_DRIVER=${SESSION_DRIVER:-file}
CACHE_STORE=${CACHE_STORE:-file}

IAE_API_KEYS=${IAE_API_KEYS:-102022400023,102022400039,102022400126}
L5_SWAGGER_GENERATE_ALWAYS=${L5_SWAGGER_GENERATE_ALWAYS:-true}
EOF

php artisan key:generate --force --no-interaction
php artisan config:clear
php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction 2>/dev/null || true
php artisan l5-swagger:generate 2>/dev/null || true

echo ""
echo "  INFO  Server running on [http://0.0.0.0:8000]."
echo ""

exec php artisan serve --host=0.0.0.0 --port=8000
