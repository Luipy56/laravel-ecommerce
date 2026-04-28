# Agent prompts (laravel-ecommerce)

Markdown prompts for **`cursor-agent`** (see **`laravel-ecommerce-agent-loop.sh`**). Paths are relative to **`agents/`**.

| # | File | Role |
|---|------|------|
| 001 | [001-log-reviewer/LOG-REVIEWER-PROMPT.md](001-log-reviewer/LOG-REVIEWER-PROMPT.md) | Log / incident analyst → task files |
| 002 | [002-coder/CODER.md](002-coder/CODER.md) | Implement **NEW-** tasks and continue **WIP-** (`app/`, `resources/js/`, etc.) |
| 003 | [003-tester/TESTER.md](003-tester/TESTER.md) | Verify **UNTESTED-** tasks |
| 004 | [004-closing-reviewer/CLOSING-REVIEWER-PROMPT.md](004-closing-reviewer/CLOSING-REVIEWER-PROMPT.md) | Archive **CLOSED-** tasks → **`tasks/done/YYYY/MM/DD/`** |
| 006 | [006-feature-coder/FEATURE-CODER.md](006-feature-coder/FEATURE-CODER.md) | Implement **FEAT-** tasks only |
| 007 | [007-committer/COMMITTER.md](007-committer/COMMITTER.md) | Changelog + version + git on integration branch |

Workflow: **`docs/agent-loop.md`**. Task rules: **`tasks/README.md`**.

**Git:** Before editing the repo, sync with **`./scripts/git-sync-agent-branch.sh`** (see **`.cursor/rules/git-agent-branch-workflow.mdc`**). **`laravel-ecommerce-agent-loop.sh`** runs it at the start of each step unless **`AGENT_GIT_SYNC=0`**. Branch: **`AGENT_GIT_BRANCH`** (default **`agentdevelop`**).
