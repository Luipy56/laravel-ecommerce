import React, { useCallback, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import AdminFeatureForm from '../../components/admin/AdminFeatureForm';
import { useAdminToast } from '../../contexts/AdminToastContext';

export default function AdminFeatureNewPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { showSuccess } = useAdminToast();
  const [featureNames, setFeatureNames] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const fetchFeatureNames = useCallback(async () => {
    try {
      const { data } = await api.get('admin/feature-names');
      if (data.success) setFeatureNames(data.data || []);
    } catch {
      setFeatureNames([]);
    }
  }, []);

  useEffect(() => {
    fetchFeatureNames();
  }, [fetchFeatureNames]);

  const handleSubmit = async (payload) => {
    setError('');
    setLoading(true);
    try {
      const { data } = await api.post('admin/features', payload);
      if (data.success) {
        showSuccess(t('common.saved'));
        navigate('/admin/features');
      }
      else setError(data.message || t('common.error'));
    } catch (err) {
      setError(err.response?.data?.message || err.response?.data?.errors?.value?.[0] || t('common.error'));
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <PageTitle className="mb-0">{t('admin.features.new')}</PageTitle>
      </div>
      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <AdminFeatureForm
            featureNames={featureNames}
            onSubmit={handleSubmit}
            loading={loading}
            error={error}
          />
        </div>
      </div>
    </div>
  );
}
