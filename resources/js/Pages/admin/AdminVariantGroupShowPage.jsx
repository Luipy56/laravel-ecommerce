import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';

export default function AdminVariantGroupShowPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { id } = useParams();
  const [group, setGroup] = useState(null);
  const [loaded, setLoaded] = useState(false);
  const [loadError, setLoadError] = useState('');

  const fetchGroup = useCallback(async () => {
    if (!id) return;
    try {
      const { data } = await api.get(`admin/variant-groups/${id}`);
      if (data.success) setGroup(data.data);
      else setLoadError(t('common.error'));
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      else setLoadError(err.response?.data?.message || t('common.error'));
    } finally {
      setLoaded(true);
    }
  }, [id, navigate, t]);

  useEffect(() => {
    fetchGroup();
  }, [fetchGroup]);

  if (loadError) {
    return (
      <div className="space-y-4">
        <div className="flex justify-end">
          <Link to="/admin/variant-groups" className="btn btn-ghost btn-sm">{t('common.back')}</Link>
        </div>
        <div className="alert alert-error">{loadError}</div>
      </div>
    );
  }

  if (!loaded || !group) {
    return (
      <div className="flex justify-center py-12">
        <span className="loading loading-spinner loading-lg" aria-hidden="true" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <PageTitle className="mb-0">{t('admin.variant_groups.group_label')} #{group.id}</PageTitle>
        <div className="flex gap-2">
          <Link to="/admin/variant-groups" className="btn btn-ghost btn-sm shrink-0">{t('common.back')}</Link>
          <Link to={`/admin/variant-groups/${id}/edit`} className="btn btn-primary btn-sm shrink-0">{t('common.edit')}</Link>
        </div>
      </div>

      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <h2 className="font-semibold text-lg border-b border-base-300 pb-2 mb-4">
            {t('admin.variant_groups.products_in_group')} ({group.products?.length ?? 0})
          </h2>
          {!group.products?.length ? (
            <p className="text-base-content/70">{t('admin.products.no_products')}</p>
          ) : (
            <ul className="list-disc list-inside space-y-1">
              {group.products.map((p) => (
                <li key={p.id}>{p.name}{p.code ? ` (${p.code})` : ''}</li>
              ))}
            </ul>
          )}
        </div>
      </div>
    </div>
  );
}
