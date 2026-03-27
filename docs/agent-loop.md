# Agent loop — multi-agent workflow (laravel-ecommerce)

This document defines a **multi-agent workflow** for this repository, modeled on the **POS** (`~/Repos/pos/`) and **mac-stats-reviewer** pattern: separate **coordination** (tasks, prompts, optional CLI loop) from **implementation** (Laravel + React in this repo). Application code and agent coordination both live here; the split is **logical** (roles and folders).

---

## Goals

- **Traceable work:** Each change flows through named stages (task file renames).
- **Separation of concerns:** Review/analysis agents do not implement; coders implement; testers verify with evidence; closing reviewer archives; committer handles changelog/version only.
- **Stack alignment:** Follow **`AGENTS.md`**, **`.cursor/rules/`** (catalog: **`docs/agent-cursor-rules.md`**), **`php artisan test`**, **`php artisan routes:smoke`**, **`npm run build`**, and project i18n/standards rules.

---

## Git branching and production (essential)

| Branch | Role |
|--------|------|
| **`agentdevelop`** (default integration) | **Routine work.** Agents commit here and **`git push origin agentdevelop`** unless **`AGENT_GIT_BRANCH`** overrides. |
| **`master`** | **Stable line** — merge or promote only per policy below. |
| **`prod`** | Optional **deployment** branch for some teams; align human release docs with actual practice. |

### When to merge `agentdevelop` → `master` (or cut a release)

Merge or promote **only** if **at least one** applies:

1. **~2-hour cadence** — Batch integrate tested commits about every **two hours** (operator choice), not every tiny commit.
2. **Big production change** — Security, payments, data integrity, critical breakage, blocking migrations.
3. **Urgent / explicit production** — GitHub issue or human says **urgent**, **hotfix**, **production**, **deploy now**, or similar. Label **`production-urgent`** when used.

If **none** of the above applies: **push `agentdevelop`** (or your **`AGENT_GIT_BRANCH`**) only; do **not** routinely merge to **`master`**.

**Cursor / agents:** **`.cursor/rules/git-agent-branch-workflow.mdc`** (`alwaysApply: true`) encodes sync and branch habits.

**Committer agent:** Changelog and version bumps happen on the **integration branch**; promoting to **`master`** is a **separate** step.

### Sync before edits (multi-agent)

Before any step that edits the repo (code, **`agents/tasks/*.md`**, **`CHANGELOG.md`**, etc.):

- Run **`./scripts/git-sync-agent-branch.sh`** from the repo root (or equivalent fetch + checkout **`AGENT_GIT_BRANCH`** + **`git pull --rebase --autostash origin <branch>`**).
- **`laravel-ecommerce-agent-loop.sh`** runs that script at the start of each step unless **`AGENT_GIT_SYNC=0`**.
- Before **push**: pull/rebase again so **`git push`** does not race another agent.

---

## Roles

| Agent | POS-style role | Typical inputs | Writes / edits |
|-------|----------------|----------------|----------------|
| **001** Log reviewer | GitHub → **FEAT**; logs → **NEW** | Issues, **`storage/logs/laravel.log`**, optional Docker logs | **`agents/tasks/`** only; **`time-of-last-review.txt`** |
| **002** Coder | Implementer (main) | **NEW** / **WIP** | **`app/`**, **`resources/js/`**, tests, task file → **UNTESTED** |
| **006** Feature coder | **FEAT** queue only | **FEAT** → **WIP** | Same as coder |
| **003** Tester | Verifier | **UNTESTED** → **TESTING** | Test report; **CLOSED** or **WIP** |
| **004** Closing reviewer | Archivist | **CLOSED** in **`agents/tasks/`** | Prepends summary; **`move-agent-task-to-done.sh`** |
| **007** Committer | Changelog / version | Dirty git tree | **`CHANGELOG.md`**, root **`package.json`** / lockfile; git commit/push |

---

## Task workflow

See **`agents/tasks/README.md`** for filename pattern and statuses.

```text
  new   ─┐
         ├─→  wip  →  untested  →  testing  →  closed  →  done/YYYY/MM/DD/
  feat  ─┘
```

**Tester loop protection:** If the same task fails verification **more than three** times, document in **Test report** and follow team policy (e.g. close with explanation).

---

## Agent loop script (`agents/laravel-ecommerce-agent-loop.sh`)

| Invocation | Behaviour |
|------------|-----------|
| **`./agents/laravel-ecommerce-agent-loop.sh`** | Full cycle every **`AGENT_LOOP_SLEEP_MINUTES`** (default **5**); requires **`cursor-agent`** on `PATH`. |
| **`./agents/laravel-ecommerce-agent-loop.sh log`** (or **`001`**) | Run **001** log / GitHub reviewer. |
| **`./agents/laravel-ecommerce-agent-loop.sh coder`** | Coder if **`NEW-*.md`** or **`WIP-*.md`** exists. |
| **`./agents/laravel-ecommerce-agent-loop.sh feat`** | Feature coder if **`FEAT-*.md`** exists. |
| **`./agents/laravel-ecommerce-agent-loop.sh tester`** | Tester if **`UNTESTED-*.md`** exists. |
| **`./agents/laravel-ecommerce-agent-loop.sh closing-review`** | Closer if **`CLOSED-*.md`** still in **`agents/tasks/`**. |
| **`./agents/laravel-ecommerce-agent-loop.sh committer`** | Committer if there are unstaged/staged changes. |
| **`./agents/laravel-ecommerce-agent-loop.sh help`** | Usage. |

**Git:** each step begins with **`scripts/git-sync-agent-branch.sh`** (unless **`AGENT_GIT_SYNC=0`**). **Branch:** **`AGENT_GIT_BRANCH`** (default **`agentdevelop`**).

**Stack:** start **`php artisan serve`** and **`npm run dev`** (or your stack) separately; the loop does **not** start the app.

**Do not** run two loop steps **in parallel** in the same clone (each step runs **`git pull --rebase`**; concurrent pulls can fail with *Cannot rebase onto multiple branches*).

---

## Committer (this repo)

- Update **`CHANGELOG.md`** under **`[Unreleased]`** (Keep a Changelog).
- Version: root **`package.json`** and **`package-lock.json`** (see **`.cursor/rules/commit-changelog-version.mdc`**).
- **No** application source edits in this role.
- **Push** the integration branch; merge to **`master`** only per the branching table above.

---

## GitHub Issues integration (optional)

**Repo:** [github.com/Luipy56/laravel-ecommerce/issues](https://github.com/Luipy56/laravel-ecommerce/issues)

Use **`gh`** with **`gh auth login`** or **`GH_TOKEN`**. Suggested labels (create if useful): **`agent:planned`**, **`agent:wip`**, **`agent:testing`**, **`production-urgent`**.

| Role | When | Suggested issue update |
|------|------|-------------------------|
| **001** | After creating **`FEAT-…`** for **#NN** | Comment with task path; **`agent:planned`**. |
| **002 / 006** | **new/feat → wip** | Comment; **`agent:planned` → `agent:wip`**. |
| **003** | **untested → testing** | Comment; **`agent:wip` → `agent:testing`**. |
| **004** | After archive | Comment outcome; remove agent labels; close issue if done. |

**GitHub → always `FEAT-`**, never **`NEW-`**. **Logs/incidents → `NEW-`**.

---

## Related

- **`AGENTS.md`** — operator guide and smoke checks.
- **`.cursor/rules/git-agent-branch-workflow.mdc`** — always-on branch rules.
- **`.cursor/rules/commit-changelog-version.mdc`** — changelog and version bump.
- **`agents/README.md`** — prompt file index.
