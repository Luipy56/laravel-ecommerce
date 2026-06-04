"""Load autoagents/.env into os.environ (setdefault — shell exports win)."""

from __future__ import annotations

import os
from pathlib import Path

SCRIPT_DIR = Path(__file__).resolve().parent.parent


def load_env() -> None:
    env_file = SCRIPT_DIR / ".env"
    if not env_file.is_file():
        return
    for line in env_file.read_text(encoding="utf-8").splitlines():
        line = line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        key, _, val = line.partition("=")
        os.environ.setdefault(key.strip(), val.strip().strip('"').strip("'"))


def gh_repo(default: str = "Luipy56/laravel-ecommerce") -> str:
    load_env()
    return os.environ.get("AGENT_GH_REPO", default)
