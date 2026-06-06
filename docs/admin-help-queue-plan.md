# Admin Help intake — queue and autoissue plan

Plan for collecting internal admin requests from `/admin/help` and turning them into GitHub issues via `cursor-agent` + `gh`, aligned with the km0-web user ideas queue pattern.

**Status:** Admin UI, API receiver, Laravel queue processor (cursor-agent draft + `gh issue create`), daily scheduler fallback.  
**Related:** [DEPLOY.md](./DEPLOY.md), [agent-loop.md](./agent-loop.md).

---

## Goal

```text
Admin form (/admin/help)
  → POST /api/v1/admin/help-requests (auth:admin, body includes label)
  → Atomic write to storage/app/admin-help/pending/{uuid}.json
  → ProcessAdminHelpIssueJob (queue, immediate)
  → AdminHelpIssueProcessor: cursor-agent drafts markdown, then gh issue create
  → GitHub issue with label from JSON (to-staging | waiting for human validation)
  → to-staging: autoagents 001 picks up → FEAT task → staging deploy
  → waiting for human validation: skip until human removes/changes label
```

---

## Directory layout (Laravel storage)

| Path | Purpose |
|------|---------|
| `storage/app/admin-help/pending/` | New submissions |
| `storage/app/admin-help/processing/` | Claimed jobs |
| `storage/app/admin-help/drafts/` | Ephemeral cursor-agent drafts at runtime |
| `storage/app/admin-help/processed/` | Successful runs + `{id}.meta.json` audit |
| `storage/app/admin-help/failed/` | Validation or runtime errors |

Repo prompt: **`autoissue/admin-help-agent.md`** (same role as km0 `autoissue/autoissue-agent.md`).

---

## Queue file format (JSON)

**Filename:** `{uuid}.json`

**Body:**

```json
{
  "id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
  "receivedAt": "2026-06-04T15:30:12Z",
  "submittedBy": { "id": 1, "username": "manager" },
  "title": "Optional title",
  "comment": "Plain text from the admin.",
  "label": "to-staging",
  "meta": {
    "userAgent": "Mozilla/5.0 ...",
    "remoteAddr": "203.0.113.42",
    "source": "admin_help"
  },
  "status": "pending"
}
```

| Field | Rules |
|-------|--------|
| `comment` | Required, max 4000 chars |
| `title` | Optional, max 200 chars |
| `label` | Optional in POST; allowed: `to-staging`, `waiting for human validation`. Invalid or missing → **`waiting for human validation`** (safe fallback) |
| `meta` | Set by API only |

---

## Processor (AdminHelpIssueProcessor)

1. **Claim** jobs atomically (`pending` → `processing`) with file lock (`.processor.lock`).
2. Run **cursor-agent** with `autoissue/admin-help-agent.md` to write draft `.md` from queue JSON.
3. Parse draft frontmatter (`title`) and body; **`gh issue create`** with **`label`** from queue JSON.
4. Archive JSON + draft + `{id}.meta.json` under `processed/`; on failure release to `pending/` or move to `failed/`.

**cursor-agent flags (same as km0 autoissue):**

```bash
cursor-agent --yolo --print --trust --workspace <repo_root> "<prompt>"
```

**Timeout:** 900 seconds (config `ADMIN_HELP_CURSOR_AGENT_TIMEOUT`).

---

## Triggers

| Trigger | Mechanism | When |
|---------|-----------|------|
| **Instant** | `ProcessAdminHelpIssueJob` dispatched after successful POST | Sub-second to minutes (queue worker) |
| **Fallback 24h** | `php artisan admin-help:process` in scheduler at `03:00` UTC | Catches missed jobs (worker down, cursor-agent failure) |

Equivalent to km0 systemd `.path` (instant) + `.timer` (`OnUnitActiveSec=24h`).

---

## Issue labels (autoagents)

Admin chooses **`label`** in the form (stored in queue JSON):

| Label | autoagents |
|-------|------------|
| **`to-staging`** | Picked up on next cycle → **`FEAT-<N>-*.md`**, **`agent:planned`**, staging deploy |
| **`waiting for human validation`** | **Skipped** by **`issue_checker_agent.py`** and **`001-gh-reviewer.md`** until a human removes or changes the label |

Bootstrap both labels: **`./scripts/gh-bootstrap-agent-labels.sh`**.

---

## Runtime dependencies (stage / production)

The **queue** worker container must have on `PATH`:

- **`cursor-agent`** (same CLI as autoagents loop on the host)
- **`gh`** (installed in the PHP Docker image)

Set **`GH_TOKEN`** in the stack `.env` (Issues read/write on the target repo). The processor passes it to `gh` subprocesses.

See [DEPLOY.md](./DEPLOY.md) · Admin Help processor.

---

## Verification

```bash
# Enqueue via API (authenticated admin)
curl -sS -X POST http://localhost:8080/api/v1/admin/help-requests \
  -H 'Content-Type: application/json' \
  -b cookies.txt \
  -d '{"comment":"Test internal request"}'

# Manual drain
php artisan admin-help:process --limit=1

# Inspect queue
ls -la storage/app/admin-help/pending/
ls -la storage/app/admin-help/processed/
```

Expect GitHub issue with the chosen **`label`**. With **`to-staging`**, autoagents creates a FEAT task on the next cycle; with **`waiting for human validation`**, the loop skips until triage.

---

## References

- km0-web: `docs/user-ideas-queue-plan.md` (public `/ideas/` intake; host systemd processor)
- Original feature task: `autoagents/tasks/done/2026/06/04/CLOSED-26-*.md`
