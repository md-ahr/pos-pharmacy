#!/bin/sh
set -e

export PORT="${PORT:-80}"

envsubst '${PORT}' < /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf

if [ "${SKIP_MIGRATIONS:-}" != "true" ]; then
    php artisan migrate --force || echo "WARNING: migrations failed, continuing startup"
    php artisan db:seed --force || echo "WARNING: seeding failed, continuing startup"
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
