import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import { useAdminToast } from '../../contexts/AdminToastContext';

const STATUSES = ['pending_review', 'reviewed', 'client_contacted'];

export default function AdminPersonalizedSolutionEditPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { showSuccess } = useAdminToast();
  const { id } = useParams();
  const [resolution, setResolution] = useState('');
  const [status, setStatus] = useState('pending_review');
  const [isActive, setIsActive] = useState(true);
  const [loading, setLoading] = useState(false);
  const [loaded, setLoaded] = useState(false);
  const [loadError, setLoadError] = useState('');
  const [submitError, setSubmitError] = useState('');

  const fetchSolution = useCallback(async () => {
    if (!id) return;
    try {
      const { data } = await api.get(`admin/personalized-solutions/${id}`);
      if (data.success && data.data) {
        setResolution(data.data.resolution ?? '');
        setStatus(data.data.status || 'pending_review');
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
    fetchSolution();
  }, [fetchSolution]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitError('');
    setLoading(true);
    try {
      const { data } = await api.put(`admin/personalized-solutions/${id}`, {
        resolution: resolution.trim() || null,
        status,
        is_active: isActive,
      });
      if (data.success) {
        showSuccess(t('common.saved'));
        navigate(`/admin/personalized-solutions/${id}`);
      } else setSubmitError(data.message || t('common.error'));
    } catch (err) {
      setSubmitError(err.response?.data?.message || err.response?.data?.errors?.status?.[0] || t('common.error'));
    } finally {
      setLoading(false);
    }
  };

  if (loadError) {
    return (
      <div className="space-y-4">
        <div className="flex justify-end">
          <Link to="/admin/personalized-solutions" className="btn btn-ghost btn-sm">{t('common.back')}</Link>
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
        <PageTitle className="mb-0">{t('admin.personalized_solutions.edit')} #{id}</PageTitle>
        <Link to={`/admin/personalized-solutions/${id}`} className="btn btn-ghost btn-sm shrink-0">{t('common.back')}</Link>
      </div>
      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <form onSubmit={handleSubmit} className="space-y-6">
            {submitError && (
              <div role="alert" className="alert alert-error text-sm">{submitError}</div>
            )}
            <label className="form-field">
              <span className="form-label">{t('admin.personalized_solutions.status')} *</span>
              <select
                className="select select-bordered w-full"
                value={status}
                onChange={(e) => setStatus(e.target.value)}
                required
                aria-label={t('admin.personalized_solutions.status')}
              >
                {STATUSES.map((s) => (
                  <option key={s} value={s}>{t(`admin.personalized_solutions.status_${s}`)}</option>
                ))}
              </select>
            </label>
            <label className="form-field">
              <span className="form-label">{t('admin.personalized_solutions.resolution')}</span>
              <textarea
                className="textarea textarea-bordered w-full min-h-32"
                value={resolution}
                onChange={(e) => setResolution(e.target.value)}
                placeholder={t('admin.personalized_solutions.resolution_placeholder')}
                aria-label={t('admin.personalized_solutions.resolution')}
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
              <Link to={`/admin/personalized-solutions/${id}`} className="btn btn-ghost">{t('common.back')}</Link>
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
