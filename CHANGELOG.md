# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed

- Root `README.md` replaces the default Laravel skeleton with a project-specific overview (stack, quick start, verification commands, links to `AGENTS.md` and `docs/*`); screenshot policy documented without adding binary assets (GitHub #3).
- Agent log reviewer: latest pass appended to `agents/001-log-reviewer/time-of-last-review.txt` (2026-03-30T10:12Z).

## [0.1.2] - 2026-03-29

### Added

- `scripts/gh-bootstrap-agent-labels.sh`: idempotent `gh` helper to create GitHub labels used by the multi-agent workflow (`agent:planned`, `agent:wip`, `agent:testing`, `production-urgent`).

### Changed

- PayPal storefront: SDK script URL uses `intent=capture` and `commit=true`; checkout copy (ca/es) and `docs/CONFIGURACION_PAGOS_CORREO.md` clarify popup/overlay vs full-page redirect and that server capture implies PayPal-side approval.
- Agent pipeline (GitHub #2 smoke): removed queue pickup `agents/tasks/UNTESTED-20260327-1614-test.md`; updated archived `agents/tasks/done/2026/03/27/CLOSED-20260327-1614-test.md` with tester report; added `agents/tasks/done/2026/03/27/CLOSED-20260327-1614-test-hello-world-coder-artifact.md` for verification traceability.
- Agent pipeline: closed PayPal sandbox E2E verification task (`agents/tasks/UNTESTED-20260327-1401-paypal-checkout-sandbox-e2e.md` → `agents/tasks/done/2026/03/27/CLOSED-20260327-1401-paypal-checkout-sandbox-e2e.md` with tester report); `agents/001-log-reviewer/time-of-last-review.txt` updated.
- Agent pipeline: archived PayPal buyer-approval UI task (`agents/tasks/CLOSED-20260327-1745-paypal-approval-ui-popup-vs-redirect.md` → `agents/tasks/done/2026/03/27/CLOSED-20260327-1745-paypal-approval-ui-popup-vs-redirect.md`).
- Agent log reviewer: latest pass appended to `agents/001-log-reviewer/time-of-last-review.txt` (2026-03-27T18:14Z).
- Agent pipeline: Stripe order-pay task tracking moved to `agents/tasks/WIP-20260329-2114-stripe-not-configured-order-pay.md` (coder + tester notes).

### Fixed

- Order pay / Stripe: when card checkout is started without valid Stripe keys, respond with **422** and `code: payment_method_not_configured` (translated message) instead of treating it as an application failure; `PaymentProviderNotConfiguredException` is not reported to the log; `GET /api/v1/payments/config` availability aligns with the same credential rules as checkout start (`dontReport` in `bootstrap/app.php`).

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
