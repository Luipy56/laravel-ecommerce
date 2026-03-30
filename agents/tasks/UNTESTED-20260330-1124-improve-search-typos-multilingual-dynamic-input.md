# Improve search: typos, multilingual (ca/es), dynamic input (lightweight)

## GitHub

- **Issue:** https://github.com/Luipy56/laravel-ecommerce/issues/7

## Problem / goal

Product data mixes Catalan and Spanish names, but storefront search is brittle: no typo tolerance, no cross-language matching (query in one locale misses products titled in the other), and search only runs on explicit submit (Enter) instead of while typing. The issue asks for better relevance and UX **without** heavy infrastructure: fuzzy matching, normalization, a lightweight bilingual bridge (synonyms / mapping), debounced “as-you-type” queries, optional small caches, and careful performance so the server stays stable.

## High-level instructions for coder

- Map the current end-to-end search path (React search bar, API params, DB/query layer) and measure or reason about cost before adding logic.
- Add **debounced** search on input change (roughly 200–400 ms) so typing does not spam the backend; keep accessibility (keyboard, reduced motion) in mind.
- **Normalize** query and comparable product fields (case, accents/diacritics) consistently on both sides.
- Introduce **lightweight fuzzy / approximate** matching with bounded cost (e.g. distance threshold, prefix + edit distance on tokens, or SQL-friendly patterns)—avoid full-table scans that scale poorly without guardrails.
- Add a **small, maintainable Catalan ↔ Spanish** mapping or synonym layer so equivalent terms can match across stored titles/descriptions; prefer config or lang-driven data over hardcoded UI strings.
- Preserve **ranking** that feels sensible (exact and normalized matches before fuzzy hits); cap result sets and complexity.
- Add or extend **automated tests** for typo cases, cross-language cases, and debounce/API behaviour where practical; run the project’s usual verification (`php artisan test`, route smoke, `npm run build` if front-end touched).
- Document any new env knobs or operational limits briefly for deployers if behaviour depends on configuration.

---

## Testing instructions

1. From repo root: `./scripts/git-sync-agent-branch.sh` (if needed), then `php artisan test`, `php artisan routes:smoke`, and `npm run build` (front-end touched).
2. **API:** New coverage in `tests/Unit/ProductSearchTest.php` and `tests/Feature/ProductStorefrontSearchTest.php` (synonym `cargol` → product titled with “Tornillo”, fuzzy typo `SpecialGadgetWidgit` → `SpecialGadgetWidget`, `GET /api/v1/products/search?q=martillo` vs product “Kit martell groc”).
3. **Manual storefront:** Open `/products` or `/categories/{id}/products`; type at least **two characters** in the navbar search and wait ~300ms without submitting — the URL `search` param should update (`replace: true`) and the catalog refetch. Single-character input should **not** change the URL until a second character or clear. Submit still navigates/updates immediately (including one character if submitted).
4. **Config (optional):** `.env.example` documents `PRODUCT_SEARCH_FUZZY_FALLBACK`, `PRODUCT_SEARCH_FUZZY_CANDIDATES`, `PRODUCT_SEARCH_MAX_VARIANTS`; synonym lists live in `config/product_search.php`.
