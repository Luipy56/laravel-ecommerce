import React from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';

/**
 * Compact GDPR informational notice shown on forms that collect personal data.
 * Displays a short explanation of purpose, legal basis, and a link to the full
 * Privacy Policy. Does not replace consent checkboxes where required.
 */
export default function GdprNotice({ noticeKey }) {
  const { t } = useTranslation();

  return (
    <div className="rounded-box border border-base-300 bg-base-200/50 px-4 py-3 text-xs text-base-content/70">
      <p>{t(noticeKey)}</p>
      <p className="mt-1">
        <Link to="/privacy-policy" className="link link-primary font-medium">
          {t('gdpr.privacy_policy_link')}
        </Link>
      </p>
    </div>
  );
}
