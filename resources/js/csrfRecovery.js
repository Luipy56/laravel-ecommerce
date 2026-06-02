const CSRF_COOKIE_PATH = '/csrf-cookie';
const AUTH_RELOAD_KEY = 'csrf419AuthReloadAttempted';
const SESSION_EXPIRED_RELOAD_KEY = 'sessionExpiredAutoReload';

/**
 * Refresh Laravel session CSRF cookie and meta token (for web forms and axios XSRF).
 * @returns {Promise<string>} fresh CSRF token for hidden _token fields
 */
export async function refreshCsrfCookie() {
  const res = await fetch(CSRF_COOKIE_PATH, {
    method: 'GET',
    credentials: 'same-origin',
    headers: {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  });
  if (!res.ok) {
    throw new Error(`CSRF refresh failed (${res.status})`);
  }
  const data = await res.json().catch(() => ({}));
  if (typeof data.token === 'string' && data.token.length > 0) {
    updateMetaCsrfToken(data.token);
    return data.token;
  }
  return getMetaCsrfToken();
}

export function getMetaCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta?.getAttribute('content') ?? '';
}

export function updateMetaCsrfToken(token) {
  const meta = document.querySelector('meta[name="csrf-token"]');
  if (meta && token) {
    meta.setAttribute('content', token);
  }
  return token || getMetaCsrfToken();
}

export function isStorefrontAuthPath(path) {
  return /^\/(login|register|forgot-password|reset-password)(\/|$)/.test(path);
}

/** @returns {boolean} true when a reload was scheduled (once per tab) */
export function tryAuthPageReloadOnce() {
  if (typeof sessionStorage === 'undefined') {
    return false;
  }
  if (sessionStorage.getItem(AUTH_RELOAD_KEY)) {
    return false;
  }
  sessionStorage.setItem(AUTH_RELOAD_KEY, '1');
  return true;
}

/** @returns {boolean} true when auto-reload is allowed (once per tab) */
export function trySessionExpiredAutoReloadOnce() {
  if (typeof sessionStorage === 'undefined') {
    return false;
  }
  if (sessionStorage.getItem(SESSION_EXPIRED_RELOAD_KEY)) {
    return false;
  }
  sessionStorage.setItem(SESSION_EXPIRED_RELOAD_KEY, '1');
  return true;
}
