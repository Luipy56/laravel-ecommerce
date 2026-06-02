import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import TranslationFields from '../../components/admin/TranslationFields';
import { useAdminToast } from '../../contexts/AdminToastContext';

export default function AdminCategoryNewPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { showSuccess } = useAdminToast();

  const [code, setCode] = useState('');
  const [names, setNames] = useState({ ca: '', es: '', en: '' });
  const [isActive, setIsActive] = useState(true);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleNamesChange = (locale, value) => setNames((prev) => ({ ...prev, [locale]: value }));

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);
    try {
      const payload = {
        name: names.ca.trim(),
        is_active: isActive,
        translations: {
          es: { name: names.es.trim() },
          en: { name: names.en.trim() },
        },
      };
      if (code.trim()) payload.code = code.trim();
      const { data } = await api.post('admin/categories', payload);
      if (data.success) {
        showSuccess(t('common.saved'));
        navigate('/admin/categories');
      } else {
        setError(data.message || t('common.error'));
      }
    } catch (err) {
      const errs = err.response?.data?.errors ?? {};
      setError(errs.name?.[0] ?? errs.code?.[0] ?? err.response?.data?.message ?? t('common.error'));
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <PageTitle className="mb-0">{t('admin.categories.new')}</PageTitle>
        <Link to="/admin/categories" className="btn btn-ghost btn-sm shrink-0">
          {t('common.back')}
        </Link>
      </div>
      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <form onSubmit={handleSubmit} className="space-y-6">
            {error && (
              <div role="alert" className="alert alert-error text-sm">
                {error}
              </div>
            )}

            <label className="form-field">
              <span className="form-label">{t('admin.products.code')}</span>
              <input
                type="text"
                className="input input-bordered w-full"
                value={code}
                onChange={(e) => setCode(e.target.value)}
                aria-label={t('admin.products.code')}
              />
            </label>

            <TranslationFields
              field="name"
              values={names}
              onChange={handleNamesChange}
              label={t('admin.categories.name_translations')}
              required
            />

            <label className="label cursor-pointer gap-2">
              <input
                type="checkbox"
                className="checkbox checkbox-sm"
                checked={isActive}
                onChange={(e) => setIsActive(e.target.checked)}
              />
              <span className="label-text">{t('admin.products.is_active')}</span>
            </label>

            <div className="flex justify-between gap-2 pt-4">
              <Link to="/admin/categories" className="btn btn-ghost">
                {t('common.back')}
              </Link>
              <button type="submit" className="btn btn-primary" disabled={loading}>
                {loading ? t('common.loading') : t('common.save')}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}
