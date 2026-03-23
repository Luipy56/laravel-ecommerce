/**
 * Dispatch global toasts from non-React code (e.g. axios interceptors).
 * ToastProvider subscribes to this event.
 */
export function emitAppToast(message, type = 'info') {
  if (typeof window === 'undefined') return;
  window.dispatchEvent(new CustomEvent('app:toast', { detail: { message, type } }));
}
