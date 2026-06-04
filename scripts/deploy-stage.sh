#!/usr/bin/env bash
# Manual staging deploy on VPS. Run from /srv/serra/stage or set STAGE_ROOT.
set -euo pipefail
ROOT="${STAGE_ROOT:-/srv/serra/stage}"
cd "$ROOT"
git -C source fetch origin autoagents
git -C source reset --hard origin/autoagents
docker compose -f docker-compose.stage.yml build
docker compose -f docker-compose.stage.yml up -d
docker compose -f docker-compose.stage.yml exec -T app php artisan migrate --force --no-interaction
echo "Stage deploy done: https://stage-serra.ldeluipy.es"
