import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import AdminFaqForm from '../../components/admin/AdminFaqForm';
import { useAdminToast } from '../../contexts/AdminToastContext';

export default function AdminFaqNewPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { showSuccess } = useAdminToast();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async (payload) => {
    setError('');
    setLoading(true);
    try {
      const { data } = await api.post('admin/faqs', payload);
      if (data.success) {
        showSuccess(t('common.saved'));
        navigate('/admin/faqs');
      } else setError(data.message || t('common.error'));
    } catch (err) {
      setError(err.response?.data?.message || t('common.error'));
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="space-y-6">
      <PageTitle className="mb-0">{t('admin.faqs.new')}</PageTitle>
      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <AdminFaqForm onSubmit={handleSubmit} loading={loading} error={error} />
        </div>
      </div>
    </div>
  );
}
