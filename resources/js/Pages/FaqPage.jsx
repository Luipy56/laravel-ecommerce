import '../scss/main_shop.scss'
import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import PageTitle from '../components/PageTitle';

export default function FaqPage() {
  const { t, i18n } = useTranslation();
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;
    (async () => {
      setLoading(true);
      try {
        const { data } = await api.get('faqs', { params: { locale: i18n.language } });
        if (!cancelled && data.success) setItems(data.data || []);
      } catch {
        if (!cancelled) setItems([]);
      } finally {
        if (!cancelled) setLoading(false);
      }
    })();
    return () => {
      cancelled = true;
    };
  }, [i18n.language]);

  return (
    <div className="products-page">
    <div className="products-page__container">
    <div className="mx-auto w-full min-w-0 max-w-3xl space-y-6">
      <PageTitle title={t('shop.faq.title')} />

      {loading ? (
        <div className="flex justify-center py-12">
          <span className="loading loading-spinner loading-lg" aria-hidden="true" />
        </div>
      ) : items.length === 0 ? (
        <p className="text-base-content/70">{t('shop.faq.empty')}</p>
      ) : (
        <div className="flex flex-col gap-2">
          {items.map((item, idx) => (
            <div key={idx} tabIndex={0} className="collapse collapse-arrow bg-base-100 border border-base-300 rounded-box">
              <div className="collapse-title font-medium">{item.question}</div>
              <div className="collapse-content text-sm">
                <p className="whitespace-pre-wrap">{item.answer}</p>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
    </div>
    </div>
  );
}
