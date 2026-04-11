/**
 * Dispatch global toasts from non-React code (e.g. axios interceptors, CartContext).
 * ToastProvider listens for `app:toast` and renders top-end toasts below the nav.
 *
 * Types: `success` (orange), `error`, `warning`, `info`.
 */
export function emitAppToast(message, type = 'info') {
  if (typeof window === 'undefined') return;
  window.dispatchEvent(new CustomEvent('app:toast', { detail: { message, type } }));
}
