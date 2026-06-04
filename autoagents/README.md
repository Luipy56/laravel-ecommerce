# autoagents — laravel-ecommerce

Markdown prompts and orchestration for **`cursor-agent`**. Paths are relative to **`autoagents/`**.

## Loop

```bash
./autoagents/autoagents-loop.sh                    # full cycle every 5 minutes
./autoagents/autoagents-loop.sh --dashboard        # loop + LAN task dashboard (port 8765)
./autoagents/autoagents-loop.sh log                # 001 GitHub / log reviewer
./autoagents/autoagents-loop.sh feat               # feature coder (FEAT-*.md)
./autoagents/autoagents-loop.sh coder              # main coder (NEW / WIP)
./autoagents/autoagents-loop.sh handoff            # 012 WIP → UNTESTED
./autoagents/autoagents-loop.sh tester
./autoagents/autoagents-loop.sh closing-review
./autoagents/autoagents-loop.sh committer
```

Requires **`cursor-agent`** on PATH. GitHub: **`./scripts/setup-autoagents-gh.sh`**.

## Dashboard

Ephemeral web UI for task pipeline progress (Kanban-style columns: GITHUB, NEW, FEAT, WIP, …):

```bash
./autoagents/autoagents-dashboard.sh
# or with the loop:
./autoagents/autoagents-loop.sh --dashboard
```

Configure in **`autoagents/.env`** (copy from **`.env.example`**):

| Variable | Default | Purpose |
|----------|---------|---------|
| `AGENT_GH_REPO` | `Luipy56/laravel-ecommerce` | GitHub issues for GITHUB column |
| `AGENT_DASHBOARD_PORT` | `8765` | HTTP port |
| `AGENT_PRIMORDIAL_CSS` | `~/Documents/primordial.css` | Theme stylesheet |
| `AGENT_TASKDIR` | `autoagents/tasks` | Active task queue path |

## Prompt index

| Step | File | Role |
|------|------|------|
| 001 | `001-gh-reviewer.md` | GitHub → FEAT; logs → NEW |
| 010 | `010-feature-coder.md` | Implement FEAT queue |
| 002 | `002-coder/CODER.md` | Implement NEW / WIP |
| 012 | `012-feature-coder-handoff.md` | WIP handoff check |
| 020 | `020-test.md` | Tester |
| 030 | `030-closing-reviewer.md` | Archive CLOSED |
| 040 | `040-committer.md` | CHANGELOG + package.json + git |

## Tasks

Active tasks: **`autoagents/tasks/`**. Archive: **`autoagents/tasks/done/`**. See **`TASKS-README.md`**.

## Git

Integration branch: **`autoagents`** (**`AGENT_GIT_BRANCH`** overrides). Sync: **`./scripts/git-sync-autoagents-branch.sh`**. See **`docs/agent-loop.md`** and **`.cursor/rules/git-agent-branch-workflow.mdc`**.

## Python helpers

- **`issue_checker_agent.py`** — auto-create FEAT from open issues
- **`sync_github_from_tasks.py`** — GitHub comment/label/close sync (planned + closed)
- **`lib/gh_issue_actions.py`** — shared gh CLI helpers
- **`gh_issue_sync.py`** — close issue when archiving via **`scripts/move-agent-task-to-done.sh`**
