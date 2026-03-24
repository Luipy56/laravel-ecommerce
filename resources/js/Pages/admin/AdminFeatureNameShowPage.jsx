import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';

export default function AdminFeatureNameShowPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { id } = useParams();
  const [featureName, setFeatureName] = useState(null);
  const [loaded, setLoaded] = useState(false);
  const [loadError, setLoadError] = useState('');

  const fetchFeatureName = useCallback(async () => {
    if (!id) return;
    try {
      const { data } = await api.get(`admin/feature-names/${id}`);
      if (data.success) setFeatureName(data.data);
      else setLoadError(t('common.error'));
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

  if (loadError) {
    return (
      <div className="space-y-4">
        <div className="flex justify-end">
          <Link to="/admin/features" className="btn btn-ghost btn-sm">{t('common.back')}</Link>
        </div>
        <div className="alert alert-error">{loadError}</div>
      </div>
    );
  }

  if (!loaded || !featureName) {
    return (
      <div className="flex justify-center py-12">
        <span className="loading loading-spinner loading-lg" aria-hidden="true" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <PageTitle className="mb-0">{featureName.name}</PageTitle>
        <div className="flex gap-2">
          <Link to="/admin/features" className="btn btn-ghost btn-sm shrink-0">{t('common.back')}</Link>
          <Link to={`/admin/feature-names/${id}/edit`} className="btn btn-primary btn-sm shrink-0">{t('common.edit')}</Link>
        </div>
      </div>

      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <dl className="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div><dt className="text-sm text-base-content/70">{t('admin.features.type')}</dt><dd>{featureName.name}</dd></div>
            <div><dt className="text-sm text-base-content/70">{t('admin.products.is_active')}</dt><dd>{featureName.is_active ? t('common.yes') : t('common.no')}</dd></div>
          </dl>
        </div>
      </div>
    </div>
  );
}
