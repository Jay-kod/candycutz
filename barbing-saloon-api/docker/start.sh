#!/bin/sh
set -e
# Render provides $PORT. Locally it defaults to 10000.
export SERVER_NAME=":${PORT:-10000}"

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migrations on boot are acceptable for a single-instance demo only
# (Render's free plan has no pre-deploy command). Controlled by an env flag.
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  php artisan migrate --force
fi

exec frankenphp run --config /etc/caddy/Caddyfile --adapter caddyfile
