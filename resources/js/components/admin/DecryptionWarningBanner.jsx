import React from 'react';
import { useTranslation } from 'react-i18next';

/**
 * Shows a warning banner when one or more encrypted fields could not be decrypted.
 * This happens when APP_KEY in .env does not match the key used to encrypt the data.
 * The affected fields will appear blank/empty.
 */
export default function DecryptionWarningBanner() {
  const { t } = useTranslation();

  return (
    <div role="alert" className="alert alert-warning text-sm">
      <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
      </svg>
      <div>
        <p className="font-semibold">{t('common.decryption_warning_title')}</p>
        <p>{t('common.decryption_warning_body')}</p>
      </div>
    </div>
  );
}
