import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import PageTitle from '../components/PageTitle';

export default function TermsPage() {
  const { t, i18n } = useTranslation();
  const [content, setContent] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api
      .get('shop/public-settings')
      .then(({ data }) => {
        if (data.success && data.data) {
          const lang = i18n.language?.slice(0, 2) ?? 'ca';
          const key = `terms_${lang}`;
          const text = data.data[key] || data.data.terms_ca || data.data.terms_es || data.data.terms_en || '';
          setContent(text);
        }
      })
      .catch(() => setContent(''))
      .finally(() => setLoading(false));
  }, [i18n.language]);

  const paragraphs = content
    ? content
        .split(/\n{2,}/)
        .map((p) => p.trim())
        .filter(Boolean)
    : [];

  return (
    <div className="mx-auto w-full min-w-0 max-w-3xl space-y-8 pb-12">
      <PageTitle title={t('terms.title')} />

      {loading ? (
        <div className="flex justify-center py-12">
          <span className="loading loading-spinner loading-lg" aria-hidden="true" />
        </div>
      ) : paragraphs.length === 0 ? (
        <div className="card bg-base-100 border border-base-300 p-4 sm:p-6">
          <p className="text-sm text-base-content/60">{t('terms.empty')}</p>
        </div>
      ) : (
        <div className="card bg-base-100 border border-base-300 p-4 sm:p-6 flex flex-col gap-4">
          {paragraphs.map((paragraph, i) => (
            <p key={i} className="text-sm text-base-content/80 whitespace-pre-line">
              {paragraph}
            </p>
          ))}
        </div>
      )}
    </div>
  );
}
