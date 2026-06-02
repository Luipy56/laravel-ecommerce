# Archived tasks (`done/`)

Closed agent tasks live here under **year / month / day** folders derived from each file’s date segment in the **`CLOSED-…`** name.

## Layout

```text
done/
  README.md          ← this file
  2026/
    03/
      23/
        CLOSED-20260323-1200-example-task.md
        CLOSED-20260323-1530-second-task-same-day.md
```

- **Legacy filenames:** `CLOSED-YYYYMMDD-HHMM-slug.md` (historical — do not rename).
- **New filenames:** `CLOSED-<issue>-YYYYMMDD-HHMM-slug.md` when linked to GitHub **#N**.
- **Year / month / day** come from the **8-digit `YYYYMMDD`** in the filename.

## Moving a file here

From the repository root:

```bash
./scripts/move-agent-task-to-done.sh autoagents/tasks/CLOSED-22-20260424-1602-slug.md
```

Do not add new closed tasks directly under **`done/`** without **`YYYY/MM/DD`** parents; the helper enforces the layout.
