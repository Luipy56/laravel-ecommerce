#!/usr/bin/env bash
# Move a CLOSED-* task from autoagents/tasks/ to autoagents/tasks/done/YYYY/MM/DD/
# Supports legacy CLOSED-YYYYMMDD-... and new CLOSED-<issue>-YYYYMMDD-... patterns.
#
# Usage (repo root):
#   ./scripts/move-agent-task-to-done.sh autoagents/tasks/CLOSED-12-20260526-1200-slug.md
#   ./scripts/move-agent-task-to-done.sh autoagents/tasks/CLOSED-20260323-1200-slug.md

set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"

if [ "${1:-}" = "" ]; then
  echo "usage: $0 <path-to-CLOSED-*.md>" >&2
  exit 1
fi

TASK_PATH="$(cd "$(dirname "$1")" && pwd)/$(basename "$1")"
BASENAME="$(basename "$TASK_PATH")"

if [[ ! "$BASENAME" =~ ^CLOSED- ]]; then
  echo "$0: expected basename starting with CLOSED-, got: $BASENAME" >&2
  exit 1
fi

if [ ! -f "$TASK_PATH" ]; then
  echo "$0: file not found: $TASK_PATH" >&2
  exit 1
fi

TASKS_DIR="$REPO_ROOT/autoagents/tasks"
EXPECTED_PREFIX="$TASKS_DIR/"

case "$TASK_PATH" in
  "$EXPECTED_PREFIX"*) ;;
  *)
    echo "$0: file must live under $TASKS_DIR (got $TASK_PATH)" >&2
    exit 1
    ;;
esac

# Legacy: CLOSED-YYYYMMDD-HHMM-slug
# New:    CLOSED-<issue>-YYYYMMDD-HHMM-slug
if [[ "$BASENAME" =~ ^CLOSED-[0-9]{8}- ]]; then
  DATE_PART="${BASENAME#CLOSED-}"
elif [[ "$BASENAME" =~ ^CLOSED-[0-9]+-([0-9]{8})- ]]; then
  DATE_PART="${BASH_REMATCH[1]}"
else
  echo "$0: could not parse YYYYMMDD from filename: $BASENAME" >&2
  exit 1
fi

YEAR="${DATE_PART:0:4}"
MONTH="${DATE_PART:4:2}"
DAY="${DATE_PART:6:2}"

if ! [[ "$YEAR" =~ ^[0-9]{4}$ && "$MONTH" =~ ^(0[1-9]|1[0-2])$ && "$DAY" =~ ^(0[1-9]|[12][0-9]|3[01])$ ]]; then
  echo "$0: invalid date segment in filename: $BASENAME" >&2
  exit 1
fi

DEST_DIR="$TASKS_DIR/done/$YEAR/$MONTH/$DAY"
mkdir -p "$DEST_DIR"

DEST_PATH="$DEST_DIR/$BASENAME"
if [ -e "$DEST_PATH" ]; then
  echo "$0: destination already exists: $DEST_PATH" >&2
  exit 1
fi

# GitHub close when issue number is in filename (CLOSED-N-...)
if [[ "$BASENAME" =~ ^CLOSED-[0-9]+- ]] && command -v python3 >/dev/null 2>&1; then
  if [[ -f "$REPO_ROOT/autoagents/gh_issue_sync.py" ]]; then
    set +e
    python3 "$REPO_ROOT/autoagents/gh_issue_sync.py" close "$TASK_PATH"
    gh_rc=$?
    set -e
    if (( gh_rc != 0 )); then
      echo "$0: warn: gh_issue_sync close failed (continuing archive)" >&2
    fi
  fi
fi

mv "$TASK_PATH" "$DEST_PATH"
echo "moved to autoagents/tasks/done/$YEAR/$MONTH/$DAY/$BASENAME"
