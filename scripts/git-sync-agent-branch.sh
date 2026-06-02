#!/usr/bin/env bash
# Deprecated name — delegates to git-sync-autoagents-branch.sh (integration branch: autoagents).
exec "$(dirname "$0")/git-sync-autoagents-branch.sh" "$@"
