import React from 'react';
import { useTranslation } from 'react-i18next';

/**
 * Shows a warning banner when one or more encrypted fields could not be decrypted.
 *
 * variant="admin"  (default) — technical message mentioning APP_KEY, for admin/developer pages.
 * variant="client"           — user-friendly message with no technical details, for client pages.
 */
export default function DecryptionWarningBanner({ variant = 'admin' }) {
  const { t } = useTranslation();

  const bodyKey = variant === 'client'
    ? 'common.decryption_warning_body_client'
    : 'common.decryption_warning_body';

  return (
    <div role="alert" className="alert alert-warning text-sm">
      <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
      </svg>
      <div>
        <p className="font-semibold">{t('common.decryption_warning_title')}</p>
        <p>{t(bodyKey)}</p>
      </div>
    </div>
  );
}
