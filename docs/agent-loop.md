# Agent loop — multi-agent workflow (laravel-ecommerce)

Multi-agent workflow: **coordination** (`autoagents/`, tasks, optional CLI loop) separate from **implementation** (Laravel + React). See **`autoagents/README.md`** for prompt index.

---

## Goals

- **Traceable work:** Named stages via task file renames.
- **Separation of concerns:** Reviewers plan; coders implement; testers verify; closer archives; committer handles changelog/version.
- **Stack alignment:** **`AGENTS.md`**, **`.cursor/rules/`**, **`php artisan test`**, **`php artisan routes:smoke`**, **`npm run build`**, i18n (ca/es/en).

---

## Git branching & deploy

| Branch | Role |
|--------|------|
| **`autoagents`** | Features and fixes → **staging** (`stage.yml`, https://stage-serra.ldeluipy.es). |
| **`prod`** | Release line → **production** (`prod.yml`, https://serra.ldeluipy.es) after staging sign-off. |
| **`main`** | Optional stable snapshot; no VPS auto-deploy. |

**Flow:** develop → push **`autoagents`** → validate on staging → merge **`autoagents` → `prod`** → production workflow.

Full ops detail: **`docs/DEPLOY.md`**. Deploy toggles: `STAGE_DEPLOY_ENABLED`, `DEPLOY_ENABLED` (both default off until secrets are verified).

**Rules:** **`.cursor/rules/git-agent-branch-workflow.mdc`**, **`.cursor/rules/autoagents-workflow.mdc`**.

### Sync before edits

```bash
./scripts/git-sync-autoagents-branch.sh
```

**`autoagents/autoagents-loop.sh`** runs this each step unless **`AGENT_GIT_SYNC=0`**.

---

## Roles

| Step | Agent | Typical inputs | Writes |
|------|-------|----------------|--------|
| **001** | GitHub / log reviewer | Issues, `laravel.log`, Docker logs | **`autoagents/tasks/`** FEAT / NEW; **`001-gh-reviewer/time-of-last-review.txt`** |
| **010** | Feature coder | **FEAT** → **WIP** | `app/`, `resources/js/`, tests |
| **002** | Main coder | **NEW** / **WIP** | Same |
| **012** | Feature handoff | **WIP** ready? | Rename → **UNTESTED** |
| **020** | Tester | **UNTESTED** → **TESTING** | Test report; **CLOSED** or **WIP** |
| **030** | Closing reviewer | **CLOSED** in tasks root | Summary; **`move-agent-task-to-done.sh`** |
| **040** | Committer | Dirty tree | **`CHANGELOG.md`**, **`package.json`**; git push |

**001 automation:** **`autoagents/issue_checker_agent.py`** creates FEAT files from open issues before cursor-agent runs.

---

## Task workflow

See **`autoagents/TASKS-README.md`**.

```text
  new   ─┐
         ├─→  wip  →  untested  →  testing  →  closed  →  done/YYYY/MM/DD/
  feat  ─┘
```

**New filenames:** `FEAT-<N>-YYYYMMDD-HHMM-slug.md`, `CLOSED-<N>-…`. **Legacy** `CLOSED-YYYYMMDD-…` remains in **`done/`** archive only.

**Discarded:** **`autoagents/tasks/discarded/DISCARDED-…`**.

**Tester loop protection:** After **three** failures, document in Test report and follow team policy.

---

## Agent loop script

```bash
./autoagents/autoagents-loop.sh              # full cycle every 5 min
./autoagents/autoagents-loop.sh log          # 001
./autoagents/autoagents-loop.sh feat         # 010
./autoagents/autoagents-loop.sh coder        # 002
./autoagents/autoagents-loop.sh handoff      # 012
./autoagents/autoagents-loop.sh tester     # 020
./autoagents/autoagents-loop.sh closing-review
./autoagents/autoagents-loop.sh committer    # 040
```

Requires **`cursor-agent`** on PATH. GitHub: **`./scripts/setup-autoagents-gh.sh`**.

**Stack:** start app separately (`docker compose up`, or `php artisan serve` + `npm run dev`). Do not run two loop steps in parallel in the same clone.

---

## Committer

- **`CHANGELOG.md`** under **`## [X.Y.Z] - date`** matching **`package.json`** bump.
- Patch bump: **`npm version patch --no-git-tag-version`** per **`.cursor/rules/commit-changelog-version.mdc`**.
- Push **`autoagents`**; merge **`master`** only per branching table.

---

## GitHub Issues

**Repo:** [github.com/Luipy56/laravel-ecommerce/issues](https://github.com/Luipy56/laravel-ecommerce/issues)

Labels: **`agent:planned`**, **`agent:wip`**, **`agent:untested`**, **`agent:testing`**, **`production-urgent`**. Bootstrap: **`./scripts/gh-bootstrap-agent-labels.sh`**.

| Role | When | Issue update |
|------|------|--------------|
| **001** / issue_checker | FEAT created for **#NN** | Comment + **`agent:planned`** |
| **010 / 002** | **feat/new → wip** | Comment; **`agent:wip`** |
| **012** | **wip → untested** | **`agent:untested`** |
| **020** | **untested → testing** | **`agent:testing`** |
| **030** / move script | Archive **CLOSED-<N>-*** | Comment, remove agent labels, close **#N** |

**GitHub → always `FEAT-<N>-`**, never **`NEW-`**. **Logs → `NEW-`**.

---

## Create task from chat

See **`.cursor/rules/autoagents-create-task-from-chat.mdc`**.

---

## Related

- **`AGENTS.md`**
- **`autoagents/README.md`**
- **`.cursor/rules/autoagents-workflow.mdc`**
