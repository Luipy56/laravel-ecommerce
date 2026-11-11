import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import {
  applyAnalyticsConsent,
  loadConsent,
  saveConsent,
} from '../analytics';

/**
 * Cookie consent v2 + Consent Mode bridge.
 * AEPD: reject optional tracking with equal prominence to accept.
 */
export default function CookieConsentBanner({ forceOpen = false, onClose } = {}) {
  const { t } = useTranslation();
  const [visible, setVisible] = useState(false);
  const [analyticsChecked, setAnalyticsChecked] = useState(false);

  useEffect(() => {
    if (typeof window === 'undefined') return;
    const existing = loadConsent();
    if (forceOpen) {
      setAnalyticsChecked(!!existing?.analytics);
      setVisible(true);
      return;
    }
    if (!existing) setVisible(true);
  }, [forceOpen]);

  const finish = (analytics) => {
    saveConsent({ essential: true, analytics: !!analytics });
    applyAnalyticsConsent(!!analytics);
    setVisible(false);
    onClose?.();
  };

  if (!visible) return null;

  return (
    <div
      className="fixed bottom-0 left-0 right-0 z-[90] p-4 md:p-6 pointer-events-none"
      role="dialog"
      aria-labelledby="cookie-consent-title"
      aria-describedby="cookie-consent-desc"
    >
      <div className="max-w-3xl mx-auto pointer-events-auto card bg-base-100 shadow-xl border border-base-300">
        <div className="card-body flex flex-col gap-4">
          <div className="min-w-0">
            <h2 id="cookie-consent-title" className="font-semibold text-base">
              {t('cookies.title')}
            </h2>
            <p id="cookie-consent-desc" className="text-sm text-base-content/80 mt-1">
              {t('cookies.description')}
            </p>
          </div>

          <div className="flex flex-col gap-2">
            <label className="flex items-center justify-between gap-4 cursor-default select-none text-sm">
              <span className="font-medium">{t('cookies.essential_label')}</span>
              <input type="checkbox" className="toggle toggle-sm toggle-primary opacity-60" checked disabled readOnly />
            </label>
            <label className="flex items-center justify-between gap-4 cursor-pointer text-sm">
              <span>{t('cookies.analytics_label')}</span>
              <input
                type="checkbox"
                className="toggle toggle-sm toggle-primary"
                checked={analyticsChecked}
                onChange={(e) => setAnalyticsChecked(e.target.checked)}
              />
            </label>
          </div>

          <p className="text-xs text-base-content/60">
            {t('cookies.footer_note')}{' '}
            <Link to="/privacy-policy" className="link link-primary">
              {t('footer.privacy_policy')}
            </Link>
          </p>

          <div className="flex flex-wrap gap-2 justify-end">
            <button type="button" className="btn btn-outline btn-sm" onClick={() => finish(false)}>
              {t('cookies.reject_optional')}
            </button>
            <button type="button" className="btn btn-ghost btn-sm" onClick={() => finish(analyticsChecked)}>
              {t('cookies.accept_selected')}
            </button>
            <button type="button" className="btn btn-primary btn-sm" onClick={() => finish(true)}>
              {t('cookies.accept_all')}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
