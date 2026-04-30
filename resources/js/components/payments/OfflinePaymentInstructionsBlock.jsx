import React from 'react';
import { useTranslation } from 'react-i18next';

/**
 * @param {{ instructions: { type?: string, lines?: Record<string, string> } | null | undefined }} props
 */
export default function OfflinePaymentInstructionsBlock({ instructions }) {
  const { t } = useTranslation();
  if (!instructions?.type) return null;

  const lines = instructions.lines && typeof instructions.lines === 'object' ? instructions.lines : {};

  if (instructions.type === 'bank_transfer') {
    return (
      <div role="region" aria-labelledby="offline-pay-bank-heading" className="alert alert-info text-sm mt-4 items-start">
        <div className="min-w-0 space-y-3">
          <p id="offline-pay-bank-heading" className="font-semibold text-base-content m-0">
            {t('shop.payment.offline_bank_title')}
          </p>
          <p className="m-0 text-base-content/90">{t('shop.payment.offline_disclaimer')}</p>
          <dl className="grid gap-2 m-0 sm:grid-cols-1">
            {lines.iban ? (
              <div>
                <dt className="text-xs font-semibold uppercase tracking-wide text-base-content/70">{t('shop.payment.offline_iban')}</dt>
                <dd className="m-0 font-mono text-sm break-all">{lines.iban}</dd>
              </div>
            ) : null}
            {lines.beneficiary ? (
              <div>
                <dt className="text-xs font-semibold uppercase tracking-wide text-base-content/70">{t('shop.payment.offline_beneficiary')}</dt>
                <dd className="m-0">{lines.beneficiary}</dd>
              </div>
            ) : null}
            {lines.reference_hint ? (
              <div>
                <dt className="text-xs font-semibold uppercase tracking-wide text-base-content/70">{t('shop.payment.offline_reference')}</dt>
                <dd className="m-0 whitespace-pre-wrap">{lines.reference_hint}</dd>
              </div>
            ) : null}
          </dl>
        </div>
      </div>
    );
  }

  if (instructions.type === 'bizum_manual') {
    return (
      <div role="region" aria-labelledby="offline-pay-bizum-heading" className="alert alert-info text-sm mt-4 items-start">
        <div className="min-w-0 space-y-3">
          <p id="offline-pay-bizum-heading" className="font-semibold text-base-content m-0">
            {t('shop.payment.offline_bizum_title')}
          </p>
          <p className="m-0 text-base-content/90">{t('shop.payment.offline_disclaimer')}</p>
          {lines.phone ? (
            <p className="m-0">
              <span className="text-xs font-semibold uppercase tracking-wide text-base-content/70">{t('shop.payment.offline_bizum_phone')}</span>
              <span className="block font-medium tabular-nums">{lines.phone}</span>
            </p>
          ) : null}
          {lines.instructions ? (
            <p className="m-0 whitespace-pre-wrap text-base-content/90">{lines.instructions}</p>
          ) : null}
        </div>
      </div>
    );
  }

  return null;
}
