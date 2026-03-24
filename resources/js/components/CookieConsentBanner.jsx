import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
const STORAGE_KEY = 'cookie_consent_v1';

export default function CookieConsentBanner() {
  const { t } = useTranslation();
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    try {
      if (typeof window === 'undefined') return;
      if (!window.localStorage.getItem(STORAGE_KEY)) setVisible(true);
    } catch {
      setVisible(true);
    }
  }, []);

  const accept = () => {
    try {
      window.localStorage.setItem(STORAGE_KEY, 'essential');
    } catch {
      /* ignore */
    }
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
        <div className="card-body flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div className="min-w-0">
            <h2 id="cookie-consent-title" className="font-semibold text-base">
              {t('cookies.title')}
            </h2>
            <p id="cookie-consent-desc" className="text-sm text-base-content/80 mt-1">
              {t('cookies.description')}
            </p>
            <p className="text-xs text-base-content/60 mt-2">{t('cookies.footer_note')}</p>
          </div>
          <div className="flex flex-wrap gap-2 shrink-0 justify-end">
            <button type="button" className="btn btn-primary btn-sm" onClick={accept}>
              {t('cookies.accept')}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
