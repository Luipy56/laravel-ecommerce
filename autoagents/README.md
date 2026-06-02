# autoagents — laravel-ecommerce

Markdown prompts and orchestration for **`cursor-agent`**. Paths are relative to **`autoagents/`**.

## Loop

```bash
./autoagents/autoagents-loop.sh          # full cycle every 5 minutes
./autoagents/autoagents-loop.sh log      # 001 GitHub / log reviewer
./autoagents/autoagents-loop.sh feat     # feature coder (FEAT-*.md)
./autoagents/autoagents-loop.sh coder    # main coder (NEW / WIP)
./autoagents/autoagents-loop.sh handoff  # 012 WIP → UNTESTED
./autoagents/autoagents-loop.sh tester
./autoagents/autoagents-loop.sh closing-review
./autoagents/autoagents-loop.sh committer
```

Requires **`cursor-agent`** on PATH. GitHub: **`./scripts/setup-autoagents-gh.sh`**.

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
- **`gh_issue_sync.py`** — GitHub comment/label/close on archive
