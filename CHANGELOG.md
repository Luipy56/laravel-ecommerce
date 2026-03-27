# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- `scripts/gh-bootstrap-agent-labels.sh`: idempotent `gh` helper to create GitHub labels used by the multi-agent workflow (`agent:planned`, `agent:wip`, `agent:testing`, `production-urgent`).

### Changed

- `agents/001-log-reviewer/time-of-last-review.txt` updated (log reviewer runs).

## [0.1.1] - 2026-03-27

### Added

- Multi-agent workflow documentation and tooling (`AGENTS.md`, `agents/`, `docs/agent-loop.md`, `docs/agent-cursor-rules.md`, `scripts/git-sync-agent-branch.sh`).
- Cursor rules for commit/changelog/version workflow and git integration branch policy (`.cursor/rules/commit-changelog-version.mdc`, `.cursor/rules/git-agent-branch-workflow.mdc`).
- PayPal sandbox E2E operator checklist in `docs/CONFIGURACION_PAGOS_CORREO.md`.
- Feature test for PayPal-only checkout payments config (`CheckoutPaymentConfigTest`).

### Changed

- Default multi-agent integration branch is **`agentdevelop`** (`AGENT_GIT_BRANCH` overrides). Sync script and docs updated accordingly.
- **`laravel-ecommerce-agent-loop.sh`:** invoke **`cursor-agent`** with **`--print`** and the prompt file contents (current CLI; `-p` is `--print`, not a path).
- **`.cursor/rules/auth.mdc`:** cross-link to testing verification for unauthenticated `api/*` behaviour.

### Removed

- Obsolete agent task file `agents/tasks/UNTESTED-20260327-1542-paypal-authorizedjson-stdclass-typeerror.md`.
