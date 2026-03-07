import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import AdminVariantGroupForm from '../../components/admin/AdminVariantGroupForm';
import { useAdminToast } from '../../contexts/AdminToastContext';

export default function AdminVariantGroupEditPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { showSuccess } = useAdminToast();
  const { id } = useParams();
  const [group, setGroup] = useState(null);
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(false);
  const [loaded, setLoaded] = useState(false);
  const [loadError, setLoadError] = useState('');
  const [submitError, setSubmitError] = useState('');

  const fetchGroup = useCallback(async () => {
    if (!id) return;
    try {
      const { data } = await api.get(`admin/variant-groups/${id}`);
      if (data.success) setGroup(data.data);
      else setLoadError(t('common.error'));
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      else setLoadError(err.response?.data?.message || t('common.error'));
    } finally {
      setLoaded(true);
    }
  }, [id, navigate, t]);

  const fetchProducts = useCallback(async () => {
    try {
      const { data } = await api.get('admin/products', { params: { per_page: 500 } });
      if (data.success) setProducts(data.data || []);
    } catch {
      setProducts([]);
    }
  }, []);

  useEffect(() => {
    fetchGroup();
  }, [fetchGroup]);

  useEffect(() => {
    fetchProducts();
  }, [fetchProducts]);

  const handleSubmit = async (payload) => {
    setSubmitError('');
    setLoading(true);
    try {
      const { data } = await api.put(`admin/variant-groups/${id}`, payload);
      if (data.success) {
        showSuccess(t('common.saved'));
        setGroup(data.data);
      } else setSubmitError(data.message || t('common.error'));
    } catch (err) {
      setSubmitError(err.response?.data?.message || t('common.error'));
    } finally {
      setLoading(false);
    }
  };

  if (loadError) {
    return (
      <div className="space-y-4">
        <div className="flex justify-end">
          <Link to="/admin/variant-groups" className="btn btn-ghost btn-sm">
            {t('common.back')}
          </Link>
        </div>
        <div className="alert alert-error">{loadError}</div>
      </div>
    );
  }

  if (!loaded || !group) {
    return (
      <div className="flex justify-center py-12">
        <span className="loading loading-spinner loading-lg" aria-hidden="true" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <PageTitle className="mb-0">{t('admin.variant_groups.edit')}{group.name ? ` · ${group.name}` : ` · #${group.id}`}</PageTitle>
        <Link to="/admin/variant-groups" className="btn btn-ghost btn-sm shrink-0">
          {t('common.back')}
        </Link>
      </div>
      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <AdminVariantGroupForm
            group={group}
            products={products}
            onSubmit={handleSubmit}
            loading={loading}
            error={submitError}
          />
        </div>
      </div>
    </div>
  );
}
