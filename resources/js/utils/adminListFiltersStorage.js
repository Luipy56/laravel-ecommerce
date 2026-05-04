/**
 * Admin list toolbar filters (search, dropdowns) persisted in localStorage
 * so they survive in-app navigation and full reloads. One JSON blob, keyed by pageId.
 */

const ROOT_KEY = 'le-admin-list-filters';
const SCHEMA_VERSION = 1;

function readRoot() {
  if (typeof localStorage === 'undefined') {
    return { v: SCHEMA_VERSION, pages: {} };
  }
  try {
    const raw = localStorage.getItem(ROOT_KEY);
    if (!raw) return { v: SCHEMA_VERSION, pages: {} };
    const o = JSON.parse(raw);
    if (!o || typeof o !== 'object' || o.v !== SCHEMA_VERSION || typeof o.pages !== 'object') {
      return { v: SCHEMA_VERSION, pages: {} };
    }
    return { v: SCHEMA_VERSION, pages: { ...o.pages } };
  } catch {
    return { v: SCHEMA_VERSION, pages: {} };
  }
}

/**
 * @param {string} pageId Stable id (e.g. "orders", "products").
 * @returns {Record<string, unknown>|null}
 */
export function loadAdminListFilters(pageId) {
  const block = readRoot().pages[pageId];
  if (!block || typeof block !== 'object') return null;
  return { ...block };
}

/**
 * @param {string} pageId
 * @param {Record<string, unknown>} data
 */
export function saveAdminListFilters(pageId, data) {
  if (typeof localStorage === 'undefined') return;
  try {
    const root = readRoot();
    root.pages[pageId] = data;
    localStorage.setItem(ROOT_KEY, JSON.stringify(root));
  } catch {
    // QuotaExceededError, private mode, etc.
  }
}

export const ADMIN_LIST_FILTER_SEARCH_MAX = 500;

/** @returns {string} */
export function normalizedStoredSearch(raw, fallback = '') {
  if (typeof raw !== 'string') return fallback;
  return raw.length > ADMIN_LIST_FILTER_SEARCH_MAX ? raw.slice(0, ADMIN_LIST_FILTER_SEARCH_MAX) : raw;
}

/** @returns {'' | '0' | '1' | null} */
export function normalizedActiveTriState(raw) {
  if (raw === '' || raw === '0' || raw === '1') return raw;
  return null;
}

export const ADMIN_LIST_PERIOD_VALUES = ['week', 'month', 'year', 'all'];

/** @returns {string | null} */
export function normalizedPeriod(raw) {
  return ADMIN_LIST_PERIOD_VALUES.includes(raw) ? raw : null;
}

/** Optional numeric id as string (feature type, product category). */
export function normalizedDigitsId(raw) {
  if (raw === '' || raw == null) return '';
  const s = String(raw);
  return /^\d{1,12}$/.test(s) ? s : '';
}
