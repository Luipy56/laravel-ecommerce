# Search: synonyms and multilingual preparation (extendable, not over-engineered)

## Epic / tracking

- **Program:** PostgreSQL + Elasticsearch search platform.

## Dependencies

- **Requires:** `FEAT-20260330-1845-search-api-elasticsearch-primary-postgresql-fallback.md` (or at minimum SearchService + API contract stable enough to extend).

## Goal

Prepare **extension points** for:

1. **Synonyms:** configurable list or file consumed by **both** PostgreSQL query expansion (token-level) **and** Elasticsearch analyzer settings (e.g. synonym filter) — **one source of truth** preferred (e.g. `config/search_synonyms.php` or JSON under `config/`).
2. **Multilingual:** do **not** fully implement N languages; add **structure** for future fields (e.g. `name_ca`, `name_es`, `name_en` **or** JSON `name` map) — **only** if product schema evolution is approved under project standards (remember: **no stray new migrations for column changes** unless they are **new tables** — if new localized columns are required, edit existing product migration per **`.cursor/rules/project-standards.mdc`** and **`diagramZero.dbml`**).
3. **User-facing strings:** follow **`.cursor/rules/i18n.mdc`** (ca/es); configuration keys and code comments in English.

## Constraints

- **Do not over-engineer:** no full CMS translation workflow, no ML.
- **Performance:** synonym expansion must stay bounded (small lists, cache in config).

## Verification (before **UNTESTED-**)

- Unit tests: synonym expansion affects query or ES query body as expected (mock ES).
- Document how operators add synonyms and reindex (cross-link to tooling FEAT).

---

## Testing instructions

1. `./scripts/git-sync-agent-branch.sh`.
2. `php artisan test` — synonym / locale-structure tests.
3. `php artisan routes:smoke` if routes touched.
