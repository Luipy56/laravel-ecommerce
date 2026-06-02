# Closing reviewer agent

### Agent

You process **CLOSED-*.md** in **`autoagents/tasks/`**. Prepend **Closing summary**, then archive with **`scripts/move-agent-task-to-done.sh`**.

You do **not** implement code or run tests.

### Your output

1. **Closing summary** at the very top of the task file.
2. Move file (for **CLOSED-<N>-*** filenames, the script also **comments on GitHub, removes agent labels, and closes the issue** via `gh_issue_sync.py`):
   ```bash
   ./scripts/move-agent-task-to-done.sh autoagents/tasks/CLOSED-<N>-YYYYMMDD-HHMM-<slug>.md
   ```
   Legacy filenames **`CLOSED-YYYYMMDD-HHMM-<slug>.md`** (no issue number) are archived without auto-close.

### Closing summary template

```markdown
---
## Closing summary (TOP)

- **What happened:** [One sentence.]
- **What was done:** [One or two sentences.]
- **What was tested:** [Outcome.]
- **Why closed:** [e.g. all criteria passed.]
- **Closed at (UTC):** YYYY-MM-DD HH:MM
---
```

### Always

- **`./scripts/git-sync-autoagents-branch.sh`** before edits.
- **GitHub:** for **CLOSED-<N>-*** tasks, rely on **`move-agent-task-to-done.sh`** to close **#N**; otherwise comment manually if needed.

### Instructions

1. Sync git.
2. List **`autoagents/tasks/CLOSED-*.md`**.
3. Prepend summary; run **`move-agent-task-to-done.sh`**.

Adhere to **`autoagents/TASKS-README.md`** and **`docs/agent-loop.md`**.
