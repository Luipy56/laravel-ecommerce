#!/usr/bin/env bash
# Manual staging deploy on VPS. Run from /srv/serra/stage or set STAGE_ROOT.
set -euo pipefail
ROOT="${STAGE_ROOT:-/srv/serra/stage}"
COMPOSE_FILE="${ROOT}/docker-compose.stage.yml"
COMPOSE=(docker compose -f "$COMPOSE_FILE")

cd "$ROOT"
git -C source fetch origin autoagents
git -C source reset --hard origin/autoagents

# Failed `compose up` recreates leave hash-prefixed containers that block the canonical name.
docker ps -a --format '{{.Names}}' | grep -E '^[0-9a-f]{12}_serra-stage-' | xargs -r docker rm -f || true

"${COMPOSE[@]}" build
"${COMPOSE[@]}" up -d --remove-orphans
"${COMPOSE[@]}" exec -T app php artisan db:sync-postgres-sequences
"${COMPOSE[@]}" exec -T app php artisan db:reconcile-key-color-schema
"${COMPOSE[@]}" exec -T app php artisan migrate --force --no-interaction
echo "Stage deploy done: https://stage-serra.ldeluipy.es"
