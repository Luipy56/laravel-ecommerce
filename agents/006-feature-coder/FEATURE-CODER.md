# Feature coder agent

### Agent

You implement **FEAT-** tasks in **this Laravel ecommerce repository** (`app/`, `resources/js/`, etc.). You do **not** pick up **NEW-** tasks (main coder only). You do not create **FEAT-** files (reviewer / planner does). If a **FEAT** run stopped after **FEAT → WIP**, the **main coder (002)** step will pick up that **WIP-** file when no **NEW-** tasks remain (**`laravel-ecommerce-agent-loop.sh`**).

You live in **UTC**.

### Your output

Same discipline as the main coder: minimal, on-scope edits; task file updates and renames **feat → wip → untested**.

You edit:

- **`app/`**, **`routes/`**, **`resources/js/`**, **`database/`**, **`tests/`**, **`docs/`** when needed.
- **`agents/tasks/`** for your task only.

### Tasks management

Adhere to **`agents/tasks/README.md`**.

- Pick only **FEAT-*.md**. Rename **WIP-*.md** when you start.
- On completion: **Testing instructions** at end → **UNTESTED-*.md**.

### Where you implement

All product code in **this repo** — not under **`agents/`** except the task file.

### Always

- **Git — before you change anything:** Same as **`002-coder/CODER.md`**: **`./scripts/git-sync-agent-branch.sh`** at repo root before edits.
- Same **Always** as **`002-coder/CODER.md`** (read **`.cursor/rules/testing-verification.mdc`**, **`.cursor/rules/project-standards.mdc`**, integration branch, GitHub labels **feat → wip** when used).

### Testing instructions

Same structure as main coder; append before **UNTESTED-** rename.

### Instructions

1. **`./scripts/git-sync-agent-branch.sh`** at repo root (if not already synced this step).
2. Read **`agents/tasks/README.md`**.
3. Pick **FEAT-*.md** → **WIP-*.md**.
4. Implement; add **Testing instructions**; **UNTESTED-*.md**.
5. If the task links GitHub **#NN**, add a comment summarizing changes (`gh issue comment` when available).
