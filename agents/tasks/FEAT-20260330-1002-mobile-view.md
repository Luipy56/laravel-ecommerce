# Mobile view

## GitHub
- **Issue:** https://github.com/Luipy56/laravel-ecommerce/issues/4

## Problem / goal
Several pages do not behave well on small viewports, and there is no clear, project-specific guidance on how to implement consistent responsive layouts (storefront and admin).

## High-level instructions for coder
- Review main user-facing and admin routes at typical mobile widths; fix layout issues (overflow, navigation, tables, forms, spacing) using Tailwind responsive utilities and daisyUI components per project rules.
- Prefer reusing shared layout and list patterns so fixes stay consistent across pages.
- Add brief, actionable documentation for contributors (for example in the root README or an existing `docs/` page) describing breakpoints and conventions for new UI work.
- After changes, smoke-check critical flows on a narrow viewport; keep all user-visible copy on translation keys (`ca` / `es`).
