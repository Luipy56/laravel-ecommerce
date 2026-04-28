import React, { useCallback, useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import AdminFaqForm from '../../components/admin/AdminFaqForm';
import { useAdminToast } from '../../contexts/AdminToastContext';

export default function AdminFaqEditPage() {
  const { t } = useTranslation();
  const { id } = useParams();
  const navigate = useNavigate();
  const { showSuccess } = useAdminToast();
  const [loading, setLoading] = useState(false);
  const [loadingData, setLoadingData] = useState(true);
  const [error, setError] = useState('');
  const [faq, setFaq] = useState(null);

  const fetchFaq = useCallback(async () => {
    setLoadingData(true);
    try {
      const { data } = await api.get(`admin/faqs/${id}`);
      if (data.success) setFaq(data.data);
      else navigate('/admin/faqs');
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      else navigate('/admin/faqs');
    } finally {
      setLoadingData(false);
    }
  }, [id, navigate]);

  useEffect(() => {
    fetchFaq();
  }, [fetchFaq]);

  const handleSubmit = async (payload) => {
    setError('');
    setLoading(true);
    try {
      const { data } = await api.put(`admin/faqs/${id}`, payload);
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

  if (loadingData || !faq) {
    return (
      <div className="flex justify-center py-16">
        <span className="loading loading-spinner loading-lg" aria-hidden="true" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <PageTitle className="mb-0">{t('admin.faqs.edit')}</PageTitle>
      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <AdminFaqForm initial={faq} onSubmit={handleSubmit} loading={loading} error={error} />
        </div>
      </div>
    </div>
  );
}
