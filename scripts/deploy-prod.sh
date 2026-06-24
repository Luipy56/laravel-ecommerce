#!/usr/bin/env bash
# Manual production deploy on VPS. Run from /srv/serra/prod or set PROD_ROOT.
set -euo pipefail
ROOT="${PROD_ROOT:-/srv/serra/prod}"
cd "$ROOT"
git -C source fetch origin prod
git -C source reset --hard origin/prod
docker compose -f docker-compose.prod.yml build
docker compose -f docker-compose.prod.yml up -d
docker compose -f docker-compose.prod.yml exec -T app php artisan db:sync-postgres-sequences
docker compose -f docker-compose.prod.yml exec -T app php artisan db:reconcile-key-color-schema
docker compose -f docker-compose.prod.yml exec -T app php artisan migrate --force --no-interaction
docker compose -f docker-compose.prod.yml exec -T app php artisan db:reconcile-key-color-schema
echo "Prod deploy done: https://serra.ldeluipy.es"
