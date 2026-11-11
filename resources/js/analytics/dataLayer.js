/**
 * Safe dataLayer / gtag helpers. Never throw into checkout / cart flows.
 */

export function ensureDataLayer() {
  if (typeof window === 'undefined') return [];
  window.dataLayer = window.dataLayer || [];
  if (typeof window.gtag !== 'function') {
    window.gtag = function gtag() {
      // eslint-disable-next-line prefer-rest-params
      window.dataLayer.push(arguments);
    };
  }
  return window.dataLayer;
}

/** @param {Record<string, unknown>} obj */
export function pushDataLayer(obj) {
  try {
    if (typeof window === 'undefined' || !obj || typeof obj !== 'object') return;
    ensureDataLayer().push(obj);
  } catch {
    /* never break UX */
  }
}

/**
 * GA4 ecommerce events require clearing previous ecommerce object.
 * @param {string} event
 * @param {Record<string, unknown>} ecommerce
 */
export function pushEcommerceEvent(event, ecommerce) {
  pushDataLayer({ ecommerce: null });
  pushDataLayer({ event, ecommerce });
}

/** Consent Mode v2 defaults (before GTM loads). */
export function pushConsentDefault(denied = true) {
  try {
    ensureDataLayer();
    const state = denied ? 'denied' : 'granted';
    window.gtag('consent', 'default', {
      analytics_storage: state,
      ad_storage: 'denied',
      ad_user_data: 'denied',
      ad_personalization: 'denied',
      wait_for_update: 500,
    });
  } catch {
    /* ignore */
  }
}

/** @param {boolean} analyticsGranted */
export function pushConsentUpdate(analyticsGranted) {
  try {
    ensureDataLayer();
    window.gtag('consent', 'update', {
      analytics_storage: analyticsGranted ? 'granted' : 'denied',
      ad_storage: 'denied',
      ad_user_data: 'denied',
      ad_personalization: 'denied',
    });
    pushDataLayer({
      event: analyticsGranted ? 'serra_consent_analytics_granted' : 'serra_consent_analytics_denied',
    });
  } catch {
    /* ignore */
  }
}
