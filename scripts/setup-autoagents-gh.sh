#!/usr/bin/env bash
# Authenticate gh for autoagents (Issues read/write on laravel-ecommerce).
# Usage: export GH_TOKEN=... && ./scripts/setup-autoagents-gh.sh
# Or interactive: gh auth login
set -euo pipefail

REPO="${AGENT_GH_REPO:-Luipy56/laravel-ecommerce}"

if ! command -v gh >/dev/null 2>&1; then
  echo "setup-autoagents-gh: install GitHub CLI (gh)" >&2
  exit 1
fi

if [[ -n "${GH_TOKEN:-}" ]]; then
  printf '%s\n' "$GH_TOKEN" | gh auth login --hostname github.com --with-token
fi

if ! gh auth status -h github.com >/dev/null 2>&1; then
  echo "setup-autoagents-gh: not logged in. Run: gh auth login" >&2
  echo "  Or: export GH_TOKEN with Issues read/write on ${REPO}" >&2
  exit 1
fi

"$(dirname "$0")/gh-bootstrap-agent-labels.sh"
echo "setup-autoagents-gh: ok (${REPO})"
