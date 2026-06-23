import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import AdminKeyColorForm from '../../components/admin/AdminKeyColorForm';
import { useAdminToast } from '../../contexts/AdminToastContext';

export default function AdminKeyColorNewPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { showSuccess } = useAdminToast();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async (payload) => {
    setError('');
    setLoading(true);
    try {
      const { data } = await api.post('admin/key-colors', payload);
      if (data.success) {
        showSuccess(t('common.saved'));
        navigate('/admin/key-colors');
      } else {
        setError(data.message || t('common.error'));
      }
    } catch (err) {
      const errs = err.response?.data?.errors ?? {};
      setError(errs.rgb_code?.[0] ?? errs.name?.[0] ?? err.response?.data?.message ?? t('common.error'));
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <PageTitle className="mb-0">{t('admin.key_colors.new')}</PageTitle>
        <Link to="/admin/key-colors" className="btn btn-ghost btn-sm shrink-0">
          {t('common.back')}
        </Link>
      </div>
      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <AdminKeyColorForm onSubmit={handleSubmit} loading={loading} error={error} />
        </div>
      </div>
    </div>
  );
}
