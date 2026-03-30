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
