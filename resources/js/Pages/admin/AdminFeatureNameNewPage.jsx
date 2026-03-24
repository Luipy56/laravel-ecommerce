import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import { useAdminToast } from '../../contexts/AdminToastContext';

export default function AdminFeatureNameNewPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { showSuccess } = useAdminToast();
  const [name, setName] = useState('');
  const [isActive, setIsActive] = useState(true);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);
    try {
      const { data } = await api.post('admin/feature-names', { name: name.trim(), is_active: isActive });
      if (data.success) {
        showSuccess(t('common.saved'));
        navigate('/admin/features');
      }
      else setError(data.message || t('common.error'));
    } catch (err) {
      setError(err.response?.data?.message || err.response?.data?.errors?.name?.[0] || t('common.error'));
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <PageTitle className="mb-0">{t('admin.feature_types.new')}</PageTitle>
        <Link to="/admin/features" className="btn btn-ghost btn-sm shrink-0">
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
              <span className="form-label">{t('admin.features.type')} *</span>
              <input
                type="text"
                className="input input-bordered w-full"
                value={name}
                onChange={(e) => setName(e.target.value)}
                required
                aria-label={t('admin.features.type')}
              />
            </label>
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
              <Link to="/admin/features" className="btn btn-ghost">
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
