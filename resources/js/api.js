import axios from 'axios';

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

api.interceptors.response.use(
  (r) => r,
  (err) => {
    if (err.response?.status === 401) {
      // Optional: trigger logout in auth context
    }
    return Promise.reject(err);
  }
);

export default api;
