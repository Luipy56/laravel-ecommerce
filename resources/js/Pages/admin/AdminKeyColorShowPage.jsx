import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';

export default function AdminKeyColorShowPage() {
  const { id } = useParams();
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [color, setColor] = useState(null);
  const [loading, setLoading] = useState(true);

  const fetchColor = useCallback(async () => {
    setLoading(true);
    try {
      const { data } = await api.get(`admin/key-colors/${id}`);
      if (data.success) setColor(data.data);
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      setColor(null);
    } finally {
      setLoading(false);
    }
  }, [id, navigate]);

  useEffect(() => {
    fetchColor();
  }, [fetchColor]);

  if (loading) {
    return <p className="text-sm text-base-content/70">{t('common.loading')}</p>;
  }

  if (!color) {
    return (
      <div className="space-y-4">
        <p className="text-sm text-base-content/70">{t('common.error')}</p>
        <Link to="/admin/key-colors" className="btn btn-ghost btn-sm">{t('common.back')}</Link>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <PageTitle className="mb-0">{color.name || color.rgb_code}</PageTitle>
        <div className="flex gap-2 shrink-0">
          <Link to="/admin/key-colors" className="btn btn-ghost btn-sm">{t('common.back')}</Link>
          <Link to={`/admin/key-colors/${id}/edit`} className="btn btn-primary btn-sm">{t('common.edit')}</Link>
        </div>
      </div>
      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body space-y-3">
          <div className="flex items-center gap-3">
            <span
              className="inline-block w-12 h-12 rounded-full border border-base-300 shrink-0"
              style={{ backgroundColor: color.rgb_code }}
              aria-hidden
            />
            <div>
              <p className="font-medium">{color.name}</p>
              <p className="text-sm font-mono text-base-content/70">{color.rgb_code}</p>
            </div>
          </div>
          <dl className="grid gap-2 sm:grid-cols-2 text-sm">
            <div>
              <dt className="text-base-content/70">{t('admin.products.is_active')}</dt>
              <dd>{color.is_active ? t('common.yes') : t('common.no')}</dd>
            </div>
            <div>
              <dt className="text-base-content/70">{t('admin.key_colors.sort_order')}</dt>
              <dd className="tabular-nums">{color.sort_order}</dd>
            </div>
          </dl>
        </div>
      </div>
    </div>
  );
}
