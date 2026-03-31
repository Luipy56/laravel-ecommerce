# Inconsistent Toast on Product Searchbar

## GitHub
- **Issue:** https://github.com/Luipy56/laravel-ecommerce/issues/8

## Problem / goal
While typing continuously in the storefront product search bar, repeated query reloads can show a misleading toast (“No se pudo conectar con el servidor…”) even though the app is working. The reporter suspects a brief transient failure during overlapping requests rather than a sustained outage. An occasional browser console message about `content.js` / message port may be extension-related and should be distinguished from app bugs. Goal: find what triggers the toast, avoid surfacing transient search states as user-facing errors, and align UX with actual connectivity.

## High-level instructions for coder
- Trace the search bar / debounced live search flow (API calls, abort/cancel of in-flight requests, error handling) and where the “could not connect to server” (or equivalent i18n key) toast is fired.
- Reproduce under rapid typing; confirm whether errors are aborted `fetch`/axios, race conditions, or real HTTP failures.
- Fix so cancelled/overlapping requests or benign network noise do not show as fatal toasts; keep real failures visible.
- Document or dismiss `content.js` message-port noise if it is from a browser extension, without changing product code solely for that unless the team agrees.

## Implementation summary
- **Cause:** The global axios response interceptor in `resources/js/api.js` showed `errors.network` whenever `!err.response && err.request`. React Query passes an `AbortSignal` to `api.get('products', …)` on `ProductListPage`; when the debounced navbar search updates the URL rapidly, in-flight catalog requests are **cancelled**. Cancelled axios errors have no `response` but still have `request`, so they were misclassified as “could not connect.”
- **Change:** Skip the network toast when the error is an axios cancellation (`axios.isCancel`, `err.code === 'ERR_CANCELED'`, or `err.name === 'CanceledError'`). Real offline / connection failures still show the toast.
- **Note:** Console `content.js` / message port messages come from browser extensions, not this app; no product code change for that.

## Testing instructions
1. Build already run: `npm run build` (pass); `php artisan test` (pass).
2. Manual: Open `/products`, type quickly in the header search (debounced live navigation). Confirm **no** red “could not connect to server” toast appears while the list updates normally.
3. Optional: DevTools Network → throttle to “Slow 3G”, trigger a **real** failed request (e.g. stop server mid-request) and confirm the network error toast **still** appears for genuine failures.
4. Regression: Smoke another API flow (e.g. add to cart) with network disconnected to ensure error toasts still work when not cancelled.
