import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import AdminKeyColorForm from '../../components/admin/AdminKeyColorForm';
import { useAdminToast } from '../../contexts/AdminToastContext';

export default function AdminKeyColorEditPage() {
  const { id } = useParams();
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { showSuccess } = useAdminToast();
  const [color, setColor] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');

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

  const handleSubmit = async (payload) => {
    setError('');
    setSaving(true);
    try {
      const { data } = await api.put(`admin/key-colors/${id}`, payload);
      if (data.success) {
        showSuccess(t('common.saved'));
        navigate(`/admin/key-colors/${id}`);
      } else {
        setError(data.message || t('common.error'));
      }
    } catch (err) {
      const errs = err.response?.data?.errors ?? {};
      setError(errs.rgb_code?.[0] ?? errs.name?.[0] ?? err.response?.data?.message ?? t('common.error'));
    } finally {
      setSaving(false);
    }
  };

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
        <PageTitle className="mb-0">
          {t('admin.key_colors.edit')}{color.name ? ` · ${color.name}` : ` · #${color.id}`}
        </PageTitle>
        <Link to={`/admin/key-colors/${id}`} className="btn btn-ghost btn-sm shrink-0">
          {t('common.back')}
        </Link>
      </div>
      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <AdminKeyColorForm color={color} onSubmit={handleSubmit} loading={saving} error={error} />
        </div>
      </div>
    </div>
  );
}
