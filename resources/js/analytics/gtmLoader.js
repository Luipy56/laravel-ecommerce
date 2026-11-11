import { ensureDataLayer, pushConsentDefault, pushConsentUpdate } from './dataLayer';
import { hasAnalyticsConsent, loadConsent } from './consentStorage';

function readConfig() {
  const fromWindow = typeof window !== 'undefined' ? window.__SERRA_ANALYTICS__ : null;
  const id = (fromWindow?.gtm_container_id || import.meta.env.VITE_GTM_CONTAINER_ID || '').trim();
  const enabled = fromWindow?.enabled ?? String(import.meta.env.VITE_ANALYTICS_ENABLED || '') === 'true';
  const ga4 = (fromWindow?.ga4_measurement_id || import.meta.env.VITE_GA4_MEASUREMENT_ID || '').trim();
  return {
    enabled: !!enabled && !!id,
    gtmId: id,
    ga4Id: ga4,
  };
}

let gtmInjected = false;

/** Apply Consent Mode defaults / restore before any tags fire. */
export function initConsentMode() {
  try {
    ensureDataLayer();
    const existing = loadConsent();
    if (existing?.analytics) {
      pushConsentDefault(false);
      pushConsentUpdate(true);
    } else {
      pushConsentDefault(true);
    }
  } catch {
    /* ignore */
  }
}

/**
 * Load GTM only after analytics consent (blocks optional cookies until then).
 * Idempotent.
 */
export function ensureGtmLoaded() {
  try {
    const { enabled, gtmId } = readConfig();
    if (!enabled || !gtmId || !hasAnalyticsConsent()) return false;
    if (gtmInjected || document.getElementById('serra-gtm-script')) {
      gtmInjected = true;
      return true;
    }
    ensureDataLayer();
    ensureDataLayer().push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
    const script = document.createElement('script');
    script.id = 'serra-gtm-script';
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtm.js?id=${encodeURIComponent(gtmId)}`;
    document.head.appendChild(script);

    if (!document.getElementById('serra-gtm-noscript')) {
      const noscript = document.createElement('noscript');
      noscript.id = 'serra-gtm-noscript';
      noscript.innerHTML = `<iframe src="https://www.googletagmanager.com/ns.html?id=${encodeURIComponent(gtmId)}" height="0" width="0" style="display:none;visibility:hidden" title="gtm"></iframe>`;
      document.body.insertBefore(noscript, document.body.firstChild);
    }
    gtmInjected = true;
    return true;
  } catch {
    return false;
  }
}

export function getAnalyticsConfig() {
  return readConfig();
}

/** Call after user accepts/rejects analytics. */
export function applyAnalyticsConsent(analyticsGranted) {
  pushConsentUpdate(!!analyticsGranted);
  if (analyticsGranted) {
    ensureGtmLoaded();
  }
}
