import React from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import PageTitle from '../components/PageTitle';

export default function NotFoundPage() {
  const { t } = useTranslation();

  return (
    <div className="max-w-lg mx-auto text-center py-16">
      <PageTitle>{t('errors.not_found_title')}</PageTitle>
      <p className="text-base-content/80 mb-6">{t('errors.not_found_body')}</p>
      <Link to="/" className="btn btn-primary">
        {t('errors.back_home')}
      </Link>
    </div>
  );
}
