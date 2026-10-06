# Serra admin chat (v1) — context pack

Maestro-style **catalog → context**: this folder is the **minimum map** the admin chatbot must load every turn.

| File | Role |
|------|------|
| [CONTEXT.md](CONTEXT.md) | Who the bot is, domain, providers, load order |
| [DENY.md](DENY.md) | Hard no (read first after CONTEXT) |
| [ALLOW.md](ALLOW.md) | What v1 may do |
| [SCHEMA-BRIEF.md](SCHEMA-BRIEF.md) | Allowlisted tables (not the full schema dump) |
| [TOOLS.md](TOOLS.md) | Tool contracts — **truth lives in PHP tools**, not in the model |
| [EXAMPLES.md](EXAMPLES.md) | Few-shot Q → tool → compact answer |

**v1 scope:** read + exports only. No UI, no chat API, no writes.

**Not this pack:** storefront end-user chatbot (canned / Scout search). That is a later surface.

When wiring the runtime, inject these files (or a built concatenation) as system context. Do not paste `AGENTS.md`, `.env`, or full `database/migrations`.
