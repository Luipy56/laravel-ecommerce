import React from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import PageTitle from '../components/PageTitle';

/**
 * Shown when API returns 419 (CSRF / session expired). User should refresh or sign in again.
 */
export default function SessionExpiredPage() {
  const { t } = useTranslation();

  return (
    <div className="mx-auto w-full min-w-0 max-w-lg text-center py-16">
      <PageTitle>{t('errors.session_expired_title')}</PageTitle>
      <p className="text-base-content/80 mb-6">{t('errors.session_expired_body')}</p>
      <div className="flex flex-wrap gap-3 justify-center">
        <button type="button" className="btn btn-primary" onClick={() => window.location.reload()}>
          {t('errors.reload')}
        </button>
        <Link to="/login" className="btn btn-outline">
          {t('auth.login')}
        </Link>
      </div>
    </div>
  );
}
