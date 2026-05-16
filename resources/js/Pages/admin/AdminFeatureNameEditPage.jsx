import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import TranslationFields from '../../components/admin/TranslationFields';
import { useAdminToast } from '../../contexts/AdminToastContext';

export default function AdminFeatureNameEditPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { showSuccess } = useAdminToast();
  const { id } = useParams();

  const [code, setCode] = useState('');
  const [names, setNames] = useState({ ca: '', es: '', en: '' });
  const [isActive, setIsActive] = useState(true);
  const [loading, setLoading] = useState(false);
  const [loaded, setLoaded] = useState(false);
  const [loadError, setLoadError] = useState('');
  const [submitError, setSubmitError] = useState('');

  const handleNamesChange = (locale, value) => setNames((prev) => ({ ...prev, [locale]: value }));

  const fetchFeatureName = useCallback(async () => {
    if (!id) return;
    try {
      const { data } = await api.get(`admin/feature-names/${id}`);
      if (data.success && data.data) {
        const d = data.data;
        setCode(d.code ?? '');
        setIsActive(!!d.is_active);
        setNames({
          ca: d.translations?.ca?.name ?? d.name ?? '',
          es: d.translations?.es?.name ?? '',
          en: d.translations?.en?.name ?? '',
        });
      } else {
        setLoadError(t('common.error'));
      }
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      else setLoadError(err.response?.data?.message || t('common.error'));
    } finally {
      setLoaded(true);
    }
  }, [id, navigate, t]);

  useEffect(() => {
    fetchFeatureName();
  }, [fetchFeatureName]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitError('');
    setLoading(true);
    try {
      const payload = {
        code: code.trim(),
        name: names.ca.trim(),
        is_active: isActive,
        translations: {
          es: { name: names.es.trim() },
          en: { name: names.en.trim() },
        },
      };
      const { data } = await api.put(`admin/feature-names/${id}`, payload);
      if (data.success) {
        showSuccess(t('common.saved'));
        navigate('/admin/features');
      } else {
        setSubmitError(data.message || t('common.error'));
      }
    } catch (err) {
      const errs = err.response?.data?.errors ?? {};
      setSubmitError(errs.code?.[0] ?? errs.name?.[0] ?? err.response?.data?.message ?? t('common.error'));
    } finally {
      setLoading(false);
    }
  };

  if (loadError) {
    return (
      <div className="space-y-4">
        <div className="flex justify-end">
          <Link to={`/admin/feature-names/${id}`} className="btn btn-ghost btn-sm">
            {t('common.back')}
          </Link>
        </div>
        <div className="alert alert-error">{loadError}</div>
      </div>
    );
  }

  if (!loaded) {
    return (
      <div className="flex justify-center py-12">
        <span className="loading loading-spinner loading-lg" aria-hidden="true" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <PageTitle className="mb-0">{t('admin.feature_types.edit')}</PageTitle>
        <Link to={`/admin/feature-names/${id}`} className="btn btn-ghost btn-sm shrink-0">
          {t('common.back')}
        </Link>
      </div>
      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <form onSubmit={handleSubmit} className="space-y-6">
            {submitError && (
              <div role="alert" className="alert alert-error text-sm">
                {submitError}
              </div>
            )}

            <label className="form-field">
              <span className="form-label">{t('admin.feature_types.code')} *</span>
              <input
                type="text"
                className="input input-bordered w-full font-mono"
                value={code}
                onChange={(e) => setCode(e.target.value)}
                required
                pattern="[a-z0-9][a-z0-9_\-]*"
                placeholder="ex: brand, key_type"
                aria-label={t('admin.feature_types.code')}
              />
              <span className="label text-xs text-base-content/50">{t('admin.feature_types.code_hint')}</span>
            </label>

            <TranslationFields
              field="name"
              values={names}
              onChange={handleNamesChange}
              label={t('admin.feature_types.name_translations')}
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
              <Link to={`/admin/feature-names/${id}`} className="btn btn-ghost">
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
