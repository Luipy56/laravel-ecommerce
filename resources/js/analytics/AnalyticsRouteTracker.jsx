import { useEffect } from 'react';
import { useLocation } from 'react-router-dom';
import { ensureGtmLoaded, initConsentMode } from './gtmLoader';
import { hasAnalyticsConsent } from './consentStorage';
import { trackPageView } from './ecommerce';

/**
 * SPA page_view → dataLayer (GTM maps to GA4 page_view).
 * Skips /admin* routes.
 */
export default function AnalyticsRouteTracker() {
  const location = useLocation();

  useEffect(() => {
    initConsentMode();
    if (hasAnalyticsConsent()) ensureGtmLoaded();
  }, []);

  useEffect(() => {
    const path = `${location.pathname}${location.search || ''}`;
    if (path.startsWith('/admin')) return;
    trackPageView(path, typeof document !== 'undefined' ? document.title : undefined);
  }, [location.pathname, location.search]);

  return null;
}
