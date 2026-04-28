#!/usr/bin/env bash
# Laravel ecommerce agent loop orchestrator (mac-stats-reviewer / POS style). Run from repo root:
#   ./agents/laravel-ecommerce-agent-loop.sh [COMMAND]
# or:
#   cd agents && ./laravel-ecommerce-agent-loop.sh [COMMAND]
#
# App stack: start separately (php artisan serve, npm run dev, etc.).
# Requires: cursor-agent on PATH (Cursor CLI), unless you only use subcommands that skip.
#
# Task dir: agents/tasks/ (sibling of this script).

set -euo pipefail

SCRIPTDIR="$(cd "$(dirname "$0")" && pwd)"
TASKDIR="${SCRIPTDIR}/tasks"
REPO_ROOT="$(cd "${SCRIPTDIR}/.." && pwd)"
sleepminutes="${AGENT_LOOP_SLEEP_MINUTES:-5}"
sleepseconds=$((sleepminutes * 60))

cd "$SCRIPTDIR" || exit 1

have_cursor_agent() {
  command -v cursor-agent >/dev/null 2>&1
}

sync_repo() {
  if [[ "${AGENT_GIT_SYNC:-1}" == "0" ]]; then
    echo "----- git sync (skip: AGENT_GIT_SYNC=0)"
    return 0
  fi
  echo "-----> git sync agent branch $(date "+%Y-%m-%d %H:%M:%S") <----"
  if ! bash "${REPO_ROOT}/scripts/git-sync-agent-branch.sh"; then
    echo "ERROR: git sync failed (conflicts, network, or missing origin branch). Resolve and retry." >&2
    exit 1
  fi
}

# Prompt paths are relative to agents/ (cwd for cursor-agent).
run_agent() {
  local desc="$1" cond="$2" prompt="$3" msg="$4"
  local p="${SCRIPTDIR}/${prompt}"
  if [[ ! -f "$p" ]]; then
    echo "----- $desc (skip: missing prompt $prompt — see docs/agent-loop.md)"
    return 0
  fi
  if ! have_cursor_agent; then
    echo "----- $desc (skip: cursor-agent not on PATH)"
    return 0
  fi
  if eval "$cond" 2>/dev/null; then
    echo "-----> $desc $(date "+%Y-%m-%d %H:%M:%S") <----"
    # cursor-agent: --print is non-interactive console mode; prompt file body + operator line (see `cursor-agent --help`).
    local combined
    combined="$(printf '%s\n\n---\n\nOperator instruction: %s\n' "$(cat "$p")" "$msg")"
    cursor-agent --yolo --print "$combined"
  else
    echo "----- $desc (skip: nothing to do)"
  fi
  echo "<-- end of $desc $(date "+%Y-%m-%d %H:%M:%S") -->"
  echo "--------------------------------"
  echo ""
}

step_log_reviewer() {
  sync_repo
  echo "-----> log reviewer (001) <----"
  run_agent "log reviewer (001)" \
    "true" \
    "001-log-reviewer/LOG-REVIEWER-PROMPT.md" \
    "Run 001: (A) GitHub — up to 3 open issues → create FEAT-*.md only (feature coders). (B) Laravel logs / runtime — NEW-*.md only for real incidents. gh comment/label on issues when applicable. Do your job."
}

step_feat() {
  sync_repo
  echo "-----> feature coding (FEAT) <----"
  run_agent "feature coding (FEAT)" \
    "test -n \"\$(find \"$TASKDIR\" -maxdepth 1 -name 'FEAT-*.md' 2>/dev/null)\"" \
    "006-feature-coder/FEATURE-CODER.md" \
    "Start feature coding now. Pick up a FEAT task if any. Do your job."
}

step_coder() {
  sync_repo
  echo "-----> coding (NEW / WIP) <----"
  run_agent "coding" \
    "test -n \"\$(find \"$TASKDIR\" -maxdepth 1 \\( -name 'NEW-*.md' -o -name 'WIP-*.md' \\) 2>/dev/null)\"" \
    "002-coder/CODER.md" \
    "Start coding now. Prefer a NEW task if any (rename to WIP on start); otherwise continue an existing WIP to UNTESTED. Implement in this repo (app/, routes/, resources/js/, tests/, database/). Do your job."
}

step_tester() {
  sync_repo
  echo "-----> testing <----"
  run_agent "testing" \
    "test -n \"\$(find \"$TASKDIR\" -maxdepth 1 -name 'UNTESTED-*.md' 2>/dev/null)\"" \
    "003-tester/TESTER.md" \
    "Start testing now. Pick up an UNTESTED task if any. Do your job."
}

step_closing_review() {
  sync_repo
  echo "-----> closing reviewer (CLOSED in tasks/) <----"
  run_agent "closing" \
    "test -n \"\$(find \"$TASKDIR\" -maxdepth 1 -name 'CLOSED-*.md' 2>/dev/null)\"" \
    "004-closing-reviewer/CLOSING-REVIEWER-PROMPT.md" \
    "Start closing review now. Process CLOSED-*.md in agents/tasks/; prepend summary; move to done/YYYY/MM/DD with scripts/move-agent-task-to-done.sh when done. Do your job."
}

step_committer() {
  sync_repo
  echo "-----> committer (changelog + commit) <----"
  run_agent "committer (changelog + commit)" \
    "cd \"$REPO_ROOT\" && { ! git diff --quiet 2>/dev/null || ! git diff --staged --quiet 2>/dev/null; }" \
    "007-committer/COMMITTER.md" \
    "Check this repo for uncommitted changes on the integration branch (default agentdevelop; override with AGENT_GIT_BRANCH). Update CHANGELOG.md and root package.json per project rules; commit. Push origin. Merge to master only per .cursor/rules/git-agent-branch-workflow.mdc (2h batch, big prod change, production-urgent issue, or explicit user ask)."
}

run_full_cycle() {
  echo "$(date)"
  step_log_reviewer
  local i
  for i in 1 2 3 4 5; do
    step_feat
  done
  step_coder
  step_tester
  step_closing_review
  step_committer
}

usage() {
  cat >&2 <<EOF
Usage: $(basename "$0") [COMMAND]

  (no args)       Run full agent cycle every ${AGENT_LOOP_SLEEP_MINUTES:-5} minutes (loop).

  Single run:
    log, log-reviewer, 001   Log / incident reviewer (001; runs first in full cycle)
    feat, feature   Feature coder (FEAT-*.md in agents/tasks/)
    coder           Coder (NEW-*.md or WIP-*.md)
    tester          Tester (UNTESTED-*.md)
    closing-review  Closing reviewer (CLOSED-*.md still in agents/tasks/)
    committer       Changelog + commit when repo has local changes

    help, -h, --help   Show this help

Environment:
  AGENT_LOOP_SLEEP_MINUTES   Sleep between full cycles when looping (default: 5).
  AGENT_GIT_SYNC             If 0, skip git fetch/pull before each step (default: 1).
  AGENT_GIT_BRANCH           Integration branch (default: agentdevelop).

Git: each step runs scripts/git-sync-agent-branch.sh first (fetch + checkout + pull --rebase --autostash).

Prompt files live under agents/ (e.g. 002-coder/CODER.md). See docs/agent-loop.md.
EOF
}

if [[ -n "${1:-}" ]]; then
  case "$1" in
    help | -h | --help)
      usage
      exit 0
      ;;
    log | log-reviewer | 001) step_log_reviewer ;;
    feat | feature) step_feat ;;
    coder) step_coder ;;
    tester) step_tester ;;
    closing-review | closing-closed) step_closing_review ;;
    committer) step_committer ;;
    *)
      usage
      exit 1
      ;;
  esac
  exit 0
fi

if ! have_cursor_agent; then
  echo "cursor-agent not found on PATH. Install Cursor CLI or add to PATH." >&2
  echo "Loop aborted. Single commands will no-op until cursor-agent is available." >&2
  exit 1
fi

while true; do
  run_full_cycle
  echo "----- sleeping ${sleepminutes}m (${sleepseconds}s)"
  sleep "$sleepseconds"
done
