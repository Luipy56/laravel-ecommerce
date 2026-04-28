import axios from 'axios';
import i18n from './i18n';
import { navigationRef } from './routerBridge';
import { emitAppToast } from './toastEvents';

/**
 * Use xsrfCookieName/xsrfHeaderName so axios reads the XSRF-TOKEN cookie before each request.
 * The meta tag token is stale after login (session regeneration), causing 419 on cart/merge.
 * Laravel sends the fresh token in the cookie with every response.
 */
export const api = axios.create({
  baseURL: '/api/v1',
  withCredentials: true,
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
});

api.interceptors.request.use((config) => {
  if (config.data instanceof FormData) {
    delete config.headers['Content-Type'];
  }
  return config;
});

/** In-flight requests on this axios instance (storefront + admin shared client). Used for global loading UI (e.g. navbar gradient line). */
let apiPendingCount = 0;
const apiPendingListeners = new Set();

function notifyApiPending() {
  apiPendingListeners.forEach((fn) => fn(apiPendingCount));
}

function bumpApiPending(delta) {
  apiPendingCount = Math.max(0, apiPendingCount + delta);
  notifyApiPending();
}

/**
 * Subscribe to the count of pending `api` requests. Listener is called immediately and on each change.
 * @param {(n: number) => void} listener
 * @returns {() => void} unsubscribe
 */
export function subscribeApiPending(listener) {
  apiPendingListeners.add(listener);
  listener(apiPendingCount);
  return () => apiPendingListeners.delete(listener);
}

export function getApiPendingCount() {
  return apiPendingCount;
}

api.interceptors.request.use((config) => {
  bumpApiPending(1);
  return config;
});

api.interceptors.response.use(
  (r) => r,
  (err) => {
    const status = err.response?.status;
    const path = typeof window !== 'undefined' ? window.location.pathname : '';

    if (status === 419) {
      emitAppToast(i18n.t('errors.session_expired_toast'), 'warning');
      const onAdminLogin = /^\/admin\/login\/?$/.test(path);
      const onSessionExpired = path.startsWith('/session-expired');
      if (onAdminLogin || onSessionExpired) {
        return Promise.reject(err);
      }
      const target = path.startsWith('/admin') ? '/admin/login' : '/session-expired';
      if (navigationRef.current) {
        navigationRef.current(target, { replace: true });
      } else {
        window.location.assign(target);
      }
      return Promise.reject(err);
    }

    if (!err.response && err.request) {
      const canceled =
        axios.isCancel(err) ||
        err.code === 'ERR_CANCELED' ||
        err.name === 'CanceledError';
      if (!canceled) {
        emitAppToast(i18n.t('errors.network'), 'error');
      }
    }

    if (status === 401) {
      // Optional: trigger logout in auth context
    }
    return Promise.reject(err);
  }
);

api.interceptors.response.use(
  (response) => {
    bumpApiPending(-1);
    return response;
  },
  (error) => {
    bumpApiPending(-1);
    return Promise.reject(error);
  }
);

export default api;
