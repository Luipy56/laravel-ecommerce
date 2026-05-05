import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';

const STORAGE_KEY = 'cookie_consent_v2';

function loadConsent() {
  try {
    const raw = window.localStorage.getItem(STORAGE_KEY);
    if (!raw) return null;
    return JSON.parse(raw);
  } catch {
    return null;
  }
}

function saveConsent(consent) {
  try {
    window.localStorage.setItem(STORAGE_KEY, JSON.stringify(consent));
  } catch {
    /* ignore */
  }
}

export default function CookieConsentBanner() {
  const { t } = useTranslation();
  const [visible, setVisible] = useState(false);
  const [analyticsChecked, setAnalyticsChecked] = useState(false);

  useEffect(() => {
    if (typeof window === 'undefined') return;
    if (!loadConsent()) setVisible(true);
  }, []);

  const acceptSelected = () => {
    saveConsent({ essential: true, analytics: analyticsChecked });
    setVisible(false);
  };

  const acceptAll = () => {
    saveConsent({ essential: true, analytics: true });
    setVisible(false);
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

          {/* Granular options */}
          <div className="flex flex-col gap-2">
            <label className="flex items-center gap-2 text-sm">
              <input type="checkbox" className="checkbox checkbox-sm" checked disabled readOnly />
              <span className="font-medium">{t('cookies.essential_label')}</span>
              <span className="text-base-content/60 text-xs">({t('cookies.essential_note')})</span>
            </label>
            <label className="flex items-center gap-2 cursor-pointer text-sm">
              <input
                type="checkbox"
                className="checkbox checkbox-sm checkbox-primary"
                checked={analyticsChecked}
                onChange={(e) => setAnalyticsChecked(e.target.checked)}
              />
              <span>{t('cookies.analytics_label')}</span>
            </label>
          </div>

          <p className="text-xs text-base-content/60">
            {t('cookies.footer_note')}{' '}
            <Link to="/privacy-policy" className="link link-primary">
              {t('footer.privacy_policy')}
            </Link>
          </p>

          <div className="flex flex-wrap gap-2 justify-end">
            <button type="button" className="btn btn-ghost btn-sm" onClick={acceptSelected}>
              {t('cookies.accept_selected')}
            </button>
            <button type="button" className="btn btn-primary btn-sm" onClick={acceptAll}>
              {t('cookies.accept_all')}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
