#!/usr/bin/env bash
# Create GitHub labels used by docs/agent-loop.md (idempotent).
# Requires: gh authenticated (gh auth login, or GH_TOKEN / GITHUB_TOKEN).
# Override repo: GH_AGENT_REPO=owner/name
set -euo pipefail

REPO="${GH_AGENT_REPO:-Luipy56/laravel-ecommerce}"

if ! command -v gh >/dev/null 2>&1; then
  echo "gh-bootstrap-agent-labels: install GitHub CLI (gh)" >&2
  exit 1
fi

if ! gh auth status -h github.com >/dev/null 2>&1; then
  echo "gh-bootstrap-agent-labels: not logged in. Run: gh auth login" >&2
  echo "  Non-interactive: export GH_TOKEN with Issues read/write on ${REPO}" >&2
  exit 1
fi

label_exists() {
  local name="$1"
  gh label list --repo "$REPO" --limit 500 --json name --jq -r '.[].name' | grep -Fxq "$name"
}

ensure_label() {
  local name="$1" color="$2" desc="$3"
  if label_exists "$name"; then
    echo "ok (exists): $name"
    return 0
  fi
  gh label create "$name" --repo "$REPO" --color "$color" --description "$desc"
  echo "created: $name"
}

echo "Repo: $REPO"
ensure_label "agent:planned" "0E8A16" "Agent queue: task file created / planned"
ensure_label "agent:wip" "FBCA04" "Agent queue: implementation in progress"
ensure_label "agent:testing" "7057FF" "Agent queue: under verification"
ensure_label "production-urgent" "B60205" "Needs fast track to production / hotfix"
echo "Done."
