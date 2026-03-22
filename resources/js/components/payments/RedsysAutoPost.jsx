import React, { useEffect } from 'react';
import { useTranslation } from 'react-i18next';

/**
 * Submits a hidden POST form to the Redsys / Bizum endpoint (full-page redirect).
 */
export default function RedsysAutoPost({ actionUrl, fields }) {
  const { t } = useTranslation();

  useEffect(() => {
    if (!actionUrl || !fields || typeof document === 'undefined') return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = actionUrl;
    Object.entries(fields).forEach(([name, value]) => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = name;
      input.value = value == null ? '' : String(value);
      form.appendChild(input);
    });
    document.body.appendChild(form);
    form.submit();
  }, [actionUrl, fields]);

  return (
    <div className="flex flex-col items-center justify-center gap-4 py-12" role="status">
      <span className="loading loading-spinner loading-lg" aria-hidden="true" />
      <p className="text-base-content/80 text-center text-sm max-w-md">{t('checkout.payment.redirecting_gateway')}</p>
    </div>
  );
}
