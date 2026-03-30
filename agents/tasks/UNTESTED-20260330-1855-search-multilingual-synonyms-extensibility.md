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

## Implementation summary (coder)

- **`config/search_synonyms.php`:** `groups` (synonym sets), `enabled`, `max_expansions_per_token` (bounds SQL-side variants per token). Terms normalized with `Product::normalizeSearchText` when the dictionary is built.
- **`App\Services\Search\SearchSynonymDictionary`:** expands normalized query tokens for PostgreSQL/SQLite (`OR` within each token slot, `AND` across slots); builds Elasticsearch `synonym_graph` lines and an index overlay (`product_synonym` analyzer on text fields; **`suggest`** completion unchanged).
- **`ProductSearchService`:** uses the dictionary after tokenization for both `pgsql` and fallback drivers.
- **`AppServiceProvider::boot`:** merges `elasticsearchIndexOverlay()` into `scout.elasticsearch.index_definitions.products` when synonym lines are non-empty (so ES index create uses one config source with SQL).
- **`config/search_locales.php`:** documents future per-locale product labeling strategies without DB changes.
- **`docs/elasticsearch.md`:** operator steps for synonyms and reindex; points to tooling FEAT for fuller Artisan automation.
- **Tests:** `tests/Unit/SearchSynonymDictionaryTest.php`, synonym feature test on `ProductSearchService`, mapping merge assertion in `ScoutElasticsearchMappingTest`.
- **`CHANGELOG.md`:** `[Unreleased]` entry for synonym config and locale placeholder.

---

## Testing instructions

1. `./scripts/git-sync-agent-branch.sh`.
2. `php artisan test` — synonym dictionary unit tests, `ProductSearchServiceTest` (including synonym catalog case), `ScoutElasticsearchMappingTest`.
3. `php artisan routes:smoke` — no HTTP 500 (routes unchanged; quick regression).
4. **Optional (Elasticsearch):** set non-empty `search_synonyms.groups`, recreate product index (`scout:flush` / `scout:index` / `scout:import` per `docs/elasticsearch.md`), confirm search behaviour for synonym pairs on the ES path.

## Test report

- (Tester fills after running the above.)
