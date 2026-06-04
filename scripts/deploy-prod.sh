#!/usr/bin/env bash
# Manual production deploy on VPS. Run from /srv/serra/prod or set PROD_ROOT.
set -euo pipefail
ROOT="${PROD_ROOT:-/srv/serra/prod}"
cd "$ROOT"
git -C source fetch origin prod
git -C source reset --hard origin/prod
docker compose -f docker-compose.prod.yml build
docker compose -f docker-compose.prod.yml up -d
docker compose -f docker-compose.prod.yml exec -T app php artisan migrate --force --no-interaction
echo "Prod deploy done: https://serra.ldeluipy.es"
