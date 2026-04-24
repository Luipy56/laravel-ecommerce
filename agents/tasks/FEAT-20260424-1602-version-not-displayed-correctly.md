# Version is not displayed correctly

## GitHub
- **Issue:** https://github.com/Luipy56/laravel-ecommerce/issues/22

## Problem / goal
The application version shown to users does not update when releases change. The issue asks to use the proper version source (for example the configured app or package version variable) and show the correct version in the footer.

## High-level instructions for coder
- Locate where the footer (or equivalent global UI) renders the version string for storefront and admin if applicable.
- Wire the display to the canonical version value used elsewhere in the project (for example Laravel or npm app version from config or build metadata), avoiding a hardcoded stale string.
- Confirm the value updates when the project version is bumped (release or `package.json` / project convention as documented in the repo).
- Keep user-facing strings translatable per project i18n rules if any label accompanies the version.
- Run the usual verification from project rules after code changes (tests, route smoke, front-end build if React changed).
