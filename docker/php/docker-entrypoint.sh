#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

# Optional: wait for PostgreSQL before php-fpm or queue workers start.
if [[ "${WAIT_FOR_DB:-0}" == "1" ]] && [[ "${DB_CONNECTION:-}" == "pgsql" ]]; then
  host="${DB_HOST:-postgres}"
  port="${DB_PORT:-5432}"
  user="${DB_USERNAME:-postgres}"
  echo "Waiting for PostgreSQL at ${host}:${port}..."
  until pg_isready -h "$host" -p "$port" -U "$user" >/dev/null 2>&1; do
    sleep 1
  done
  echo "PostgreSQL is ready."
fi

# Writable dirs for Laravel (especially bind-mounted dev trees).
if [[ -d storage ]] && [[ -d bootstrap/cache ]]; then
  owner="${APP_UID:-www-data}"
  if id "$owner" &>/dev/null; then
    chown -R "$owner:$owner" storage bootstrap/cache 2>/dev/null || true
  else
    chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
  fi
  chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true
fi

# Production-only optimizations on the web PHP-FPM container only (avoids races on
# shared bootstrap/cache volumes when queue/scheduler run the same image).
if [[ "${APP_ENV:-local}" == "production" ]] && [[ -f .env ]] && [[ "${1:-}" == "php-fpm" ]]; then
  php artisan package:discover --ansi --no-interaction 2>/dev/null || true
  php artisan config:cache --no-interaction 2>/dev/null || true
  php artisan route:cache --no-interaction 2>/dev/null || true
  php artisan view:cache --no-interaction 2>/dev/null || true
fi

exec "$@"
