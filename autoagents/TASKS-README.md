# Task workflow (autoagents / laravel-ecommerce)

Tasks move through a single pipeline. See **`docs/agent-loop.md`** for roles and GitHub label conventions. **Before renaming task files**, sync with **`./scripts/git-sync-autoagents-branch.sh`**.

## Filename pattern

**With GitHub issue:**

`<STATUS>-<GITHUB-ISSUE-NUMBER>-<YYYYMMDD-HHMM>-<slug>.md`

**Without issue (log incidents):**

`NEW-0-<YYYYMMDD-HHMM>-<slug>.md` or `NEW-<YYYYMMDD-HHMM>-<slug>.md` (legacy)

Examples: `FEAT-22-20260424-1602-version-not-displayed-correctly.md`, `CLOSED-22-20260424-1602-version-not-displayed-correctly.md`

**Legacy archive** (pre-migration, under **`done/`** only): `CLOSED-YYYYMMDD-HHMM-slug.md` — do not rename historical files.

The **`<YYYYMMDD>`** segment places archived tasks under **`done/YYYY/MM/DD/`**.

## Statuses

| Status       | Meaning |
|--------------|--------|
| **new**      | Task defined, not started (incidents from logs). |
| **feat**     | Feature-sized task from GitHub issue. |
| **wip**      | Work in progress. When done → **UNTESTED-**. |
| **untested** | Implementation done; **Testing instructions** appended. |
| **testing**  | Tester is verifying. |
| **closed**   | Verified; ready for closing reviewer. |

## Flow

```text
  new   ─┐
         ├─→  wip  →  untested  →  testing  →  closed  →  done/YYYY/MM/DD/
  feat  ─┘
```

On test failure: **testing → wip**, then **wip → untested** when ready.

## Discarded tasks

Tasks abandoned before implementation live under **`autoagents/tasks/discarded/`** with prefix **`DISCARDED-`**. They do not enter the pipeline.

## Archiving

```bash
./scripts/move-agent-task-to-done.sh autoagents/tasks/CLOSED-22-20260424-1602-example.md
```

→ **`autoagents/tasks/done/2026/04/24/CLOSED-22-20260424-1602-example.md`**

When the filename includes an issue number (**`CLOSED-<N>-***`), the move script closes GitHub **#N** via **`autoagents/gh_issue_sync.py`**.

## Rules of thumb

- **feat → wip** when feature coder starts.
- **wip → untested** when implementation complete + **Testing instructions** at end (012 handoff may confirm).
- **untested → testing** when tester starts.
- **testing → closed** on pass; **testing → wip** on fail.
- **closed → done/** after closing summary (move script).
