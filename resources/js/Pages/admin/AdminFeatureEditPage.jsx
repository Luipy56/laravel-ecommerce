import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import AdminFeatureForm from '../../components/admin/AdminFeatureForm';
import { useAdminToast } from '../../contexts/AdminToastContext';

export default function AdminFeatureEditPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { showSuccess } = useAdminToast();
  const { id } = useParams();
  const [feature, setFeature] = useState(null);
  const [featureNames, setFeatureNames] = useState([]);
  const [loading, setLoading] = useState(false);
  const [loadError, setLoadError] = useState('');
  const [submitError, setSubmitError] = useState('');

  const fetchFeature = useCallback(async () => {
    if (!id) return;
    try {
      const { data } = await api.get(`admin/features/${id}`);
      if (data.success) setFeature(data.data);
      else setLoadError(t('common.error'));
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      else setLoadError(err.response?.data?.message || t('common.error'));
    }
  }, [id, navigate, t]);

  const fetchFeatureNames = useCallback(async () => {
    try {
      const { data } = await api.get('admin/feature-names');
      if (data.success) setFeatureNames(data.data || []);
    } catch {
      setFeatureNames([]);
    }
  }, []);

  useEffect(() => {
    fetchFeature();
  }, [fetchFeature]);

  useEffect(() => {
    fetchFeatureNames();
  }, [fetchFeatureNames]);

  const handleSubmit = async (payload) => {
    setSubmitError('');
    setLoading(true);
    try {
      const { data } = await api.put(`admin/features/${id}`, payload);
      if (data.success) {
        showSuccess(t('common.saved'));
        setFeature(data.data);
      }
      else setSubmitError(data.message || t('common.error'));
    } catch (err) {
      setSubmitError(err.response?.data?.message || err.response?.data?.errors?.value?.[0] || t('common.error'));
    } finally {
      setLoading(false);
    }
  };

  if (loadError) {
    return (
      <div className="space-y-4">
        <div className="flex justify-end">
          <Link to="/admin/features" className="btn btn-ghost btn-sm">
            {t('common.back')}
          </Link>
        </div>
        <div className="alert alert-error">{loadError}</div>
      </div>
    );
  }

  if (!feature) {
    return (
      <div className="flex justify-center py-12">
        <span className="loading loading-spinner loading-lg" aria-hidden="true" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <PageTitle className="mb-0">{t('admin.features.edit')} · {feature.feature_name}: {feature.value}</PageTitle>
      </div>
      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <AdminFeatureForm
            feature={feature}
            featureNames={featureNames}
            onSubmit={handleSubmit}
            loading={loading}
            error={submitError}
          />
        </div>
      </div>
    </div>
  );
}
