import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import AdminPackForm from '../../components/admin/AdminPackForm';
import { useAdminToast } from '../../contexts/AdminToastContext';

export default function AdminPackNewPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { showSuccess } = useAdminToast();
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const fetchProducts = useCallback(async () => {
    try {
      const { data } = await api.get('admin/products', { params: { per_page: 500 } });
      if (data.success) setProducts(data.data || []);
    } catch {
      setProducts([]);
    }
  }, []);

  useEffect(() => {
    fetchProducts();
  }, [fetchProducts]);

  const handleSubmit = async (payload) => {
    setError('');
    setLoading(true);
    try {
      const { data } = await api.post('admin/packs', payload);
      if (data.success) {
        showSuccess(t('common.saved'));
        navigate('/admin/packs');
      } else setError(data.message || t('common.error'));
    } catch (err) {
      setError(err.response?.data?.message || err.response?.data?.errors?.name?.[0] || t('common.error'));
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <PageTitle className="mb-0">{t('admin.packs.new')}</PageTitle>
        <Link to="/admin/packs" className="btn btn-ghost btn-sm shrink-0">
          {t('common.back')}
        </Link>
      </div>
      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <AdminPackForm products={products} onSubmit={handleSubmit} loading={loading} error={error} />
        </div>
      </div>
    </div>
  );
}
