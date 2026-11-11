export const CONSENT_STORAGE_KEY = 'cookie_consent_v2';

/**
 * @typedef {{ essential: boolean, analytics: boolean, updated_at?: string }} CookieConsent
 */

/** @returns {CookieConsent|null} */
export function loadConsent() {
  if (typeof window === 'undefined') return null;
  try {
    const raw = window.localStorage.getItem(CONSENT_STORAGE_KEY);
    if (!raw) return null;
    const parsed = JSON.parse(raw);
    if (!parsed || typeof parsed !== 'object') return null;
    return {
      essential: true,
      analytics: !!parsed.analytics,
      updated_at: typeof parsed.updated_at === 'string' ? parsed.updated_at : undefined,
    };
  } catch {
    return null;
  }
}

/** @param {CookieConsent} consent */
export function saveConsent(consent) {
  if (typeof window === 'undefined') return;
  const payload = {
    essential: true,
    analytics: !!consent.analytics,
    updated_at: new Date().toISOString(),
  };
  try {
    window.localStorage.setItem(CONSENT_STORAGE_KEY, JSON.stringify(payload));
  } catch {
    /* ignore quota */
  }
  try {
    window.dispatchEvent(new CustomEvent('serra:cookie-consent', { detail: payload }));
  } catch {
    /* ignore */
  }
  return payload;
}

export function hasAnalyticsConsent() {
  return !!loadConsent()?.analytics;
}
