# Task workflow (laravel-ecommerce)

Tasks move through a single pipeline from creation to closure. See **`docs/agent-loop.md`** for roles and ecommerce-specific rules. **Before renaming or editing task files**, sync the integration branch with **`./scripts/git-sync-agent-branch.sh`** (multi-agent workflow; **`laravel-ecommerce-agent-loop.sh`** does this each step).

## Filename pattern

`<STATUS>-<YYYYMMDD-HHMM>-<slug>.md`

Examples: `NEW-20260323-1030-fix-checkout-validation.md`, `CLOSED-20260323-1200-fix-checkout-validation.md`

The **`<YYYYMMDD>`** segment (8 digits after the first `-`) is used to place archived tasks under **`done/YYYY/MM/DD/`** (see below). When renaming a task to **`CLOSED-…`**, use the **calendar day that work finished** (UTC or your team convention) in `YYYYMMDD` so each day’s folder reflects tasks completed that day.

## Statuses

| Status       | Meaning |
|--------------|--------|
| **new**      | Task is defined and not yet started. |
| **feat**     | Feature-sized task (optional parallel queue). |
| **wip**      | Work in progress. |
| **untested** | Implementation done; **Testing instructions** appended; waiting for tester. |
| **testing**  | Tester is running verification. |
| **closed**   | Verified; ready for closing reviewer to archive. |

## Flow

```text
  new   ─┐
         ├─→  wip  →  untested  →  testing  →  closed  →  done/YYYY/MM/DD/
  feat  ─┘
```

Do not skip statuses. On test failure: **testing → wip** (coder fixes), then **wip → untested** again when ready.

## Archiving closed tasks (`done/` layout)

Closed tasks are **not** kept in a single flat **`done/`** directory. After the closing reviewer prepends the **Closing summary**, the file is moved to:

```text
agents/tasks/done/<YYYY>/<MM>/<DD>/<same-filename>.md
```

- **`<YYYY>`**, **`<MM>`**, and **`<DD>`** come from the **8-digit date in the filename** (`YYYYMMDD` right after the status prefix), not from “today” when you run the script.
- **Same basename** as in **`agents/tasks/`**; only the directory changes.

**Helper (recommended):** from repo root,

```bash
./scripts/move-agent-task-to-done.sh agents/tasks/CLOSED-20260323-1200-example-slug.md
```

The script only accepts **`CLOSED-`** filenames.

See **`done/README.md`** for a short index of the archive tree.

## Rules of thumb

- **new → wip** / **feat → wip** when work starts.
- **wip → untested** when implementation is complete and **Testing instructions** are at the end of the task file.
- **untested → testing** when the tester starts.
- **testing → closed** when verification passes: rename **`TESTING-…`** → **`CLOSED-…`** (keep the same **`YYYYMMDD-HHMM-slug`**; only change the status prefix). On failure, **testing → wip**.
- **closed → done/YYYY/MM/DD/** after the closing summary is added (use **`move-agent-task-to-done.sh`** or an equivalent `mkdir` + `mv`).
