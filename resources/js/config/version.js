/* global __APP_VERSION__ */
/**
 * Application semver: prefer Laravel-injected `window.__LARAVEL_APP_VERSION__` (current `package.json`
 * on each page load) over Vite `define` (frozen until dev server / build).
 */
function resolveAppVersion() {
  if (typeof window !== 'undefined') {
    const w = window.__LARAVEL_APP_VERSION__;
    if (typeof w === 'string' && w !== '') {
      return w;
    }
  }
  return typeof __APP_VERSION__ !== 'undefined' ? __APP_VERSION__ : '0.0.0';
}

export const APP_VERSION = resolveAppVersion();
