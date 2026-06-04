#!/usr/bin/env python3
"""
Issue Checker Agent — creates FEAT task files from open GitHub issues.
Uses gh CLI; repo from AGENT_GH_REPO env (default Luipy56/laravel-ecommerce).
After each new FEAT file, posts a GitHub comment and sets agent:planned (see lib/gh_issue_actions.py).
"""
from __future__ import annotations

import json
import os
import subprocess
import sys
from datetime import datetime, timezone

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, SCRIPT_DIR)

from lib.env import gh_repo, load_env  # noqa: E402
from lib.gh_issue_actions import ensure_agent_labels, notify_feat_planned  # noqa: E402

load_env()

TASKS_DIR = os.path.join(SCRIPT_DIR, "tasks")
MAX_PER_RUN = int(os.environ.get("AGENT_001_MAX_ISSUES", "3"))


def has_task_file(issue_num: int) -> bool:
    """True if any pipeline task for this issue exists (active queue or done/)."""
    prefixes = (
        f"FEAT-{issue_num}-",
        f"NEW-{issue_num}-",
        f"WIP-{issue_num}-",
        f"UNTESTED-{issue_num}-",
        f"TESTING-{issue_num}-",
        f"CLOSED-{issue_num}-",
    )

    def dir_has_match(directory: str) -> bool:
        if not os.path.isdir(directory):
            return False
        for f in os.listdir(directory):
            if f in ("README.md", "TEMPLATE.md") or not f.endswith(".md"):
                continue
            for p in prefixes:
                if f.startswith(p):
                    return True
            path = os.path.join(directory, f)
            if os.path.isfile(path):
                with open(path, encoding="utf-8") as fh:
                    text = fh.read()
                if f"#{issue_num}" in text or f"/issues/{issue_num}" in text:
                    return True
        return False

    if dir_has_match(TASKS_DIR):
        return True
    done_root = os.path.join(TASKS_DIR, "done")
    if not os.path.isdir(done_root):
        return False
    for root, _dirs, files in os.walk(done_root):
        for f in files:
            if not f.endswith(".md"):
                continue
            for p in prefixes:
                if f.startswith(p):
                    return True
    return False


def get_open_issues() -> list[dict]:
    repo = gh_repo()
    try:
        result = subprocess.run(
            [
                "gh", "issue", "list",
                "--repo", repo,
                "--state", "open",
                "--json", "number,title,url",
            ],
            capture_output=True,
            text=True,
            check=True,
            timeout=60,
        )
        return json.loads(result.stdout)
    except (subprocess.CalledProcessError, json.JSONDecodeError, FileNotFoundError):
        return []


def fetch_issue_details(issue_num: int) -> dict | None:
    repo = gh_repo()
    try:
        result = subprocess.run(
            [
                "gh", "issue", "view", str(issue_num),
                "--repo", repo,
                "--json", "body,state,title,url,labels,createdAt",
            ],
            capture_output=True,
            text=True,
            check=True,
            timeout=30,
        )
        data = json.loads(result.stdout)
        data["number"] = int(issue_num)
        return data
    except (subprocess.CalledProcessError, json.JSONDecodeError, FileNotFoundError):
        return None


def create_task(issue: dict) -> str:
    num = issue["number"]
    title = issue["title"]
    url = issue["url"]
    body = issue.get("body") or ""
    created = issue.get("createdAt", "")
    labels = issue.get("labels", [])
    labels_str = ", ".join(str(l.get("name", "")) for l in labels) if labels else "none"
    repo = gh_repo()

    clean_body = body.replace("\n", " ") if body else "[No issue body]"
    summary = clean_body[:250].strip()
    if len(clean_body) > 250:
        summary += "..."

    slug = title.lower()
    for ch in " /_":
        slug = slug.replace(ch, "-")
    slug = "".join(c if c.isalnum() or c == "-" else "" for c in slug)[:40].strip("-") or "issue"

    now = datetime.now(timezone.utc).strftime("%Y%m%d-%H%M")
    filename = f"FEAT-{num}-{now}-{slug}.md"
    filepath = os.path.join(TASKS_DIR, filename)

    content = f"""# {title}

## GitHub Issue
- **Issue:** {url}
- **Number:** #{num}
- **Labels:** {labels_str}
- **Created:** {created}

## Problem / goal
{summary}

## High-level instructions for coder
- Read the full issue at {url}
- Identify affected paths under app/, routes/, resources/js/, database/, tests/
- Implement minimal, on-scope changes for laravel-ecommerce
- Follow .cursor/rules/project-standards.mdc and i18n (ca/es/en)
- Add **Testing instructions** before renaming to UNTESTED-

## References
- Repo: https://github.com/{repo}
- Agent loop: docs/agent-loop.md
"""
    os.makedirs(TASKS_DIR, exist_ok=True)
    with open(filepath, "w", encoding="utf-8") as f:
        f.write(content)
    return filepath


def run_workflow() -> bool:
    repo = gh_repo()
    print("=" * 60)
    print("Issue Checker (autoagents / laravel-ecommerce)")
    print(f"Repo: {repo}")
    print("=" * 60)

    ensure_agent_labels()

    issues = get_open_issues()
    if not issues:
        print("\nNo open GitHub issues (or gh not authenticated).")
        return False

    created = 0
    for issue in issues:
        if created >= MAX_PER_RUN:
            print(f"\nReached limit ({MAX_PER_RUN}) for this run.")
            break
        num = issue["number"]
        if has_task_file(num):
            print(f"  skip #{num} — task already exists (queue or done/)")
            continue
        details = fetch_issue_details(num)
        if not details:
            print(f"  skip #{num} — could not fetch details")
            continue
        labels = [l.get("name", "") for l in details.get("labels", [])]
        if "waiting for human validation" in labels:
            print(f"  skip #{num}, waiting for human validation")
            continue
        if "agent:planned" in labels:
            print(f"  skip #{num} — agent:planned")
            continue
        path = create_task(details)
        bn = os.path.basename(path)
        print(f"  created: {bn}")
        if notify_feat_planned(num, bn):
            print(f"  GitHub: comment + agent:planned on #{num}")
        else:
            print(f"  GitHub: notify failed for #{num} (check GH_TOKEN / label permissions)", file=sys.stderr)
        created += 1

    print(f"\nCreated {created} task file(s)")
    return created > 0


if __name__ == "__main__":
    run_workflow()
