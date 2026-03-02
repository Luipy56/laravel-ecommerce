import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import { useAdminToast } from '../../contexts/AdminToastContext';

export default function AdminAdminEditPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { showSuccess } = useAdminToast();
  const { id } = useParams();
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [isActive, setIsActive] = useState(true);
  const [loading, setLoading] = useState(false);
  const [loaded, setLoaded] = useState(false);
  const [loadError, setLoadError] = useState('');
  const [submitError, setSubmitError] = useState('');

  const fetchAdmin = useCallback(async () => {
    if (!id) return;
    try {
      const { data } = await api.get(`admin/admins/${id}`);
      if (data.success && data.data) {
        setUsername(data.data.username ?? '');
        setIsActive(!!data.data.is_active);
      } else setLoadError(t('common.error'));
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      else setLoadError(err.response?.data?.message || t('common.error'));
    } finally {
      setLoaded(true);
    }
  }, [id, navigate, t]);

  useEffect(() => {
    fetchAdmin();
  }, [fetchAdmin]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitError('');
    setLoading(true);
    try {
      const payload = { username: username.trim(), is_active: isActive };
      if (password.trim()) payload.password = password;
      const { data } = await api.put(`admin/admins/${id}`, payload);
      if (data.success) {
        showSuccess(t('common.saved'));
        setPassword('');
      } else setSubmitError(data.message || t('common.error'));
    } catch (err) {
      setSubmitError(err.response?.data?.message || err.response?.data?.errors?.username?.[0] || t('common.error'));
    } finally {
      setLoading(false);
    }
  };

  if (loadError) {
    return (
      <div className="space-y-4">
        <div className="flex justify-end">
          <Link to="/admin/admins" className="btn btn-ghost btn-sm">{t('common.back')}</Link>
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
        <PageTitle className="mb-0">{t('admin.admins.edit')} — {username}</PageTitle>
        <Link to={`/admin/admins/${id}`} className="btn btn-ghost btn-sm shrink-0">{t('common.back')}</Link>
      </div>
      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <form onSubmit={handleSubmit} className="space-y-6">
            {submitError && (
              <div role="alert" className="alert alert-error text-sm">{submitError}</div>
            )}
            <label className="form-field">
              <span className="form-label">{t('admin.admins.username')} *</span>
              <input
                type="text"
                className="input input-bordered w-full"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                required
                autoComplete="username"
                aria-label={t('admin.admins.username')}
              />
            </label>
            <label className="form-field">
              <span className="form-label">{t('admin.admins.password_optional')}</span>
              <input
                type="password"
                className="input input-bordered w-full"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                autoComplete="new-password"
                aria-label={t('admin.login.password')}
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
              <Link to={`/admin/admins/${id}`} className="btn btn-ghost">{t('common.back')}</Link>
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
